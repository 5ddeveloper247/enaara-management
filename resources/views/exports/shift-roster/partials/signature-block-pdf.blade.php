@php
    $signatures = $signatures ?? [];
    $appliedName = $signatures['applied_by_name'] ?? '';
    $appliedDesignation = $signatures['applied_by_designation'] ?? '';
    $approvedName = $signatures['approved_by_name'] ?? '';
    $approvedDesignation = $signatures['approved_by_designation'] ?? '';
    $blank = '________________';
@endphp

<div class="signature-block">
    <table class="signature-table">
        <tr>
            <td class="signature-cell signature-cell-left">
                <div class="signature-heading">Prepared By</div>
                <div class="signature-line"></div>
                <div class="signature-meta">
                    {{ $appliedName !== '' ? $appliedName : $blank }}
                </div>
                <div class="signature-meta">
                    {{ $appliedDesignation !== '' ? $appliedDesignation : $blank }}
                </div>
            </td>
            <td class="signature-gap"></td>
            <td class="signature-cell signature-cell-right">
                <div class="signature-heading">Approved By</div>
                <div class="signature-line"></div>
                <div class="signature-meta">
                    {{ $approvedName !== '' ? $approvedName : $blank }}
                </div>
                <div class="signature-meta">
                    {{ $approvedDesignation !== '' ? $approvedDesignation : $blank }}
                </div>
            </td>
        </tr>
    </table>
</div>
