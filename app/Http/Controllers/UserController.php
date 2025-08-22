<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function store(Request $request){
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'role' => 'required|string|in:admin,employee',
        ]);

        $temporaryPassword = Str::random(10); 

        $user = new User();
        $user->name = $request->name;
        $user->email = $request->email;
        $user->role = $request->role;
        $user->password = Hash::make($temporaryPassword);
        $user->must_change_password = true;
        $user->password_expires_at = now()->addDays(30);
        $user->save();

        return response()->json([
            'result' => true,
            'message' => 'User created successfully. Temporary password: ' . $temporaryPassword,
        ], 201);
    }

    public function list(Request $request)
    {
        try{
            $search = $request->input('search');
            $users = User::query()
                ->when($search, function ($query) use ($search) {
                    return $query->where('name', 'like', "%{$search}%")
                                 ->orWhere('email', 'like', "%{$search}%");
                })
                ->orderByDesc('id')
                ->paginate(10);

            $data = $users->getCollection()->map(function ($user) {
                return [
                    'id' => encrypt($user->id),
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                ];
            });

            return response()->json([
                "result" => true,
                "data" => $data,
                "message" => 'Users retrieved successfully.',
                'pagination' => [
                    'current_page' => $users->currentPage(),
                    'last_page' => $users->lastPage(),
                    'per_page' => $users->perPage(),
                    'total' => $users->total(),
                ],
            ]);
        }catch (\Exception $e) {
            return response()->json([
                'result' => false,
                'message' => 'An error occurred while retrieving users: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function edit($id)
    {
        try {
            $userId = decrypt($id);
            $user = User::findOrFail($userId);

            return response()->json([
                'result' => true,
                'data' => [
                    'id' => encrypt($user->id),
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                ],
                'message' => 'User retrieved successfully.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'result' => false,
                'message' => 'An error occurred while retrieving the user: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $userId = decrypt($id);
            $user = User::findOrFail($userId);

            $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
                'role' => 'required|string|in:admin,employee',
            ]);

            $user->name = $request->name;
            $user->email = $request->email;
            $user->role = $request->role;
            $user->save();

            return response()->json([
                'result' => true,
                'message' => 'User updated successfully.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'result' => false,
                'message' => 'An error occurred while updating the user: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $userId = decrypt($id);
            $user = User::findOrFail($userId);
            $user->delete();

            return response()->json([
                'result' => true,
                'message' => 'User deleted successfully.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'result' => false,
                'message' => 'An error occurred while deleting the user: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function show($id){
        try {
            $userId = decrypt($id);
            $user = User::findOrFail($userId);

            return response()->json([
                'result' => true,
                'data' => [
                    'id' => encrypt($user->id),
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                ],
                'message' => 'User retrieved successfully.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'result' => false,
                'message' => 'An error occurred while retrieving the user: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function selectList(Request $request){
        try{
            $search = $request->input('search');
            $users = User::query()
                ->when($search, function ($query) use ($search) {
                    return $query->where('name', 'like', "%{$search}%")
                                 ->orWhere('email', 'like', "%{$search}%");
                })
                ->orderByDesc('id')
                ->get();

            $data = $users->map(function ($user) {
                return [
                    'id' => encrypt($user->id),
                    'name' => $user->name,
                    'role' => $user->role,
                ];
            });

            return response()->json([
                'result' => true,
                'data' => $data,
                'message' => 'Users retrieved successfully.',
            ]);

        }catch(\Exception $e){
            return response()->json([
                'result' => false,
                'message' => 'An error occurred while retrieving the user: ' . $e->getMessage(),
            ], 500);
        }
    }
}
