
<style>
    .stepper-track { display: flex; align-items: center; }

    .step-item {
        display: flex; align-items: center; gap: .4rem;
        padding: .28rem .65rem .28rem .35rem;
        border-radius: 999px;
        border: 1.5px solid #dee2e6;
        background: #fff;
        white-space: nowrap;
        flex-shrink: 0;
        transition: all .25s ease;
    }
    .step-item .step-bubble {
        width: 24px; height: 24px; border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        font-size: .75rem;
        background: #f1f3f5;
        color: #6a6b6b;
        transition: all .25s ease;
        flex-shrink: 0;
    }
    .step-item .step-label {
        font-size: .65rem; font-weight: 500;
        color: #adb5bd; text-transform: uppercase;
        letter-spacing: .03em;
        transition: color .25s;
    }

    /* Active */
    .step-item.is-active { border-color: var(--bs-primary); background: #eff6ff; }
    .step-item.is-active .step-bubble { background: var(--bs-primary); color: #fff; }
    .step-item.is-active .step-label  { color: var(--bs-primary); font-weight: 600; }

    /* Done */
    .step-item.is-done { border-color: var(--bs-primary); background: var(--bs-primary); }
    .step-item.is-done .step-bubble { background: rgba(255,255,255,.25); color: #fff; }
    .step-item.is-done .step-label  { color: #fff; font-weight: 600; }

    /* Connector */
    .step-connector {
        flex: 1; height: 1.5px; min-width: 8px;
        background: #afb0b0;
        transition: background .3s;
    }
    .step-connector.is-done { background: var(--bs-primary); }
</style>

<div class="p-3 pb-4">
    <div class="stepper-track">

        <div class="step-item is-active" id="step-pill-1">
            <div class="step-bubble" id="circle-1"><i class="bi bi-person-fill"></i></div>
            <span class="step-label" id="label-1">General</span>
        </div>
        <div class="step-connector" id="con-1"></div>

        <div class="step-item" id="step-pill-2">
            <div class="step-bubble" id="circle-2"><i class="bi bi-shield-fill"></i></div>
            <span class="step-label" id="label-2">Police</span>
        </div>
        <div class="step-connector" id="con-2"></div>

        <div class="step-item" id="step-pill-3">
            <div class="step-bubble" id="circle-3"><i class="bi bi-award-fill"></i></div>
            <span class="step-label" id="label-3">Armed</span>
        </div>
        <div class="step-connector" id="con-3"></div>

        <div class="step-item" id="step-pill-4">
            <div class="step-bubble" id="circle-4"><i class="bi bi-telephone-fill"></i></div>
            <span class="step-label" id="label-4">Contact</span>
        </div>
        <div class="step-connector" id="con-4"></div>

        <div class="step-item" id="step-pill-5">
            <div class="step-bubble" id="circle-5"><i class="bi bi-bank2"></i></div>
            <span class="step-label" id="label-5">Bank</span>
        </div>
        <div class="step-connector" id="con-5"></div>

        <div class="step-item" id="step-pill-6">
            <div class="step-bubble" id="circle-6"><i class="bi bi-people-fill"></i></div>
            <span class="step-label" id="label-6">Family</span>
        </div>
        <div class="step-connector" id="con-6"></div>

        <div class="step-item" id="step-pill-7">
            <div class="step-bubble" id="circle-7"><i class="bi bi-plus fs-5"></i></div>
            <span class="step-label" id="label-7">More</span>
        </div>

    </div>
</div>
