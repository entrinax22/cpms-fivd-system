<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Client;
use App\Models\ProjectManager;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    /**
     * Display a paginated list of projects with optional search.
     */
    public function list(Request $request)
    {
        try {
            $search = $request->input('search');
            $perPage = $request->input('per_page', 10);
            $page = $request->input('page', 1);

            $query = Project::with(['client', 'manager'])
                ->when($search, function ($q) use ($search) {
                    $q->where('project_name', 'like', "%{$search}%")
                        ->orWhere('project_description', 'like', "%{$search}%")
                        ->orWhereHas('client', function ($q) use ($search) {
                            $q->where('client_name', 'like', "%{$search}%");
                        })
                        ->orWhereHas('manager', function ($q) use ($search) {
                            $q->where('manager_name', 'like', "%{$search}%");
                        });
                })
                ->orderByDesc('project_id');

            $projects = $query->paginate($perPage, ['*'], 'page', $page);

            $data = $projects->getCollection()->map(function ($project) {
                return [
                    'project_id' => encrypt($project->project_id),
                    'project_name' => $project->project_name,
                    'client_id' => encrypt($project->client_id),
                    'client_name' => optional($project->client)->client_name,
                    'manager_id' => encrypt($project->manager_id),
                    'manager_name' => optional($project->manager)->manager_name,
                    'start_date' => $project->start_date,
                    'estimated_end_date' => $project->estimated_end_date,
                    'project_description' => $project->project_description,
                    'status' => $project->status,
                    'created_at' => $project->created_at,
                    'updated_at' => $project->updated_at,
                ];
            });

            return response()->json([
                'result' => true,
                'data' => $data,
                'pagination' => [
                    'current_page' => $projects->currentPage(),
                    'per_page' => $projects->perPage(),
                    'total' => $projects->total(),
                    'last_page' => $projects->lastPage(),
                ],
                'message' => 'Projects retrieved successfully.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'result' => false,
                'message' => 'Error retrieving projects: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Store a newly created project.
     */
    public function store(Request $request)
    {
        $request->validate([
            'project_name' => 'required|string|max:255',
            'client_id' => 'required|string',
            'manager_id' => 'required|string',
            'start_date' => 'required|date',
            'estimated_end_date' => 'nullable|date|after_or_equal:start_date',
            'project_description' => 'nullable|string',
            'status' => 'required|in:planning,in_progress,completed,on_hold,cancelled',
        ]);

        try {
            $clientId = decrypt($request->input('client_id'));
            $managerId = decrypt($request->input('manager_id'));

            $project = Project::create([
                'project_name' => $request->input('project_name'),
                'client_id' => $clientId,
                'manager_id' => $managerId,
                'start_date' => $request->input('start_date'),
                'estimated_end_date' => $request->input('estimated_end_date'),
                'project_description' => $request->input('project_description'),
                'status' => $request->input('status'),
            ]);

            return response()->json([
                'result' => true,
                'message' => 'Project created successfully.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'result' => false,
                'message' => 'Error creating project: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Show the details of a single project.
     */
    public function edit($project_id)
    {
        try {
            $projectId = decrypt($project_id);

            // Fetch project with relations
            $project = Project::with(['client', 'manager'])->findOrFail($projectId);

            // 🔹 Encrypt all client IDs only once
            $encryptedClientIDs = Client::pluck('client_id')->mapWithKeys(function ($id) {
                return [$id => encrypt($id)];
            });

            // 🔹 Encrypt all manager IDs only once
            $encryptedManagerIDs = ProjectManager::pluck('manager_id')->mapWithKeys(function ($id) {
                return [$id => encrypt($id)];
            });

            // 🔹 Prepare the main project data
            $data = [
                'project_id'           => encrypt($project->project_id),
                'project_name'         => $project->project_name,
                'project_description'  => $project->project_description,
                'status'               => $project->status,
                'start_date'           => $project->start_date,
                'estimated_end_date'   => $project->estimated_end_date,
                'client_id'            => $encryptedClientIDs[$project->client_id] ?? null,
                'client_name'          => $project->client->client_name ?? null,
                'manager_id'           => $encryptedManagerIDs[$project->manager_id] ?? null,
                'manager_name'         => $project->manager->manager_name ?? null,
            ];

            // 🔹 Convert the full client and manager lists using the pre-encrypted mappings
            $clients = Client::select('client_id', 'client_name')->get()->map(function ($client) use ($encryptedClientIDs) {
                return [
                    'client_id' => $encryptedClientIDs[$client->client_id] ?? null,
                    'client_name' => $client->client_name,
                ];
            });

            $managers = ProjectManager::select('manager_id', 'manager_name')->get()->map(function ($manager) use ($encryptedManagerIDs) {
                return [
                    'manager_id' => $encryptedManagerIDs[$manager->manager_id] ?? null,
                    'manager_name' => $manager->manager_name,
                ];
            });

            // 🔹 Final JSON response
            return response()->json([
                'result' => true,
                'data' => [
                    'project' => $data,
                    'clients' => $clients,
                    'managers' => $managers,
                ],
                'message' => 'Project retrieved successfully.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'result' => false,
                'message' => 'An error occurred while retrieving the project: ' . $e->getMessage(),
            ], 500);
        }
    }


    /**
     * Update the specified project.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'project_name' => 'required|string|max:255',
            'client_id' => 'required|string',
            'manager_id' => 'required|string',
            'start_date' => 'required|date',
            'estimated_end_date' => 'nullable|date|after_or_equal:start_date',
            'project_description' => 'nullable|string',
            'status' => 'required|string|max:50',
        ]);

        try {
            $projectId = decrypt($id);
            $clientId = decrypt($request->input('client_id'));
            $managerId = decrypt($request->input('manager_id'));

            $project = Project::findOrFail($projectId);
            $project->update([
                'project_name' => $request->input('project_name'),
                'client_id' => $clientId,
                'manager_id' => $managerId,
                'start_date' => $request->input('start_date'),
                'estimated_end_date' => $request->input('estimated_end_date'),
                'project_description' => $request->input('project_description'),
                'status' => $request->input('status'),
            ]);

            return response()->json([
                'result' => true,
                'message' => 'Project updated successfully.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'result' => false,
                'message' => 'Error updating project: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified project.
     */
    public function destroy($id)
    {
        try {
            $projectId = decrypt($id);
            $project = Project::findOrFail($projectId);
            $project->delete();

            return response()->json([
                'result' => true,
                'message' => 'Project deleted successfully.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'result' => false,
                'message' => 'Error deleting project: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Return list of projects for dropdown or select input.
     */
    public function selectList(Request $request)
    {
        try {
            $search = $request->search;

            $projects = Project::with('client')
                ->when($search, function ($q) use ($search) {
                    $q->where('project_name', 'like', "%{$search}%")
                        ->orWhereHas('client', function ($q) use ($search) {
                            $q->where('client_name', 'like', "%{$search}%");
                        });
                })
                ->orderByDesc('project_id')
                ->get();

            $data = $projects->map(function ($project) {
                return [
                    'project_id' => encrypt($project->project_id),
                    'project_name' => $project->project_name,
                    'client_name' => optional($project->client)->client_name,
                ];
            });

            return response()->json([
                'result' => true,
                'data' => $data,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'result' => false,
                'message' => 'Error retrieving project list: ' . $e->getMessage(),
            ], 500);
        }
    }
}
