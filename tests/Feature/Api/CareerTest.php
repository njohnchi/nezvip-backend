<?php

use App\Models\Career;

it('returns active careers from the api', function () {
    Career::factory()->active()->count(3)->create();
    Career::factory()->inactive()->count(2)->create();

    $response = $this->getJson('/api/v1/careers');

    $response->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonCount(3, 'data');
});

it('returns career detail for an active career', function () {
    $career = Career::factory()->active()->create();

    $response = $this->getJson("/api/v1/careers/{$career->id}");

    $response->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.id', $career->id)
        ->assertJsonPath('data.responsibilities', $career->responsibilities);
});

it('returns 404 for an inactive career', function () {
    $career = Career::factory()->inactive()->create();

    $this->getJson("/api/v1/careers/{$career->id}")
        ->assertNotFound();
});

it('accepts a valid career application', function () {
    $career = Career::factory()->active()->create();

    $payload = [
        'full_name' => 'Jane Doe',
        'email' => 'jane@example.com',
        'location' => 'Lagos, Nigeria',
        'relevant_experience' => 'Five years in venture operations and project management.',
        'profile_link' => 'https://linkedin.com/in/janedoe',
    ];

    $this->postJson("/api/v1/careers/{$career->id}/apply", $payload)
        ->assertCreated()
        ->assertJsonPath('success', true);

    $this->assertDatabaseHas('career_applications', [
        'career_id' => $career->id,
        'email' => 'jane@example.com',
        'status' => 'pending',
    ]);
});

it('rejects an application with missing required fields', function () {
    $career = Career::factory()->active()->create();

    $this->postJson("/api/v1/careers/{$career->id}/apply", [])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['full_name', 'email', 'location', 'relevant_experience', 'profile_link']);
});

it('rejects an application for an inactive career', function () {
    $career = Career::factory()->inactive()->create();

    $this->postJson("/api/v1/careers/{$career->id}/apply", [
        'full_name' => 'Jane Doe',
        'email' => 'jane@example.com',
        'location' => 'Remote',
        'relevant_experience' => 'Some experience.',
        'profile_link' => 'https://example.com/cv',
    ])->assertUnprocessable();
});
