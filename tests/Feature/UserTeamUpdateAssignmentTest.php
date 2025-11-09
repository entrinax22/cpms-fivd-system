<?php

use App\Models\User;
use App\Models\ProjectManager;
use App\Models\DevelopmentTeam;
use App\Models\TestingTeam;

it('updates a user teams: detach all when empty arrays sent and reassigns', function () {
    // Prepare manager and two teams
    $managerUser = User::factory()->create();

    $pm = ProjectManager::create([
        'user_id' => $managerUser->id,
        'manager_name' => 'Manager Update',
        'expertise_area' => 'Area',
        'contact_information' => 'm@example.com',
        'years_of_experience' => '3',
    ]);

    $devA = DevelopmentTeam::create([
        'manager_id' => $pm->manager_id,
        'team_name' => 'Dev A',
        'team_size' => 2,
        'specialization' => 'API',
    ]);

    $devB = DevelopmentTeam::create([
        'manager_id' => $pm->manager_id,
        'team_name' => 'Dev B',
        'team_size' => 4,
        'specialization' => 'Frontend',
    ]);

    $testA = TestingTeam::create([
        'manager_id' => $pm->manager_id,
        'team_name' => 'Test A',
        'team_size' => 1,
        'specialization' => 'Manual',
    ]);

    $testB = TestingTeam::create([
        'manager_id' => $pm->manager_id,
        'team_name' => 'Test B',
        'team_size' => 3,
        'specialization' => 'Automation',
    ]);

    // Create target user and assign initial teams via controller flow
    $admin = User::factory()->create(['role' => 'admin', 'must_change_password' => false]);
    $this->actingAs($admin);

    $createPayload = [
        'name' => 'Switch User',
        'email' => 'switch@example.com',
        'phone' => '09170000041',
        'role' => 'employee',
        'development_team_ids' => [encrypt($devA->team_id)],
        'testing_team_ids' => [encrypt($testA->testing_team_id)],
    ];

    $createResp = $this->post(route('admin.users.store'), $createPayload);
    $createResp->assertStatus(201);

    $user = User::where('email', 'switch@example.com')->firstOrFail();

    // Confirm initial pivots exist
    $this->assertDatabaseHas('development_team_user', ['user_id' => $user->id, 'team_id' => $devA->team_id]);
    $this->assertDatabaseHas('testing_team_user', ['user_id' => $user->id, 'testing_team_id' => $testA->testing_team_id]);

    // Now update the user sending empty arrays - should detach all
    $updatePayloadDetach = [
        'name' => $user->name,
        'email' => $user->email,
        'role' => $user->role,
        'development_team_ids' => [],
        'testing_team_ids' => [],
    ];

    $updateResp = $this->post(route('admin.users.update', ['id' => encrypt($user->id)]), $updatePayloadDetach);
    $updateResp->assertStatus(200);

    $this->assertDatabaseMissing('development_team_user', ['user_id' => $user->id, 'team_id' => $devA->team_id]);
    $this->assertDatabaseMissing('testing_team_user', ['user_id' => $user->id, 'testing_team_id' => $testA->testing_team_id]);

    // Now assign the user to different teams
    $updatePayloadAssign = [
        'name' => $user->name,
        'email' => $user->email,
        'role' => $user->role,
        'phone' => $user->phone,
        'development_team_ids' => [encrypt($devB->team_id)],
        'testing_team_ids' => [encrypt($testB->testing_team_id)],
    ];

    $updateResp2 = $this->post(route('admin.users.update', ['id' => encrypt($user->id)]), $updatePayloadAssign);
    $updateResp2->assertStatus(200);

    $this->assertDatabaseHas('development_team_user', ['user_id' => $user->id, 'team_id' => $devB->team_id]);
    $this->assertDatabaseHas('testing_team_user', ['user_id' => $user->id, 'testing_team_id' => $testB->testing_team_id]);
});
