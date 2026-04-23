<?php
$duplicates = \App\Models\OutsourcedEmployee::withTrashed()->where('cnic_number', '3232323233333')->get();
foreach ($duplicates as $index => $record) {
    if ($index > 0) {
        $record->forceDelete();
    }
}
echo "Duplicates removed.\n";
