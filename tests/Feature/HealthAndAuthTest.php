<?php

test('health endpoint returns healthy status', function () {
    $response = $this->get('/health');

    $response->assertStatus(200)
        ->assertJson([
            'status' => 'healthy',
        ])
        ->assertJsonStructure([
            'status',
            'timestamp',
            'app',
            'environment',
        ]);
});

test('detailed health endpoint checks all dependencies', function () {
    $response = $this->get('/health/detailed');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'status',
            'timestamp',
            'checks' => [
                'database' => ['status'],
                'cache' => ['status'],
                'storage' => ['status'],
            ],
            'version',
        ]);
});

test('login page loads successfully', function () {
    $response = $this->get('/login');

    $response->assertStatus(200);
});

test('register page loads successfully', function () {
    $response = $this->get('/register');

    $response->assertStatus(200);
});

test('unauthenticated user is redirected from protected routes', function () {
    $response = $this->get('/admin/dashboard');

    $response->assertRedirect('/login');
});
