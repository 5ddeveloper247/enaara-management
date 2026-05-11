<?php
$file = 'd:/enaara-management/resources/views/admin/employeeregistration/partials/steps/step-more.blade.php';
$content = file_get_contents($file);

// Add HR fields to the initialization script
$oldScript = "reason_for_leaving: @json(\$record?->reason_for_leaving)";
$newScript = "reason_for_leaving: @json(\$record?->reason_for_leaving),\n                                                                              hr_contact: @json(\$record?->hr_contact),\n                                                                              hr_email: @json(\$record?->hr_email)";
$content = str_replace($oldScript, $newScript, $content);

// Fix the template duplication and add HR fields
// The duplication started after salary input.
// I'll look for the specific block that got messed up.

// Wait, I'll just try to find the "Reason for Leaving" block in the template and add the fields.
$oldTemplateBlock = '<div class="col-12">
                                                         <label class="form-label">Reason for Leaving</label>
                                                         <input type="text" class="form-control employment-field-input" name="employments[][reason_for_leaving]"
                                                             data-employment-reason placeholder="Enter reason for leaving" maxlength="200">
                                                         <div class="employment-field-preview" data-employment-preview-reason-for-leaving>-</div>
                                                     </div>';

$newTemplateBlock = '<div class="col-12 col-md-6">
                                                         <label class="form-label">Reason for Leaving</label>
                                                         <input type="text" class="form-control employment-field-input" name="employments[][reason_for_leaving]"
                                                             data-employment-reason placeholder="Enter reason for leaving" maxlength="200">
                                                         <div class="employment-field-preview" data-employment-preview-reason-for-leaving>-</div>
                                                     </div>
                                                     <div class="col-12 col-md-3">
                                                         <label class="form-label">HR Contact Number</label>
                                                         <input type="text" class="form-control employment-field-input" name="employments[][hr_contact]"
                                                             data-employment-hr-contact placeholder="Enter HR contact" maxlength="15">
                                                         <div class="employment-field-preview" data-employment-preview-hr-contact>-</div>
                                                     </div>
                                                     <div class="col-12 col-md-3">
                                                         <label class="form-label">HR Email</label>
                                                         <input type="email" class="form-control employment-field-input" name="employments[][hr_email]"
                                                             data-employment-hr-email placeholder="Enter HR email" maxlength="100">
                                                         <div class="employment-field-preview" data-employment-preview-hr-email>-</div>
                                                     </div>';

$content = str_replace($oldTemplateBlock, $newTemplateBlock, $content);

// And remove the duplicated part.
// I'll look for the second occurrence of <div class="employment-record-row mb-2 bg-light" data-employment-row>
$needle = '<div class="employment-record-row mb-2 bg-light" data-employment-row>';
$firstPos = strpos($content, $needle);
if ($firstPos !== false) {
    $secondPos = strpos($content, $needle, $firstPos + 1);
    if ($secondPos !== false) {
        // Find the next </template>
        $templateEnd = strpos($content, '</template>', $secondPos);
        if ($templateEnd !== false) {
            $content = substr_replace($content, '', $secondPos, $templateEnd - $secondPos);
        }
    }
}

file_put_contents($file, $content);
echo "File updated successfully";
