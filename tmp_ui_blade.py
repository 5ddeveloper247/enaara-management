p = r'resources/views/admin/shift-planner/roster_shift_canvas.blade.php'
with open(p, encoding='utf-8') as f:
    c = f.read()

old = """            <section class="roster-shift-section roster-shift-change-history-card mb-4" id="rosterShiftChangeHistoryCard" style="display: none;" aria-label="Change history">
                <div class="roster-shift-section-label mb-2">What changed</motion>
                <div id="rosterShiftChangeHistoryLoading" class="roster-shift-change-history-loading text-muted small py-2" style="display: none;">
                    <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Loading changes…
                </div>
                <p id="rosterShiftChangeHistoryEmpty" class="roster-shift-change-history-empty text-muted small mb-0" style="display: none;">No recorded field changes yet.</p>
                <ul class="list-unstyled mb-0 roster-shift-change-history-list" id="rosterShiftChangeHistoryList"></ul>
            </section>""".replace('</motion>', '</div>').replace('<motion', '<motion')

old = """            <section class="roster-shift-section roster-shift-change-history-card mb-4" id="rosterShiftChangeHistoryCard" style="display: none;" aria-label="Change history">
                <div class="roster-shift-section-label mb-2">What changed</div>
                <motion id="rosterShiftChangeHistoryLoading" class="roster-shift-change-history-loading text-muted small py-2" style="display: none;">
                    <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Loading changes…
                </div>
                <p id="rosterShiftChangeHistoryEmpty" class="roster-shift-change-history-empty text-muted small mb-0" style="display: none;">No recorded field changes yet.</p>
                <ul class="list-unstyled mb-0 roster-shift-change-history-list" id="rosterShiftChangeHistoryList"></ul>
            </section>""".replace('<motion id', '<div id')

new = """            <section class="roster-shift-section roster-shift-change-history-card mb-4" id="rosterShiftChangeHistoryCard" style="display: none;" aria-label="Change history">
                <div class="d-flex align-items-center justify-content-between gap-2 mb-1">
                    <span class="roster-shift-section-label mb-0">Change history</span>
                    <span class="roster-shift-change-count badge rounded-pill" id="rosterShiftChangeHistoryCount" style="display: none;">0</span>
                </div>
                <p class="roster-shift-change-history-hint mb-3">What was modified on this shift (time, shift, floor, notes, etc.)</p>
                <div id="rosterShiftChangeHistoryLoading" class="roster-shift-change-history-loading" style="display: none;" aria-live="polite">
                    <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                    <span>Loading changes…</span>
                </div>
                <div id="rosterShiftChangeHistoryEmpty" class="roster-shift-change-history-empty" style="display: none;">
                    <i class="bi bi-journal-text" aria-hidden="true"></i>
                    <span>No field changes recorded yet. Updates will appear here after you save edits.</span>
                </div>
                <ul class="list-unstyled mb-0 roster-shift-change-timeline" id="rosterShiftChangeHistoryList"></ul>
            </section>"""

if old in c:
    c = c.replace(old, new, 1)
    with open(p, 'w', encoding='utf-8') as f:
        f.write(c)
    print('blade ok')
else:
    print('blade not found')
