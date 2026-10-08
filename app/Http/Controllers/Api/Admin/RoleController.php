<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    public function index()
    {
        $roles = Role::withCount('users')->latest()->get();

        return response()->json([
            'data' => $roles,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:roles'],
        ]);

        $role = Role::create($validated);

        return response()->json([
            'message' => 'Role berhasil dibuat.',
            'data' => $role,
        ], 201);
    }

    public function show(Role $role)
    {
        $role->loadCount('users');

        return response()->json([
            'data' => $role,
        ]);
    }

    public function update(Request $request, Role $role)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:roles,name,'.$role->id],
        ]);

        $role->update($validated);

        return response()->json([
            'message' => 'Role berhasil diperbarui.',
            'data' => $role,
        ]);
    }

    public function destroy(Role $role)
    {
        if ($role->name === 'admin') {
            return response()->json([
                'message' => 'Role admin tidak dapat dihapus.',
            ], 422);
        }

        $role->delete();

        return response()->json([
            'message' => 'Role berhasil dihapus.',
        ]);
    }
}
