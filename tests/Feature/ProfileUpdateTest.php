<?php

use App\Models\User;

it('allows authenticated user to update profile', function () {
    $user = User::factory()->create(['name' => 'Old Name', 'email' => 'old@example.com', 'phone' => '09170000001']);
    $this->actingAs($user);

    $resp = $this->post(route('profile.api.update'), [
        'name' => 'New Name',
        'email' => 'new@example.com',
        'phone' => '09170000002',
    ]);

    $resp->assertStatus(200)->assertJson(['result' => true]);

    $user->refresh();
    expect($user->name)->toBe('New Name');
    expect($user->email)->toBe('new@example.com');
    expect($user->phone)->toBe('09170000002');
});

it('validates uniqueness of email and phone on profile update', function () {
    $userA = User::factory()->create(['email' => 'a@example.com', 'phone' => '09170000011']);
    $userB = User::factory()->create(['email' => 'b@example.com', 'phone' => '09170000012']);

    $this->actingAs($userA);

    $resp = $this->post(route('profile.api.update'), [
        'name' => 'A',
        'email' => 'b@example.com', // taken
        'phone' => '09170000012', // taken
    ]);

    $resp->assertStatus(422)->assertJson(['result' => false]);
});
