<?php

use App\Models\User;
use App\Models\ProjectManager;
use App\Models\DevelopmentTeam;
use App\Models\TestingTeam;

it('creates a user and assigns them to development and testing teams', function () {
    // create a user to be the project manager
    $managerUser = User::factory()->create();

    $pm = ProjectManager::create([
        'user_id' => $managerUser->id,
        'manager_name' => 'Test Manager',
        'expertise_area' => 'Testing',
        'contact_information' => 'test@example.com',
        'years_of_experience' => '5',
    ]);

    $devTeam = DevelopmentTeam::create([
        'manager_id' => $pm->manager_id,
        'team_name' => 'Dev Team A',
        'team_size' => 3,
        'specialization' => 'Backend',
    ]);

    $testTeam = TestingTeam::create([
        'manager_id' => $pm->manager_id,
        'team_name' => 'QA Team A',
        'team_size' => 2,
        'specialization' => 'Automation',
    ]);

    $payload = [
        'name' => 'Assigned User',
        'email' => 'assigned@example.com',
        'phone' => '09170000021',
        'role' => 'employee',
        'development_team_ids' => [encrypt($devTeam->team_id)],
        'testing_team_ids' => [encrypt($testTeam->testing_team_id)],
    ];

    // create and authenticate an admin so middleware allows access
    $admin = User::factory()->create([
        'role' => 'admin',
        'must_change_password' => false,
    ]);

    $this->actingAs($admin);

    $response = $this->post(route('admin.users.store'), $payload);

    $response->assertStatus(201);

    $this->assertDatabaseHas('users', ['email' => 'assigned@example.com']);

    $user = User::where('email', 'assigned@example.com')->first();
    expect($user)->not->toBeNull();

    $this->assertDatabaseHas('development_team_user', [
        'user_id' => $user->id,
        'team_id' => $devTeam->team_id,
    ]);

    $this->assertDatabaseHas('testing_team_user', [
        'user_id' => $user->id,
        'testing_team_id' => $testTeam->testing_team_id,
    ]);
});
