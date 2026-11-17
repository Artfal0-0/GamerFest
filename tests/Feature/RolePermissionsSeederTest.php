<?php

use App\Models\User;
use Database\Seeders\RolePermissionsSeeder;
use Illuminate\Support\Env;
use Illuminate\Support\Facades\Hash;

test('role seeder creates the configured admin idempotently', function () {
    $email = 'seed-test@example.invalid';
    $password = 'test-password-with-24-chars';
    $environment = Env::getRepository();
    $originalEmail = $environment->get('APP_ADMIN_INITIAL_EMAIL');
    $originalPassword = $environment->get('APP_ADMIN_INITIAL_PASSWORD');

    $environment->set('APP_ADMIN_INITIAL_EMAIL', $email);
    $environment->set('APP_ADMIN_INITIAL_PASSWORD', $password);

    try {
        $this->seed(RolePermissionsSeeder::class);
        $this->seed(RolePermissionsSeeder::class);

        $admin = User::query()->where('email', $email)->first();

        expect($admin)->not->toBeNull()
            ->and($admin->hasRole('admin'))->toBeTrue()
            ->and(Hash::check($password, $admin->password))->toBeTrue()
            ->and(User::query()->where('email', $email)->count())->toBe(1);
    } finally {
        $environment->set('APP_ADMIN_INITIAL_EMAIL', $originalEmail ?? '');
        $environment->set('APP_ADMIN_INITIAL_PASSWORD', $originalPassword ?? '');
    }
});
