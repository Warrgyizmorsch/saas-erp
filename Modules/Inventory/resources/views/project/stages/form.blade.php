    @extends('shared::layouts.app')
@section('content')

        <style>
            @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500&display=swap');

            :root {
                --bg: #f1f5f9;
                --bg-card: #ffffff;
                --bg-card2: #f8fafc;
                --bg-input: #f8fafc;
                --border: #e2e8f0;
                --border-hover: rgba(99, 102, 241, 0.4);
                --accent: #6366f1;
                --accent-glow: rgba(99, 102, 241, 0.2);
                --accent2: #0891b2;
                --success: #059669;
                --danger: #e11d48;
                --warning: #d97706;
                --text: #0f172a;
                --text-muted: #64748b;
                --text-dim: #475569;
                --radius: 14px;
                --radius-sm: 8px;
                --shadow: 0 4px 24px rgba(15, 23, 42, 0.08);
                --shadow-accent: 0 0 20px var(--accent-glow);
            }

            * {
                box-sizing: border-box;
            }

            .sm-wrap {
                font-family: 'Plus Jakarta Sans', sans-serif;
                min-height: 100vh;
                padding: 32px 24px 64px;
                color: var(--text);
            }

            /* ── PAGE HEADER ── */
            .sm-header {
                display: flex;
                justify-content: space-between;
                align-items: flex-start;
                margin-bottom: 36px;
            }

            .sm-header-left h1 {
                font-size: 26px;
                font-weight: 800;
                letter-spacing: -0.5px;
                margin: 0 0 4px;
                background: linear-gradient(135deg, #0f172a 30%, var(--accent));
                -webkit-background-clip: text;
                -webkit-text-fill-color: transparent;
            }

            .sm-header-left p {
                font-size: 13px;
                color: var(--text-muted);
                margin: 0;
                display: flex;
                align-items: center;
                gap: 6px;
            }

            .sm-header-left p::before {
                content: '';
                display: inline-block;
                width: 6px;
                height: 6px;
                border-radius: 50%;
                background: var(--warning);
                flex-shrink: 0;
            }

            .btn-back {
                display: inline-flex;
                align-items: center;
                gap: 8px;
                padding: 10px 20px;
                background: var(--bg-card2);
                border: 1px solid var(--border);
                border-radius: var(--radius-sm);
                color: var(--text-dim);
                font-size: 13px;
                font-weight: 600;
                font-family: 'Plus Jakarta Sans', sans-serif;
                text-decoration: none;
                transition: all 0.2s;
                cursor: pointer;
            }

            .btn-back:hover {
                border-color: var(--border-hover);
                color: var(--text);
                background: var(--bg-card);
            }

            /* ── ALERTS ── */
            .sm-alert {
                padding: 14px 18px;
                border-radius: var(--radius-sm);
                font-size: 13.5px;
                font-weight: 500;
                margin-bottom: 20px;
                border-left: 3px solid;
            }

            .sm-alert-success {
                background: rgba(5, 150, 105, 0.07);
                border-color: var(--success);
                color: #065f46;
            }

            .sm-alert-danger {
                background: rgba(225, 29, 72, 0.07);
                border-color: var(--danger);
                color: #9f1239;
            }

            /* ── MAIN CARD ── */
            .sm-card {
                background: var(--bg-card);
                border: 1px solid var(--border);
                border-radius: var(--radius);
                overflow: hidden;
                box-shadow: var(--shadow);
            }

            .sm-card-header {
                display: flex;
                justify-content: space-between;
                align-items: center;
                padding: 20px 28px;
                border-bottom: 1px solid var(--border);
                background: var(--bg-card2);
            }

            .sm-card-header h2 {
                font-size: 15px;
                font-weight: 700;
                margin: 0;
                letter-spacing: -0.2px;
            }

            .sm-card-body {
                padding: 28px;
            }

            /* ── BUTTONS ── */
            .btn-primary {
                display: inline-flex;
                align-items: center;
                gap: 6px;
                padding: 9px 18px;
                background: var(--accent);
                border: none;
                border-radius: var(--radius-sm);
                color: #fff;
                font-size: 13px;
                font-weight: 700;
                font-family: 'Plus Jakarta Sans', sans-serif;
                cursor: pointer;
                transition: all 0.2s;
                box-shadow: 0 0 0 0 var(--accent-glow);
                letter-spacing: 0.2px;
            }

            .btn-primary:hover:not(:disabled) {
                background: #4f46e5;
                box-shadow: var(--shadow-accent);
                transform: translateY(-1px);
            }

            .btn-primary:disabled {
                opacity: 0.35;
                cursor: not-allowed;
                transform: none;
            }

            .btn-danger {
                display: inline-flex;
                align-items: center;
                gap: 6px;
                padding: 7px 14px;
                background: rgba(244, 63, 94, 0.1);
                border: 1px solid rgba(244, 63, 94, 0.25);
                border-radius: var(--radius-sm);
                color: var(--danger);
                font-size: 12px;
                font-weight: 600;
                font-family: 'Plus Jakarta Sans', sans-serif;
                cursor: pointer;
                transition: all 0.2s;
            }

            .btn-danger:hover {
                background: rgba(225, 29, 72, 0.12);
                border-color: var(--danger);
                color: #9f1239;
            }

            .btn-sub-add {
                display: inline-flex;
                align-items: center;
                gap: 6px;
                padding: 7px 14px;
                background: rgba(8, 145, 178, 0.07);
                border: 1px solid rgba(8, 145, 178, 0.25);
                border-radius: var(--radius-sm);
                color: var(--accent2);
                font-size: 12px;
                font-weight: 700;
                font-family: 'Plus Jakarta Sans', sans-serif;
                cursor: pointer;
                transition: all 0.2s;
            }

            .btn-sub-add:hover {
                background: rgba(8, 145, 178, 0.13);
                border-color: rgba(8, 145, 178, 0.5);
            }

            .btn-save {
                width: 100%;
                padding: 16px;
                background: linear-gradient(135deg, var(--accent), #4f46e5);
                border: none;
                border-radius: var(--radius-sm);
                color: #fff;
                font-size: 15px;
                font-weight: 800;
                font-family: 'Plus Jakarta Sans', sans-serif;
                cursor: pointer;
                transition: all 0.25s;
                letter-spacing: 0.3px;
                box-shadow: 0 4px 20px rgba(99, 102, 241, 0.3);
                margin-top: 24px;
            }

            .btn-save:hover:not(:disabled) {
                transform: translateY(-2px);
                box-shadow: 0 8px 28px rgba(99, 102, 241, 0.45);
            }

            .btn-save:disabled {
                opacity: 0.3;
                cursor: not-allowed;
                transform: none;
                box-shadow: none;
            }

            /* ── PROGRESS BAR ── */
            .progress-container {
                margin-bottom: 28px;
            }

            .progress-labels {
                display: flex;
                justify-content: space-between;
                align-items: center;
                margin-bottom: 10px;
            }

            .progress-labels span {
                font-size: 12px;
                color: var(--text-muted);
                font-weight: 600;
                text-transform: uppercase;
                letter-spacing: 0.6px;
            }

            .progress-pct {
                font-family: 'JetBrains Mono', monospace;
                font-size: 13px;
                color: var(--text) !important;
                font-weight: 500 !important;
                letter-spacing: 0 !important;
                text-transform: none !important;
            }

            .progress-bar-track {
                height: 8px;
                background: #e2e8f0;
                border-radius: 99px;
                overflow: hidden;
            }

            .progress-bar-fill {
                height: 100%;
                border-radius: 99px;
                background: linear-gradient(90deg, var(--accent), var(--accent2));
                transition: width 0.35s cubic-bezier(.4, 0, .2, 1);
                box-shadow: 0 0 8px var(--accent-glow);
            }

            .progress-bar-fill.over {
                background: linear-gradient(90deg, var(--danger), #ff6b6b);
            }

            .progress-bar-fill.done {
                background: linear-gradient(90deg, var(--success), #34d399);
            }

            /* ── TOTALS ROW ── */
            .totals-row {
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 16px;
                margin-top: 24px;
            }

            .total-box {
                background: var(--bg-card2);
                border: 1px solid var(--border);
                border-radius: var(--radius-sm);
                padding: 18px 22px;
                position: relative;
                overflow: hidden;
            }

            .total-box::after {
                content: '';
                position: absolute;
                top: 0;
                left: 0;
                right: 0;
                height: 2px;
            }

            .total-box.main-box::after {
                background: linear-gradient(90deg, var(--accent), var(--accent2));
            }

            .total-box.rem-box::after {
                background: linear-gradient(90deg, var(--danger), #fb923c);
            }

            .total-box label {
                display: block;
                font-size: 11px;
                font-weight: 700;
                text-transform: uppercase;
                letter-spacing: 1px;
                color: var(--text-muted);
                margin-bottom: 8px;
            }

            .total-box .val {
                font-family: 'JetBrains Mono', monospace;
                font-size: 32px;
                font-weight: 500;
                line-height: 1;
            }

            .total-box.main-box .val {
                color: var(--accent);
            }

            .total-box.rem-box .val {
                color: var(--danger);
            }

            /* ── STAGE CARD ── */
            .stage-card {
                background: var(--bg-card2);
                border: 1px solid var(--border);
                border-radius: var(--radius);
                margin-bottom: 20px;
                overflow: hidden;
                animation: slideIn 0.25s ease;
            }

            @keyframes slideIn {
                from {
                    opacity: 0;
                    transform: translateY(12px);
                }

                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }

            .stage-card-header {
                display: flex;
                justify-content: space-between;
                align-items: center;
                padding: 16px 22px;
                border-bottom: 1px solid var(--border);
                background: rgba(99, 102, 241, 0.03);
            }

            .stage-badge {
                display: inline-flex;
                align-items: center;
                gap: 8px;
            }

            .stage-badge .dot {
                width: 8px;
                height: 8px;
                border-radius: 50%;
                background: var(--accent);
                box-shadow: 0 0 8px var(--accent);
            }

            .stage-badge span {
                font-size: 13px;
                font-weight: 700;
                color: var(--text-dim);
                text-transform: uppercase;
                letter-spacing: 0.8px;
            }

            .stage-card-body {
                padding: 22px;
            }

            /* ── FORM FIELDS ── */
            .field-group {
                display: grid;
                grid-template-columns: 1fr 140px 120px;
                gap: 16px;
                margin-bottom: 4px;
            }

            .field label {
                display: block;
                font-size: 11.5px;
                font-weight: 700;
                text-transform: uppercase;
                letter-spacing: 0.7px;
                color: var(--text-muted);
                margin-bottom: 8px;
            }

            .field input {
                width: 100%;
                padding: 11px 14px;
                background: var(--bg-input);
                border: 1px solid var(--border);
                border-radius: var(--radius-sm);
                color: var(--text);
                font-size: 14px;
                font-family: 'Plus Jakarta Sans', sans-serif;
                font-weight: 500;
                transition: border-color 0.2s, box-shadow 0.2s;
                outline: none;
            }

            .field input:focus {
                border-color: var(--accent);
                box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.15);
            }

            .field input[type=number] {
                font-family: 'JetBrains Mono', monospace;
            }

            /* ── SUB STAGE SECTION ── */
            .sub-section-header {
                display: flex;
                justify-content: space-between;
                align-items: center;
                margin: 20px 0 14px;
                padding-top: 20px;
                border-top: 1px solid var(--border);
            }

            .sub-section-title {
                font-size: 12px;
                font-weight: 700;
                text-transform: uppercase;
                letter-spacing: 1px;
                color: var(--text-muted);
                display: flex;
                align-items: center;
                gap: 8px;
            }

            .sub-section-title::before {
                content: '';
                display: block;
                width: 3px;
                height: 14px;
                background: var(--accent2);
                border-radius: 2px;
            }

            .sub-stage-wrapper {
                display: flex;
                flex-direction: column;
                gap: 10px;
            }

            .sub-row {
                display: grid;
                grid-template-columns: 1fr 120px 90px 80px;
                gap: 12px;
                align-items: end;
                background: #f8fafc;
                border: 1px solid var(--border);
                border-radius: var(--radius-sm);
                padding: 14px 16px;
                animation: slideIn 0.2s ease;
            }

            .sub-row .field label {
                font-size: 10.5px;
            }

            .sub-row .field input {
                padding: 9px 12px;
                font-size: 13px;
            }

            .sub-row .btn-danger {
                width: 100%;
                padding: 9px 8px;
                justify-content: center;
            }

            /* ── SUB TOTAL PILL ── */
            .sub-total-pill {
                display: inline-flex;
                align-items: center;
                gap: 6px;
                margin-top: 12px;
                padding: 6px 14px;
                background: rgba(217, 119, 6, 0.07);
                border: 1px solid rgba(217, 119, 6, 0.2);
                border-radius: 99px;
                font-size: 12px;
                font-weight: 600;
                color: #92400e;
            }

            .sub-total-pill .subTotal {
                font-family: 'JetBrains Mono', monospace;
            }

            /* Scrollbar */
            ::-webkit-scrollbar {
                width: 6px;
            }

            ::-webkit-scrollbar-track {
                background: transparent;
            }

            ::-webkit-scrollbar-thumb {
                background: #cbd5e1;
                border-radius: 3px;
            }
        </style>

        <div class="sm-wrap">

            {{-- PAGE HEADER --}}
            <div class="sm-header">
                <div class="sm-header-left">
                    <h1>Stage Management</h1>
                    <p>Main Stage total must always equal 100%</p>
                </div>
                <a href="{{ route('stages.index') }}" class="btn-back">
                    ← Back
                </a>
            </div>

            {{-- ALERTS --}}
            @if(session('success'))
            <div class="sm-alert sm-alert-success">✓ &nbsp;{{ session('success') }}</div>
            @endif

            @if(session('error'))
            <div class="sm-alert sm-alert-danger">✕ &nbsp;{{ session('error') }}</div>
            @endif

            {{-- SECTION ADD TOP BAR --}}
            <div style="display:flex; gap:12px; align-items:center; margin-bottom:16px; flex-wrap:wrap;">

                {{-- NEW SECTION INPUT --}}
                <input type="text"
                    id="newSectionInput"
                    placeholder="Enter new section name"
                    style="
                padding:10px 12px;
                border:1px solid var(--border);
                border-radius:8px;
                font-size:13px;
                min-width:220px;
            ">

                {{-- ADD SECTION BUTTON --}}
                <button type="button"
                    onclick="addSection()"
                    class="btn-primary">
                    + Add Section
                </button>

                {{-- SECTION DROPDOWN --}}
                <select id="sectionDropdown"
                    onchange="onSectionChange(this)"
                    style="
                padding:10px 12px;
                border:1px solid var(--border);
                border-radius:8px;
                font-size:13px;
                min-width:200px;
                background:#fff;
            ">
                    <option value="">-- Select Section --</option>
                </select>

            </div>

            <form action="{{ count($stages) ? route('stages.update') : route('stages.store') }}" method="POST">
                @csrf

                <input type="hidden" name="section" id="selectedSection">

                <div class="sm-card">

                    <div class="sm-card-header">
                        <h2>Stage Form</h2>
                        <button type="button" class="btn-primary" id="addStageBtn" onclick="addStage()">
                            + Add Stage
                        </button>
                    </div>

                    <div class="sm-card-body">

                        {{-- PROGRESS BAR --}}
                        <div class="progress-container">
                            <div class="progress-labels">
                                <span>Progress</span>
                                <span class="progress-pct"><span id="progPct">0</span>% allocated</span>
                            </div>
                            <div class="progress-bar-track">
                                <div class="progress-bar-fill" id="progressFill" style="width:0%"></div>
                            </div>
                        </div>

                        {{-- STAGE WRAPPER --}}
                        <div id="stageWrapper"></div>

                        {{-- TOTALS --}}
                        <div class="totals-row">
                            <div class="total-box main-box">
                                <label>Main Total</label>
                                <div class="val"><span id="mainTotal">0</span>%</div>
                            </div>
                            <div class="total-box rem-box">
                                <label>Remaining</label>
                                <div class="val"><span id="remainingTotal">100</span>%</div>
                            </div>
                        </div>

                        <button class="btn-save" id="submitBtn" disabled>
                            Save Stages
                        </button>

                    </div>
                </div>

            </form>
        </div>

        <script>
            let stageIndex = 0;
            let currentSection = "";

            // ===========================
            // GET MAIN TOTAL
            // ===========================
            function getMainTotal() {

                let total = 0;

                document.querySelectorAll('.main-present').forEach(input => {

                    total += Number(input.value) || 0;

                });

                return total;
            }

            // ===========================
            // VALIDATE ALL SUB TOTALS
            //
            // RULES:
            //
            // 1. NO SUB ROWS => VALID
            // 2. IF SUB ROW EXISTS => TOTAL MUST BE 100
            // 3. EMPTY SUB ROW => INVALID
            // 4. SAVE BTN ONLY WHEN:
            //      MAIN TOTAL = 100
            //      AND ALL SUB TOTALS VALID
            // ===========================
            function validateAllSubTotals() {

                let valid = true;

                document.querySelectorAll('.stage-card').forEach(card => {

                    let rows = card.querySelectorAll('.sub-row');

                    let total = 0;

                    let hasRows = rows.length > 0;

                    rows.forEach(row => {

                        let nameInput = row.querySelector('.sub-name');
                        let perInput = row.querySelector('.sub-present');

                        let nameVal = nameInput.value.trim();
                        let perVal = perInput.value;

                        // EMPTY ROW INVALID
                        if (nameVal !== '' || perVal !== '') {

                            if (nameVal === '' || perVal === '') {

                                valid = false;
                            }
                        }

                        total += Number(perVal) || 0;

                    });

                    // UPDATE UI
                    card.querySelector('.subTotal').innerText = total;

                    let pill = card.querySelector('.sub-total-pill');

                    // ===========================
                    // NO SUB STAGES
                    // ===========================
                    if (!hasRows) {

                        pill.style.borderColor = 'rgba(217,119,6,0.2)';
                        pill.style.background = 'rgba(217,119,6,0.07)';
                        pill.style.color = '#92400e';

                        return;
                    }

                    // ===========================
                    // SUB ROW EXISTS
                    // TOTAL MUST BE 100
                    // ===========================
                    if (total !== 100) {

                        valid = false;

                        pill.style.borderColor = '#e11d48';
                        pill.style.background = 'rgba(225,29,72,0.08)';
                        pill.style.color = '#be123c';

                    } else {

                        pill.style.borderColor = 'rgba(5,150,105,0.25)';
                        pill.style.background = 'rgba(5,150,105,0.07)';
                        pill.style.color = '#065f46';
                    }

                });

                return valid;
            }

            // ===========================
            // ENABLE / DISABLE SAVE BTN
            // ===========================
            function updateSaveButton() {

                let total = getMainTotal();

                let subValid = validateAllSubTotals();

                let submitBtn = document.getElementById('submitBtn');
                let addStageBtn = document.getElementById('addStageBtn');

                // MAIN TOTAL UI
                document.getElementById('mainTotal').innerText = total;

                document.getElementById('remainingTotal').innerText = 100 - total;

                document.getElementById('progPct').innerText = total;

                // PROGRESS BAR
                let fill = document.getElementById('progressFill');

                fill.style.width = Math.min(total, 100) + '%';

                fill.className = 'progress-bar-fill';

                if (total > 100) {

                    fill.classList.add('over');

                } else if (total === 100) {

                    fill.classList.add('done');
                }

                // SAVE BTN
                if (total === 100 && subValid && currentSection !== "") {
                    submitBtn.disabled = false;
                } else {
                    submitBtn.disabled = true;
                }

                // ADD STAGE BTN
                if (total >= 100) {

                    addStageBtn.disabled = true;

                } else {

                    addStageBtn.disabled = false;
                }
            }

            // ===========================
            // ADD MAIN STAGE
            // ===========================
            function addStage(data = null) {

                let total = getMainTotal();

                if (total >= 100 && data == null) {

                    alert('100% already completed. Reduce existing stage % first.');

                    return;
                }

                let currentIndex = stageIndex;
                let id = data?.id ?? '';

                let name = data?.name ?? '';
                let present = data?.present ?? '';
                let order_no = data?.order_no ?? 1;

                let html = `

        <div class="stage-card" data-stage-index="${currentIndex}">

            <div class="stage-card-header">

                <div class="stage-badge">

                    <div class="dot"></div>

                    <span>Main Stage</span>

                </div>

                <button type="button"
                    class="btn-danger"
                    onclick="removeStage(this)">

                    ✕ Delete

                </button>

            </div>

            <div class="stage-card-body">

                <div class="field-group">
                    <input type="hidden" name="stages[${currentIndex}][id]" value="${id}">

                    <div class="field">

                        <label>Stage Name</label>

                        <input type="text"
                            name="stages[${currentIndex}][name]"
                            value="${name}"
                            required>

                    </div>

                    <div class="field">

                        <label>Present %</label>

                        <input type="number"
                            name="stages[${currentIndex}][present]"
                            class="main-present"
                            value="${present}"
                            min="0"
                            max="100"
                            onkeyup="updateSaveButton()"
                            onchange="updateSaveButton()"
                            required>

                    </div>

                    <div class="field">

                        <label>Order No</label>

                        <input type="number"
                            name="stages[${currentIndex}][order_no]"
                            value="${order_no}">

                    </div>

                </div>

                <div class="sub-section-header">

                    <div class="sub-section-title">
                        Sub Stages
                    </div>

                    <button type="button"
                        class="btn-sub-add"
                        onclick="addSubStage(this, ${currentIndex})">

                        + Add Sub Stage

                    </button>

                </div>

                <div class="sub-stage-wrapper"></div>

                <div class="sub-total-pill">

                    Sub Total :
                    <span class="subTotal">0</span>%

                </div>

            </div>

        </div>

        `;

                document
                    .getElementById('stageWrapper')
                    .insertAdjacentHTML('beforeend', html);

                // LOAD OLD SUB STAGES
                if (data?.sub_stages && data.sub_stages.length > 0) {

                    data.sub_stages.forEach((sub, subIndex) => {

                        appendSubStage(currentIndex, subIndex, sub);

                    });
                }

                stageIndex++;

                updateSaveButton();
            }

            // ===========================
            // APPEND SUB STAGE
            // ===========================
            function appendSubStage(index, count, data = null) {

                let id = data?.id ?? '';
                let name = data?.name ?? '';
                let present = data?.present ?? '';
                let order_no = data?.order_no ?? '';

                let html = `

        <div class="sub-row">
            <input type="hidden" name="stages[${index}][sub_stages][${count}][id]" value="${id}">

            <div class="field">

                <label>Name</label>

                <input type="text"
                    name="stages[${index}][sub_stages][${count}][name]"
                    class="sub-name"
                    value="${name}"
                    onkeyup="updateSaveButton()"
                    onchange="updateSaveButton()">

            </div>

            <div class="field">

                <label>Present %</label>

                <input type="number"
                    name="stages[${index}][sub_stages][${count}][present]"
                    class="sub-present"
                    value="${present}"
                    min="0"
                    max="100"
                    onkeyup="calculateSubTotal(this)"
                    onchange="calculateSubTotal(this)">

            </div>

            <div class="field">

                <label>Order</label>

                <input type="number"
                    name="stages[${index}][sub_stages][${count}][order_no]"
                    value="${order_no}">

            </div>

            <div class="field">

                <label style="opacity:0">_</label>

                <button type="button"
                    class="btn-danger"
                    onclick="removeSubStage(this)">

                    ✕

                </button>

            </div>

        </div>

        `;

                document
                    .querySelector(`.stage-card[data-stage-index="${index}"] .sub-stage-wrapper`)
                    .insertAdjacentHTML('beforeend', html);

                updateSaveButton();
            }

            // ===========================
            // ADD SUB STAGE
            // ===========================
            function addSubStage(btn, index) {

                let wrapper = btn
                    .closest('.stage-card')
                    .querySelector('.sub-stage-wrapper');

                let count = wrapper.querySelectorAll('.sub-row').length;

                appendSubStage(index, count);

                updateSaveButton();
            }

            // ===========================
            // REMOVE MAIN STAGE
            // ===========================
            function removeStage(btn) {

                btn.closest('.stage-card').remove();

                updateSaveButton();
            }

            // ===========================
            // REMOVE SUB STAGE
            // ===========================
            function removeSubStage(btn) {

                btn.closest('.sub-row').remove();

                updateSaveButton();
            }

            // ===========================
            // CALCULATE SUB TOTAL
            // ===========================
            function calculateSubTotal(el) {

                let card = el.closest('.stage-card');

                let total = 0;

                card.querySelectorAll('.sub-present').forEach(input => {

                    total += Number(input.value) || 0;

                });

                card.querySelector('.subTotal').innerText = total;

                if (total > 100) {

                    alert('Sub stage total cannot exceed 100%');
                }

                updateSaveButton();
            }

            // ===========================
            // LOAD OLD DATA / DB DATA
            // ===========================
            document.addEventListener('DOMContentLoaded', function() {

                let dbSections = @json($sections);
                let dropdown = document.getElementById('sectionDropdown');

                dbSections.forEach(sec => {
                    if (sec) {
                        let option = document.createElement('option');
                        option.value = sec;
                        option.text = sec;
                        dropdown.appendChild(option);
                    }
                });

                // 🚨 IMPORTANT: NO AUTO SELECT
                currentSection = "";
                document.getElementById('selectedSection').value = "";

                // EMPTY FORM ON LOAD
                document.getElementById('stageWrapper').innerHTML = "";


                // OLD FORM DATA
                let oldStages = @json(old('stages', []));

                // DB DATA
                let dbStages = @json($stages);

                // ===========================
                // VALIDATION ERROR OLD DATA
                // ===========================
                if (oldStages.length > 0) {

                    oldStages.forEach(stage => {

                        addStage({
                            id: stage.id ?? '',
                            name: stage.name ?? '',
                            present: stage.present ?? '',
                            order_no: stage.order_no ?? '',
                            sub_stages: stage.sub_stages ?? []

                        });

                    });

                }

                // ===========================
                // DB DATA
                // ===========================
                else if (dbStages.length > 0) {

                    dbStages.forEach(stage => {

                        addStage({
                            id: stage.id,
                            name: stage.name,
                            present: stage.present,
                            order_no: stage.order_no,
                            sub_stages: stage.children || []

                        });

                    });

                }

                // ===========================
                // EMPTY FIRST TIME
                // ===========================
                else {

                    addStage();
                }

                updateSaveButton();

            });

            let sections = [];

            // ADD SECTION
            function addSection() {

    let input = document.getElementById('newSectionInput');
    let value = input.value.trim();

    if (!value) {
        alert('Please enter section name');
        return;
    }

    let dropdown = document.getElementById('sectionDropdown');

    // already exists check
    let exists = [...dropdown.options].some(opt => 
        opt.value.toLowerCase() === value.toLowerCase()
    );

    if (exists) {
        alert('Section already exists');
        return;
    }

    // ADD OPTION
    let option = document.createElement('option');
    option.value = value;
    option.text = value;

    dropdown.appendChild(option);

    // AUTO SELECT NEW SECTION
    dropdown.value = value;

    currentSection = value;

    document.getElementById('selectedSection').value = value;

    // =========================
    // IMPORTANT
    // CLEAR OLD DB DATA
    // =========================
    document.getElementById('stageWrapper').innerHTML = '';

    stageIndex = 0;

    // OPTIONAL:
    // first empty stage show karna ho
    addStage();

    input.value = '';

    updateSaveButton();
}

            // ON SECTION CHANGE
            function onSectionChange(el) {

                currentSection = el.value;

                document.getElementById('selectedSection').value = currentSection;

                if (!currentSection) {
                    document.getElementById('stageWrapper').innerHTML = '';
                    updateSaveButton();
                    return;
                }

                loadSectionData(currentSection);
            }

            function loadSectionData(section) {

                fetch(`/stages/by-section?section=${section}`)
                    .then(res => res.json())
                    .then(data => {

                        document.getElementById('stageWrapper').innerHTML = '';
                        stageIndex = 0;

                        data.forEach(stage => {
                            addStage({
                                id: stage.id,
                                name: stage.name,
                                present: stage.present,
                                order_no: stage.order_no,
                                sub_stages: stage.children || []
                            });
                        });

                        updateSaveButton();
                    });
            }
        </script>

    @endsection