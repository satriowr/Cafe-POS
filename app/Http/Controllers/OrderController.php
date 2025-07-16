<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Crypt;

class OrderController extends Controller
{
    public function create(Request $request)
    {
        $token = $request->query('token');  

        if (!$token) {
            return abort(404, 'Token tidak ditemukan');
        }   

        try {
            $table_number = Crypt::decrypt($token);
        } catch (\Exception $e) {
            return abort(403, 'Token tidak valid');
        }   

        $cartItems = DB::table('carts')
            ->where('table_number', $table_number)
            ->get();    

        if ($cartItems->isEmpty()) {
            return back()->with('error', 'Keranjang masih kosong!');
        }   

        $latestOrderToday = DB::table('orders')
            ->whereDate('created_at', today())
            ->orderByDesc('queue_number')
            ->first();  

        $queue_number = $latestOrderToday ? $latestOrderToday->queue_number + 1 : 1;    

        $order_id = DB::table('orders')->insertGetId([
            'table_number' => $table_number,
            'queue_number' => $queue_number,
            'status' => 'Menunggu',
            'created_at' => now(),
            'updated_at' => now(),
        ]); 

        foreach ($cartItems as $item) {
            DB::table('order_items')->insert([
                'order_id' => $order_id,
                'menu_id' => $item->menu_id,
                'quantity' => $item->quantity,
                'note' => $item->note,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }   

        DB::table('carts')->where('table_number', $table_number)->delete(); 

        return redirect()->route('user.status', ['token' => $token])->with('success', 'Pesanan berhasil dibuat!');
    }
}
