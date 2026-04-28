/* ── Estimation JS ─────────────────────────────────────────── */
'use strict';

// ── Type de bien — cartes sélectionnables ──────────────────────
document.querySelectorAll('.type-card').forEach(card => {
    const radio = card.querySelector('input[type="radio"]');
    if (!radio) return;

    // Restaure l'état si le navigateur conserve les valeurs
    if (radio.checked) card.classList.add('active');

    card.addEventListener('click', () => {
        document.querySelectorAll('.type-card').forEach(c => {
            c.classList.remove('active');
            c.setAttribute('aria-pressed', 'false');
        });
        card.classList.add('active');
        card.setAttribute('aria-pressed', 'true');
        radio.checked = true;

        // Masque l'erreur type si elle était affichée
        const errType = document.getElementById('err-type');
        if (errType) errType.hidden = true;
    });

    card.setAttribute('role', 'button');
    card.setAttribute('tabindex', '0');
    card.setAttribute('aria-pressed', radio.checked ? 'true' : 'false');

    card.addEventListener('keydown', e => {
        if (e.key === ' ' || e.key === 'Enter') {
            e.preventDefault();
            card.click();
        }
    });
});

// ── Projet — boutons radio (synchro état « active ») ────────────
(function syncProjetToggle() {
    const radios = document.querySelectorAll('input[name="projet"]');
    if (!radios.length) return;

    function apply() {
        document.querySelectorAll('.projet-btn').forEach(btn => {
            const r = btn.querySelector('input[name="projet"]');
            btn.classList.toggle('active', !!(r && r.checked));
        });
    }

    radios.forEach(r => r.addEventListener('change', apply));
    apply();
}());

// ── Autocomplete ville/CP via api-adresse.data.gouv.fr ─────────
(function () {
    const input       = document.getElementById('est-localite');
    const suggestions = document.getElementById('localite-suggestions');
    const latInput    = document.getElementById('geo-lat');
    const lngInput    = document.getElementById('geo-lng');

    if (!input || !suggestions) return;

    let debounceTimer = null;
    let selectedIndex = -1;

    function renderSuggestions(features) {
        suggestions.innerHTML = '';
        selectedIndex = -1;

        if (!features || features.length === 0) {
            suggestions.hidden = true;
            return;
        }

        features.forEach((feat, i) => {
            const label   = feat.properties.label;
            const city    = feat.properties.city || feat.properties.name;
            const postcode = feat.properties.postcode || '';
            const coords  = feat.geometry && feat.geometry.coordinates;

            const li = document.createElement('li');
            li.className   = 'autocomplete-item';
            li.textContent = label;
            li.setAttribute('role', 'option');
            li.setAttribute('tabindex', '-1');
            li.dataset.index = i;

            li.addEventListener('mousedown', e => {
                e.preventDefault(); // empêche le blur de l'input
                input.value = city + (postcode ? ' ' + postcode : '');
                if (coords && coords.length >= 2) {
                    lngInput.value = coords[0];
                    latInput.value = coords[1];
                }
                suggestions.hidden = true;
                suggestions.innerHTML = '';
            });

            suggestions.appendChild(li);
        });

        suggestions.hidden = false;
    }

    function fetchSuggestions(q) {
        if (q.length < 2) {
            suggestions.hidden = true;
            return;
        }
        const url = 'https://api-adresse.data.gouv.fr/search/?q='
            + encodeURIComponent(q)
            + '&type=municipality&limit=6&autocomplete=1';

        fetch(url)
            .then(r => r.json())
            .then(data => renderSuggestions(data.features || []))
            .catch(() => { suggestions.hidden = true; });
    }

    input.addEventListener('input', () => {
        // Efface les coords si l'utilisateur retape
        latInput.value = '';
        lngInput.value = '';

        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => fetchSuggestions(input.value.trim()), 220);
    });

    input.addEventListener('keydown', e => {
        const items = suggestions.querySelectorAll('.autocomplete-item');
        if (!items.length) return;

        if (e.key === 'ArrowDown') {
            e.preventDefault();
            selectedIndex = Math.min(selectedIndex + 1, items.length - 1);
            items[selectedIndex]?.focus();
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            selectedIndex = Math.max(selectedIndex - 1, -1);
            if (selectedIndex === -1) input.focus();
            else items[selectedIndex]?.focus();
        } else if (e.key === 'Escape') {
            suggestions.hidden = true;
        }
    });

    document.addEventListener('click', e => {
        if (!input.contains(e.target) && !suggestions.contains(e.target)) {
            suggestions.hidden = true;
        }
    });
}());

// ── Submit — validation + état chargement ─────────────────────
(function () {
    const form = document.getElementById('form-estimation');
    const btn = document.getElementById('btn-submit-estimation')
        || document.querySelector('#form-estimation .btn--submit-estimation');
    const btnTxt = btn?.querySelector('.btn-text');
    const btnLdr = btn?.querySelector('.btn-loader');
    const btnIco = btn?.querySelector('.btn-icon');
    const errType = document.getElementById('err-type');

    if (!form || !btn) return;

    form.addEventListener('submit', e => {
        const typeChecked = form.querySelector('input[name="type_bien"]:checked');
        if (!typeChecked) {
            e.preventDefault();
            if (errType) errType.hidden = false;
            form.querySelector('.type-grid')?.scrollIntoView({ behavior: 'smooth', block: 'center' });
            return;
        }
        if (errType) errType.hidden = true;

        if (btnTxt) btnTxt.hidden = true;
        if (btnLdr) btnLdr.hidden = false;
        if (btnIco) btnIco.hidden = true;
        btn.disabled = true;
        btn.classList.add('is-loading');
    });
}());
