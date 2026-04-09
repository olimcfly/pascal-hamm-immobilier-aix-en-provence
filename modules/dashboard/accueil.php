<?php
$pageTitle = "Tableau de bord";
$pageDescription = "Vue d'ensemble de votre activité immobilière";

function renderContent() {
    ?>
    <div class="section-header">
        <h1>HUB <span class="section-title">Tableau de bord</span></h1>
        <p class="section-subtitle">Vue d'ensemble de votre activité</p>
    </div>

    <div class="dashboard-grid">
        <div class="cards-container">
            <div class="card">
                <div class="card-header">
                    <div class="card-icon">🏠</div>
                    <div class="card-title">Biens en gestion</div>
                </div>
                <div class="card-value" id="biens-count">Chargement...</div>
            </div>

            <div class="card">
                <div class="card-header">
                    <div class="card-icon">📞</div>
                    <div class="card-title">Leads ce mois</div>
                </div>
                <div class="card-value" id="leads-count">Chargement...</div>
            </div>

            <div class="card">
                <div class="card-header">
                    <div class="card-icon">📰</div>
                    <div class="card-title">Articles publiés</div>
                </div>
                <div class="card-value" id="blog-count">Chargement...</div>
            </div>

            <div class="card">
                <div class="card-header">
                    <div class="card-icon">📅</div>
                    <div class="card-title">Posts planifiés</div>
                </div>
                <div class="card-value" id="social-count">Chargement...</div>
            </div>

            <div class="card">
                <div class="card-header">
                    <div class="card-icon">🔎</div>
                    <div class="card-title">Score SEO global</div>
                </div>
                <div class="card-value" id="seo-score">Chargement...</div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <div class="card-icon">🧾</div>
                <div class="card-title">Derniers leads reçus</div>
            </div>
            <div id="latest-leads" class="card-description">Chargement...</div>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        fetch('/admin/api/dashboard/stats')
            .then(function(response) {
                if (!response.ok) {
                    throw new Error('Erreur API dashboard');
                }
                return response.json();
            })
            .then(function(data) {
                document.getElementById('biens-count').textContent = Number(data.biens || 0).toLocaleString('fr-FR');
                document.getElementById('leads-count').textContent = Number(data.leads || 0).toLocaleString('fr-FR');
                document.getElementById('blog-count').textContent = Number(data.blog_articles_publies || 0).toLocaleString('fr-FR');
                document.getElementById('social-count').textContent = Number(data.social_posts_planifies || 0).toLocaleString('fr-FR');

                var seoValue = document.getElementById('seo-score');
                if (data.seo_score_global === null || data.seo_score_global === undefined) {
                    seoValue.textContent = '—';
                } else {
                    seoValue.textContent = Number(data.seo_score_global).toLocaleString('fr-FR') + '/100';
                }

                var leadsContainer = document.getElementById('latest-leads');
                var leads = Array.isArray(data.derniers_leads) ? data.derniers_leads : [];

                if (leads.length === 0) {
                    leadsContainer.innerHTML = '<p class="empty">Aucun lead récent.</p>';
                    return;
                }

                var list = document.createElement('ul');
                list.className = 'kw-list';

                leads.forEach(function(lead) {
                    var date = lead.created_at ? new Date(lead.created_at.replace(' ', 'T')) : null;
                    var dateText = date && !isNaN(date.getTime())
                        ? date.toLocaleDateString('fr-FR', { day: '2-digit', month: '2-digit', year: 'numeric' })
                        : 'Date inconnue';

                    var item = document.createElement('li');
                    item.className = 'kw-item';
                    item.innerHTML =
                        '<span>' +
                            '<strong>' + (lead.nom || 'Lead sans nom') + '</strong><br>' +
                            '<small>' + (lead.email || 'Email non renseigné') + ' · ' + (lead.source || 'source inconnue') + '</small>' +
                        '</span>' +
                        '<span class="badge badge-muted">' + dateText + '</span>';
                    list.appendChild(item);
                });

                leadsContainer.innerHTML = '';
                leadsContainer.appendChild(list);
            })
            .catch(function() {
                document.getElementById('biens-count').textContent = '—';
                document.getElementById('leads-count').textContent = '—';
                document.getElementById('blog-count').textContent = '—';
                document.getElementById('social-count').textContent = '—';
                document.getElementById('seo-score').textContent = '—';
                document.getElementById('latest-leads').innerHTML = '<p class="empty">Impossible de charger les leads.</p>';
            });
    });
    </script>
    <?php
}
