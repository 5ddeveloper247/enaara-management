<?php
use App\Traits\HasPermissionsTrait;
use Illuminate\Support\Facades\Storage;

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



if (!function_exists('storeUploadedFile')) {

    /**
     * Store uploaded file safely with cleaned filename.
     *
     * @param \Illuminate\Http\UploadedFile $uploadedFile
     * @param string $targetFolder
     * @param string|null $namePrefix
     * @return array|bool
     */

    function storeUploadedFile($uploadedFile, $targetFolder, $namePrefix = null)
    {
        // Extract original file name
        $originalFile = pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_BASENAME);

        // Remove unwanted characters
        $cleanFileName = preg_replace('/[^a-zA-Z\.]/', '', $originalFile);

        // Apply prefix if provided
        if ($namePrefix) {
            $cleanPrefix = preg_replace('/[^a-zA-Z]/', '', str_replace(' ', '_', $namePrefix));
            $cleanFileName = $cleanPrefix . '_' . $cleanFileName;
        }

        // Remove extra underscores or dots
        $cleanFileName = trim($cleanFileName, '_.');

        // Prepare full storage path
        $storageLocation = $targetFolder . '/' . $cleanFileName;

        // Prevent duplicate file names
        if (Storage::disk('public')->exists($storageLocation)) {
            $cleanFileName = uniqid() . '_' . $cleanFileName;
            $storageLocation = $targetFolder . '/' . $cleanFileName;
        }

        // Save file
        $savedPath = $uploadedFile->storeAs($targetFolder, $cleanFileName, 'public');

        return $savedPath ? [
            'file_path' => $savedPath,
            'file_name' => $cleanFileName
        ] : false;
    }
}

function validatePermissions($slug){
    return HasPermissionsTrait::getPremissionsByRoute($slug);
    //return true;
}

function getLeftMenu($moduleCatId){
    return HasPermissionsTrait::getLeftMenuByCategory($moduleCatId);
}