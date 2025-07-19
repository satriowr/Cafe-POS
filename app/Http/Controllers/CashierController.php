<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Table;
use App\Models\Receipt;
use App\Models\Menu;
use App\Models\OrderItem;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\PDF;



class CashierController extends Controller
{
    public function index()
    {
        $userId = session('user_id');
    
        $role = DB::table('users')->where('id', $userId)->value('role');
    
        if ($role !== 'admin') {
            return redirect('/login');
        }

        return view('admin.cashier');
    }

    public function getData()
    {
        $userId = session('user_id');

        $role = DB::table('users')->where('id', $userId)->value('role');

        if ($role !== 'admin') {
            return redirect('/login');
        }

        $tableNumbers = DB::table('tables')->pluck('table_number')->toArray();

        $ordersCashless = Order::whereIn('table_number', $tableNumbers)
            ->where('is_paid', 1)
            ->where('payment_method', 'cashless')
            ->with(['items.menu'])
            ->get()
            ->groupBy('table_number');

        $ordersCash = Order::whereIn('table_number', $tableNumbers)
            ->where('is_paid', 0)
            ->where('payment_method', 'cash')
            ->with(['items.menu'])
            ->get()
            ->groupBy('table_number');

        $tables = [];

        foreach ($ordersCashless as $table_number => $orderGroup) {
            $total = 0;
            $orderIds = [];

            foreach ($orderGroup as $order) {
                $orderIds[] = $order->id;
                foreach ($order->items as $item) {
                    $total += $item->quantity * $item->menu->price;
                }
            }

            $tables[] = [
                'table_number' => $table_number,
                'total' => $total,
                'order_ids' => $orderIds,
                'payment_method' => 'cashless',
            ];
        }

        foreach ($ordersCash as $table_number => $orderGroup) {
            if ($orderGroup->count() > 1 && $orderGroup->every(fn($order) => $order->status === 'Selesai')) {
                $total = 0;
                $orderIds = [];

                foreach ($orderGroup as $order) {
                    $orderIds[] = $order->id;
                    foreach ($order->items as $item) {
                        $total += $item->quantity * $item->menu->price;
                    }
                }

                $tables[] = [
                    'table_number' => $table_number,
                    'total' => $total,
                    'order_ids' => $orderIds,
                    'payment_method' => 'cash',
                ];
            }
        }

        return response()->json($tables);
    }

    public function show($table_number)
    {
        $userId = session('user_id');

        $role = DB::table('users')->where('id', $userId)->value('role');

        if ($role !== 'admin') {
            return redirect('/login');
        }

        $orders = Order::where('table_number', $table_number)
            ->where('is_paid', 0)
            ->with('items.menu')
            ->get();

        $isPaid = $orders->first()?->is_paid ?? 0;

        $availableMenus = Menu::where('is_available', 1)->get();

        return view('admin.cashier-detail', [
            'tableNumber' => $table_number,
            'orders' => $orders,
            'isPaid' => $isPaid,
            'availableMenus' => $availableMenus
        ]);
    }

    public function payBill(Request $request, $table_number)
    {
        $userId = session('user_id');
        $role = DB::table('users')->where('id', $userId)->value('role');
        $name = DB::table('users')->where('id', $userId)->value('name');
    
        if ($role !== 'admin') {
            return redirect('/login');
        }
    
        $orders = Order::where('table_number', $table_number)
            ->where('is_paid', 0)
            ->get();
    
        $totalPrice = $orders->sum(function($order) {
            return $order->items->sum(function($item) {
                return $item->menu->price * $item->quantity;
            });
        });
    
        $paymentType = $request->input('payment_type'); // bisa berupa 'cash' atau 'qris'
        $cashAmount = $paymentType === 'cash' ? $request->input('cash_amount') : null;
        $change = $paymentType === 'cash' ? $request->input('change') : null;
    
        $receipt = Receipt::create([
            'invoice_number' => 'INV-' . now()->format('YmdHis'),
            'table_number' => $table_number,
            'total_price' => $totalPrice,
            'tax_amount' => $totalPrice * 0.1,
            'service_charge' => $totalPrice * 0.05,
            'grand_total' => $totalPrice * 1.15,
            'cashier_name' => $name,
            'paid_at' => now('Asia/Jakarta'),
            'payment_type' => $paymentType,
            'cash_amount' => $cashAmount,
            'change' => $change, 
        ]);
    
        foreach ($orders as $order) {
            $order->update([
                'is_paid' => 1,
                'receipt_id' => $receipt->id,
            ]);
        }
    
        Table::where('table_number', $table_number)->delete();
    
        return redirect()->route('admin.receipt.show', ['receipt' => $receipt->id])
            ->with('success', 'Pembayaran berhasil diproses.');
    }

    public function showReceipt(Receipt $receipt)
    {
        $orders = Order::with('items.menu')
            ->where('receipt_id', $receipt->id)
            ->get();

        $pdf = Pdf::loadView('admin.receipt-template', [
            'receipt' => $receipt,
            'orders' => $orders,
        ])->setPaper([0, 0, 283.5, 600], 'portrait');

        return $pdf->stream('receipt-'.$receipt->invoice_number.'.pdf');
    }


    public function addItem(Request $request, $table_number, $menu_id)
    {
        $orderIds = $request->query('order_ids');
        $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        $order = DB::table('orders')
            ->where('table_number', $table_number)
            ->where('is_paid', false)
            ->where('status', 'Menunggu')
            ->first();

        DB::table('order_items')->insert([
            'order_id' => $orderIds,
            'menu_id' => $menu_id, 
            'quantity' => $request->quantity,
            'created_at' => now(),        
            'updated_at' => now(),     
        ]);

        return redirect()->back()->with('success', 'Item berhasil ditambahkan!');
    }

    public function updateItem(Request $request, $order_id, $item_id)
    {
        // Validasi input
        $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        // Cari order dan item yang dimaksud
        $orderItem = OrderItem::where('order_id', $order_id)
                            ->where('id', $item_id)
                            ->first();

        if (!$orderItem) {
            return redirect()->back()->with('error', 'Item tidak ditemukan');
        }

        // Update jumlah item
        $orderItem->quantity = $request->quantity;
        $orderItem->save();

        return redirect()->back()->with('success', 'Item berhasil diperbarui!');
    }

    public function removeItem($order_id, $item_id)
    {
        // Cari item yang ingin dihapus
        $orderItem = OrderItem::where('order_id', $order_id)
                            ->where('id', $item_id)
                            ->first();

        if (!$orderItem) {
            return redirect()->back()->with('error', 'Item tidak ditemukan');
        }

        $orderItem->delete();

        return redirect()->back()->with('success', 'Item berhasil dihapus!');
    }

    public function inquiry_index()
    {
        $userId = session('user_id');

        $role = DB::table('users')->where('id', $userId)->value('role');

        if ($role !== 'admin') {
            return redirect('/login');
        }

        return view('admin.cashier-inquiry');
    }

    public function inquiry(Request $request)
    {
        $id = $request->input('id');

        $order = Order::with('items.menu')
            ->where('id', $id)
            ->where('is_paid', 1)
            ->first();

        //dd($order);

        if (!$order) {
            return redirect('/admin/inquiry');
        }

        $receipt = Receipt::where('id', $order->receipt_id)->first();
        
        return view('admin.cashier-inquiry-result', [
            'order' => $order,
            'receipt' => $receipt
        ]);
    }


    
    
}
