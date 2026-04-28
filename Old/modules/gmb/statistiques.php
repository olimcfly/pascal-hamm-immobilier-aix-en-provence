<style>
.gmb-stats-kpis{display:grid;grid-template-columns:repeat(2,1fr);gap:.85rem}
.gmb-stat-card{background:#fff;border:1px solid var(--hub-border,#e2e8f0);border-radius:var(--hub-radius-md,14px);padding:1rem 1.2rem;box-shadow:var(--hub-shadow-sm)}
.gmb-stat-label{font-size:.72rem;text-transform:uppercase;letter-spacing:.06em;color:#64748b;font-weight:700;margin-bottom:.2rem}
.gmb-stat-value{font-size:2rem;font-weight:800;color:#0f172a;line-height:1}
.gmb-stat-sub{font-size:.78rem;color:#64748b;margin-top:.15rem}
@media(min-width:680px){.gmb-stats-kpis{grid-template-columns:repeat(4,1fr)}}
</style>

<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1rem;flex-wrap:wrap;gap:.5rem">
    <h2 style="margin:0;font-size:1rem;font-weight:700;color:#0f172a"><i class="fas fa-chart-bar" style="color:#3b82f6;margin-right:.4rem"></i>Données de performance</h2>
    <button class="hub-btn hub-btn--sm" data-action="get-stats"><i class="fas fa-rotate"></i> Actualiser</button>
</div>

<div id="gmb-stats-grid" class="gmb-stats-kpis">
    <div class="gmb-stat-card">
        <div class="gmb-stat-label">Impressions</div>
        <div class="gmb-stat-value" data-stat="impressions">—</div>
        <div class="gmb-stat-sub">vues de la fiche</div>
    </div>
    <div class="gmb-stat-card">
        <div class="gmb-stat-label">Clics site</div>
        <div class="gmb-stat-value" data-stat="clics_site">—</div>
        <div class="gmb-stat-sub">vers votre site</div>
    </div>
    <div class="gmb-stat-card">
        <div class="gmb-stat-label">Appels</div>
        <div class="gmb-stat-value" data-stat="appels">—</div>
        <div class="gmb-stat-sub">depuis Google</div>
    </div>
    <div class="gmb-stat-card">
        <div class="gmb-stat-label">Itinéraires</div>
        <div class="gmb-stat-value" data-stat="itineraires">—</div>
        <div class="gmb-stat-sub">demandes de route</div>
    </div>
    <div class="gmb-stat-card">
        <div class="gmb-stat-label">Photos vues</div>
        <div class="gmb-stat-value" data-stat="photos_vues">—</div>
        <div class="gmb-stat-sub">affichages photos</div>
    </div>
    <div class="gmb-stat-card">
        <div class="gmb-stat-label">Rech. directes</div>
        <div class="gmb-stat-value" data-stat="recherches_dir">—</div>
        <div class="gmb-stat-sub">par nom exact</div>
    </div>
    <div class="gmb-stat-card">
        <div class="gmb-stat-label">Rech. découvertes</div>
        <div class="gmb-stat-value" data-stat="recherches_disc">—</div>
        <div class="gmb-stat-sub">par catégorie</div>
    </div>
</div>
