<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string', 'min:6'],
        ]);

        if (Auth::attempt([
            'email' => $credentials['email'],
            'password' => $credentials['password'],
            'status' => 'active'
        ])) {
            $request->session()->regenerate();
            $user = Auth::user();

            session(['admin_user_id' => $user->id]);
            session(['admin_user' => $user]);

            // update last_login_at pakai UUID
            DB::table('users')
                ->where('id', '=', (string) $user->id)
                ->update([
                    'last_login_at' => now(),
                    'updated_at' => now(),
                ]);

            return redirect()->intended(route('admin.dashboard'))
                ->with('success', 'Login berhasil!');
        }

        throw ValidationException::withMessages([
            'email' => ['Email atau password salah, atau akun tidak aktif.'],
        ]);
    }


    public function showRegisterForm()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => 'admin',
            'status' => 'active'
        ]);

        Auth::login($user);

        return redirect()->route('admin.dashboard')
            ->with('success', 'Registrasi berhasil!');
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login')
            ->with('success', 'Logout berhasil!');
    }
    
    public function dashboard()
    {
        $adminUser = session('admin_user');
        
        if (!$adminUser) {
            return redirect()->route('admin.login');
        }

        // Dashboard statistics
        $stats = [
            'users_count' => DB::table('users')->whereNull('deleted_at')->count(),
            'stores_count' => DB::table('stores')->whereNull('deleted_at')->count(),
            'products_count' => DB::table('products')->whereNull('deleted_at')->count(),
            'blogs_count' => DB::table('blogs')->whereNull('deleted_at')->count(),
            'brands_count' => DB::table('brands')->whereNull('deleted_at')->count(),
            'categories_count' => DB::table('product_categories')->whereNull('deleted_at')->count(),
        ];

        // Recent activities
        $recentProducts = DB::table('products')
            ->select('id', 'name', 'status', 'created_at')
            ->whereNull('deleted_at')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        $recentBlogs = DB::table('blogs')
            ->select('id', 'title', 'status', 'created_at')
            ->whereNull('deleted_at')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return view('admin.dashboard', compact('stats', 'recentProducts', 'recentBlogs'));
    }
}