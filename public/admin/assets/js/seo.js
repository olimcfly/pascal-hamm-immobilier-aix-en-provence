(function () {
    async function postForm(url, payload) {
        const body = new URLSearchParams(payload || {});
        const response = await fetch(url, {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body
        });
        const data = await response.json().catch(() => ({}));
        if (!response.ok || data.success === false) {
            throw new Error(data.message || data.error || 'Action impossible.');
        }
        return data;
    }

    function sitemapToken() {
        const input = document.getElementById('seo-sitemap-csrf');
        return input ? input.value : '';
    }

    function showSitemapResult(message, type) {
        const box = document.getElementById('sitemap-action-result');
        if (!box) return;
        box.hidden = false;
        box.classList.remove('is-success', 'is-error');
        if (type) box.classList.add(type === 'error' ? 'is-error' : 'is-success');
        box.textContent = message;
    }

    function setButtonBusy(button, busy) {
        if (!button) return;
        if (busy) {
            button.dataset.originalText = button.innerHTML;
            button.innerHTML = 'Traitement...';
            button.disabled = true;
        } else {
            button.innerHTML = button.dataset.originalText || button.innerHTML;
            button.disabled = false;
        }
    }

    window.runSitemapAction = async function runSitemapAction(action, button) {
        const csrf = sitemapToken();
        if (!csrf) {
            showSitemapResult('Token de sécurité introuvable. Rechargez la page.', 'error');
            return;
        }

        const endpoint = '/admin/api/seo/sitemap.php';

        setButtonBusy(button, true);
        showSitemapResult('Action en cours...', 'success');

        try {
            const result = await postForm(endpoint, {csrf_token: csrf, action});
            const data = result.data || {};

            if (action === 'generate') {
                const preview = document.getElementById('sitemap-xml-preview');
                if (preview && data.xml) preview.textContent = data.xml;
                showSitemapResult('Sitemap généré : ' + (data.total_urls || 0) + ' URL(s). Rechargez la page pour voir les statistiques à jour.', 'success');
                return;
            }

            if (action === 'verify') {
                const issues = Array.isArray(data.issues) ? data.issues.length : 0;
                showSitemapResult('Vérification terminée : ' + issues + ' anomalie(s), ' + (data.total_urls || 0) + ' URL(s) détectées.', 'success');
                return;
            }

            if (action === 'submit') {
                showSitemapResult(data.message || 'Soumission préparée. Connectez Google Search Console pour activer l’envoi automatique.', data.status === 'warning' ? 'success' : 'success');
            }
        } catch (error) {
            showSitemapResult(error.message || 'Erreur pendant l’action sitemap.', 'error');
        } finally {
            setButtonBusy(button, false);
        }
    };
})();
