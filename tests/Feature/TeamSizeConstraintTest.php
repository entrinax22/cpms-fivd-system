<?php

use App\Models\User;
use App\Models\ProjectManager;
use App\Models\TestingTeam;
use App\Services\EncryptionService;

it('enforces team size limit when assigning users', function () {
    // Create manager
    $managerUser = User::factory()->create();
    $pm = ProjectManager::create([
        'user_id' => $managerUser->id,
        'manager_name' => 'Team Size Manager',
        'expertise_area' => 'Testing',
        'contact_information' => 'size@example.com',
        'years_of_experience' => '5',
    ]);

    // Create a testing team with size limit of 2
    $testTeam = TestingTeam::create([
        'manager_id' => $pm->manager_id,
        'team_name' => 'Limited QA Team',
        'team_size' => 2,
        'specialization' => 'Manual Testing',
    ]);

    // Create and authenticate admin
    $admin = User::factory()->create([
        'role' => 'admin',
        'must_change_password' => false,
    ]);
    $this->actingAs($admin);

    // Get encryption service instance
    $encryptionService = app(EncryptionService::class);
    $encryptedTeamId = $encryptionService->getEncryptedTestingTeamId($testTeam->testing_team_id);

    // Successfully add first user
    $response1 = $this->post(route('admin.users.store'), [
        'name' => 'Test User 1',
        'email' => 'test1@example.com',
        'phone' => '09170000031',
        'role' => 'employee',
        'testing_team_ids' => [$encryptedTeamId],
    ]);
    $response1->assertStatus(201);

    // Successfully add second user
    $response2 = $this->post(route('admin.users.store'), [
        'name' => 'Test User 2',
        'email' => 'test2@example.com',
        'phone' => '09170000032',
        'role' => 'employee',
        'testing_team_ids' => [$encryptedTeamId],
    ]);
    $response2->assertStatus(201);

    // Attempt to add third user - should fail due to team size limit
    $response3 = $this->post(route('admin.users.store'), [
        'name' => 'Test User 3',
        'email' => 'test3@example.com',
        'phone' => '09170000033',
        'role' => 'employee',
        'testing_team_ids' => [$encryptedTeamId],
    ]);
    
    // Should return 422 with validation error
    $response3->assertStatus(422)
        ->assertJsonValidationErrors(['testing_team_ids']);

    // Verify only 2 users are in the team
    expect(TestingTeam::find($testTeam->testing_team_id)->users()->count())->toBe(2);
});

it('allows updating user team assignments within size limits', function () {
    // Create manager
    $managerUser = User::factory()->create();
    $pm = ProjectManager::create([
        'user_id' => $managerUser->id,
        'manager_name' => 'Update Manager',
        'expertise_area' => 'Testing',
        'contact_information' => 'update@example.com',
        'years_of_experience' => '5',
    ]);

    // Create two testing teams
    $teamA = TestingTeam::create([
        'manager_id' => $pm->manager_id,
        'team_name' => 'Team A',
        'team_size' => 2,
        'specialization' => 'API Testing',
    ]);

    $teamB = TestingTeam::create([
        'manager_id' => $pm->manager_id,
        'team_name' => 'Team B',
        'team_size' => 1,
        'specialization' => 'Performance Testing',
    ]);

    // Create and authenticate admin
    $admin = User::factory()->create([
        'role' => 'admin',
        'must_change_password' => false,
    ]);
    $this->actingAs($admin);

    // Get encryption service instance
    $encryptionService = app(EncryptionService::class);
    $encryptedTeamAId = $encryptionService->getEncryptedTestingTeamId($teamA->testing_team_id);
    $encryptedTeamBId = $encryptionService->getEncryptedTestingTeamId($teamB->testing_team_id);

    // Create initial user in Team A
    $createResponse = $this->post(route('admin.users.store'), [
        'name' => 'Move User',
        'email' => 'move@example.com',
        'phone' => '09170000034',
        'role' => 'employee',
        'testing_team_ids' => [$encryptedTeamAId],
    ]);
    $createResponse->assertStatus(201);

    $user = User::where('email', 'move@example.com')->firstOrFail();
    $encryptedUserId = $encryptionService->getEncryptedUserId($user->id);

    // Fill Team B to capacity
    $this->post(route('admin.users.store'), [
        'name' => 'Static User',
        'email' => 'static@example.com',
        'phone' => '09170000035',
        'role' => 'employee',
        'testing_team_ids' => [$encryptedTeamBId],
    ]);

    // Try to move user to Team B - should fail due to size limit
    $updateResponse = $this->post(route('admin.users.update', ['id' => $encryptedUserId]), [
        'name' => 'Move User',
        'email' => 'move@example.com',
        'role' => 'employee',
        'testing_team_ids' => [$encryptedTeamBId],
    ]);

    $updateResponse->assertStatus(422)
        ->assertJsonValidationErrors(['testing_team_ids']);

    // Verify user remains in Team A
    expect($user->testingTeams()->first()->testing_team_id)->toBe($teamA->testing_team_id);
});