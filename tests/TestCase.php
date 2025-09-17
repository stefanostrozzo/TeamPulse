<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\WithoutMiddleware;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication, RefreshDatabase, WithFaker, WithoutMiddleware;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Seed roles and permissions for testing
        $this->seed(\Database\Seeders\PermissionSeeder::class);

        // Disable CSRF middleware for tests to avoid 419 responses
        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);
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
        $this->assertInertia(fn ($page) => $page->component($component));
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