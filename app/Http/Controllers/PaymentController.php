<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class PaymentController extends Controller
{
    public function handleSuccess(Request $request)
    {
        $paymentToken = $request->input('order_id');

        $order = DB::table('orders')
            ->where('payment_token', $paymentToken)
            ->first();

        if ($order) {
            DB::table('orders')
                ->where('id', $order->id)
                ->update(['is_paid' => true]);

            DB::table('carts')
                ->where('table_number', $order->table_number)
                ->delete();

            return response()->json(['message' => 'Pembayaran sukses, cart dihapus']);
        }

        return response()->json(['message' => 'Order tidak ditemukan'], 404);
    }
}
