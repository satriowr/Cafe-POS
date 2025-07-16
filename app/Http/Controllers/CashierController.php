<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Table;
use App\Models\Receipt;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;


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

        $orders = Order::where('is_paid', false)
            ->with(['items.menu'])
            ->get()
            ->groupBy('table_number');

        $tables = [];

        foreach ($orders as $table_number => $orderGroup) {
            if ($orderGroup->every(fn($order) => $order->status === 'Selesai')) {
                $total = 0;
                foreach ($orderGroup as $order) {
                    foreach ($order->items as $item) {
                        $total += $item->quantity * $item->menu->price;
                    }
                }

                $tables[] = [
                    'table_number' => $table_number,
                    'total' => $total,
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
            ->where('status', 'Selesai')
            ->with('items.menu')
            ->get();
    
            $isPaid = $orders->first()?->is_paid ?? 0;

            return view('admin.cashier-detail', [
                'tableNumber' => $table_number,
                'orders' => $orders,
                'isPaid' => $isPaid
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
    
        // Tangani pembayaran tunai dan QRIS
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
            'payment_type' => $paymentType, // Simpan payment_type
            'cash_amount' => $cashAmount,  // Simpan cash_amount jika cash
            'change' => $change,           // Simpan change jika cash
        ]);
    
        foreach ($orders as $order) {
            $order->update([
                'is_paid' => 1,
                'receipt_id' => $receipt->id,
            ]);
        }
    
        Table::where('table_number', $table_number)->update(['is_active' => 0]);
    
        return redirect()->route('admin.cashier.show', ['table_number' => $table_number])
            ->with('success', 'Pembayaran berhasil diproses.')
            ->with('receipt_id', $receipt->id);
    }
    
    
}
