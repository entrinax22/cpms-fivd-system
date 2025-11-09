<?php

use App\Models\User;
use App\Services\OtpService;
use Illuminate\Support\Facades\Cache;

it('sends an otp to a user phone and resets password with correct otp', function () {
    $user = User::factory()->create(['phone' => '09171234567', 'password' => bcrypt('oldpassword')]);

    // Request OTP
    $resp = $this->post(route('forgot.request'), ['phone' => '09171234567']);
    $resp->assertStatus(200)->assertJson(['result' => true]);

    // Read OTP from cache
    $otp = Cache::get('otp_phone_09171234567');
    expect($otp)->not->toBeNull();

    // Reset password using OTP
    $resetResp = $this->post(route('forgot.reset'), [
        'phone' => '09171234567',
        'otp' => $otp,
        'password' => 'newpassword',
        'password_confirmation' => 'newpassword',
    ]);

    $resetResp->assertStatus(200)->assertJson(['result' => true]);

    $user->refresh();
    expect(password_verify('newpassword', $user->password))->toBeTrue();
});

it('rejects invalid otp attempts', function () {
    $user = User::factory()->create(['phone' => '09177654321', 'password' => bcrypt('oldpassword')]);

    $this->post(route('forgot.request'), ['phone' => '09177654321'])->assertStatus(200);

    // Attempt reset with wrong otp
    $resetResp = $this->post(route('forgot.reset'), [
        'phone' => '09177654321',
        'otp' => '000000',
        'password' => 'newpassword',
        'password_confirmation' => 'newpassword',
    ]);

    $resetResp->assertStatus(422)->assertJson(['result' => false]);
});
