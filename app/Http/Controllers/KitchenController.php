<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use Illuminate\Support\Facades\DB;


class KitchenController extends Controller
{
    public function dashboard()
    {
        $userId = session('user_id');
    
        $role = DB::table('users')->where('id', $userId)->value('role');
    
        if ($role !== 'kitchen') {
            return redirect('/login');
        }
        return view('kitchen.dashboard');
    }
    
    public function fetchOrders()
    {
        $orders = Order::with(['items.menu'])
            ->orderBy('queue_number')
            ->whereIn('status', ['Menunggu', 'Selesai']) // atau 'processing'
            ->get();
    
        return response()->json($orders);
    }
    
    public function updateStatus(Request $request)
    {
        $userId = session('user_id');
    
        $role = DB::table('users')->where('id', $userId)->value('role');
    
        if ($role !== 'kitchen') {
            return redirect('/login');
        }
        
        $request->validate([
            'order_id' => 'required|exists:orders,id',
            'status' => 'required|in:Menunggu,Selesai',
        ]);
    
        Order::where('id', $request->order_id)->update(['status' => $request->status]);
    
        return response()->json(['success' => true]);
    }
}
