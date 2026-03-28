<?php

namespace App\Http\Requests\Admin\Geofencing;

use Illuminate\Foundation\Http\FormRequest;

class UpdateGeofenceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Assume authorization is handled in Controller
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'siteName' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'lat' => 'required|numeric|between:-90,90',
            'lng' => 'required|numeric|between:-180,180',
            'radius' => 'required|integer|min:1|max:10000',
            'radiusUnit' => 'required|string|in:meters,kilometers',
            'type' => 'required|string|in:hard-lock,soft-lock',
            'sbu_id' => 'required|exists:sbus,id',
            'antiSpoofing' => 'boolean',
            'offlineSync' => 'boolean',
            'autoCheckIn' => 'boolean',
            'status' => 'nullable|string|in:active,inactive',
        ];
    }
}
