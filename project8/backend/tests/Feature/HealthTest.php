<?php

namespace Tests\Feature;

use Tests\TestCase;

class HealthTest extends TestCase
{
    public function test_health_endpoint(): void
    {
        $response = $this->get('/health');
        $response->dump();
        $response->assertStatus(200);
    }

    public function test_up_endpoint(): void
    {
        $response = $this->get('/up');
        $response->dump();
        $response->assertStatus(200);
    }
}
