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

function validatePermissions($slug)
{
    return HasPermissionsTrait::getPremissionsByRoute($slug);
}

function userHasSystemAdminRole(): bool
{
    return HasPermissionsTrait::userHasSystemAdminRole();
}

function getLeftMenu($moduleCatId)
{
    return HasPermissionsTrait::getLeftMenuByCategory($moduleCatId);
}

// Compare two arrays and return the differences in a format suitable for audit trails
if (!function_exists('model_changes_for_audit')) {
    function model_changes_for_audit(array $old, array $new, array $fields = []): array
    {
        $changes = [];

        $checkFields = !empty($fields) ? $fields : array_unique(array_merge(array_keys($old), array_keys($new)));

        foreach ($checkFields as $field) {
            $before = $old[$field] ?? null;
            $after = $new[$field] ?? null;

            if ((string) $before !== (string) $after) {
                $changes[$field] = [
                    'old' => $before,
                    'new' => $after,
                ];
            }
        }

        return $changes;
    }
}

if (!function_exists('getPakistaniBanks')) {
    function getPakistaniBanks()
    {
        return [
            'Easypaisa Bank Limited',
            'Mashreq Bank Pakistan Limited',
            'Raqami Islamic Digital Bank Limited',
            'First Women Bank Limited',
            'National Bank of Pakistan',
            'The Bank of Khyber',
            'The Bank of Punjab',
            'Sindh Bank Limited',
            'Zarai Taraqiati Bank Limited',
            'Punjab Provincial Cooperative Bank Limited',
            'Habib Bank Limited',
            'United Bank Limited',
            'MCB Bank Limited',
            'Allied Bank limited',
            'Bank Al-Falah Limited',
            'Bank Al-Habib Limited',
            'Habib Metropolitan Bank Limited',
            'JS Bank Limited',
            'Samba Bank Limited',
            'Askari Bank Limited',
            'Standard Chartered Bank (Pakistan) Limited',
            'Soneri Bank Limited',
            'Bank Makramah Limited',
            'Meezan Bank Limited',
            'Al-Baraka Bank (Pakistan) Limited',
            'Bank Islami Pakistan Limited',
            'MCB Islamic Bank Limited',
            'Dubai Islamic Bank Pakistan',
            'Faysal Bank Limited',
            'Citi Bank N.A.',
            'Deutsche Bank AG',
            'Industrial & Commercial Bank of China',
            'Bank of China Limited',
        ];
    }
}
