<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;
use App\Models\ProjectProgress;
use Illuminate\Support\Facades\Storage;

class ProjectProgressController extends Controller
{
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

            return response()->json([
                'result' => true,
                'message' => 'Project Progress created successfully.',
            ], 201);
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

            return response()->json([
                'result' => true,
                'message' => 'Project progress updated successfully.',
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
