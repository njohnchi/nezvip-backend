<?php

namespace App\Http\Requests\Api;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class VentureArchitectureRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'data.full_name' => ['required', 'string', 'max:255'],
            'data.email' => ['required', 'email', 'max:255'],
            'data.organization' => ['nullable', 'string', 'max:255'],
            'data.undertaking_name' => ['required', 'string', 'max:255'],
            'data.architecture_entry_path' => ['required', 'string', 'in:STANDALONE,DIAGNOSTIC_LED,UNDETERMINED'],
            'data.architecture_request_type' => ['required', 'string', 'in:FULL_MASTER,SPECIFIC_SPECIALIST,RE_ARCHITECTURE,NEW_PROJECT,UNDETERMINED_SCOPE'],
            'data.undertaking_context' => ['required', 'string'],
            'data.scope_description' => ['required', 'string'],
            'data.expected_architecture_outcome' => ['required', 'string'],
            'data.known_constraints_requirements' => ['nullable', 'string'],
            'data.supporting_documents_links' => ['nullable', 'string'],
            'data.diagnostic_reference' => ['nullable', 'string', 'max:255', 'required_if:data.architecture_entry_path,DIAGNOSTIC_LED'],
            'data.notes' => ['nullable', 'string'],
            'data.authority_to_submit' => ['required', 'boolean'],
        ];
    }

    /**
     * Get custom error messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'data.full_name.required' => 'Please enter your full name.',
            'data.full_name.string' => 'Full name must be text.',
            'data.full_name.max' => 'Full name must not exceed 255 characters.',
            'data.email.required' => 'Please enter your email address.',
            'data.email.email' => 'Please enter a valid email address.',
            'data.email.max' => 'Email must not exceed 255 characters.',
            'data.organization.max' => 'Organization must not exceed 255 characters.',
            'data.undertaking_name.required' => 'Please enter the undertaking or venture name.',
            'data.undertaking_name.string' => 'Undertaking name must be text.',
            'data.undertaking_name.max' => 'Undertaking name must not exceed 255 characters.',
            'data.architecture_entry_path.required' => 'Please select an architecture entry path.',
            'data.architecture_entry_path.string' => 'Architecture entry path must be text.',
            'data.architecture_entry_path.in' => 'Please select a valid architecture entry path.',
            'data.architecture_request_type.required' => 'Please select an architecture request type.',
            'data.architecture_request_type.string' => 'Architecture request type must be text.',
            'data.architecture_request_type.in' => 'Please select a valid architecture request type.',
            'data.undertaking_context.required' => 'Please describe the undertaking context.',
            'data.undertaking_context.string' => 'Undertaking context must be text.',
            'data.scope_description.required' => 'Please describe the requested design work or scope.',
            'data.scope_description.string' => 'Scope description must be text.',
            'data.expected_architecture_outcome.required' => 'Please describe the expected architecture outcome.',
            'data.expected_architecture_outcome.string' => 'Expected architecture outcome must be text.',
            'data.known_constraints_requirements.string' => 'Known constraints must be text.',
            'data.supporting_documents_links.string' => 'Supporting documents links must be text.',
            'data.diagnostic_reference.string' => 'Diagnostic reference must be text.',
            'data.diagnostic_reference.max' => 'Diagnostic reference must not exceed 255 characters.',
            'data.diagnostic_reference.required_if' => 'A diagnostic reference is required when the entry path is "Following NezVIP Diagnostic".',
            'data.notes.string' => 'Notes must be text.',
            'data.authority_to_submit.required' => 'You must confirm you are authorized to submit this request.',
            'data.authority_to_submit.boolean' => 'Authorization to submit must be confirmed.',
        ];
    }
}
