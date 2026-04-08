<?php

namespace App\Http\Requests\Admin\Geofencing;

use Illuminate\Foundation\Http\FormRequest;

class StoreGeofenceRequest extends FormRequest
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
            'sbu_id' => 'SBU',
            'antiSpoofing' => 'Anti-spoofing',
            'offlineSync' => 'Offline sync',
            'autoCheckIn' => 'Auto check-in',
        ];
    }

    public function messages(): array
    {
        return [
            'siteName.required' => 'Please enter the site name.',
            'siteName.max' => 'Site name must not be greater than :max characters.',

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

            'sbu_id.required' => 'Please select an SBU.',
            'sbu_id.exists' => 'Selected SBU is invalid. Please choose a valid SBU.',

            'antiSpoofing.boolean' => 'Anti-spoofing must be a valid boolean value.',
            'offlineSync.boolean' => 'Offline sync must be a valid boolean value.',
            'autoCheckIn.boolean' => 'Auto check-in must be a valid boolean value.',
        ];
    }
}
