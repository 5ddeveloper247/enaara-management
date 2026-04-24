<?php

namespace App\Http\Requests\Admin\Geofencing;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
        $geofenceId = $this->route('id');

        return [
            'siteName' => [
                'required',
                'string',
                'max:255',
                Rule::unique('geofences', 'name')
                    ->ignore($geofenceId)
                    ->where(function ($query) {
                        return $query
                            ->where('organization_id', $this->input('organization_id'))
                            ->where('sbu_id', $this->input('sbu_id'));
                    }),
            ],
            'address' => 'required|string|max:255',
            'lat' => 'required|numeric|between:-90,90',
            'lng' => 'required|numeric|between:-180,180',
            'radius' => 'required|integer|min:1|max:10000',
            'radiusUnit' => 'required|string|in:meters,kilometers',
            'type' => 'required|string|in:hard-lock,soft-lock',
            'organization_id' => 'required|exists:organizations,id',
            'sbu_id' => [
                'required',
                Rule::exists('sbus', 'id')->where(function ($query) {
                    return $query->where('organization_id', $this->input('organization_id'));
                }),
            ],
            'antiSpoofing' => 'boolean',
            'offlineSync' => 'boolean',
            'autoCheckIn' => 'boolean',
            'status' => 'nullable|string|in:active,inactive',
        ];
    }

    public function attributes(): array
    {
        return [
            'siteName' => 'Site name',
            'address' => 'Address',
            'lat' => 'Latitude',
            'lng' => 'Longitude',
            'radius' => 'Radius',
            'radiusUnit' => 'Radius unit',
            'type' => 'Fence type',
            'organization_id' => 'Organization',
            'sbu_id' => 'SBU',
            'antiSpoofing' => 'Anti-spoofing',
            'offlineSync' => 'Offline sync',
            'autoCheckIn' => 'Auto check-in',
            'status' => 'Status',
        ];
    }

    public function messages(): array
    {
        return [
            'siteName.required' => 'Please enter the site name.',
            'siteName.max' => 'Site name must not be greater than :max characters.',
            'siteName.unique' => 'Site name already exists for the selected organization and SBU.',

            'address.required' => 'Please enter the address/location name.',
            'address.max' => 'Address must not be greater than :max characters.',

            'lat.required' => 'Please set the map location first: press Enter/Search for the address or click "Drop Pin".',
            'lat.numeric' => 'Latitude should be a valid number. Please drop a pin on the map.',
            'lat.between' => 'Latitude must be between :min and :max.',

            'lng.required' => 'Please set the map location first: press Enter/Search for the address or click "Drop Pin".',
            'lng.numeric' => 'Longitude should be a valid number. Please drop a pin on the map.',
            'lng.between' => 'Longitude must be between :min and :max.',

            'radius.required' => 'Please enter a radius value.',
            'radius.integer' => 'Radius must be a whole number.',
            'radius.min' => 'Radius must be at least :min.',
            'radius.max' => 'Radius must not be greater than :max.',

            'radiusUnit.required' => 'Please select a radius unit.',
            'radiusUnit.in' => 'Radius unit must be either "meters" or "kilometers".',

            'type.required' => 'Please select a fence type.',
            'type.in' => 'Fence type must be either "hard-lock" or "soft-lock".',

            'organization_id.required' => 'Please select an organization.',
            'organization_id.exists' => 'Selected organization is invalid. Please choose a valid organization.',

            'sbu_id.required' => 'Please select an SBU.',
            'sbu_id.exists' => 'Selected SBU is invalid for the selected organization.',

            'antiSpoofing.boolean' => 'Anti-spoofing must be a valid boolean value.',
            'offlineSync.boolean' => 'Offline sync must be a valid boolean value.',
            'autoCheckIn.boolean' => 'Auto check-in must be a valid boolean value.',

            'status.in' => 'Status must be either "active" or "inactive".',
        ];
    }
}
