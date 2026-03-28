<?php

namespace App\Services;

use App\Models\Policy;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class PolicyService
{
    /**
     * Store a newly created policy.
     */
    public function store(array $data, $file = null)
    {
        DB::beginTransaction();
        try {
            $policyData = [
                'title' => $data['title'],
                'category' => $data['category'],
                'status' => $data['status'],
                'effective_date' => $data['effective_date'],
                'applicable_to' => $data['applicable_to'],
                'applicable_details' => $data['applicable_details'] ?? null,
                'description' => $data['description'] ?? null,
            ];

            if ($file) {
                $path = $file->store('policies', 'public');
                $policyData['document_path'] = $path;
                $policyData['document_name'] = $file->getClientOriginalName();
            }

            $policy = Policy::create($policyData);

            DB::commit();
            return $policy;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Policy Store Error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Update an existing policy.
     */
    public function update(array $data, $id, $file = null)
    {
        DB::beginTransaction();
        try {
            $policy = Policy::findOrFail($id);

            $policyData = [
                'title' => $data['title'],
                'category' => $data['category'],
                'status' => $data['status'],
                'effective_date' => $data['effective_date'],
                'applicable_to' => $data['applicable_to'],
                'applicable_details' => $data['applicable_details'] ?? null,
                'description' => $data['description'] ?? null,
            ];

            if ($file) {
                // Delete old file if exists
                if ($policy->document_path && Storage::disk('public')->exists($policy->document_path)) {
                    Storage::disk('public')->delete($policy->document_path);
                }
                $path = $file->store('policies', 'public');
                $policyData['document_path'] = $path;
                $policyData['document_name'] = $file->getClientOriginalName();
            }

            // If user explicitly removed the document
            if (!empty($data['remove_document']) && !$file) {
                if ($policy->document_path && Storage::disk('public')->exists($policy->document_path)) {
                    Storage::disk('public')->delete($policy->document_path);
                }
                $policyData['document_path'] = null;
                $policyData['document_name'] = null;
            }

            $policy->update($policyData);

            DB::commit();
            return $policy;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Policy Update Error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Delete a policy.
     */
    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $policy = Policy::findOrFail($id);

            // Delete associated document
            if ($policy->document_path && Storage::disk('public')->exists($policy->document_path)) {
                Storage::disk('public')->delete($policy->document_path);
            }

            $policy->delete();
            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Policy Deletion Error: ' . $e->getMessage());
            throw $e;
        }
    }
}
