<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\RequestedTool;

class RequestedToolsController extends Controller
{
    // public function index(){
    //     try{
    //         if(auth()->user()->role !== 'admin'){
    //             return redirect()->route('home');
    //         }
    //         return Inertia::render('requested_tools/RequestedToolsTable');
    //     }catch(\Exception $e){
    //         return redirect()->route('home')->with('error', 'An error occurred: ' . $e->getMessage());
    //     }
    // }

    // public function create(){
    //     try{
    //         if(auth()->user()->role !== 'admin'){
    //             return redirect()->route('home');
    //         }
    //         return Inertia::render('requested_tools/CreateRequestedTool');
    //     }catch(\Exception $e){
    //         return redirect()->route('home')->with('error', 'An error occurred: ' . $e->getMessage());
    //     }
    // }

    // public function store(Request $request){
    //     try{
    //         $validate = $request->validate([
    //             'project_id' => 'required|string',
    //             'tool_id' => 'nullable|string',
    //             'testing_tool_id' => 'nullable|string',
    //             'description' => 'required|string',
    //             'status' => 'required|in:pending,approved,denied',
    //         ]);

    //         $projectId = decrypt($validate['project_id']);
    //         $toolId = isset($validate['tool_id']) ? decrypt($validate['tool_id']) : null;
    //         $testingToolId = isset($validate['testing_tool_id']) ? decrypt($validate['testing_tool_id']) : null;

    //         RequestedTool::create([
    //             'user_id' => auth()->user()->id,
    //             'project_id' => $projectId,
    //             'tool_id' => $toolId,
    //             'testing_tool_id' => $testingToolId,
    //             'description' => $validate['description'],
    //             'status' => $validate['status'],
    //         ]);

    //         return response()->json([
    //             'result' => true,
    //             'message' => 'Requested tools added successfully.',
    //         ], 200);
    //     }catch(\Exception $e){
    //          return response()->json([
    //             'result' => false,
    //             'message' => 'Error adding the requested tools: ' . $e->getMessage(),
    //         ], 500);
    //     }
    // }
}
