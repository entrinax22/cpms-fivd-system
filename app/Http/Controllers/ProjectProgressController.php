<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\Project;
use Illuminate\Http\Request;
use App\Models\ProjectProgress;
use App\Services\SemaphoreService;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Illuminate\Contracts\Encryption\DecryptException;

class ProjectProgressController extends Controller
{   

    protected $semaphore;

    public function __construct(SemaphoreService $semaphore)
    {
        $this->semaphore = $semaphore;
    }
    public function index(){
        try{
            if(auth()->user()->role !== 'admin'){
                return redirect()->route('home');
            }
            return Inertia::render('project_progress/ProjectProgressTable');
        }catch(\Exception $e){
            return redirect()->route('home')->with('error', 'An error occurred: ' . $e->getMessage());
        }
    }

    public function create(){
        try{
            if(auth()->user()->role !== 'admin'){
                return redirect()->route('home');
            }
            return Inertia::render('project_progress/CreateProjectProgress');
        }catch(\Exception $e){
            return redirect()->route('home')->with('error', 'An error occurred: ' . $e->getMessage());
        }
    }

    public function list(Request $request)
    {
        try {
            $search = $request->input('search', '');
            $perPage = $request->input('per_page', 10);
            $page = $request->input('page', 1);

            $query = ProjectProgress::with('project')
                ->when($search, function ($q) use ($search) {
                    $q->where('progress_description', 'like', '%' . $search . '%')
                    ->orWhereHas('project', function ($q2) use ($search) {
                        $q2->where('project_name', 'like', '%' . $search . '%');
                    });
                });

            $project_progress = $query->orderByDesc('progress_date')
                                    ->paginate($perPage, ['*'], 'page', $page);

            $data = $project_progress->getCollection()->map(function ($progress) {
                return [
                    'project_progress_id' => encrypt($progress->project_progress_id),
                    'project_name' => $progress->project?->project_name,
                    'progress_date' => $progress->progress_date,
                    'image_path' => $progress->image_path ? asset('storage/' . $progress->image_path) : null,
                    'file_path' => $progress->file_path ? asset('storage/' . $progress->file_path) : null,
                    'progress_description' => $progress->progress_description,
                    'created_at' => $progress->created_at->format('Y-m-d H:i:s'),
                    'updated_at' => $progress->updated_at->format('Y-m-d H:i:s'),
                ];
            });

            return response()->json([
                'result' => true,
                'data' => $data,
                'pagination' => [
                    'current_page' => $project_progress->currentPage(),
                    'per_page' => $project_progress->perPage(),
                    'total' => $project_progress->total(),
                    'last_page' => $project_progress->lastPage(),
                ],
                'message' => 'Project progress retrieved successfully.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'result' => false,
                'message' => 'Error retrieving project progress list: ' . $e->getMessage(),
            ], 500);
        }
    }


    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'project_id' => 'required|string',
                'progress_date' => 'required|date',
                'image_path' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'file_path' => 'nullable|file|mimes:pdf,doc,docx,zip|max:20480',
                'progress_description' => 'required|string',
            ]);

            try {
                $projectId = decrypt($validated['project_id']);
            } catch (DecryptException $e) {
                return response()->json([
                    'result' => false,
                    'message' => 'Invalid project ID provided.',
                    'errors' => ['project_id' => ['The project ID is invalid.']],
                ], 422);
            }
            
            $imagePath = null;
            $filePath = null;

            // Handle file uploads
            if ($request->hasFile('file_path')) {
                $file = $request->file('file_path');
                $fileName = time() . '_' . $file->getClientOriginalName();
                $filePath = $file->storeAs('project_files', $fileName, 'public');
            }

            if ($request->hasFile('image_path')) {
                $image = $request->file('image_path');
                $imageName = time() . '_' . $image->getClientOriginalName();
                $imagePath = $image->storeAs('project_images', $imageName, 'public');
            }

            $project_progress = ProjectProgress::create([
                'project_id' => $projectId,
                'progress_date' => $validated['progress_date'],
                'image_path' => $imagePath,
                'file_path' => $filePath,
                'progress_description' => $validated['progress_description'],
            ]);

            // SMS notifications
            $project = Project::with(['client', 'manager'])->find($projectId);
            $responses = [];

            if ($project?->manager?->contact_information) {
                $managerMessage = sprintf(
                    "New progress has been added to the project '%s' on %s.",
                    $project->project_name,
                    $project_progress->progress_date
                );
                $responses['manager'] = $this->semaphore->sendSMS(
                    $project->manager->contact_information,
                    $managerMessage
                );
            }

            if ($project?->client?->contact_information) {
                $clientMessage = sprintf(
                    "New progress has been added to your project '%s' on %s.",
                    $project->project_name,
                    $project_progress->progress_date
                );
                $responses['client'] = $this->semaphore->sendSMS(
                    $project->client->contact_information,
                    $clientMessage
                );
            }

            return response()->json([
                'result' => true,
                'message' => 'Project progress created successfully.',
                'responses' => $responses,
            ], 201);

        } catch (ValidationException $e) {
            return response()->json([
                'result' => false,
                'message' => 'Validation failed.',
                'errors' => $e->errors(),
            ], 422);

        } catch (\Exception $e) {
            return response()->json([
                'result' => false,
                'message' => 'Error creating project progress: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function edit($project_progress_id)
    {
        try {
            $project_progress_id = decrypt($project_progress_id);
            $project_progress = ProjectProgress::with('project')->findOrFail($project_progress_id);

            // Fix: Make sure pluck creates a proper ID => encrypted ID map
            $encryptedProjectID = Project::pluck('project_id', 'project_id')->map(function ($id) {
                return encrypt($id);
            });

            $data = [
                'project_progress_id' => encrypt($project_progress->project_progress_id),
                'project_id' => $encryptedProjectID[$project_progress->project_id] ?? null,
                'project_name' => $project_progress->project?->project_name,
                'progress_date' => $project_progress->progress_date,
                'image_path' => $project_progress->image_path ? asset('storage/' . $project_progress->image_path) : null,
                'file_path' => $project_progress->file_path ? asset('storage/' . $project_progress->file_path) : null,
                'progress_description' => $project_progress->progress_description,
                'created_at' => $project_progress->created_at->format('Y-m-d H:i:s'),
                'updated_at' => $project_progress->updated_at->format('Y-m-d H:i:s'),
            ];

            $projects = Project::select('project_id', 'project_name', 'client_id')
                ->with('client:client_id,client_name')
                ->get()
                ->map(function ($project) use ($encryptedProjectID) {
                    return [
                        'project_id' => $encryptedProjectID[$project->project_id] ?? null,
                        'project_name' => $project->project_name,
                        'client_name' => $project->client?->client_name,
                    ];
                });

            return response()->json([
                'result' => true,
                'data' => $data,
                'projects' => $projects,
                'message' => 'Project Progress retrieved successfully.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'result' => false,
                'message' => 'An error occurred while retrieving the project progress: ' . $e->getMessage(),
            ], 500);
        }
    }

    
    public function update(Request $request, $project_progress_id)
    {
        try {
            $project_progress_id = decrypt($project_progress_id);
            $projectProgress = ProjectProgress::findOrFail($project_progress_id);

            $request->validate([
                'project_id' => 'required|string',
                'progress_date' => 'required|date',
                'progress_description' => 'required|string',
                'image_path' => 'nullable|file|mimes:jpeg,png,jpg,gif|max:2048',
                'file_path' => 'nullable|file|mimes:pdf,doc,docx,zip|max:20480',
            ]);

            $projectId = decrypt($request->input('project_id'));

            // Preserve existing paths
            $imagePath = $projectProgress->image_path;
            $filePath = $projectProgress->file_path;

            // Handle image upload
            if ($request->hasFile('image_path')) {
                // Delete old image if exists
                if ($imagePath && Storage::disk('public')->exists($imagePath)) {
                    Storage::disk('public')->delete($imagePath);
                }

                // Save new image with original name
                $image = $request->file('image_path');
                $imagePath = $image->storeAs(
                    'project_images',
                    time() . '_' . $image->getClientOriginalName(),
                    'public'
                );
            }

            // Handle file upload
            if ($request->hasFile('file_path')) {
                // Delete old file if exists
                if ($filePath && Storage::disk('public')->exists($filePath)) {
                    Storage::disk('public')->delete($filePath);
                }

                // Save new file with original name
                $file = $request->file('file_path');
                $filePath = $file->storeAs(
                    'project_files',
                    time() . '_' . $file->getClientOriginalName(),
                    'public'
                );
            }

            // Update DB record
            $projectProgress->update([
                'project_id' => $projectId,
                'progress_date' => $request->input('progress_date'),
                'progress_description' => $request->input('progress_description'),
                'image_path' => $imagePath,
                'file_path' => $filePath,
            ]);

            $project = Project::with(['client', 'manager'])->find($projectId);
            $responses = [];
            $manager = $project->manager;
            if ($manager?->contact_information) {
                $managerMessage = sprintf(
                    "A new progress update was added to the project '%s' on %s.",
                    $project->project_name,
                    $projectProgress->progress_date
                );
                $responses['manager'] = $this->semaphore->sendSMS($manager->contact_information, $managerMessage);
            } else {
                Log::warning("Project Manager with ID {$project->manager_id} not found or missing contact number. SMS not sent.");
            }

            $client = $project->client;
            if ($client?->contact_information) {
                $clientMessage = sprintf(
                    "A new progress update has been added to your project '%s' on %s.",
                    $project->project_name,
                    $projectProgress->progress_date
                );
                $responses['client'] = $this->semaphore->sendSMS($client->contact_information, $clientMessage);
            } else {
                Log::warning("Client with ID {$project->client_id} not found or missing contact number. SMS not sent.");
            }

            return response()->json([
                'result' => true,
                'message' => 'Project progress updated successfully.',
                'responses' => $responses,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'result' => false,
                'message' => 'Error updating project progress: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function destroy($project_progress_id)
    {
        try {
            $project_progress_id = decrypt($project_progress_id);
            $projectProgress = ProjectProgress::findOrFail($project_progress_id);

            // Delete associated files if they exist
            if ($projectProgress->image_path && Storage::disk('public')->exists($projectProgress->image_path)) {
                Storage::disk('public')->delete($projectProgress->image_path);
            }
            if ($projectProgress->file_path && Storage::disk('public')->exists($projectProgress->file_path)) {
                Storage::disk('public')->delete($projectProgress->file_path);
            }

            $projectProgress->delete();

            return response()->json([
                'result' => true,
                'message' => 'Project progress deleted successfully.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'result' => false,
                'message' => 'Error deleting project progress: ' . $e->getMessage(),
            ], 500);
        }
    }
}
