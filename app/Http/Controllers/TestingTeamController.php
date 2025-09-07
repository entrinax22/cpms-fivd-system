<?php

namespace App\Http\Controllers;

use App\Models\TestingTeam;
use Illuminate\Http\Request;
use App\Models\ProjectManager;

class TestingTeamController extends Controller
{
    public function store(Request $request){
        try{
            $validated = $request->validate([
                'team_name' => 'required|string|max:255',
                'team_size' => 'required|integer|min:1',
                'specialization' => 'required|string|max:255',
                'manager_id' => 'required|string',
            ]);
            $validated['manager_id'] = decrypt($validated['manager_id']);
            $tool = TestingTeam::create($validated);

            return response()->json([
                'result'  => true,
                'message' => 'Testing Team created successfully.',
            ], 201);
        }catch(\Exception $e){
            return response()->json([
                'result' => false,
                'message' => ' ' . $e->getMessage(),
            ]);
        }
    }

    public function list(Request $request)
    {
        try {
            $search = $request->input('search');
            $perPage = $request->input('per_page', 10);
            $page = $request->input('page', 1);

            $query = TestingTeam::with('projectManager')
                ->when($search, function ($q) use ($search) {
                    $q->where('team_name', 'like', "%{$search}%")
                    ->orWhere('specialization', 'like', "%{$search}%");
                })
                ->orderByDesc('testing_team_id');

            $teams = $query->paginate($perPage, ['*'], 'page', $page);

            $data = $teams->getCollection()->map(function ($team) {
                return [
                    'testing_team_id' => encrypt($team->testing_team_id),
                    'team_name' => $team->team_name,
                    'team_size' => $team->team_size,
                    'specialization' => $team->specialization,
                    'manager_id' => encrypt($team->manager_id),
                    'manager' => $team->projectManager ? [
                        'manager_id' => encrypt($team->projectManager->manager_id),
                        'manager_name' => $team->projectManager->manager_name,
                        'expertise_area' => $team->projectManager->expertise_area,
                        'contact_information' => $team->projectManager->contact_information,
                        'years_of_experience' => $team->projectManager->years_of_experience,
                    ] : null,
                ];
            });

            return response()->json([
                'result' => true,
                'data' => $data,
                'pagination' => [
                    'current_page' => $teams->currentPage(),
                    'per_page' => $teams->perPage(),
                    'total' => $teams->total(),
                    'last_page' => $teams->lastPage(),
                ],
                'message' => 'Testing Teams retrieved successfully.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'result' => false,
                'message' => 'Error retrieving testing teams: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function edit($testing_team_id)
    {
        try {
            $testing_team_id = decrypt($testing_team_id);
            // Use the correct primary key for lookup
            $team = TestingTeam::where('testing_team_id', $testing_team_id)->firstOrFail();

            $encryptedManagerIds = ProjectManager::pluck('manager_id')->mapWithKeys(function ($id) {
                return [$id => encrypt($id)];
            });

            $managers = ProjectManager::orderByDesc('manager_id')->get()->map(function ($manager) use ($encryptedManagerIds) {
                return [
                    'manager_id' => $encryptedManagerIds[$manager->manager_id],
                    'manager_name' => $manager->manager_name,
                    'expertise_area' => $manager->expertise_area,
                    'contact_information' => $manager->contact_information,
                ];
            });

            return response()->json([
                'result' => true,
                'data' => [
                    'team' => [
                        'testing_team_id' => encrypt($team->testing_team_id),
                        'team_name' => $team->team_name,
                        'team_size' => $team->team_size,
                        'specialization' => $team->specialization,
                        'manager_id' => $encryptedManagerIds[$team->manager_id],
                    ],
                    'managers' => $managers
                ],
                'message' => 'Testing Team retrieved successfully with manager list.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'result' => false,
                'message' => 'An error occurred while retrieving the testing team: ' . $e->getMessage(),
            ], 500);
        }
    }


    public function update(Request $request, $testing_team_id)
    {
        $request->validate([
            'team_name'      => 'required|string|max:255',
            'team_size'      => 'required|integer|min:1',
            'specialization' => 'nullable|string|max:255',
            'manager_id'     => 'required|string', 
        ]);

        try {
            $testing_team_id = decrypt($testing_team_id);
            $manager_id = decrypt($request->input('manager_id'));

            $team = TestingTeam::findOrFail($testing_team_id);
            $team->team_name = $request->input('team_name');
            $team->team_size = $request->input('team_size');
            $team->specialization = $request->input('specialization');
            $team->manager_id = $manager_id;
            $team->save();

            return response()->json([
                'result' => true,
                'message' => 'Testing Team updated successfully.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'result' => false,
                'message' => 'An error occurred while updating the testing team: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function destroy($testing_team_id)
    {
        try {
            $testing_team_id = decrypt($testing_team_id);
            $team = TestingTeam::findOrFail($testing_team_id);
            $team->delete();

            return response()->json([
                'result' => true,
                'message' => 'Testing team deleted successfully.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'result' => false,
                'message' => 'An error occurred while deleting the testing team: ' . $e->getMessage(),
            ], 500);
        }
    }

    
    public function selectList(Request $request){
        try{
            $search = $request->search;
            $query = TestingTeam::query()
                ->when($search, function ($q) use ($search) {
                    $q->where('team_name', 'like', "%{$search}%")
                    ->orWhere('team_size', 'like', "%{$search}%")
                    ->orWhere('specialization', 'like', "%{$search}%")
                    ->orWhereHas('projectManager', function ($q) use ($search) {
                        $q->where('manager_name', 'like', "%{$search}%");
                    });
                })
                ->orderByDesc('testing_team_id');

            $testingTeam = $query->get();

            $data = $testingTeam->map(function ($testTeam) {
                return [
                    'testing_team_id' => encrypt($testTeam->testing_team_id),
                    'team_name' => $testTeam->team_name,
                    'team_size' => $testTeam->team_size,
                    'specialization' => $testTeam->specialization,
                    'manager' => $testTeam->projectManager ? [
                        'manager_id' => encrypt($testTeam->projectManager->manager_id),
                        'manager_name' => $testTeam->projectManager->manager_name,
                        'expertise_area' => $testTeam->projectManager->expertise_area,
                        'contact_information' => $testTeam->projectManager->contact_information,
                        'years_of_experience' => $testTeam->projectManager->years_of_experience,
                    ] : null,
                ];
            });

            return response()->json([
                'result' => true,
                'data' => $data,
            ]);
        }catch(\Exception $e){
            return response()->json([
                'result' => false,
                'message' => 'An error occurred while retrieving the project manager: ' . $e->getMessage(),
            ]);
        }
    }
}
