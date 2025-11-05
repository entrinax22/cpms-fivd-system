<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\Client;
use App\Models\Project;
use Illuminate\Http\Request;
use App\Models\ProjectManager;
use App\Models\ProjectProgress;
use App\Services\SemaphoreService;
use Illuminate\Support\Facades\Auth;

class ProjectController extends Controller
{
    protected $semaphore;

    public function __construct(SemaphoreService $semaphore)
    {
        $this->semaphore = $semaphore;   
    }

    /**
     * Display a paginated list of projects with optional search.
     */
    public function list(Request $request)
    {
        try {
            $search = $request->input('search');
            $perPage = $request->input('per_page', 10);
            $page = $request->input('page', 1);

            $query = Project::with(['client', 'manager', 'manager.devTeam', 'manager.testTeam'])
                ->when($search, function ($q) use ($search) {
                    $q->where('project_name', 'like', "%{$search}%")
                        ->orWhere('project_description', 'like', "%{$search}%")
                        ->orWhereHas('client', function ($q) use ($search) {
                            $q->where('client_name', 'like', "%{$search}%");
                        })
                        ->orWhereHas('manager', function ($q) use ($search) {
                            $q->where('manager_name', 'like', "%{$search}%");
                        })
                        ->orWhereHas('manager.devTeam', function ($q) use ($search) {
                            $q->where('team_name', 'like', "%{$search}%");
                        })
                        ->orWhereHas('manager.testTeam', function ($q) use ($search) {
                            $q->where('team_name', 'like', "%{$search}%");
                        });
                })
                ->orderByDesc('project_id');

            $projects = $query->paginate($perPage, ['*'], 'page', $page);

            $dev_teams = $projects->getCollection()->map(function ($project) {
                return optional($project->manager)->devTeam
                    ? $project->manager->devTeam->map(function ($team) {
                        return [
                            'team_id' => encrypt($team->team_id),
                            'team_name' => $team->team_name,
                        ];
                    })
                    : [];
            });

            $test_teams = $projects->getCollection()->map(function ($project) {
                return optional($project->manager)->testTeam
                    ? $project->manager->testTeam->map(function ($team) {
                        return [
                            'testing_team_id' => encrypt($team->testing_team_id),
                            'team_name' => $team->team_name,
                        ];
                    })
                    : [];
            });
            
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
                'development_teams' => $dev_teams,
                'testing_teams' => $test_teams,
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
            $responses = [];
            $project = Project::create([
                'project_name' => $request->input('project_name'),
                'client_id' => $clientId,
                'manager_id' => $managerId,
                'start_date' => $request->input('start_date'),
                'estimated_end_date' => $request->input('estimated_end_date'),
                'project_description' => $request->input('project_description'),
                'status' => $request->input('status'),
            ]);

            $project->load('client', 'manager');

            $client = Client::find($clientId);
            if ($client?->contact_information) {
                $clientMessage = sprintf(
                    "New project '%s' has been created for client '%s'.",
                    $project->project_name,
                    $client->client_name
                );

                $responses['client'] = $this->semaphore->sendSMS($client->contact_information, $clientMessage);
            } else {
                Log::warning("Client with ID {$clientId} not found or missing contact number. SMS not sent.");
            }

            $manager = ProjectManager::find($managerId);
            if ($manager?->contact_information) {
                $managerMessage = sprintf(
                    "You have been assigned as the manager for the new project '%s'.",
                    $project->project_name
                );

                $responses['manager'] = $this->semaphore->sendSMS($manager->contact_information, $managerMessage);
            } else {
                Log::warning("Project Manager with ID {$managerId} not found or missing contact number. SMS not sent.");
            }

            return response()->json([
                'result' => true,
                'message' => 'Project created successfully.',
                'responses' => $responses,
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

            $project->load('client', 'manager');

            $client = Client::find($clientId);
            if ($client?->contact_information) {
                $clientMessage = sprintf(
                    "Project '%s' has been updated for client '%s'.",
                    $project->project_name,
                    $client->client_name
                );
                $response = $this->semaphore->sendSMS($client->contact_information, $clientMessage);
            } else {
                Log::warning("Client with ID {$clientId} not found or missing contact number. SMS not sent.");
            }

            $manager = ProjectManager::find($managerId);
            if ($manager?->contact_information) {
                $managerMessage = sprintf(
                    "You have been assigned as the manager for the updated project '%s'.",
                    $project->project_name
                );
                $response = $this->semaphore->sendSMS($manager->contact_information, $managerMessage);
            } else {
                Log::warning("Project Manager with ID {$managerId} not found or missing contact number. SMS not sent.");
            }

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

    public function getCalendarData()
    {
        try {
            $projects = Project::with(['client', 'manager'])
                ->orderByDesc('project_id')
                ->get();

            $data = $projects->map(function ($project) {
                return [
                    'id' => encrypt($project->project_id),
                    'name' => $project->project_name,
                    'client' => optional($project->client)->client_name,
                    'manager' => optional($project->manager)->manager_name ?? 'Unassigned',
                    'status' => ucfirst($project->status),
                    'start_date' => $project->start_date,
                    'end_date' => $project->estimated_end_date,
                    'color' => match ($project->status) {
                        'completed' => '#10b981',
                        'in_progress' => '#3b82f6',
                        'pending' => '#f59e0b',
                        'delayed' => '#ef4444',
                        default => '#9ca3af',
                    },
                ];
            });

            return response()->json([
                'result' => true,
                'data' => $data,
                'message' => 'Projects retrieved successfully.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'result' => false,
                'message' => 'Error retrieving project list: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function projectListManager(){
        try{
            $user = Auth::user();
            $projects = Project::with(['client', 'manager'])
                ->whereHas('manager', function ($q) use ($user) {
                    $q->where('user_id', $user->id);
                })
                ->orderByDesc('project_id')
                ->get();


            $data = $projects->map(function ($project) {
                return [
                    'id' => encrypt($project->project_id),
                    'name' => $project->project_name,
                    'client' => optional($project->client)->client_name,
                    'manager' => optional($project->manager)->manager_name ?? 'Unassigned',
                    'status' => ucfirst($project->status),
                    'start_date' => $project->start_date,
                    'end_date' => $project->estimated_end_date,
                ];
            });
            return response()->json([
                'result' => true,
                'data' => $data,
                'message' => 'Projects retrieved successfully.',
            ]);
        }catch (\Exception $e) {
            return response()->json([
                'result' => false,
                'message' => 'Error retrieving project list: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function viewProjectDetails($project_id){
        try{
            $projectId = decrypt($project_id);
            $project = Project::with(['client', 'manager'])->findOrFail($projectId);

            $data = [
                'project_id'           => encrypt($project->project_id),
                'project_name'         => $project->project_name,
                'project_description'  => $project->project_description,
                'status'               => $project->status,
                'start_date'           => $project->start_date,
                'estimated_end_date'   => $project->estimated_end_date,
                'client_name'          => $project->client->client_name ?? null,
                'manager_name'         => $project->manager->manager_name ?? null,
            ];

            return Inertia::render('projects/ProjectDetails', [
                'data' => $data,
            ]);
        }catch (\Exception $e) {
            return response()->json([
                'result' => false,
                'message' => 'An error occurred while retrieving the project: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function getProjectProgress($project_id){
        try{
            $projectId = decrypt($project_id);
            $project = Project::with(['client', 'manager', 'project_progress'])->findOrFail($projectId);
            $progress = $project->project_progress->map(function ($progress) {
                return [
                    'project_progress_id' => encrypt($progress->project_progress_id),
                    'progress_date' => $progress->progress_date,
                    'image_path' => $progress->image_path ? asset('storage/' . $progress->image_path): null,
                    'file_path' => $progress->file_path ? asset('storage/' . $progress->file_path) : null,
                    'progress_description' => $progress->progress_description,
                ];
            });

            return response()->json([
                'result' => true,
                'data' => $progress,
                'message' => 'Project progress retrieved successfully.',
            ]);
        }catch (\Exception $e) {
            return response()->json([
                'result' => false,
                'message' => 'An error occurred while retrieving the project progress: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function addProgress(Request $request){

        $request->validate([
            'project_id' => 'required|string',
            'progress_date' => 'required|date',
            'image_path' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', 
            'file_path' => 'nullable|file|mimes:pdf,doc,docx,zip|max:20480',
            'progress_description' => 'required|string',
        ]);

        try {
            $projectId = decrypt($request->input('project_id'));

            $imagePath = null;
            $filePath = null;

            try {
                if ($request->hasFile('file_path')) {
                    $file = $request->file('file_path');
                    $originalName = $file->getClientOriginalName();
                    $fileName = time() . '_' . $originalName; 
                    $filePath = $file->storeAs('project_files', $fileName, 'public');
                }

                if ($request->hasFile('image_path')) {
                    $image = $request->file('image_path');
                    $originalName = $image->getClientOriginalName();
                    $imageName = time() . '_' . $originalName; 
                    $imagePath = $image->storeAs('project_images', $imageName, 'public');
                }

            } catch (\Exception $e) {
                return response()->json([
                    'result' => false,
                    'message' => 'File upload failed: ' . $e->getMessage(),
                ], 500);
            }

            $project_progress = ProjectProgress::create([
                'project_id' => $projectId,
                'progress_date' => $request->input('progress_date'),
                'image_path' => $imagePath,
                'file_path' => $filePath,
                'progress_description' => $request->input('progress_description'),
            ]);

            $project = Project::with(['client', 'manager'])->find($projectId);
            $responses = [];
            $manager = $project->manager;
            if ($manager?->contact_information) {
                $managerMessage = sprintf(
                    "New progress has been added to the project '%s' on %s.",
                    $project->project_name,
                    $project_progress->progress_date
                );
                $responses['manager'] = $this->semaphore->sendSMS($manager->contact_information, $managerMessage);
            } else {
                Log::warning("Project Manager with ID {$project->manager_id} not found or missing contact number. SMS not sent.");
            }

            $client = $project->client;
            if ($client?->contact_information) {
                $clientMessage = sprintf(
                    "New progress has been added to your project '%s' on %s.",
                    $project->project_name,
                    $project_progress->progress_date
                );
                $responses['client'] = $this->semaphore->sendSMS($client->contact_information, $clientMessage);
            } else {
                Log::warning("Client with ID {$project->client_id} not found or missing contact number. SMS not sent.");
            }

            return response()->json([
                'result' => true,
                'message' => 'Project Progress added successfully.',
                'responses' => $responses,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'result' => false,
                'message' => 'Error adding project progress: ' . $e->getMessage(),
            ], 500);
        }
    }
}
