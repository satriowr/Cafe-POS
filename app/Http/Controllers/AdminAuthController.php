<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;


class AdminAuthController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
            'role' => 'required',
            'name' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed.',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::create([
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'name' => $request->name,
        ]);

        return response()->json([
            'message' => 'User registered successfully.',
            'user' => $user
        ], 201);
    }

    public function getLoginAdmin(){
        return view('admin.login');
    }

    public function login(Request $request)
    {
       $credentials = $request->only('email', 'password');
    
       if (auth()->attempt($credentials)) {
           $request->session()->regenerate();
           session(['user_id' => auth()->id()]);
    
           $role = DB::table('users')->where('id', auth()->id())->value('role');
    
           if ($role === 'kitchen') {
               return redirect()->intended('/kitchen');
           } elseif ($role === 'admin') {
               return redirect()->intended('/admin/cashier');
           }
       }
    
       return back()->with('error', 'Email atau password salah')->withInput();
    }
}
