<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Crypt;
use Midtrans\Config;
use Midtrans\Snap;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;


class OrderController extends Controller
{
    public function create(Request $request)
    {
        $token = $request->query('token');  

        if (!$token) {
            return abort(404, 'Token tidak ditemukan');
        }   

        try {
            $decryptedData = Crypt::decryptString($token);
            list($table_number, $customer_identity, $orderType) = explode('|', $decryptedData);
        } catch (\Exception $e) {
            abort(403, 'Token tidak valid');
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

    public function createCashless(Request $request)
    {
        $token = $request->query('token');

        if (!$token) {
            return abort(404, 'Token tidak ditemukan');
        }

        try {
            $decryptedData = Crypt::decryptString($token);
            list($table_number, $customer_identity, $orderType) = explode('|', $decryptedData);
        } catch (\Exception $e) {
            abort(403, 'Token tidak valid');
        }

        Config::$serverKey = env('MIDTRANS_SERVER_KEY');
        Config::$isProduction = false;
        Config::$isSanitized = true;
        Config::$is3ds = true;

        $cartItems = DB::table('carts')
            ->join('menus', 'carts.menu_id', '=', 'menus.id')
            ->where('carts.table_number', $table_number)
            ->select('carts.*', 'menus.name as menu_name', 'menus.price')
            ->get();

        if ($cartItems->isEmpty()) {
            return back()->with('error', 'Keranjang masih kosong!');
        }

        $latestOrderToday = DB::table('orders')
            ->whereDate('created_at', today())
            ->orderByDesc('queue_number')
            ->first();

        $queue_number = $latestOrderToday ? $latestOrderToday->queue_number + 1 : 1;

        $itemDetails = [];
        $grossAmount = 0;

        foreach ($cartItems as $item) {
            $itemDetails[] = [
                'id'       => $item->menu_id,
                'price'    => $item->price,
                'quantity' => $item->quantity,
                'name'     => $item->menu_name
            ];
            $grossAmount += $item->price * $item->quantity;
        }

        $tax = (int) round($grossAmount * 0.10);
        $service = (int) round($grossAmount * 0.01);

        if ($tax > 0) {
            $itemDetails[] = [
                'id' => 'TAX10',
                'price' => $tax,
                'quantity' => 1,
                'name' => 'Pajak 10%'
            ];
        }

        if ($service > 0) {
            $itemDetails[] = [
                'id' => 'SERVICE1',
                'price' => $service,
                'quantity' => 1,
                'name' => 'Service Charge 1%'
            ];
        }

        $grossAmount += $tax + $service;

        $paymentToken = 'NALA-' . $table_number . '-' . now()->format('YmdHis') . '-' . Str::random(5);

        $params = [
            'transaction_details' => [
                'order_id' => $paymentToken,
                'gross_amount' => $grossAmount
            ],
            'item_details' => $itemDetails,
            'customer_details' => [
                'email' => $customer_identity,
            ],
            'expiry' => [
                'start_time' => now()->format('Y-m-d H:i:s O'),
                'unit' => 'minute',
                'duration' => 15
            ]
        ];

        $snapToken = Snap::getSnapToken($params);
        //dd($snapToken);;
        Log::info('SnapToken created: ' . $snapToken);

        $order_id = DB::table('orders')->insertGetId([
            'table_number'    => $table_number,
            'payment_method'  => 'cashless',
            'payment_token'   => $paymentToken,
            'queue_number'    => $queue_number,
            'status'          => 'Menunggu',
            'is_paid'         => false,
            'expires_at'      => now()->addMinutes(15),
            'created_at'      => now(),
            'updated_at'      => now(),
        ]);

        foreach ($cartItems as $item) {
            DB::table('order_items')->insert([
                'order_id'    => $order_id,
                'menu_id'     => $item->menu_id,
                'quantity'    => $item->quantity,
                'note'        => $item->note,
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);
        }

        return view('user.payment', compact('snapToken', 'token'));
    }


}
