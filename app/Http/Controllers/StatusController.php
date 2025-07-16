<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use Illuminate\Support\Facades\Crypt;

class StatusController extends Controller
{
    public function index(Request $request)
    {
        $token = $request->query('token');

        if (!$token) return abort(404, 'Token tidak ditemukan');

        try {
            $decryptedData = Crypt::decryptString($token);
            list($table_number, $customer_identity, $orderType) = explode('|', $decryptedData);
        } catch (\Exception $e) {
            abort(403, 'Token tidak valid');
        }

        $table = \App\Models\Table::where('table_number', $table_number)
            ->where('is_active', 1)
            ->orderByDesc('id')
            ->first();

        if (!$table) abort(403, 'Pesanan untuk meja ini sudah tidak aktif');

        $orders = Order::with(['items.menu'])
            ->where('table_number', $table_number)
            ->where('created_at', '>=', $table->created_at)
            ->latest()
            ->get();

        if ($request->ajax()) {
            return view('user.status-content', [
                'orders' => $orders
            ])->render();
        }

        return view('user.status', [
            'orders' => $orders,
            'token' => $token,
        ]);
    }
}
