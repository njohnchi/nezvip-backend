<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateEmailTemplateRequest;
use App\Models\EmailTemplate;
use Inertia\Response;

class EmailTemplateController extends Controller
{
    public function index(): Response
    {
        EmailTemplate::ensureDefaults();

        $templates = EmailTemplate::query()
            ->orderBy('name')
            ->get();

        return inertia('admin/email-templates/Index', [
            'templates' => $templates,
        ]);
    }

    public function edit(EmailTemplate $emailTemplate): Response
    {
        return inertia('admin/email-templates/Edit', [
            'template' => $emailTemplate,
        ]);
    }

    public function update(UpdateEmailTemplateRequest $request, EmailTemplate $emailTemplate)
    {
        $emailTemplate->update($request->validated());

        return redirect()->route('admin.email-templates.index')
            ->with('success', 'Email template updated successfully.');
    }
}
