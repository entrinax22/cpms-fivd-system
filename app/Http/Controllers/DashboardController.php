<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Project;
use Illuminate\Http\Request;
use App\Models\ProjectProgress;

class DashboardController extends Controller
{
    public function index()
    {
        try {
            $activeProjectsCount = Project::where('status', 'in_progress')->count();
            $completedProjectsCount = Project::where('status', 'completed')->count();

            $employees = User::where('role', 'employee')->get();
            $managers = User::where('role', 'manager')->get();
            $engineers = User::where('role', 'engineer')->get();

            $projects = Project::with(['client', 'manager'])
                ->latest()
                ->take(5)
                ->get()
                ->map(fn($project) => [
                    'project_id' => $project->project_id,
                    'project_name' => $project->project_name,
                    'client_name' => $project->client->client_name ?? 'N/A',
                    'manager_name' => $project->manager->name ?? 'N/A',
                    'start_date' => $project->start_date,
                    'estimated_end_date' => $project->estimated_end_date,
                    'status' => $project->status,
                ]);

            $recentProgress = ProjectProgress::with('project')
                ->latest()
                ->take(5)
                ->get()
                ->map(fn($progress) => [
                    'project_progress_id' => encrypt($progress->project_progress_id),
                    'project_name' => $progress->project->project_name ?? 'N/A',
                    'progress_date' => $progress->progress_date,
                    'progress_description' => $progress->progress_description,
                ]);

            // ✅ Monthly Projects Count
            $monthlyProjects = Project::selectRaw('MONTH(start_date) as month, COUNT(*) as count')
                ->groupBy('month')
                ->pluck('count', 'month');

            // ✅ Status Distribution
            $statusDistribution = Project::selectRaw('status, COUNT(*) as count')
                ->groupBy('status')
                ->pluck('count', 'status');

            return response()->json([
                'result' => true,
                'activeProjectsCount' => $activeProjectsCount,
                'completedProjectsCount' => $completedProjectsCount,
                'projects' => $projects,
                'recentProgress' => $recentProgress,
                'employees' => $employees,
                'managers' => $managers,
                'engineers' => $engineers,
                'monthlyProjects' => $monthlyProjects,
                'statusDistribution' => $statusDistribution,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'result' => false,
                'message' => 'Error retrieving dashboard data: ' . $e->getMessage(),
            ], 500);
        }
    }


}
