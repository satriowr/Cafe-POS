<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Order;
use App\Models\Table;
use App\Models\Receipt;
use Illuminate\Support\Facades\Mail;
use App\Mail\ReceiptEmail;
use Illuminate\Support\Facades\Log;


class PaymentController extends Controller
{
    public function handleSuccess(Request $request)
    {
        $paymentToken = $request->input('order_id');
        $token = $request->query('token');

        $order = DB::table('orders')->where('payment_token', $paymentToken)->first();

        if ($order) {
            DB::table('orders')
                ->where('id', $order->id)
                ->update(['is_paid' => true]);

            DB::table('carts')
                ->where('table_number', $order->table_number)
                ->delete();

            $orders = Order::with(['items.menu'])
                ->where('table_number', $order->table_number)
                ->where('is_paid', true)
                ->whereNull('receipt_id')
                ->get();

            $totalPrice = $orders->sum(function ($order) {
                return $order->items->sum(function ($item) {
                    return $item->menu->price * $item->quantity;
                });
            });

            $receipt = Receipt::create([
                'invoice_number'  => 'INV-' . now()->format('YmdHis'),
                'table_number'    => $order->table_number,
                'total_price'     => $totalPrice,
                'tax_amount'      => $totalPrice * 0.1,
                'service_charge'  => $totalPrice * 0.05,
                'grand_total'     => $totalPrice * 1.15,
                'cashier_name'    => "System NALA",
                'paid_at'         => now('Asia/Jakarta'),
                'payment_type'    => "Cashless",
                'payment_token'   => $paymentToken,
            ]);

            foreach ($orders as $ord) {
                $ord->update([
                    'receipt_id' => $receipt->id,
                ]);
            }
        }

        return redirect("/status?token={$token}");
    }

    public function updatePayment(Request $request)
    {
        $token = $request->query('token');
        
        $decodedToken = base64_decode(strtr($token, '-_', '+/'));
        [$table_number, $customer_email, $orderType] = explode('|', $decodedToken);
        $receipt = Receipt::latest()->first();
        $orders = Order::with('items.menu')->where('receipt_id', $receipt->id)->get();

        Mail::to($customer_email)->send(new ReceiptEmail($receipt, $orders));

        return redirect()->route('user.status', ['token' => $token])->with('error', 'Pesanan tidak ditemukan.');
    }


}
