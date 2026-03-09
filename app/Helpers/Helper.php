<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

if (!function_exists('dateFormat')) {
    function dateFormat($date, $format)
    {
        $return = $date;
        if ($date != '') {
            $return = date($format, strtotime($date));
        }
        return $return;
    }
}

//Sanitize All request
function sanitizeAllInput(array $input)
{
    return array_map(function ($value) {
        if (is_string($value)) {
            // Strip HTML tags and encode special characters
            return htmlspecialchars(strip_tags($value), ENT_QUOTES, 'UTF-8');
        }
        return $value;
    }, $input);
}

function stripTagsExcept($text)
{
    // Replace <strong> with <b>
    $modifiedString = str_replace('<strong>', '<b>', $text);
    $modifiedString = str_replace('</strong>', '</b>', $modifiedString);
    $allowedTags = '<b><br><br /><ul><ol><li>';
    return strip_tags($modifiedString, $allowedTags);
}

function sanitizeInput($value, $type = 'string')
{
    $value = strip_tags($value);
    switch ($type) {
        case 'int':
            return intval($value);
        case 'float':
            return floatval($value);
        case 'bool':
            return filter_var($value, FILTER_VALIDATE_BOOLEAN);
        case 'alphanumeric':
            return preg_replace('/[^a-zA-Z0-9_. ]/', '', $value);
        case 'string':
        default:
            $value = addslashes($value);
            return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }
}


if (!function_exists('uploadFile')) {
    /**
     * Upload a file with sanitized name and prevent path traversal attacks.
     *
     * @param \Illuminate\Http\UploadedFile $file
     * @param string $directory
     * @return array|bool Array with 'path' and 'name' on success, false on failure
     */
    function uploadFile($file, $directory, $prefix = null)
    {
        // Get the sanitized original filename
        $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_BASENAME);
        $sanitizedFileName = preg_replace('/[^a-zA-Z\.]/', '', $originalName);
        if ($prefix) {
            $sanitizedPrefix = preg_replace('/[^a-zA-Z]/', '', str_replace(' ', '_', $prefix));
            $sanitizedFileName = $sanitizedPrefix . '_' . $sanitizedFileName;
        }

        $sanitizedFileName = trim($sanitizedFileName, '_.');
        $filePath = $directory . '/' . $sanitizedFileName;
        if (Storage::disk('public')->exists($filePath)) {
            $sanitizedFileName = uniqid() . '_' . $sanitizedFileName;
            $filePath = $directory . '/' . $sanitizedFileName;
        }

        $storedPath = $file->storeAs($directory, $sanitizedFileName, 'public');
        return $storedPath ? ['filePath' => $storedPath, 'fileName' => $sanitizedFileName] : false;
    }
}

function validatePermissions($slug){
    return App\Traits\HasPermissionsTrait::getModulesPremissionsBySlug($slug);
    //return true;
}

function getLeftMenuItems($moduleCatId){
    return App\Traits\HasPermissionsTrait::getLeftMenuByCategory($moduleCatId);
}

function base_url()
{
    return URL::to('/');
}