/**
 * Mobile Money — app.js
 * Bootstrap 5.3 + Bootstrap Icons 1.11
 */

'use strict';

/* ──────────────────────────────────────────────────────────
   1. Auto-dismiss des alerts après 5 secondes
   ────────────────────────────────────────────────────────── */
function initAutoDismissAlerts() {
    document.querySelectorAll('.alert.alert-dismissible').forEach(alertEl => {
        setTimeout(() => {
            const bsAlert = bootstrap.Alert.getOrCreateInstance(alertEl);
            if (bsAlert) bsAlert.close();
        }, 5000);
    });
}

/* ──────────────────────────────────────────────────────────
   2. Confirmation avant soumission retrait / transfert
   ────────────────────────────────────────────────────────── */
function initConfirmForms() {
    const confirmForms = document.querySelectorAll('[data-confirm-submit]');
    confirmForms.forEach(form => {
        form.addEventListener('submit', function (e) {
            const msg = this.dataset.confirmSubmit || 'Confirmer cette opération ?';
            if (!confirm(msg)) {
                e.preventDefault();
            }
        });
    });
}

/* ──────────────────────────────────────────────────────────
   3. Calcul de frais en temps réel
   Fonctionne avec un élément <script> injectant window.feeScales
   dans la page cliente (dépôt / retrait / transfert).

   Format attendu de window.feeScales :
   {
     "deposit":    [ { amount_min, amount_max, fee_amount }, … ],
     "withdrawal": [ … ],
     "transfer":   [ … ]
   }

   Éléments HTML ciblés :
     - #amount        (input montant)
     - #operation_type (select type, optionnel si type fixe)
     - #fee-display   (élément où afficher le frais)
     - #total-display (élément où afficher le total)
   ────────────────────────────────────────────────────────── */
function getFeeFromScales(scales, amount) {
    if (!Array.isArray(scales) || !amount || amount <= 0) return null;
    for (const scale of scales) {
        if (amount >= Number(scale.amount_min) && amount <= Number(scale.amount_max)) {
            return Number(scale.fee_amount);
        }
    }
    return null;
}

function updateFeeDisplay() {
    const amountInput = document.getElementById('amount');
    const typeSelect  = document.getElementById('operation_type');
    const feeDisplay  = document.getElementById('fee-display');
    const totalDisplay = document.getElementById('total-display');

    if (!amountInput || !feeDisplay) return;

    const amount = parseFloat(amountInput.value) || 0;
    const type   = typeSelect ? typeSelect.value : (amountInput.dataset.opType || 'withdrawal');
    const scales = window.feeScales ? (window.feeScales[type] || []) : [];
    const fee    = getFeeFromScales(scales, amount);

    if (fee !== null) {
        feeDisplay.textContent = formatAr(fee);
        feeDisplay.classList.remove('text-muted');
        feeDisplay.classList.add('fw-bold', 'text-operator');

        if (totalDisplay) {
            const total = type === 'deposit' ? amount : amount + fee;
            totalDisplay.textContent = formatAr(total);
        }
    } else {
        feeDisplay.textContent = '—';
        feeDisplay.classList.add('text-muted');
        feeDisplay.classList.remove('fw-bold', 'text-operator');
        if (totalDisplay) totalDisplay.textContent = '—';
    }
}

function initFeeCalculator() {
    const amountInput = document.getElementById('amount');
    const typeSelect  = document.getElementById('operation_type');

    if (!amountInput) return;
    if (!window.feeScales) return;

    amountInput.addEventListener('input', updateFeeDisplay);
    if (typeSelect) typeSelect.addEventListener('change', updateFeeDisplay);

    // Calcul initial si un montant est déjà saisi
    updateFeeDisplay();
}

/* ──────────────────────────────────────────────────────────
   4. Fetch dynamique des frais (fallback API si pas de window.feeScales)
   GET /operator/getFeeInfo/{operatorId}/{type}/{amount}
   ────────────────────────────────────────────────────────── */
function initFeeCalculatorApi() {
    const amountInput    = document.getElementById('amount');
    const operatorSelect = document.getElementById('operator_id');
    const typeSelect     = document.getElementById('operation_type');
    const feeDisplay     = document.getElementById('fee-display');

    if (!amountInput || !feeDisplay || window.feeScales) return;

    let debounceTimer;

    async function fetchFee() {
        const amount     = parseFloat(amountInput.value) || 0;
        const operatorId = operatorSelect ? operatorSelect.value : null;
        const type       = typeSelect ? typeSelect.value : (amountInput.dataset.opType || 'withdrawal');

        if (amount <= 0 || !operatorId) return;

        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(async () => {
            try {
                const url = `/operator/getFeeInfo/${operatorId}/${type}/${amount}`;
                const res = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
                if (!res.ok) return;
                const data = await res.json();
                if (data && data.fee !== undefined) {
                    feeDisplay.textContent = formatAr(data.fee);
                    feeDisplay.classList.remove('text-muted');
                    feeDisplay.classList.add('fw-bold');
                }
            } catch (err) {
                console.warn('Fee API error:', err);
            }
        }, 400);
    }

    amountInput.addEventListener('input', fetchFee);
    if (typeSelect)     typeSelect.addEventListener('change', fetchFee);
    if (operatorSelect) operatorSelect.addEventListener('change', fetchFee);
}

/* ──────────────────────────────────────────────────────────
   5. Utilitaires
   ────────────────────────────────────────────────────────── */
function formatAr(value) {
    return new Intl.NumberFormat('fr-FR').format(value) + ' Ar';
}

function validateMalagasyPhone(phone) {
    return /^(?:\+?261|0)[2-9]\d{7,8}$/.test(phone.replace(/\s/g, ''));
}

/* ──────────────────────────────────────────────────────────
   6. Validation côté client des formulaires opération
   ────────────────────────────────────────────────────────── */
function initFormValidation() {
    const forms = document.querySelectorAll('.needs-validation');
    forms.forEach(form => {
        form.addEventListener('submit', event => {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        });
    });
}

/* ──────────────────────────────────────────────────────────
   7. Tooltips Bootstrap
   ────────────────────────────────────────────────────────── */
function initTooltips() {
    document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => {
        bootstrap.Tooltip.getOrCreateInstance(el);
    });
}

/* ──────────────────────────────────────────────────────────
   Init au chargement du DOM
   ────────────────────────────────────────────────────────── */
document.addEventListener('DOMContentLoaded', function () {
    initAutoDismissAlerts();
    initConfirmForms();
    initFeeCalculator();
    initFeeCalculatorApi();
    initFormValidation();
    initTooltips();
});
