<?php

use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Notifications\SendQueuedNotifications;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\URL;

test('email verification screen can be rendered', function () {
    $user = User::factory()->unverified()->create(['phone' => '5551234567']);

    $response = $this->actingAs($user)->get('/verify-email');

    $response->assertStatus(200);
});

test('unverified users can request another verification email', function () {
    $user = User::factory()->unverified()->create(['phone' => '5551234567']);

    Queue::fake();

    $response = $this
        ->actingAs($user)
        ->from('/verify-email')
        ->post(route('verification.send', absolute: false));

    $response
        ->assertRedirect('/verify-email')
        ->assertSessionHas('status', 'verification-link-sent');

    Queue::assertPushed(SendQueuedNotifications::class);
});

test('email can be verified', function () {
    $user = User::factory()->unverified()->create(['phone' => '5551234567']);

    Event::fake();

    $verificationUrl = URL::temporarySignedRoute(
        'verification.verify',
        now()->addMinutes(60),
        ['id' => $user->id, 'hash' => sha1($user->email)]
    );

    $response = $this->actingAs($user)->get($verificationUrl);

    Event::assertDispatched(Verified::class);
    expect($user->fresh()->hasVerifiedEmail())->toBeTrue();
    $response->assertRedirect(route('dashboard', absolute: false).'?verified=1');
});

test('email is not verified with invalid hash', function () {
    $user = User::factory()->unverified()->create(['phone' => '5551234567']);

    $verificationUrl = URL::temporarySignedRoute(
        'verification.verify',
        now()->addMinutes(60),
        ['id' => $user->id, 'hash' => sha1('wrong-email')]
    );

    $this->actingAs($user)->get($verificationUrl);

    expect($user->fresh()->hasVerifiedEmail())->toBeFalse();
});
