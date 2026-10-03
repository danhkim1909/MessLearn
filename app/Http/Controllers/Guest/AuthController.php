<?php

namespace App\Http\Controllers\Guest;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\Auth\LoginRequest;
use App\Http\Requests\User\Auth\RegisterRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('guest.pages.auth.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function login(LoginRequest $request)
    {
        $validated = $request->validated();
        $credentials = Auth::attempt($request->only('email', 'password'));
        if($credentials) {
            if (!Auth::user()->is_active) {
                Auth::logout();
                return redirect()->back()->with('error', 'Tài khoản của bạn đã bị khóa');
            }
            $request->session()->regenerate();
        } else {
            return redirect()->back()->with('error', 'Email hoặc mật khẩu không chính xác');
        }
        return redirect()->route('app.chat-board')->with('success', 'Đăng nhập thành công');
    }

    public function register(RegisterRequest $request)
    {
        $validated = $request->validated();
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => 'user',
            'is_active' => true,
        ]);
        Auth::login($user);

        return redirect()->route('app.chat-board')->with('success', 'Đăng ký thành công');
    }

    public function logout(){
        Auth::logout();
        return redirect()->route('home')->with('success', 'Đăng xuất thành công');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
