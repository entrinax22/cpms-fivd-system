<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Services\SemaphoreService;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    protected $semaphore;
    protected $encryptionService;

    public function __construct(SemaphoreService $semaphore, \App\Services\EncryptionService $encryptionService){
        $this->semaphore = $semaphore;
        $this->encryptionService = $encryptionService;
    }
    public function store(Request $request){
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'phone' => 'required|string|unique:users,phone',
            'role' => 'required|string|in:admin,employee,engineer,manager',
            'development_team_ids' => 'nullable|array',
            'development_team_ids.*' => 'string',
            'testing_team_ids' => 'nullable|array',
            'testing_team_ids.*' => 'string',
        ]);

        $temporaryPassword = Str::random(10); 

        $user = new User();
        $user->name = $request->name;
        $user->email = $request->email;
        $user->phone = $request->phone;
        $user->role = $request->role;
        $user->password = Hash::make($temporaryPassword);
        $user->must_change_password = true;
        $user->password_expires_at = now()->addDays(30);
        $user->save();

        // Validate and attach development teams
        if ($request->filled('development_team_ids')) {
            $devIds = array_map(function ($enc) {
                return decrypt($enc);
            }, $request->input('development_team_ids'));
            
            // Check team size limits for development teams
            foreach ($devIds as $teamId) {
                $team = \App\Models\DevelopmentTeam::findOrFail($teamId);
                $currentMembers = $team->users()->count();
                if ($currentMembers >= $team->team_size) {
                    return response()->json([
                        'result' => false,
                        'errors' => ['development_team_ids' => ["Development team '{$team->team_name}' has reached its maximum size of {$team->team_size} members."]],
                    ], 422);
                }
            }
            $user->developmentTeams()->sync($devIds);
        }

        // Validate and attach testing teams
        if ($request->filled('testing_team_ids')) {
            $testIds = array_map(function ($enc) {
                return decrypt($enc);
            }, $request->input('testing_team_ids'));
            
            // Check team size limits for testing teams
            foreach ($testIds as $teamId) {
                $team = \App\Models\TestingTeam::findOrFail($teamId);
                $currentMembers = $team->users()->count();
                if ($currentMembers >= $team->team_size) {
                    return response()->json([
                        'result' => false,
                        'errors' => ['testing_team_ids' => ["Testing team '{$team->team_name}' has reached its maximum size of {$team->team_size} members."]],
                    ], 422);
                }
            }
            $user->testingTeams()->sync($testIds);
        }
        $response = null;
        $to = $user->phone;
        $message = "Your account has been created. Temporary password: {$temporaryPassword}. Please change your password upon first login.";
        $response = $this->semaphore->sendSms($to, $message);
        return response()->json([
            'result' => true,
            'message' => 'User created successfully.',
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
                $developmentTeams = $user->developmentTeams()->get()->map(function ($team) {
                    return [
                        'team_id' => encrypt($team->team_id),
                        'team_name' => $team->team_name,
                    ];
                });

                $testingTeams = $user->testingTeams()->get()->map(function ($team) {
                    return [
                        'testing_team_id' => encrypt($team->testing_team_id),
                        'team_name' => $team->team_name,
                    ];
                });

                return [
                    'id' => encrypt($user->id),
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'role' => $user->role,
                    'development_teams' => $developmentTeams,
                    'testing_teams' => $testingTeams,
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

            // First, get all development and testing team IDs for consistent encryption
            $allDevTeamIds = \App\Models\DevelopmentTeam::pluck('team_id')->mapWithKeys(function ($id) {
                return [$id => encrypt($id)];
            });
            
            $allTestTeamIds = \App\Models\TestingTeam::pluck('testing_team_id')->mapWithKeys(function ($id) {
                return [$id => encrypt($id)];
            });

            $developmentTeams = $user->developmentTeams()->get()->map(function ($team) {
                return [
                    'team_id' => $this->encryptionService->getEncryptedDevelopmentTeamId($team->team_id),
                    'team_name' => $team->team_name,
                ];
            });

            $testingTeams = $user->testingTeams()->get()->map(function ($team) {
                return [
                    'testing_team_id' => $this->encryptionService->getEncryptedTestingTeamId($team->testing_team_id),
                    'team_name' => $team->team_name,
                ];
            });

            return response()->json([
                'result' => true,
                'data' => [
                    'id' => encrypt($user->id),
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'role' => $user->role,
                    'development_teams' => $developmentTeams,
                    'testing_teams' => $testingTeams,
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
                'phone' => 'required|string|unique:users,phone,' . $user->id,
                'development_team_ids' => 'nullable|array',
                'development_team_ids.*' => 'string',
                'testing_team_ids' => 'nullable|array',
                'testing_team_ids.*' => 'string',
            ]);

            $user->name = $request->name;
            $user->email = $request->email;
            $user->role = $request->role;
            $user->phone = $request->phone;
            $user->save();

            // Validate and sync development teams
            if ($request->has('development_team_ids')) {
                $devIds = array_map(function ($enc) {
                    return decrypt($enc);
                }, $request->input('development_team_ids', []));
                
                // Check team size limits for development teams
                foreach ($devIds as $teamId) {
                    $team = \App\Models\DevelopmentTeam::findOrFail($teamId);
                    $currentMembers = $team->users()->count();
                    if ($currentMembers >= $team->team_size) {
                        return response()->json([
                            'result' => false,
                            'errors' => ['development_team_ids' => ["Development team '{$team->team_name}' has reached its maximum size of {$team->team_size} members."]],
                        ], 422);
                    }
                }
                $user->developmentTeams()->sync($devIds);
            }

            // Validate and sync testing teams
            if ($request->has('testing_team_ids')) {
                $testIds = array_map(function ($enc) {
                    return decrypt($enc);
                }, $request->input('testing_team_ids', []));
                
                // Check team size limits for testing teams
                foreach ($testIds as $teamId) {
                    $team = \App\Models\TestingTeam::findOrFail($teamId);
                    $currentMembers = $team->users()->count();
                    if ($currentMembers >= $team->team_size) {
                        return response()->json([
                            'result' => false,
                            'errors' => ['testing_team_ids' => ["Testing team '{$team->team_name}' has reached its maximum size of {$team->team_size} members."]],
                        ], 422);
                    }
                }
                $user->testingTeams()->sync($testIds);
            }

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

            $developmentTeams = $user->developmentTeams()->get()->map(function ($team) {
                return [
                    'team_id' => encrypt($team->team_id),
                    'team_name' => $team->team_name,
                ];
            });

            $testingTeams = $user->testingTeams()->get()->map(function ($team) {
                return [
                    'testing_team_id' => encrypt($team->testing_team_id),
                    'team_name' => $team->team_name,
                ];
            });

            return response()->json([
                'result' => true,
                'data' => [
                    'id' => encrypt($user->id),
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                    'phone' => $user->phone,
                    'development_teams' => $developmentTeams,
                    'testing_teams' => $testingTeams,
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

    public function select(Request $request){
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
                'message' => 'Users List retrieved successfully.',
            ]);
        }catch(\Exception $e){
            return response()->json([
                'result' => false,
                'message' => 'An error occurred while retrieving the user list: ' . $e->getMessage(),
            ], 500);
        }
    }
}
