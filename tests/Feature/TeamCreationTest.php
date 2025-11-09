<?php

use App\Models\User;
use App\Models\ProjectManager;
use App\Models\DevelopmentTeam;
use App\Models\TestingTeam;
use App\Services\EncryptionService;

it('creates a development team with valid data', function () {
    // Create and authenticate an admin
    $admin = User::factory()->create([
        'role' => 'admin',
        'must_change_password' => false,
    ]);
    $this->actingAs($admin);

    // Create a project manager
    $managerUser = User::factory()->create();
    $pm = ProjectManager::create([
        'user_id' => $managerUser->id,
        'manager_name' => 'Dev Manager',
        'expertise_area' => 'Full Stack',
        'contact_information' => 'dev.manager@example.com',
        'years_of_experience' => '8',
    ]);

    $encryptionService = app(EncryptionService::class);
    $encryptedManagerId = $encryptionService->getEncryptedManagerId($pm->manager_id);

    // Test creating a development team
    $response = $this->post(route('admin.development-teams.store'), [
        'team_name' => 'Frontend Team',
        'team_size' => 5,
        'specialization' => 'React Development',
        'manager_id' => $encryptedManagerId,
    ]);

    $response->assertStatus(201)
        ->assertJson([
            'result' => true,
            'message' => 'Development team created successfully.',
        ]);

    $this->assertDatabaseHas('development_teams', [
        'team_name' => 'Frontend Team',
        'team_size' => 5,
        'specialization' => 'React Development',
        'manager_id' => $pm->manager_id,
    ]);
});

it('creates a testing team with valid data', function () {
    // Create and authenticate an admin
    $admin = User::factory()->create([
        'role' => 'admin',
        'must_change_password' => false,
    ]);
    $this->actingAs($admin);

    // Create a project manager
    $managerUser = User::factory()->create();
    $pm = ProjectManager::create([
        'user_id' => $managerUser->id,
        'manager_name' => 'QA Manager',
        'expertise_area' => 'Quality Assurance',
        'contact_information' => 'qa.manager@example.com',
        'years_of_experience' => '6',
    ]);

    $encryptionService = app(EncryptionService::class);
    $encryptedManagerId = $encryptionService->getEncryptedManagerId($pm->manager_id);

    // Test creating a testing team
    $response = $this->post(route('admin.testing-teams.store'), [
        'team_name' => 'QA Automation Team',
        'team_size' => 3,
        'specialization' => 'Selenium Testing',
        'manager_id' => $encryptedManagerId,
    ]);

    $response->assertStatus(201)
        ->assertJson([
            'result' => true,
            'message' => 'Testing Team created successfully.',
        ]);

    $this->assertDatabaseHas('testing_teams', [
        'team_name' => 'QA Automation Team',
        'team_size' => 3,
        'specialization' => 'Selenium Testing',
        'manager_id' => $pm->manager_id,
    ]);
});

it('validates required fields for development team creation', function () {
    // Create and authenticate an admin
    $admin = User::factory()->create([
        'role' => 'admin',
        'must_change_password' => false,
    ]);
    $this->actingAs($admin);

    // Test with missing required fields
    $response = $this->post(route('admin.development-teams.store'), []);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['team_name', 'team_size', 'manager_id']);
});

it('validates required fields for testing team creation', function () {
    // Create and authenticate an admin
    $admin = User::factory()->create([
        'role' => 'admin',
        'must_change_password' => false,
    ]);
    $this->actingAs($admin);

    // Test with missing required fields
    $response = $this->post(route('admin.testing-teams.store'), []);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['team_name', 'team_size', 'manager_id']);
});

it('validates team size is positive for development team', function () {
    // Create and authenticate an admin
    $admin = User::factory()->create([
        'role' => 'admin',
        'must_change_password' => false,
    ]);
    $this->actingAs($admin);

    // Create a project manager
    $managerUser = User::factory()->create();
    $pm = ProjectManager::create([
        'user_id' => $managerUser->id,
        'manager_name' => 'Dev Manager',
        'expertise_area' => 'Backend',
        'contact_information' => 'backend.manager@example.com',
        'years_of_experience' => '5',
    ]);

    $encryptionService = app(EncryptionService::class);
    $encryptedManagerId = $encryptionService->getEncryptedManagerId($pm->manager_id);

    // Test with invalid team size
    $response = $this->post(route('admin.development-teams.store'), [
        'team_name' => 'Invalid Team',
        'team_size' => 0,
        'specialization' => 'Backend',
        'manager_id' => $encryptedManagerId,
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['team_size']);
});

it('validates team size is positive for testing team', function () {
    // Create and authenticate an admin
    $admin = User::factory()->create([
        'role' => 'admin',
        'must_change_password' => false,
    ]);
    $this->actingAs($admin);

    // Create a project manager
    $managerUser = User::factory()->create();
    $pm = ProjectManager::create([
        'user_id' => $managerUser->id,
        'manager_name' => 'QA Manager',
        'expertise_area' => 'Security Testing',
        'contact_information' => 'security.qa@example.com',
        'years_of_experience' => '7',
    ]);

    $encryptionService = app(EncryptionService::class);
    $encryptedManagerId = $encryptionService->getEncryptedManagerId($pm->manager_id);

    // Test with invalid team size
    $response = $this->post(route('admin.testing-teams.store'), [
        'team_name' => 'Invalid Team',
        'team_size' => -1,
        'specialization' => 'Security Testing',
        'manager_id' => $encryptedManagerId,
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['team_size']);
});

it('prevents duplicate team names for development teams', function () {
    // Create and authenticate an admin
    $admin = User::factory()->create([
        'role' => 'admin',
        'must_change_password' => false,
    ]);
    $this->actingAs($admin);

    // Create a project manager
    $managerUser = User::factory()->create();
    $pm = ProjectManager::create([
        'user_id' => $managerUser->id,
        'manager_name' => 'Dev Lead',
        'expertise_area' => 'Full Stack',
        'contact_information' => 'lead@example.com',
        'years_of_experience' => '10',
    ]);

    $encryptionService = app(EncryptionService::class);
    $encryptedManagerId = $encryptionService->getEncryptedManagerId($pm->manager_id);

    // Create first team
    $teamName = 'Unique Dev Team';
    $this->post(route('admin.development-teams.store'), [
        'team_name' => $teamName,
        'team_size' => 3,
        'specialization' => 'Mobile Development',
        'manager_id' => $encryptedManagerId,
    ]);

    // Try to create second team with same name
    $response = $this->post(route('admin.development-teams.store'), [
        'team_name' => $teamName,
        'team_size' => 4,
        'specialization' => 'Web Development',
        'manager_id' => $encryptedManagerId,
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['team_name']);
});

it('prevents duplicate team names for testing teams', function () {
    // Create and authenticate an admin
    $admin = User::factory()->create([
        'role' => 'admin',
        'must_change_password' => false,
    ]);
    $this->actingAs($admin);

    // Create a project manager
    $managerUser = User::factory()->create();
    $pm = ProjectManager::create([
        'user_id' => $managerUser->id,
        'manager_name' => 'QA Lead',
        'expertise_area' => 'Test Automation',
        'contact_information' => 'qa.lead@example.com',
        'years_of_experience' => '8',
    ]);

    $encryptionService = app(EncryptionService::class);
    $encryptedManagerId = $encryptionService->getEncryptedManagerId($pm->manager_id);

    // Create first team
    $teamName = 'Unique QA Team';
    $this->post(route('admin.testing-teams.store'), [
        'team_name' => $teamName,
        'team_size' => 3,
        'specialization' => 'API Testing',
        'manager_id' => $encryptedManagerId,
    ]);

    // Try to create second team with same name
    $response = $this->post(route('admin.testing-teams.store'), [
        'team_name' => $teamName,
        'team_size' => 4,
        'specialization' => 'Performance Testing',
        'manager_id' => $encryptedManagerId,
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['team_name']);
});