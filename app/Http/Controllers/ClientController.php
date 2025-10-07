<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    public function list(Request $request)
    {
        try {
            $search = $request->input('search');
            $perPage = $request->input('per_page', 10);
            $page = $request->input('page', 1);

            $query = Client::query()
                ->when($search, function ($q) use ($search) {
                    $q->where('client_name', 'like', "%{$search}%")
                    ->orWhere('contact_information', 'like', "%{$search}%")
                    ->orWhere('registration_date', 'like', "%{$search}%")
                    ->orWhere('client_type', 'like', "%{$search}%");
                })
                ->orderByDesc('client_id');

            $clients = $query->paginate($perPage, ['*'], 'page', $page);

            $data = $clients->getCollection()->map(function ($client) {
                return [
                    'client_id' => encrypt($client->client_id),
                    'client_name' => $client->client_name,
                    'contact_information' => $client->contact_information,
                    'registration_date' => $client->registration_date,
                    'client_type' => $client->client_type,
                    'created_at' => $client->created_at,
                    'updated_at' => $client->updated_at,
                ];
            });

            return response()->json([
                'result' => true,
                'data' => $data,
                'pagination' => [
                    'current_page' => $clients->currentPage(),
                    'per_page' => $clients->perPage(),
                    'total' => $clients->total(),
                    'last_page' => $clients->lastPage(),
                ],
                'message' => 'Clients retrieved successfully.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'result' => false,
                'message' => 'Error retrieving clients: ' . $e->getMessage(),
            ], 500);
        }
    }


    public function store(Request $request)
    {
        $request->validate([
            'client_name' => 'required|string|max:255',
            'contact_information' => 'required|string|max:255',
            'registration_date' => 'required|date',
            'client_type' => 'required|string|max:255',
        ]);

        try {
            Client::create([
                'client_name' => $request->input('client_name'),
                'contact_information' => $request->input('contact_information'),
                'registration_date' => $request->input('registration_date'),
                'client_type' => $request->input('client_type'),  
            ]);

            return response()->json([
                'result' => true,
                'message' => 'Client created successfully.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'result' => false,
                'message' => 'An error occurred while creating the project manager: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function edit($client_id)
    {
        try {
            $clientId = decrypt($client_id);
            $client = Client::findOrFail($clientId);

            return response()->json([
                'result' => true,
                'data' => [
                    'client_id' => encrypt($client->client_id),
                    'client_name' => $client->client_name,
                    'contact_information' => $client->contact_information,
                    'registration_date' => $client->registration_date,
                    'client_type' => $client->client_type,
                    'created_at' => $client->created_at,
                    'updated_at' => $client->updated_at,
                ],
                'message' => 'Client retrieved successfully.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'result' => false,
                'message' => 'An error occurred while retrieving the client: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function update(Request $request, $client_id)
    {
        $request->validate([
            'client_name' => 'required|string|max:255',
            'contact_information' => 'required|string|max:255',
            'registration_date' => 'required|date',
            'client_type' => 'required|string|max:255',
        ]);

        try {
            $clientId = decrypt($client_id);
            $client = Client::findOrFail($clientId);

            $client->update([
                'client_name' => $request->input('client_name'),
                'contact_information' => $request->input('contact_information'),
                'registration_date' => $request->input('registration_date'),
                'client_type' => $request->input('client_type'),  
            ]);

            return response()->json([
                'result' => true,
                'message' => 'Client updated successfully.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'result' => false,
                'message' => 'An error occurred while updating the client: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function destroy($client_id)
    {
        try {
            $clientId = decrypt($client_id);
            $client = Client::findOrFail($clientId);
            $client->delete();

            return response()->json([
                'result' => true,
                'message' => 'Client deleted successfully.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'result' => false,
                'message' => 'An error occurred while deleting the client: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function selectList(Request $request){
        try{
            $search = $request->search;
            $query = Client::query()
                ->when($search, function ($q) use ($search) {
                    $q->where('client_name', 'like', "%{$search}%")
                    ->orWhere('contact_information', 'like', "%{$search}%")
                    ->orWhere('registration_date', 'like', "%{$search}%");
                })
                ->orderByDesc('client_id');

            $clients = $query->get();

            $data = $clients->map(function ($client) {
                return [
                    'client_id' => encrypt($client->client_id),
                    'client_name' => $client->client_name,
                    'contact_information' => $client->contact_information,
                    'registration_date' => $client->registration_date,
                    'client_type' => $client->client_type,
                ];
            });

            return response()->json([
                'result' => true,
                'data' => $data,
                'message' => 'Clients retrieved successfully.',
            ]);
        }catch(\Exception $e){
            return response()->json([
                'result' => false,
                'message' => 'An error occurred while retrieving the clients: ' . $e->getMessage(),
            ]);
        }
    }
}
