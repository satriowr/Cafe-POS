<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Crypt;

class menuController extends Controller
{
    public function showMenu(Request $request)
    {
        $token = $request->query('token');
        $category = $request->query('category');
    
        if (!$token) {
            abort(404, 'Token tidak ditemukan');
        }
    
        try {
            $decryptedData = Crypt::decryptString($token);
            
            list($table_number, $customer_identity) = explode('|', $decryptedData);
        } catch (\Exception $e) {
            abort(403, 'Token tidak valid');
        }

        //dd($decryptedData);
    
        $table = DB::table('tables')
            ->where('table_number', $table_number)
            ->where('qr_token', $token)
            ->where('is_active', 1)
            ->first();
    
        if (!$table) {
            abort(403, 'QR ini sudah tidak aktif');
        }
    
        $query = DB::table('menus')->where('is_available', 1);
        if ($category) {
            $query->where('category', $category);
        }
        $menus = $query->get();
    
        $categories = DB::table('menus')
            ->where('is_available', 1)
            ->select('category')
            ->distinct()
            ->pluck('category');
    
        return view('user.menu', [
            'token' => $token,
            'menus' => $menus,
            'categories' => $categories,
            'table_number' => $table_number,
            'selected_category' => $category, 
        ]);
    }
    
}
