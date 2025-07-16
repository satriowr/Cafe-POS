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
    
        if (!$token) {
            return abort(404, 'Token tidak ditemukan');
        }
    
        try {
            $table_number = Crypt::decrypt($token);
        } catch (\Exception $e) {
            return abort(403, 'Token tidak valid');
        }
    
        $table = \App\Models\Table::where('table_number', $table_number)
            ->where('is_active', 1)
            ->orderByDesc('id')
            ->first();
    
        if (!$table) {
            return abort(403, 'Pesanan untuk meja ini sudah tidak aktif');
        }
    
        $orders = Order::with(['items.menu'])
            ->where('table_number', $table_number)
            ->where('created_at', '>=', $table->created_at)
            ->latest()
            ->get();
    
        $maxQueue = Order::max('queue_number');
    
        return view('user.status', [
            'orders' => $orders,
            'token' => $token,
            'maxQueue' => $maxQueue,
        ]);
    }
}
