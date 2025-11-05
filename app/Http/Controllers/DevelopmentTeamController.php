<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ProjectManager;
use App\Models\DevelopmentTeam;

class DevelopmentTeamController extends Controller
{
    public function store(Request $request){
        try{
            $validated = $request->validate([
                'team_name'      => 'required|string|max:255',
                'team_size'      => 'required|integer|min:1',
                'specialization' => 'nullable|string|max:255',
                'manager_id'     => 'required|string', 
            ]);
            
            $validated['manager_id'] = decrypt($validated['manager_id']);

            $team = DevelopmentTeam::create($validated);

            return response()->json([
                'result'  => true,
                'message' => 'Development team created successfully.',
                'data'    => $team
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

            $query = DevelopmentTeam::with('projectManager')
                ->when($search, function ($q) use ($search) {
                    $q->where('team_name', 'like', "%{$search}%")
                    ->orWhere('specialization', 'like', "%{$search}%");
                })
                ->orderByDesc('team_id');

            $teams = $query->paginate($perPage, ['*'], 'page', $page);

            $data = $teams->getCollection()->map(function ($team) {
                return [
                    'team_id' => encrypt($team->team_id),
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
                'message' => 'Development Teams retrieved successfully.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'result' => false,
                'message' => 'Error retrieving development teams: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function edit($team_id)
    {
        try {
            $team_id = decrypt($team_id);
            $team = DevelopmentTeam::findOrFail($team_id);

            // Create a shared encrypted map for manager IDs
            $encryptedManagerIds = ProjectManager::pluck('manager_id')->mapWithKeys(function ($id) {
                return [$id => encrypt($id)];
            });

            // Build the manager list using the shared encrypted values
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
                        'team_id' => encrypt($team->team_id),
                        'team_name' => $team->team_name,
                        'team_size' => $team->team_size,
                        'specialization' => $team->specialization,
                        'manager_id' => $encryptedManagerIds[$team->manager_id], // same encrypted value
                    ],
                    'managers' => $managers
                ],
                'message' => 'Development Team retrieved successfully with manager list.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'result' => false,
                'message' => 'An error occurred while retrieving the development team: ' . $e->getMessage(),
            ], 500);
        }
    }


    public function update(Request $request, $team_id)
    {
        $request->validate([
            'team_name'      => 'required|string|max:255',
            'team_size'      => 'required|integer|min:1',
            'specialization' => 'nullable|string|max:255',
            'manager_id'     => 'required|string', 
        ]);

        try {
            $team_id = decrypt($team_id);
            $manager_id = decrypt($request->input('manager_id'));

            $team = DevelopmentTeam::findOrFail($team_id);
            $team->team_name = $request->input('team_name');
            $team->team_size = $request->input('team_size');
            $team->specialization = $request->input('specialization');
            $team->manager_id = $manager_id;
            $team->save();

            return response()->json([
                'result' => true,
                'message' => 'Development Team updated successfully.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'result' => false,
                'message' => 'An error occurred while updating the development team: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function destroy($team_id)
    {
        try {
            $team_id = decrypt($team_id);
            $team = DevelopmentTeam::findOrFail($team_id);
            $team->delete();

            return response()->json([
                'result' => true,
                'message' => 'Development Team deleted successfully.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'result' => false,
                'message' => 'An error occurred while deleting the development team: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Return list of testing tools for dropdown or select input.
     */
    public function selectList(Request $request)
    {
        try {
            $search = $request->search;

            $devTeams = DevelopmentTeam::with('projectManager')
                ->when($search, function ($query, $search) {
                    $query->where('team_name', 'like', "%{$search}%")
                        ->orWhere('specialization', 'like', "%{$search}%")
                        ->orWhereHas('projectManager', function ($q) use ($search) {
                            $q->where('manager_name', 'like', "%{$search}%")
                              ->orWhere('expertise_area', 'like', "%{$search}%");
                        });
                })
                ->orderBy('team_id')
                ->get();

            $data = $devTeams->map(function ($devTeam) {
                return [
                    'team_id' => encrypt($devTeam->team_id),
                    'team_name' => $devTeam->team_name,
                    'specialization' => $devTeam->specialization,
                    'manager' => $devTeam->projectManager ? [
                        'manager_id' => encrypt($devTeam->projectManager->manager_id),
                        'manager_name' => $devTeam->projectManager->manager_name,
                    ] : null,
                ];
            });

            return response()->json([
                'result' => true,
                'data' => $data,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'result' => false,
                'message' => 'Error retrieving development teams list: ' . $e->getMessage(),
            ], 500);
        }
    }

}
