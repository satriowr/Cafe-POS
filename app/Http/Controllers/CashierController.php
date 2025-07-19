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

        $tableNumbers = DB::table('tables')->pluck('table_number')->toArray();

        //dd($tableNumbers);

        $ordersCashless = DB::table('orders')
            ->where('is_paid', 0)
            ->where('payment_method', 'cashless')
            ->where('status', 'Selesai')
            ->get()
            ->groupBy('table_number');

        $ordersCash = DB::table('orders')
            ->where('is_paid', 0)
            ->where('payment_method', 'cash')
            ->where('status', 'Selesai')
            ->get()
            ->groupBy('table_number');

        $tables = [];

        foreach ($ordersCashless as $tableNumber => $orders) {
            foreach ($orders as $order) {
                $orderItems = DB::table('order_items')
                    ->where('order_id', $order->id)
                    ->join('menus', 'order_items.menu_id', '=', 'menus.id')
                    ->select('order_items.*', 'menus.price')
                    ->get();

                $total = $orderItems->sum(fn($item) => $item->price * $item->quantity);

                $tables[] = [
                    'table_number' => $tableNumber,
                    'total' => $total,
                    'order_ids' => [$order->id],
                ];
            }
        }

        foreach ($ordersCash as $tableNumber => $orders) {
            $orderIds = $orders->pluck('id')->toArray();

            $total = 0;
            foreach ($orderIds as $id) {
                $items = DB::table('order_items')
                    ->where('order_id', $id)
                    ->join('menus', 'order_items.menu_id', '=', 'menus.id')
                    ->select('order_items.*', 'menus.price')
                    ->get();

                $total += $items->sum(fn($item) => $item->price * $item->quantity);
            }

            $tables[] = [
                'table_number' => $tableNumber,
                'total' => $total,
                'order_ids' => $orderIds,
            ];
        }

        return view('admin.cashier', compact('tables'));
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

    public function show(Request $request, $table_number)
    {
        $userId = session('user_id');

        $id_order = $request->query('order_ids');

        $role = DB::table('users')->where('id', $userId)->value('role');

        if ($role !== 'admin') {
            return redirect('/login');
        }

        $orders = DB::table('orders')
            ->whereIn('id', explode(',', $id_order))
            ->get();

        $isPaid = $orders->first()?->is_paid ?? 0;

        $availableMenus = DB::table('menus')
            ->where('is_available', 1)
            ->get();
        //dd($availableMenus);

        $orderItems = DB::table('order_items')
            ->join('menus', 'order_items.menu_id', '=', 'menus.id')
            ->whereIn('order_items.order_id', explode(',', $id_order))
            ->select('order_items.*', 'menus.name as menu_name', 'menus.price as menu_price')
            ->get();

        $ordersWithGrandTotal = DB::table('orders')
            ->whereIn('orders.id', explode(',', $id_order)) // Menyebutkan alias untuk orders.id
            ->join('receipts', 'orders.receipt_id', '=', 'receipts.id')
            ->select('orders.id as order_id', 'orders.receipt_id', 'receipts.grand_total') // Alias untuk orders.id
            ->get();

        foreach ($orders as $order) {
            $receipt = $ordersWithGrandTotal->firstWhere('receipt_id', $order->receipt_id);
            $order->grand_total = $receipt->grand_total ?? 0;
        }

        $subtotal = 0;
        foreach ($orderItems as $item) {
            $itemSubtotal = $item->quantity * $item->menu_price;
            $subtotal += $itemSubtotal;
        }
        $subtotalWith15Percent = $subtotal * 1.15;

        //dd($ordersWithGrandTotal);
        //dd($subtotal);

        $difference = ($ordersWithGrandTotal->isEmpty() || $ordersWithGrandTotal[0]->grand_total == 0) ? 0 : $ordersWithGrandTotal[0]->grand_total - $subtotalWith15Percent;

        return view('admin.cashier-detail', [
            'tableNumber' => $table_number,
            'orders' => $orders,
            'isPaid' => $isPaid,
            'availableMenus' => $availableMenus,
            'orderItems' => $orderItems,
            'difference' => $difference,
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


    public function addItem(Request $request, $menu_id)
    {
        $order_id = $request->query('order_ids');

        //dd($menu_id);

        DB::table('order_items')->insert([
            'order_id' => $order_id,
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

    public function searchOrder(Request $request)
    {
        $userId = session('user_id');

        $role = DB::table('users')->where('id', $userId)->value('role');

        if ($role !== 'admin') {
            return redirect('/login');
        }

        $orderIds = $request->query('order_ids');
        
        if (empty($orderIds)) {
            return redirect()->route('admin.cashier.index')->with('error', 'No order IDs provided.');
        }

        $orderIdsArray = explode(',', $orderIds);

        $tableNumbers = DB::table('tables')->pluck('table_number')->toArray();

        $ordersCashless = DB::table('orders')
            ->where('is_paid', 1)
            ->where('payment_method', 'cashless')
            ->where('status', 'Selesai')
            ->whereIn('id', $orderIdsArray)
            ->get()
            ->groupBy('table_number');

        $ordersCash = DB::table('orders')
            ->where('is_paid', 0)
            ->where('payment_method', 'cash')
            ->where('status', 'Selesai')
            ->whereIn('id', $orderIdsArray)
            ->get()
            ->groupBy('table_number');

        $tables = [];

        foreach ($ordersCashless as $tableNumber => $orders) {
            foreach ($orders as $order) {
                $orderItems = DB::table('order_items')
                    ->where('order_id', $order->id)
                    ->join('menus', 'order_items.menu_id', '=', 'menus.id')
                    ->select('order_items.*', 'menus.price')
                    ->get();

                $total = $orderItems->sum(fn($item) => $item->price * $item->quantity);

                $tables[] = [
                    'table_number' => $tableNumber,
                    'total' => $total,
                    'order_ids' => [$order->id],
                ];
            }
        }

        foreach ($ordersCash as $tableNumber => $orders) {
            $orderIds = $orders->pluck('id')->toArray();

            $total = 0;
            foreach ($orderIds as $id) {
                $items = DB::table('order_items')
                    ->where('order_id', $id)
                    ->join('menus', 'order_items.menu_id', '=', 'menus.id')
                    ->select('order_items.*', 'menus.price')
                    ->get();

                $total += $items->sum(fn($item) => $item->price * $item->quantity);
            }

            $tables[] = [
                'table_number' => $tableNumber,
                'total' => $total,
                'order_ids' => $orderIds,
            ];
        }

        //dd($tables);

        return view('admin.cashier', compact('tables'));
    }

    public function updateReceipt(Request $request, $receipt_id)
    {
        //dd($receipt_id);
        $difference = $request->difference;

        DB::table('receipts')
            ->where('id', $receipt_id)
            ->update([
                'total_price' => $request->total_price,
                'tax_amount' => $request->tax_amount,
                'service_charge' => $request->service_charge,
                'grand_total' => $request->grand_total,
                'updated_at' => now()
            ]);

        $orders = Order::with('items.menu')
            ->where('receipt_id', $receipt_id)
            ->get();
        
        $receipt = DB::table('receipts')
            ->where('id', $receipt_id)
            ->first();
        //dd($receipt->invoice_number);
        //dd($difference);

        $pdf = Pdf::loadView('admin.receipt-template', [
            'receipt' => $receipt,
            'orders' => $orders,
            'difference' => $difference,
        ])->setPaper([0, 0, 283.5, 600], 'portrait');

        return $pdf->stream('receipt-'.$receipt->invoice_number.'.pdf');
    }
    public function updateReceiptQRIS(Request $request, $receipt_id)
    {
        //dd($receipt_id);
        $difference = $request->difference;

        DB::table('receipts')
            ->where('id', $receipt_id)
            ->update([
                'total_price' => $request->total_price,
                'tax_amount' => $request->tax_amount,
                'service_charge' => $request->service_charge,
                'grand_total' => $request->grand_total,
                'payment_type' => 'QRIS',
                'updated_at' => now()
            ]);

        $orders = Order::with('items.menu')
            ->where('receipt_id', $receipt_id)
            ->get();
        
        $receipt = DB::table('receipts')
            ->where('id', $receipt_id)
            ->first();

        //dd($receipt->invoice_number);
        //dd($difference);

        $pdf = Pdf::loadView('admin.receipt-template', [
            'receipt' => $receipt,
            'orders' => $orders,
            'difference' => $difference,
        ])->setPaper([0, 0, 283.5, 600], 'portrait');

        return $pdf->stream('receipt-'.$receipt->invoice_number.'.pdf');
    }
    public function updateReceiptCash(Request $request, $receipt_id)
    {
        //dd($request->all());
        $difference = $request->difference;

        DB::table('receipts')
            ->where('id', $receipt_id)
            ->update([
                'total_price' => $request->total_price,
                'tax_amount' => $request->tax_amount,
                'service_charge' => $request->service_charge,
                'grand_total' => $request->grand_total,
                'cash_amount' => $request->cash_amount,
                'change' => $request->change,
                'payment_type' => 'Cash',
                'updated_at' => now()
            ]);

        $orders = Order::with('items.menu')
            ->where('receipt_id', $receipt_id)
            ->get();
        
        $receipt = DB::table('receipts')
            ->where('id', $receipt_id)
            ->first();

        //dd($receipt->invoice_number);
        //dd($difference);

        $pdf = Pdf::loadView('admin.receipt-template', [
            'receipt' => $receipt,
            'orders' => $orders,
            'difference' => $difference,
        ])->setPaper([0, 0, 283.5, 600], 'portrait');

        return $pdf->stream('receipt-'.$receipt->invoice_number.'.pdf');
    }

    
}
