<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DevelopmentTeam;
use App\Models\DevelopmentTool;

class DevelopmentToolController extends Controller
{
    public function store(Request $request){
        try{
            $validated = $request->validate([
                'tool_name' => 'required|string|max:255',
                'tool_version' => 'required|string|max:100',
                'team_id' => 'required|string',
                'license_expiry_date' => 'nullable|date',
            ]);
            $teamId = decrypt($validated['team_id']);
            
            DevelopmentTool::create([
                'tool_name' => $validated['tool_name'],
                'tool_version' => $validated['tool_version'],
                'team_id' => $teamId,
                'license_expiry_date' => $validated['license_expiry_date'] ?? null,
            ]);

            return response()->json([
                'result'  => true,
                'message' => 'Development tool created successfully.',
            ], 201);
        }catch(\Exception $e){
            return response()->json([
                'message' => 'Unexpected error!',
                'errors' => $e->errors(),
            ], 422);
        }catch (\Exception $e) {
            return response()->json([
                'result' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function list(Request $request)
    {
        try {
            $search = $request->input('search');
            $perPage = $request->input('per_page', 10);
            $page = $request->input('page', 1);

            $query = DevelopmentTool::query()
                ->when($search, function ($q) use ($search) {
                    // Try to parse the search as a date
                    $date = date_create($search);
                    $q->where(function ($subQ) use ($search, $date) {
                        $subQ->where('tool_name', 'like', "%{$search}%")
                            ->orWhere('tool_version', 'like', "%{$search}%");
                        if ($date) {
                            // If valid date, search for exact date or partial match (Y-m-d)
                            $formatted = $date->format('Y-m-d');
                            $subQ->orWhereDate('license_expiry_date', $formatted);
                        } else {
                            // Fallback to partial string match
                            $subQ->orWhere('license_expiry_date', 'like', "%{$search}%");
                        }
                    });
                })
                ->orderByDesc('tool_id');

            $tools = $query->paginate($perPage, ['*'], 'page', $page);

            $data = $tools->getCollection()->map(function ($tool) {
                return [
                    'tool_id' => encrypt($tool->tool_id),
                    'tool_name' => $tool->tool_name,
                    'tool_version' => $tool->tool_version,
                    'license_expiry_date' => $tool->license_expiry_date,
                    'development_team' => $tool->developmentTeam ? [
                        'team_id' => encrypt($tool->developmentTeam->team_id),
                        'team_name' => $tool->developmentTeam->team_name,
                        'team_size' => $tool->developmentTeam->team_size,
                        'specialization' => $tool->developmentTeam->specialization,
                        'manager' => $tool->developmentTeam->projectManager ? [
                            'manager_id' => encrypt($tool->developmentTeam->projectManager->manager_id),
                            'manager_name' => $tool->developmentTeam->projectManager->manager_name,
                            'expertise_area' => $tool->developmentTeam->projectManager->expertise_area,
                            'contact_information' => $tool->developmentTeam->projectManager->contact_information,
                            'years_of_experience' => $tool->developmentTeam->projectManager->years_of_experience,
                        ] : null,
                    ] : null,
                ];
            });

            return response()->json([
                'result' => true,
                'data' => $data,
                'pagination' => [
                    'current_page' => $tools->currentPage(),
                    'per_page' => $tools->perPage(),
                    'total' => $tools->total(),
                    'last_page' => $tools->lastPage(),
                ],
                'message' => 'Development Tools retrieved successfully.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'result' => false,
                'message' => 'Error retrieving development tools: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function edit($tool_id)
    {
        try {
            $tool_id = decrypt($tool_id);
            $tool = DevelopmentTool::with('developmentTeam')->findOrFail($tool_id);

            // Create a shared encrypted map for team IDs
            $encryptedDevTeamIds = DevelopmentTeam::pluck('team_id')->mapWithKeys(function ($id) {
                return [$id => encrypt($id)];
            });

            // Build the team list using the shared encrypted values
            $devTeams = DevelopmentTeam::with('projectManager')->orderByDesc('team_id')->get()->map(function ($devTeam) use ($encryptedDevTeamIds) {
                return [
                    'team_id' => $encryptedDevTeamIds[$devTeam->team_id],
                    'team_name' => $devTeam->team_name,
                    'team_size' => $devTeam->team_size,
                    'specialization' => $devTeam->specialization,
                    'manager' => $devTeam->projectManager ? [
                        'manager_id' => encrypt($devTeam->projectManager->manager_id),
                        'manager_name' => $devTeam->projectManager->manager_name,
                        'expertise_area' => $devTeam->projectManager->expertise_area,
                        'contact_information' => $devTeam->projectManager->contact_information,
                        'years_of_experience' => $devTeam->projectManager->years_of_experience,
                    ] : null,
                ];
            });

            return response()->json([
                'result' => true,
                'data' => [
                    'tool' => [
                        'tool_id' => encrypt($tool->tool_id),
                        'tool_name' => $tool->tool_name,
                        'tool_version' => $tool->tool_version,
                        'license_expiry_date' => $tool->license_expiry_date,
                        'team_id' => $encryptedDevTeamIds[$tool->team_id] ?? null,
                    ],
                    'development_teams' => $devTeams,
                ],
                'message' => 'Development Tool retrieved successfully.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'result' => false,
                'message' => 'An error occurred while retrieving the development tool: ' . $e->getMessage(),
            ], 500);
        }
    }


    public function update(Request $request, $tool_id)
    {
        $request->validate([
            'tool_name' => 'required|string|max:255',
            'tool_version' => 'required|string|max:100',
            'license_expiry_date' => 'nullable|date',
        ]);

        try {
            $tool_id = decrypt($tool_id);

            $tool = DevelopmentTool::findOrFail($tool_id);
            $tool->tool_name = $request->input('tool_name');
            $tool->tool_version = $request->input('tool_version');
            $tool->license_expiry_date = $request->input('license_expiry_date');
            $tool->save();

            return response()->json([
                'result' => true,
                'message' => 'Development Tool updated successfully.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'result' => false,
                'message' => 'An error occurred while updating the development tool: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function destroy($tool_id)
    {
        try {
            $tool_id = decrypt($tool_id);
            $tool = DevelopmentTool::findOrFail($tool_id);
            $tool->delete();

            return response()->json([
                'result' => true,
                'message' => 'Development Tool deleted successfully.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'result' => false,
                'message' => 'An error occurred while deleting the development tool: ' . $e->getMessage(),
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

            $devTools = DevelopmentTool::with('developmentTeam')
                ->when($search, function ($query, $search) {
                    $query->where('tool_name', 'like', "%{$search}%")
                            ->orWhere('tool_version', 'like', "%{$search}%")
                            ->orWhere('license_expiry_date', 'like', "%{$search}%")
                            ->orWhereHas('developmentTeam', function ($q) use ($search) {
                                $q->where('team_name', 'like', "%{$search}%");
                            });
                })
                ->orderBy('tool_id')
                ->get();

            $data = $devTools->map(function ($devTool) {
                return [
                    'tool_id' => encrypt($devTool->tool_id),
                    'tool_name' => $devTool->tool_name,
                    'tool_version' => $devTool->tool_version,
                    'license_expiry_date' => $devTool->license_expiry_date,
                    'development_team' => $devTool->developmentTeam ? [
                        'team_id' => encrypt($devTool->developmentTeam->team_id),
                        'team_name' => $devTool->developmentTeam->team_name,
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
                'message' => 'Error retrieving testing tools list: ' . $e->getMessage(),
            ], 500);
        }
    }
}
