<?php

use App\Models\EmailTemplate;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

it('requires authentication for email template management', function () {
    $this->get('/admin/email-templates')
        ->assertRedirect(route('login'));
});

it('forbids users without permission from viewing templates', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get('/admin/email-templates')
        ->assertForbidden();
});

it('allows authorized users to view and update templates', function () {
    app(PermissionRegistrar::class)->forgetCachedPermissions();

    Permission::findOrCreate('manage email templates', 'web');

    $user = User::factory()->create();
    $user->givePermissionTo('manage email templates');

    EmailTemplate::ensureDefaults();

    $template = EmailTemplate::query()
        ->where('key', EmailTemplate::FORM_SUBMISSION_ACKNOWLEDGEMENT)
        ->firstOrFail();

    $this->withoutVite();

    $this->actingAs($user)
        ->get('/admin/email-templates')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/email-templates/Index')
            ->has('templates')
        );

    $this->actingAs($user)
        ->get("/admin/email-templates/{$template->id}/edit")
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/email-templates/Edit')
            ->where('template.id', $template->id)
        );

    $this->actingAs($user)
        ->put("/admin/email-templates/{$template->id}", [
            'subject' => 'Updated subject line',
            'body' => 'Hello {{name}}, this is a custom acknowledgement.',
            'is_active' => false,
        ])
        ->assertRedirect('/admin/email-templates');

    $this->assertDatabaseHas('email_templates', [
        'id' => $template->id,
        'subject' => 'Updated subject line',
        'is_active' => false,
    ]);
});
