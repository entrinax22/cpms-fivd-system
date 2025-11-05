<?php

namespace App\Http\Controllers;

use App\Models\TestingTeam;
use App\Models\TestingTool;
use Illuminate\Http\Request;

class TestingToolController extends Controller
{
    public function store(Request $request){
        try{
            $validated = $request->validate([
                'testing_tool_name' => 'required|string|max:255',
                'testing_team_id' => 'required|string',
                'license_key' => 'required|string|max:255',
            ]);
            $validated['testing_team_id'] = decrypt($validated['testing_team_id']);
            $tool = TestingTool::create($validated);

            return response()->json([
                'result'  => true,
                'message' => 'Development tool created successfully.',
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

            $query = TestingTool::with(['testingTeam.projectManager'])
                ->when($search, function ($q) use ($search) {
                    $q->where('testing_tool_name', 'like', "%{$search}%")
                    ->orWhere('license_key', 'like', "%{$search}%");
                })
                ->orderByDesc('testing_tool_id');

            $tools = $query->paginate($perPage, ['*'], 'page', $page);

            $data = $tools->getCollection()->map(function ($tool) {
                return [
                    'testing_tool_id' => encrypt($tool->testing_tool_id),
                    'testing_tool_name' => $tool->testing_tool_name,
                    'license_key' => $tool->license_key,
                    'testing_team' => $tool->testingTeam ? [
                        'testing_team_id' => encrypt($tool->testingTeam->testing_team_id),
                        'team_name' => $tool->testingTeam->team_name,
                        'team_size' => $tool->testingTeam->team_size,
                        'specialization' => $tool->testingTeam->specialization,
                        'manager' => $tool->testingTeam->projectManager ? [
                            'manager_id' => encrypt($tool->testingTeam->projectManager->manager_id),
                            'manager_name' => $tool->testingTeam->projectManager->manager_name,
                            'expertise_area' => $tool->testingTeam->projectManager->expertise_area,
                            'contact_information' => $tool->testingTeam->projectManager->contact_information,
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
                'message' => 'Testing Tools retrieved successfully.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'result' => false,
                'message' => 'Error retrieving testing tools: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function edit($testing_tool_id)
    {
        try {
            $testing_tool_id = decrypt($testing_tool_id);
            $tool = TestingTool::findOrFail($testing_tool_id);

            //  Encrypt all team IDs once
            $encryptedTestingTeamID = TestingTeam::pluck('testing_team_id')->mapWithKeys(function ($id) {
                return [$id => encrypt($id)];
            });

            //  Build teams list with consistent encrypted IDs
            $testingTeams = TestingTeam::orderByDesc('testing_team_id')->get()->map(function ($testTeam) use ($encryptedTestingTeamID) {
                return [
                    'testing_team_id' => $encryptedTestingTeamID[$testTeam->testing_team_id],
                    'team_name'       => $testTeam->team_name,
                    'team_size'       => $testTeam->team_size,
                    'specialization'  => $testTeam->specialization,
                ];
            });

            return response()->json([
                'result' => true,
                'data'   => [
                    'testing_tool' => [
                        'testing_tool_id'   => encrypt($tool->testing_tool_id),
                        'testing_tool_name' => $tool->testing_tool_name,
                        'license_key'       => $tool->license_key,
                        'testing_team_id'   => $encryptedTestingTeamID[$tool->testing_team_id],
                    ],
                    'testing_teams'      => $testingTeams,
                ],
                'message' => 'Testing Tool retrieved successfully.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'result'  => false,
                'message' => 'An error occurred while retrieving the testing tool: ' . $e->getMessage(),
            ], 500);
        }
    }



    public function update(Request $request, $testing_tool_id)
    {
        $request->validate([
            'testing_tool_name' => 'required|string|max:255',
            'testing_team_id' => 'required|string',
            'license_key' => 'required|string|max:255',
        ]);

        try {
            $testing_tool_id = decrypt($testing_tool_id);

            $tool = TestingTool::findOrFail($testing_tool_id);
            $tool->testing_tool_name = $request->input('testing_tool_name');
            $tool->testing_team_id = decrypt($request->input('testing_team_id'));
            $tool->license_key = $request->input('license_key');
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

    public function destroy($testing_tool_id)
    {
        try {
            $testing_tool_id = decrypt($testing_tool_id);
            $tool = TestingTool::findOrFail($testing_tool_id);
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

            $testing_tools = TestingTool::with('testingTeam')
                ->when($search, function ($query, $search) {
                    $query->where('testing_tool_name', 'like', "%{$search}%")
                          ->orWhere('license_key', 'like', "%{$search}%")
                          ->orWhereHas('testingTeam', function ($q) use ($search) {
                              $q->where('team_name', 'like', "%{$search}%");
                          });
                })
                ->orderBy('testing_tool_id')
                ->get();

            $data = $testing_tools->map(function ($testTool) {
                return [
                    'testing_tool_id' => encrypt($testTool->testing_tool_id),
                    'testing_tool_name' => $testTool->testing_tool_name,
                    'testing_team' => $testTool->testingTeam ? [
                        'testing_team_id' => encrypt($testTool->testingTeam->testing_team_id),
                        'team_name' => $testTool->testingTeam->team_name,
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
