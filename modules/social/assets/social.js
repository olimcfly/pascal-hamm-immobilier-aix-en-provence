/* ═══════════════════════════════════════════════════════════
   SOCIAL MODULE — Section Trafic / Communication
   IMMO LOCAL+ · Interactions JS
   ══════════════════════════════════════════════════════════ */

document.addEventListener('DOMContentLoaded', () => {
    initSequenceAccordion();
    initFilterChips();
    initPersonaFilter();
});

/* ─── Accordion séquences ─── */
function initSequenceAccordion() {
    document.querySelectorAll('.seq-head').forEach((head) => {
        head.addEventListener('click', () => {
            const row = head.closest('.seq-row');
            if (!row) return;
            row.classList.toggle('is-open');
        });
    });
}

/* ─── Chips filtre statut ─── */
function initFilterChips() {
    document.querySelectorAll('[data-filter-group="status"] .s-chip').forEach((chip) => {
        chip.addEventListener('click', () => {
            document.querySelectorAll('[data-filter-group="status"] .s-chip').forEach(c => c.classList.remove('is-active'));
            chip.classList.add('is-active');

            const value = chip.dataset.filterValue ?? 'all';
            document.querySelectorAll('.seq-row[data-statut]').forEach((row) => {
                if (value === 'all' || row.dataset.statut === value) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    });
}

/* ─── Chips filtre persona ─── */
function initPersonaFilter() {
    document.querySelectorAll('[data-filter-group="persona"] .s-chip').forEach((chip) => {
        chip.addEventListener('click', () => {
            document.querySelectorAll('[data-filter-group="persona"] .s-chip').forEach(c => c.classList.remove('is-active'));
            chip.classList.add('is-active');

            const value = chip.dataset.filterValue ?? 'all';
            document.querySelectorAll('.seq-row[data-persona]').forEach((row) => {
                if (value === 'all') {
                    row.style.display = '';
                } else {
                    const rowPersona = (row.dataset.persona ?? '').toLowerCase();
                    row.style.display = rowPersona.includes(value.toLowerCase()) ? '' : 'none';
                }
            });
        });
    });
}
