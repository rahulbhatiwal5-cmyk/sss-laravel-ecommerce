<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class AdminAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_active_admin_can_log_in(): void
    {
        $admin = $this->makeUser();

        $response = $this->post(route('admin.login.submit'), [
            'email' => $admin->email,
            'password' => 'password',
        ]);

        $response->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($admin, 'web');
    }

    public function test_login_requires_an_email_and_password(): void
    {
        $this->from(route('admin.login'))
            ->post(route('admin.login.submit'), [])
            ->assertRedirect(route('admin.login'))
            ->assertSessionHasErrors(['email', 'password']);
    }

    public function test_incorrect_customer_and_blocked_credentials_cannot_log_in_to_admin(): void
    {
        $wrongPasswordAdmin = $this->makeUser();
        $customer = $this->makeUser(['role' => 'customer']);
        $blockedAdmin = $this->makeUser(['status' => 'blocked']);

        $this->assertGenericLoginFailure($this->loginAttempt($wrongPasswordAdmin->email, 'incorrect-password'));
        $this->assertGuest('web');

        $this->assertGenericLoginFailure($this->loginAttempt($customer->email));
        $this->assertGuest('web');

        $this->assertGenericLoginFailure($this->loginAttempt($blockedAdmin->email));
        $this->assertGuest('web');
    }

    public function test_login_page_redirects_active_admins_and_denies_ineligible_users(): void
    {
        $admin = $this->makeUser();
        $customer = $this->makeUser(['role' => 'customer']);
        $blockedAdmin = $this->makeUser(['status' => 'blocked']);

        $this->actingAs($admin, 'web')
            ->get(route('admin.login'))
            ->assertRedirect(route('admin.dashboard'));

        $this->actingAs($customer, 'web')
            ->get(route('admin.login'))
            ->assertForbidden();

        $this->actingAs($blockedAdmin, 'web')
            ->get(route('admin.login'))
            ->assertForbidden();
    }

    public function test_admin_login_is_throttled_by_normalized_email_and_ip(): void
    {
        $email = 'RATE-LIMIT@EXAMPLE.TEST ';

        for ($attempt = 0; $attempt < 5; $attempt++) {
            $this->loginAttempt($email, 'incorrect-password');
        }

        $response = $this->loginAttempt($email, 'incorrect-password');

        $response->assertSessionHasErrors('email');
        $this->assertStringStartsWith(
            'Too many login attempts.',
            $response->getSession()->get('errors')->first('email'),
        );
        $this->assertGuest('web');
    }

    public function test_logout_clears_authentication_even_after_an_admin_is_blocked(): void
    {
        $admin = $this->makeUser();

        $this->actingAs($admin, 'web');
        User::query()->whereKey($admin)->update(['status' => 'blocked']);

        $this->post(route('admin.logout'))
            ->assertRedirect(route('admin.login'));

        $this->assertGuest('web');
        $this->get(route('admin.dashboard'))
            ->assertRedirect(route('admin.login'));
    }

    private function makeUser(array $attributes = []): User
    {
        return User::factory()->create(array_merge([
            'password' => 'password',
            'role' => 'admin',
            'status' => 'active',
        ], $attributes));
    }

    private function loginAttempt(string $email, string $password = 'password'): TestResponse
    {
        return $this->from(route('admin.login'))
            ->post(route('admin.login.submit'), [
                'email' => $email,
                'password' => $password,
            ]);
    }

    private function assertGenericLoginFailure(TestResponse $response): void
    {
        $response->assertRedirect(route('admin.login'));
        $response->assertSessionHasErrors('email');

        $this->assertSame(
            'Unable to sign in with these credentials.',
            $response->getSession()->get('errors')->first('email'),
        );
    }
}
