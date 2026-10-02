<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminPanelTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_open_services_resource(): void
    {
        $admin = User::factory()->create();

        $this->actingAs($admin)
            ->get('/admin/services')
            ->assertOk();
    }

    public function test_admin_can_open_doctors_resource(): void
    {
        $admin = User::factory()->create();

        $this->actingAs($admin)
            ->get('/admin/doctors')
            ->assertOk();
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/admin/services')->assertRedirect('/admin/login');
    }
}
