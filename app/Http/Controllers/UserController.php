<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class UserController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        
        if ($user->role === 'super-admin') {
            $users = User::whereIn('role', ['admin', 'operator'])->latest()->get();
        } elseif ($user->role === 'admin') {
            $users = User::where('created_by', $user->id)->where('role', 'operator')->latest()->get();
        } else {
            abort(403);
        }

        return view('users.index', compact('users'));
    }

    public function store(Request $request)
    {
        $currentUser = auth()->user();
        
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'role' => ['required', 'string', 'in:admin,operator'],
            'max_parkings' => ['nullable', 'integer', 'min:1'],
        ]);

        // Security checks
        if ($currentUser->role === 'admin' && $request->role !== 'operator') {
            return back()->with('error', 'Un administrador solo puede crear operadores.');
        }

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'created_by' => $currentUser->id,
            'max_parkings' => $request->max_parkings,
        ]);

        return redirect()->route('users.index')->with('success', 'Usuario creado exitosamente.');
    }

    public function update(Request $request, User $user)
    {
        $currentUser = auth()->user();
        
        // Only allow editing users they created or if they are super-admin
        if ($currentUser->role !== 'super-admin' && $user->created_by !== $currentUser->id) {
            abort(403);
        }

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email,'.$user->id],
            'password' => ['nullable', 'confirmed', Rules\Password::defaults()],
            'max_parkings' => ['nullable', 'integer', 'min:1'],
        ]);

        $data = [
            'name' => $request->name,
            'email' => $request->email,
        ];

        if ($request->password) {
            $data['password'] = Hash::make($request->password);
        }

        if ($currentUser->role === 'super-admin') {
            $data['max_parkings'] = $request->max_parkings;
        }

        $user->update($data);

        return redirect()->route('users.index')->with('success', 'Usuario actualizado correctamente.');
    }

    public function destroy(User $user)
    {
        $currentUser = auth()->user();
        
        // Only allow deleting users they created or if they are super-admin
        if ($currentUser->role !== 'super-admin' && $user->created_by !== $currentUser->id) {
            abort(403);
        }

        $user->delete();
        return redirect()->route('users.index')->with('success', 'Usuario eliminado.');
    }
}
