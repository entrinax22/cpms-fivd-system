<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ProjectManager;

class ProjectManagerController extends Controller
{
    public function list(Request $request)
    {
        try {
            $search = $request->input('search');
            $perPage = $request->input('per_page', 10);
            $page = $request->input('page', 1);

            $query = ProjectManager::query()
                ->when($search, function ($q) use ($search) {
                    $q->where('manager_name', 'like', "%{$search}%")
                    ->orWhere('expertise_area', 'like', "%{$search}%")
                    ->orWhere('contact_information', 'like', "%{$search}%");
                })
                ->orderByDesc('id');

            $managers = $query->paginate($perPage, ['*'], 'page', $page);

            $data = $managers->getCollection()->map(function ($manager) {
                return [
                    'manager_id' => encrypt($manager->manager_id),
                    'manager_name' => $manager->manager_name,
                    'expertise_area' => $manager->expertise_area,
                    'contact_information' => $manager->contact_information,
                    'years_of_experience' => $manager->years_of_experience,
                ];
            });

            return response()->json([
                'result' => true,
                'data' => $data,
                'pagination' => [
                    'current_page' => $managers->currentPage(),
                    'per_page' => $managers->perPage(),
                    'total' => $managers->total(),
                    'last_page' => $managers->lastPage(),
                ],
                'message' => 'Project Managers retrieved successfully.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'result' => false,
                'message' => 'Error retrieving project managers: ' . $e->getMessage(),
            ], 500);
        }
    }


    public function store(Request $request)
    {
        $request->validate([
            'manager_name' => 'required|string|max:255',
            'expertise_area' => 'required|string|max:255',
            'contact_information' => 'required|string|max:255',
            'years_of_experience' => 'required|integer|min:0',
        ]);

        try {
            ProjectManager::create([
                'manager_name' => $request->input('manager_name'),
                'expertise_area' => $request->input('expertise_area'),
                'contact_information' => $request->input('contact_information'),
                'years_of_experience' => $request->input('years_of_experience'),
            ]);

            return response()->json([
                'result' => true,
                'message' => 'Project Manager created successfully.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'result' => false,
                'message' => 'An error occurred while creating the project manager: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function edit($id)
    {
        try {
            $managerId = decrypt($id);
            $manager = ProjectManager::findOrFail($managerId);

            return response()->json([
                'result' => true,
                'data' => [
                    'id' => encrypt($manager->manager_id),
                    'manager_name' => $manager->manager_name,
                    'expertise_area' => $manager->expertise_area,
                    'contact_information' => $manager->contact_information,
                    'years_of_experience' => $manager->years_of_experience,
                ],
                'message' => 'Project Manager retrieved successfully.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'result' => false,
                'message' => 'An error occurred while retrieving the project manager: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function update(Request $request, $manager_id)
    {
        $request->validate([
            'manager_name' => 'required|string|max:255',
            'expertise_area' => 'required|string|max:255',
            'contact_information' => 'required|string|max:255',
            'years_of_experience' => 'required|integer|min:0',
        ]);

        try {
            $managerId = decrypt($manager_id);
            $manager = ProjectManager::findOrFail($managerId);

            $manager->manager_name = $request->input('manager_name');
            $manager->expertise_area = $request->input('expertise_area');
            $manager->contact_information = $request->input('contact_information');
            $manager->years_of_experience = $request->input('years_of_experience');
            $manager->save();

            return response()->json([
                'result' => true,
                'message' => 'Project Manager updated successfully.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'result' => false,
                'message' => 'An error occurred while updating the project manager: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function destroy($manager_id)
    {
        try {
            $managerId = decrypt($manager_id);
            $manager = ProjectManager::findOrFail($managerId);
            $manager->delete();

            return response()->json([
                'result' => true,
                'message' => 'Project Manager deleted successfully.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'result' => false,
                'message' => 'An error occurred while deleting the project manager: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function selectList(Request $request){
        try{
            $search = $request->search;
            $query = ProjectManager::query()
                ->when($search, function ($q) use ($search) {
                    $q->where('manager_name', 'like', "%{$search}%")
                    ->orWhere('expertise_area', 'like', "%{$search}%")
                    ->orWhere('contact_information', 'like', "%{$search}%");
                })
                ->orderByDesc('manager_id');

            $projectManagers = $query->get();

            $data = $projectManagers->map(function ($manager) {
                return [
                    'manager_id' => encrypt($manager->manager_id),
                    'manager_name' => $manager->manager_name,
                    'expertise_area' => $manager->expertise_area,
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
