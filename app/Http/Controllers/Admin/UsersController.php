<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class UsersController extends Controller
{

    public function index(Request $request)
    {
        $query = DB::table('users')
            ->whereNull('deleted_at')
            ->orderBy('created_at', 'desc');

        // Search
        if ($request->has('search') && $request->search) {
            $search = '%' . $request->search . '%';
            $query->where(function($q) use ($search) {
                $q->where('first_name', 'like', $search)
                  ->orWhere('last_name', 'like', $search)
                  ->orWhere('email', 'like', $search)
                  ->orWhere('username', 'like', $search);
            });
        }

        // Filter by role
        if ($request->has('role') && $request->role) {
            $query->where('role', $request->role);
        }

        // Filter by status
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        $users = $query->paginate(20);

        return view('admin.users.index', compact('users'));
    }

    public function show($id)
    {
        $user = DB::table('users')
            ->where('id', $id)
            ->whereNull('deleted_at')
            ->first();

        if (!$user) {
            return redirect()->route('admin.users.index')
                ->with('error', 'User not found!');
        }

        // Get user's stores if manager
        $stores = collect();
        if ($user->role === 'manager') {
            $stores = DB::table('stores')
                ->where('owner_id', $id)
                ->whereNull('deleted_at')
                ->get();
        }

        // Get user's blogs if author
        $blogs = collect();
        if ($user->role === 'author') {
            $blogs = DB::table('blogs')
                ->where('author_id', $id)
                ->whereNull('deleted_at')
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get();
        }

        return view('admin.users.show', compact('user', 'stores', 'blogs'));
    }

    public function create()
    {
        return view('admin.users.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'first_name' => 'required|string|max:50',
            'last_name' => 'required|string|max:50',
            'username' => 'required|string|max:50|unique:users,username',
            'email' => 'required|string|email|max:100|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
            'role' => 'required|in:admin,manager,customer,author',
            'status' => 'required|in:active,inactive,suspended',
            'phone' => 'nullable|string|max:20',
            'date_of_birth' => 'nullable|date',
            'gender' => 'nullable|in:male,female,other',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $data = [
            'id' => Str::uuid(),
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'status' => $request->status,
            'phone' => $request->phone,
            'date_of_birth' => $request->date_of_birth,
            'gender' => $request->gender,
            'created_at' => now(),
            'updated_at' => now(),
        ];

        // Handle avatar upload
        if ($request->hasFile('avatar')) {
            $avatarPath = $request->file('avatar')->store('avatars', 'public');
            $data['avatar'] = $avatarPath;
        }

        DB::table('users')->insert($data);

        return redirect()->route('admin.users.index')
            ->with('success', 'User created successfully!');
    }

    public function edit($id)
    {
        $user = DB::table('users')
            ->where('id', $id)
            ->whereNull('deleted_at')
            ->first();

        if (!$user) {
            return redirect()->route('admin.users.index')
                ->with('error', 'User not found!');
        }

        return view('admin.users.edit', compact('user'));
    }

    public function update(Request $request, $id)
    {
        $user = DB::table('users')
            ->where('id', $id)
            ->whereNull('deleted_at')
            ->first();

        if (!$user) {
            return redirect()->route('admin.users.index')
                ->with('error', 'User not found!');
        }

        $request->validate([
            'first_name' => 'required|string|max:50',
            'last_name' => 'required|string|max:50',
            'username' => 'required|string|max:50|unique:users,username,' . $id . ',id',
            'email' => 'required|string|email|max:100|unique:users,email,' . $id . ',id',
            'password' => 'nullable|string|min:6|confirmed',
            'role' => 'required|in:admin,manager,customer,author',
            'status' => 'required|in:active,inactive,suspended',
            'phone' => 'nullable|string|max:20',
            'date_of_birth' => 'nullable|date',
            'gender' => 'nullable|in:male,female,other',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $data = [
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'username' => $request->username,
            'email' => $request->email,
            'role' => $request->role,
            'status' => $request->status,
            'phone' => $request->phone,
            'date_of_birth' => $request->date_of_birth,
            'gender' => $request->gender,
            'updated_at' => now(),
        ];

        // Update password if provided
        if ($request->password) {
            $data['password'] = Hash::make($request->password);
        }

        // Handle avatar upload
        if ($request->hasFile('avatar')) {
            // Delete old avatar
            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }
            
            $avatarPath = $request->file('avatar')->store('avatars', 'public');
            $data['avatar'] = $avatarPath;
        }

        DB::table('users')
            ->where('id', $id)
            ->update($data);

        return redirect()->route('admin.users.index')
            ->with('success', 'User updated successfully!');
    }

    public function destroy($id)
    {
        $user = DB::table('users')
            ->where('id', $id)
            ->whereNull('deleted_at')
            ->first();

        if (!$user) {
            return redirect()->route('admin.users.index')
                ->with('error', 'User not found!');
        }

        // Soft delete
        DB::table('users')
            ->where('id', $id)
            ->update([
                'deleted_at' => now(),
                'updated_at' => now()
            ]);

        return redirect()->route('admin.users.index')
            ->with('success', 'User deleted successfully!');
    }

    public function restore($id)
    {
        $user = DB::table('users')
            ->where('id', $id)
            ->whereNotNull('deleted_at')
            ->first();

        if (!$user) {
            return redirect()->route('admin.users.index')
                ->with('error', 'User not found!');
        }

        DB::table('users')
            ->where('id', $id)
            ->update([
                'deleted_at' => null,
                'updated_at' => now()
            ]);

        return redirect()->route('admin.users.index')
            ->with('success', 'User restored successfully!');
    }

    public function forceDelete($id)
    {
        $user = DB::table('users')
            ->where('id', $id)
            ->first();

        if (!$user) {
            return redirect()->route('admin.users.index')
                ->with('error', 'User not found!');
        }

        // Delete avatar file
        if ($user->avatar) {
            Storage::disk('public')->delete($user->avatar);
        }

        // Permanently delete
        DB::table('users')->where('id', $id)->delete();

        return redirect()->route('admin.users.index')
            ->with('success', 'User permanently deleted!');
    }
}