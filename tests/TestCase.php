<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication, DatabaseTransactions, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed roles and permissions for testing
        $this->seed(\Database\Seeders\PermissionSeeder::class);

        // Disable only CSRF validation — NOT all middleware.
        // Using WithoutMiddleware trait would also disable SubstituteBindings,
        // breaking route model binding (Team $team, User $user → raw IDs).
        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class);
    }

    /**
     * Create an authenticated user with specific role
     */
    protected function createUserWithRole(string $role = 'user', array $attributes = []): \App\Models\User
    {
        $user = \App\Models\User::factory()->create($attributes);
        $user->assignRole($role);
        return $user;
    }

    /**
     * Create an admin user with management permissions
     */
    protected function createAdminUser(array $attributes = []): \App\Models\User
    {
        $user = $this->createUserWithRole('admin', $attributes);
        $user->givePermissionTo('manage-users');
        return $user;
    }

    /**
     * Assert that the response is an Inertia response with specific component
     */
    protected function assertInertiaComponent(string $component): void
    {
        $this->assertInertia(fn($page) => $page->component($component));
    }

    /**
     * Assert that the response has specific session data
     */
    protected function assertSessionHasSuccess(string $message): void
    {
        $this->assertSessionHas('success', $message);
    }

    /**
     * Assert that the response has specific session errors
     */
    protected function assertSessionHasErrors(array $fields): void
    {
        foreach ($fields as $field) {
            $this->assertSessionHasErrors([$field]);
        }
    }
}