<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_pages_render_the_brand_discovery_site(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('Connecting Hospitals, Connecting Care')
            ->assertSee('Information arrives before the patient');

        $this->get('/how-it-works')
            ->assertOk()
            ->assertSee('referral lifecycle');

        $this->get('/features')
            ->assertOk()
            ->assertSee('Emergency pre-alert');

        $this->get('/about')
            ->assertOk()
            ->assertSee('messenger between hospitals');

        $this->get('/login')
            ->assertOk()
            ->assertSee('Sign in');
    }
}
