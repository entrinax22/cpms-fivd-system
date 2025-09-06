<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DevelopmentTool;

class DevelopmentToolController extends Controller
{
    public function store(Request $request){
        try{
            $validated = $request->validate([
                'tool_name' => 'required|string|max:255',
                'tool_version' => 'required|string|max:100',
                'license_expiry_date' => 'nullable|date',
            ]);
            
            $tool = DevelopmentTool::create($validated);

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
            $tool = DevelopmentTool::findOrFail($tool_id);

            // Create a shared encrypted map for manager IDs
            // $encryptedManagerIds = ProjectManager::pluck('manager_id')->mapWithKeys(function ($id) {
            //     return [$id => encrypt($id)];
            // });

            // Build the manager list using the shared encrypted values
            // $managers = ProjectManager::orderByDesc('manager_id')->get()->map(function ($manager) use ($encryptedManagerIds) {
            //     return [
            //         'manager_id' => $encryptedManagerIds[$manager->manager_id],
            //         'manager_name' => $manager->manager_name,
            //         'expertise_area' => $manager->expertise_area,
            //         'contact_information' => $manager->contact_information,
            //     ];
            // });

            return response()->json([
                'result' => true,
                'data' => [
                    'tool' => [
                        'tool_id' => encrypt($tool->tool_id),
                        'tool_name' => $tool->tool_name,
                        'tool_version' => $tool->tool_version,
                        'license_expiry_date' => $tool->license_expiry_date,
                    ],
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
}
