<script>
    function updateStepperUI(target) {
        for (let i = 1; i <= total; i++) {
            const pill = document.getElementById('step-pill-' + i);
            const connector = document.getElementById('con-' + (i-1));
            
            if (!pill) continue;

            pill.classList.remove('is-active', 'is-done');
            if (connector) connector.classList.remove('is-done');

            if (i < target) {
                pill.classList.add('is-done');
                if (connector) connector.classList.add('is-done');
            } else if (i === target) {
                pill.classList.add('is-active');
                if (connector) connector.classList.add('is-done');
            }
        }
    }

    function updateStepGateStyles() {
        // Obsolete as we use handleStepperUI now
    }

    function applyStepNavigation(target) {
        if (typeof clearStepErrors === 'function') clearStepErrors();
        
        const currentStepEl = document.getElementById('step-' + current);
        if (currentStepEl) currentStepEl.classList.remove('active');
        
        updateStepperUI(target);

        current = target;
        document.getElementById('step-' + current).classList.add('active');
        
        document.getElementById('prevBtn').style.display = (current === 1 || current === 6) ? 'none' : 'inline-block';
        
        const nextBtn = document.getElementById('nextBtn');
        const navContainer = document.getElementById('wizard-navigation');
        if (navContainer) navContainer.style.display = 'flex';

        if (current === total) {
            const activeSubEl = document.querySelector('#step-6 .sub-section:not(.d-none)');
            const activeSub = activeSubEl ? activeSubEl.id : null;
            if (activeSub === 's6-references') {
                nextBtn.style.display = 'inline-block';
                nextBtn.textContent = 'Submit Registration';
                nextBtn.className = 'btn ms-auto text-decoration-none text-white btn-success rounded-2 d-flex align-items-center border-0 px-3';
            } else {
                nextBtn.style.display = 'inline-block';
                nextBtn.textContent = 'Next Section';
                nextBtn.className = 'btn ms-auto text-decoration-none text-white bg-main rounded-2 d-flex align-items-center border-0 px-3';
            }
        } else {
            if (nextBtn) {
                nextBtn.style.display = 'inline-block';
                nextBtn.textContent = 'Next';
                nextBtn.className = 'btn ms-auto text-decoration-none text-white bg-main rounded-2 d-flex align-items-center border-0 px-3';
            }
        }
        updateStepGateStyles();
    }

    window.goToStep = function(target) {
        try {
            if (target < 1 || target > total) return;
            if (!isEditMode) {
                if (target === 2 && current === 1) {
                    if (!window.validateStep(1)) return;
                }
                if (target >= 3 && !advancedUnlocked) {
                    if (!window.validateStep(1)) { applyStepNavigation(1); return; }
                    if (!window.validateStep(2)) { applyStepNavigation(2); return; }
                    advancedUnlocked = true;
                }
            }
            applyStepNavigation(target);
        } catch (e) {
            console.error('goToStep error:', e);
        }
    }

    window.changeStep = function(dir) {
        try {
            console.log('changeStep called with dir:', dir, 'current:', current);
            
            // Step 6 Subsection Navigation Logic
            if (current === 6) {
                const subSections = ['s6-contact', 's6-family', 's6-academic', 's6-employment', 's6-medical', 's6-references'];
                const activeSubEl = document.querySelector('#step-6 .sub-section:not(.d-none)');
                const activeSub = activeSubEl ? activeSubEl.id : null;
                const activeIdx = subSections.indexOf(activeSub);

                if (dir === 1) {
                    if (activeIdx !== -1 && activeIdx < subSections.length - 1) {
                        const nextSubId = subSections[activeIdx + 1];
                        const nextBtnInSidebar = document.querySelector(`[data-target="${nextSubId}"]`);
                        
                        // Action-specific persistence before moving
                        if (activeSub === 's6-contact') {
                            if (typeof window.saveContactSubsection === 'function') {
                                window.saveContactSubsection(() => { if(nextBtnInSidebar) nextBtnInSidebar.click(); });
                                return;
                            }
                        } else if (activeSub === 's6-medical') {
                            if (typeof window.saveMedicalSubsection === 'function') {
                                window.saveMedicalSubsection(() => { if(nextBtnInSidebar) nextBtnInSidebar.click(); });
                                return;
                            }
                        }
                        
                        if (nextBtnInSidebar) nextBtnInSidebar.click();
                        return;
                    } else if (activeSub === 's6-references') {
                        // Final step of the wizard
                        if (typeof window.serializeArrayData === 'function') window.serializeArrayData();
                        if (typeof window.processStepSave === 'function') {
                            window.processStepSave(6, () => {
                                window.location.href = '/admin/employee'; // Or your index route
                            });
                        }
                        return;
                    }
                } else if (dir === -1) {
                    if (activeIdx > 0) {
                        const prevSubId = subSections[activeIdx - 1];
                        const prevBtnInSidebar = document.querySelector(`[data-target="${prevSubId}"]`);
                        if (prevBtnInSidebar) prevBtnInSidebar.click();
                        return;
                    }
                }
            }

            // Normal Step Navigation
            const target = current + dir;
            if (target < 1 || target > total) return;
            
            if (dir > 0) {
                if (!window.validateStep(current)) return;
                if (typeof window.processStepSave === 'function') {
                    window.processStepSave(current, () => applyStepNavigation(target));
                } else {
                    applyStepNavigation(target);
                }
            } else {
                applyStepNavigation(target);
            }
        } catch (e) {
            console.error('changeStep failed:', e);
        }
    }
</script>
