<?php

namespace App\Http\Requests\Admin\Policy;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePolicyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'category' => 'required|in:Leave Policy,Attendance Grace Period,Geofencing Rules,Shift Rota Protocols,Security Policy,HR Policy',
            'status' => 'required|in:active,draft,archived',
            'effective_date' => 'required|date',
            'applicable_to' => 'required|in:global,organization,branch,floor',
            'applicable_details' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:5000',
            'document' => 'nullable|file|mimes:pdf,doc,docx|max:10240',
            'remove_document' => 'nullable|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'The policy title is required.',
            'category.required' => 'Please select a category.',
            'status.required' => 'Please select a status.',
            'effective_date.required' => 'The effective date is required.',
            'applicable_to.required' => 'Please select the scope.',
            'document.mimes' => 'Only PDF and Word documents are allowed.',
            'document.max' => 'Document size must not exceed 10MB.',
        ];
    }
}
