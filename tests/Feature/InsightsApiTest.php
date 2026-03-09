<?php

use App\Models\Insight;
use App\Models\User;

it('returns preview only for premium insight when guest is not subscribed', function () {
    $insight = Insight::create([
        'title' => 'Premium Strategy Playbook',
        'slug' => 'premium-strategy-playbook',
        'excerpt' => 'A high-level summary for everyone.',
        'content' => str_repeat('Premium content paragraph. ', 80),
        'preview_chars' => 250,
        'is_premium' => true,
        'is_published' => true,
        'published_at' => now(),
        'author_id' => User::factory()->create()->id,
    ]);

    $response = $this->getJson('/api/v1/insights/'.$insight->slug);

    $response->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.is_locked', true)
        ->assertJsonPath('data.is_premium', true)
        ->assertJsonPath('data.content', null);

    expect($response->json('data.preview_content'))->not->toBeEmpty();
});

it('returns full content for premium insight when user has active subscription', function () {
    $author = User::factory()->create();
    $subscriber = User::factory()->create([
        'insights_subscribed_at' => now()->subDay(),
        'insights_subscription_expires_at' => now()->addMonth(),
    ]);

    $insight = Insight::create([
        'title' => 'Premium Market Blueprint',
        'slug' => 'premium-market-blueprint',
        'excerpt' => 'A high-level summary for everyone.',
        'content' => 'This is full premium content.',
        'preview_chars' => 200,
        'is_premium' => true,
        'is_published' => true,
        'published_at' => now(),
        'author_id' => $author->id,
    ]);

    $response = $this->actingAs($subscriber)->getJson('/api/v1/insights/'.$insight->slug);

    $response->assertOk()
        ->assertJsonPath('data.is_locked', false)
        ->assertJsonPath('data.content', 'This is full premium content.');
});

it('returns full content for non-premium insight for guests', function () {
    $insight = Insight::create([
        'title' => 'Open Insight',
        'slug' => 'open-insight',
        'excerpt' => 'A public summary.',
        'content' => 'Public full content.',
        'preview_chars' => 180,
        'is_premium' => false,
        'is_published' => true,
        'published_at' => now(),
        'author_id' => User::factory()->create()->id,
    ]);

    $response = $this->getJson('/api/v1/insights/'.$insight->slug);

    $response->assertOk()
        ->assertJsonPath('data.is_locked', false)
        ->assertJsonPath('data.content', 'Public full content.');
});

