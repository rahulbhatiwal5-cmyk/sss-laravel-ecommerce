<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAccessControlTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_admin_login_for_every_protected_admin_page(): void
    {
        foreach ($this->protectedAdminUrls() as $url) {
            $this->get($url)->assertRedirect(route('admin.login'));
        }
    }

    public function test_active_admin_can_access_the_dashboard(): void
    {
        $admin = $this->makeUser();

        $this->actingAs($admin, 'web')
            ->get(route('admin.dashboard'))
            ->assertOk();
    }

    public function test_authenticated_customer_is_denied_admin_access(): void
    {
        $customer = $this->makeUser(['role' => 'customer']);

        $this->actingAs($customer, 'web')
            ->get(route('admin.dashboard'))
            ->assertForbidden();
    }

    public function test_blocking_or_demoting_an_authenticated_admin_denies_the_next_protected_request(): void
    {
        $blockedAdmin = $this->makeUser();
        $this->actingAs($blockedAdmin, 'web');
        User::query()->whereKey($blockedAdmin)->update(['status' => 'blocked']);

        $this->get(route('admin.dashboard'))->assertForbidden();

        $demotedAdmin = $this->makeUser();
        $this->actingAs($demotedAdmin, 'web');
        User::query()->whereKey($demotedAdmin)->update(['role' => 'customer']);

        $this->get(route('admin.dashboard'))->assertForbidden();
    }

    /**
     * @return array<int, string>
     */
    private function protectedAdminUrls(): array
    {
        return [
            route('admin.dashboard'),
            route('admin.products.index'),
            route('admin.products.create'),
            route('admin.products.edit', 1),
            route('admin.categories.index'),
            route('admin.orders.index'),
            route('admin.orders.show', 'SSS-1048'),
            route('admin.customers.index'),
            route('admin.settings'),
        ];
    }

    private function makeUser(array $attributes = []): User
    {
        return User::factory()->create(array_merge([
            'password' => 'password',
            'role' => 'admin',
            'status' => 'active',
        ], $attributes));
    }
}
