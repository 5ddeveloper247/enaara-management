<?php

return [
    'outstation_base_city' => env('HR_OUTSTATION_BASE_CITY', 'rawalpindi'),
    'outstation_travel_exempt_days' => (float) env('HR_OUTSTATION_TRAVEL_EXEMPT_DAYS', 1),

    /*
    | Fixed employee number prefix for new hires (e.g. ENR-10150).
    | When set, overrides SBU-name-based prefixes like M-.
    | Leave empty to keep legacy SBU-initial generation.
    */
    'employee_code_prefix' => env('HR_EMPLOYEE_CODE_PREFIX', 'ENR'),
];
