<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\AdminLoginRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminLoginController extends Controller {
    /**
     * 管理者ログイン画面表示
     */
    public function show() {
        return view('auth.admin-login');
    }

    /**
     * 管理者ログイン処理
     */
    public function store(AdminLoginRequest $request) {
        $credentials = $request->only('email', 'password');

        if (!Auth::attempt($credentials)) {
            return back()
                ->withInput($request->only('email'))
                ->withErrors([
                    'login' => 'ログイン情報が登録されていません',
                ]);
        }

        $user = Auth::user();

        if ($user->role !== 'admin') {
            Auth::logout();

            return back()
                ->withInput($request->only('email'))
                ->withErrors([
                    'login' => 'ログイン情報が登録されていません',
                ]);
        }

        $request->session()->regenerate();

        return redirect()->route('admin.attendance.list');
    }

    /**
     * 管理者ログアウト処理
     */
    public function destroy(Request $request) {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }

}
