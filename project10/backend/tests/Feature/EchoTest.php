<?php

namespace Tests\Feature;

use Tests\TestCase;

class EchoTest extends TestCase
{
    public function test_get_echo_default(): void
    {
        $response = $this->getJson('/api/echo');

        $response->assertStatus(200)
                 ->assertJson([
                     'success' => true,
                     'message' => 'Hello from EchoController',
                     'method' => 'GET',
                 ]);
    }

    public function test_get_echo_with_custom_message(): void
    {
        $response = $this->getJson('/api/echo?message=AmjadMassaoud');

        $response->assertStatus(200)
                 ->assertJson([
                     'success' => true,
                     'message' => 'AmjadMassaoud',
                     'method' => 'GET',
                 ]);
    }

    public function test_post_echo_default(): void
    {
        $response = $this->postJson('/api/echo');

        $response->assertStatus(200)
                 ->assertJson([
                     'success' => true,
                     'message' => 'Hello from EchoController',
                     'method' => 'POST',
                 ]);
    }

    public function test_post_echo_with_body_message(): void
    {
        $response = $this->postJson('/api/echo', [
            'message' => 'PostMessageTest',
        ]);

        $response->assertStatus(200)
                 ->assertJson([
                     'success' => true,
                     'message' => 'PostMessageTest',
                     'method' => 'POST',
                 ]);
    }

    public function test_ping_endpoint(): void
    {
        $response = $this->getJson('/api/echo/ping');

        $response->assertStatus(200)
                 ->assertJson([
                     'success' => true,
                     'status' => 'service-online',
                 ]);
    }
}
