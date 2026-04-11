<?php

namespace App\Http\Controllers;

use App\Models\Country;
use App\Models\Province;
use App\Models\District;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LocationController extends Controller
{
    /**
     * Get all countries.
     */
    public function getCountries(): JsonResponse
    {
        $countries = Country::orderBy('name')
            ->get(['id', 'name']);

        return response()->json($countries);
    }

    /**
     * Get active provinces for a specific country name.
     */
    public function getProvinces(string $countryName): JsonResponse
    {
        $country = Country::where('name', $countryName)->first();

        if (!$country) {
            return response()->json([]);
        }

        $provinces = Province::where('country_id', $country->id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);

        return response()->json($provinces);
    }

    /**
     * Get active districts for a specific country and province name.
     */
    public function getDistricts(string $countryName, string $provinceName): JsonResponse
    {
        $country = Country::where('name', $countryName)->first();
        if (!$country) {
            return response()->json([]);
        }

        $province = Province::where('name', $provinceName)
            ->where('country_id', $country->id)
            ->first();

        if (!$province) {
            return response()->json([]);
        }

        $districts = District::where('province_id', $province->id)
            ->where('country_id', $country->id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);

        return response()->json($districts);
    }
}
