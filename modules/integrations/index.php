<?php

declare(strict_types=1);

$pageTitle       = 'Santé des intégrations';
$pageDescription = 'État de toutes les APIs et connexions externes';

function renderContent(): void
{
    ?>
    <style>
    .int-kpis{display:grid;grid-template-columns:repeat(2,1fr);gap:.85rem}
    .int-kpi{background:#fff;border:1px solid var(--hub-border,#e2e8f0);border-radius:var(--hub-radius-md,14px);padding:1rem 1.2rem;box-shadow:var(--hub-shadow-sm)}
    .int-kpi-label{font-size:.72rem;text-transform:uppercase;letter-spacing:.06em;color:#64748b;font-weight:700;margin-bottom:.2rem}
    .int-kpi-value{font-size:2rem;font-weight:800;line-height:1}
    .int-kpi-sub{font-size:.78rem;color:#64748b;margin-top:.15rem}
    .int-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:1rem}
    .int-card{background:#fff;border:1px solid var(--hub-border,#e2e8f0);border-radius:var(--hub-radius-md,14px);padding:1rem 1.2rem;box-shadow:var(--hub-shadow-sm);border-left:4px solid #e2e8f0;transition:border-color .2s}
    .int-card.status-configured,.int-card.status-connected{border-left-color:#10b981}
    .int-card.status-missing{border-left-color:#ef4444}
    .int-card.status-warning{border-left-color:#f59e0b}
    .int-card.status-partial{border-left-color:#3b82f6}
    .int-card-head{display:flex;align-items:center;gap:.6rem;margin-bottom:.5rem}
    .int-card-icon{width:34px;height:34px;border-radius:9px;display:flex;align-items:center;justify-content:center;font-size:.9rem;flex-shrink:0}
    .int-card-title{font-size:.92rem;font-weight:700;color:#0f172a;margin:0}
    .int-card-source{font-size:.72rem;color:#94a3b8;margin:.1rem 0 0}
    .int-badge{display:inline-flex;align-items:center;gap:.25rem;padding:.18rem .55rem;border-radius:999px;font-size:.72rem;font-weight:700;margin-top:.35rem}
    .int-badge-configured,.int-badge-connected{background:#dcfce7;color:#166534}
    .int-badge-missing{background:#fee2e2;color:#991b1b}
    .int-badge-warning{background:#fef3c7;color:#92400e}
    .int-badge-partial{background:#dbeafe;color:#1e40af}
    .int-detail{font-size:.82rem;color:#64748b;margin-top:.3rem;line-height:1.4}
    .int-score-ring{width:90px;height:90px;flex-shrink:0}
    .int-header-row{display:flex;align-items:center;gap:1.5rem;background:#fff;border:1px solid var(--hub-border,#e2e8f0);border-radius:var(--hub-radius,16px);padding:1.25rem 1.4rem;box-shadow:var(--hub-shadow-sm)}
    @media(min-width:680px){.int-kpis{grid-template-columns:repeat(4,1fr)}}
    </style>

    <div class="hub-page">

        <header class="hub-hero">
            <div class="hub-hero-badge"><i class="fas fa-plug"></i> Configuration</div>
            <h1>Santé des intégrations</h1>
            <p>État en temps réel de toutes les APIs et connexions externes — identifiez et corrigez les manques.</p>
        </header>

        <div class="integ-info-wrap">
            <button class="integ-info-btn" type="button"><i class="fas fa-circle-info"></i> Comment fonctionne ce module ?</button>
            <div class="integ-info-tooltip" role="tooltip">
                <div class="integ-info-row"><i class="fas fa-plug" style="color:#3b82f6"></i><div><strong>À quoi ça sert</strong><br>Chaque API déconnectée désactive un module. Ce tableau identifie les manques en un coup d'œil.</div></div>
                <div class="integ-info-row"><i class="fas fa-check-circle" style="color:#10b981"></i><div><strong>Comment corriger</strong><br>Cliquez sur "Configurer" à côté d'une API manquante pour accéder directement à son formulaire dans Paramètres.</div></div>
                <div class="integ-info-row"><i class="fas fa-shield-halved" style="color:#f59e0b"></i><div><strong>Sécurité</strong><br>Les clés sont stockées dans .env ou en base chiffrée. Jamais dans le code source.</div></div>
            </div>
        </div>
        <style>.integ-info-wrap{position:relative;display:inline-block;margin-bottom:1.25rem;}.integ-info-btn{background:none;border:1px solid #e2e8f0;border-radius:6px;padding:.4rem .85rem;font-size:.85rem;color:#64748b;cursor:pointer;display:inline-flex;align-items:center;gap:.45rem;transition:background .15s,color .15s;}.integ-info-btn:hover{background:#f1f5f9;color:#334155;}.integ-info-tooltip{display:none;position:absolute;top:calc(100% + 8px);left:0;z-index:200;background:#fff;border:1px solid #e2e8f0;border-radius:10px;box-shadow:0 8px 24px rgba(0,0,0,.1);padding:1rem 1.1rem;width:400px;max-width:90vw;}.integ-info-tooltip.is-open{display:block;}.integ-info-row{display:flex;gap:.75rem;align-items:flex-start;padding:.55rem 0;font-size:.84rem;line-height:1.45;color:#374151;}.integ-info-row+.integ-info-row{border-top:1px solid #f1f5f9;}.integ-info-row>i{margin-top:2px;flex-shrink:0;width:16px;text-align:center;}</style>
        <script>(function(){var b=document.querySelector('.integ-info-btn'),t=document.querySelector('.integ-info-tooltip');if(!b||!t)return;b.addEventListener('click',function(e){e.stopPropagation();t.classList.toggle('is-open');});document.addEventListener('click',function(){t.classList.remove('is-open');});})();</script>

        <!-- Score + résumé -->
        <div id="int-summary-row" class="int-header-row">
            <div id="int-score-wrap" style="text-align:center">
                <div id="int-score-num" style="font-size:2.5rem;font-weight:800;color:#0f172a;line-height:1">—</div>
                <div style="font-size:.78rem;color:#64748b;margin-top:.2rem">Score santé</div>
            </div>
            <div style="flex:1">
                <div style="font-size:.95rem;font-weight:700;color:#0f172a;margin-bottom:.4rem" id="int-summary-text">Chargement…</div>
                <div id="int-progress-wrap">
                    <div style="background:#e2e8f0;border-radius:999px;height:8px;overflow:hidden">
                        <div id="int-progress-bar" style="height:100%;background:linear-gradient(90deg,#10b981,#3b82f6);transition:width .5s;width:0%"></div>
                    </div>
                </div>
            </div>
            <button class="hub-btn hub-btn--sm" onclick="loadHealth()" id="int-refresh-btn">
                <i class="fas fa-rotate"></i> Actualiser
            </button>
        </div>

        <!-- KPIs -->
        <div class="int-kpis" id="int-kpis" style="display:none">
            <div class="int-kpi">
                <div class="int-kpi-label">Configurées</div>
                <div class="int-kpi-value" id="kpi-configured" style="color:#10b981">—</div>
                <div class="int-kpi-sub">actives</div>
            </div>
            <div class="int-kpi">
                <div class="int-kpi-label">Manquantes</div>
                <div class="int-kpi-value" id="kpi-missing" style="color:#ef4444">—</div>
                <div class="int-kpi-sub">à configurer</div>
            </div>
            <div class="int-kpi">
                <div class="int-kpi-label">Avertissements</div>
                <div class="int-kpi-value" id="kpi-warning" style="color:#f59e0b">—</div>
                <div class="int-kpi-sub">à vérifier</div>
            </div>
            <div class="int-kpi">
                <div class="int-kpi-label">Vérifiées le</div>
                <div id="kpi-date" style="font-size:1rem;font-weight:700;color:#0f172a;line-height:1.2">—</div>
                <div class="int-kpi-sub">dernière vérification</div>
            </div>
        </div>

        <!-- Cards -->
        <div class="int-grid" id="int-grid">
            <div style="padding:2rem;text-align:center;color:#94a3b8;grid-column:1/-1">
                <i class="fas fa-spinner fa-spin fa-2x" style="opacity:.3;display:block;margin-bottom:.6rem"></i>
                <div style="font-size:.88rem">Vérification en cours…</div>
            </div>
        </div>

        <div class="hub-final-cta">
            <div>
                <h2>Configurer les APIs manquantes</h2>
                <p>Tous les paramètres sont centralisés dans les Paramètres du compte.</p>
            </div>
            <a href="/admin?module=parametres" class="hub-btn hub-btn--gold"><i class="fas fa-gear"></i> Ouvrir les Paramètres</a>
        </div>

    </div><!-- /.hub-page -->

    <script>
    const STATUS_CONFIG = {
        configured: { label: 'Configuré',  icon: 'fa-check-circle',  cls: 'int-badge-configured' },
        connected:  { label: 'Connecté',   icon: 'fa-link',           cls: 'int-badge-connected'  },
        missing:    { label: 'Manquant',   icon: 'fa-circle-xmark',   cls: 'int-badge-missing'    },
        warning:    { label: 'Attention',  icon: 'fa-triangle-exclamation', cls: 'int-badge-warning' },
        partial:    { label: 'Partiel',    icon: 'fa-circle-half-stroke', cls: 'int-badge-partial' },
    };

    function loadHealth() {
        const btn = document.getElementById('int-refresh-btn');
        if (btn) { btn.disabled = true; btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Vérification…'; }

        fetch('/admin/api/settings/integrations-health.php')
            .then(r => r.json())
            .then(data => {
                if (!data.ok) throw new Error(data.error || 'Erreur inconnue');
                renderHealth(data);
            })
            .catch(err => {
                document.getElementById('int-summary-text').textContent = 'Erreur : ' + err.message;
            })
            .finally(() => {
                if (btn) { btn.disabled = false; btn.innerHTML = '<i class="fas fa-rotate"></i> Actualiser'; }
            });
    }

    function renderHealth(data) {
        // Score
        const score = data.score || 0;
        document.getElementById('int-score-num').textContent = score + '%';
        document.getElementById('int-score-num').style.color = score >= 75 ? '#10b981' : score >= 50 ? '#f59e0b' : '#ef4444';
        document.getElementById('int-progress-bar').style.width = score + '%';

        // Summary text
        const total = Object.keys(data.integrations).length;
        const ok = (data.counts.configured || 0) + (data.counts.connected || 0);
        document.getElementById('int-summary-text').textContent =
            ok + '/' + total + ' intégrations opérationnelles';

        // KPIs
        document.getElementById('kpi-configured').textContent = ok;
        document.getElementById('kpi-missing').textContent = data.counts.missing || 0;
        document.getElementById('kpi-warning').textContent = (data.counts.warning || 0) + (data.counts.partial || 0);
        document.getElementById('kpi-date').textContent = data.checked_at || '—';
        document.getElementById('int-kpis').style.display = '';

        // Cards
        const grid = document.getElementById('int-grid');
        grid.innerHTML = '';
        Object.entries(data.integrations).forEach(([key, api]) => {
            const cfg = STATUS_CONFIG[api.status] || STATUS_CONFIG.missing;
            const iconBg = api.status === 'missing' ? '#fee2e2' :
                           api.status === 'warning' ? '#fef3c7' :
                           api.status === 'partial' ? '#dbeafe' : '#dcfce7';
            const iconColor = api.status === 'missing' ? '#ef4444' :
                              api.status === 'warning' ? '#f59e0b' :
                              api.status === 'partial' ? '#3b82f6' : '#10b981';

            grid.innerHTML += `
            <div class="int-card status-${api.status}">
                <div class="int-card-head">
                    <div class="int-card-icon" style="background:${iconBg};color:${iconColor}">
                        <i class="fas ${api.icon}"></i>
                    </div>
                    <div>
                        <div class="int-card-title">${api.name}</div>
                        <div class="int-card-source">Source : ${api.source}</div>
                    </div>
                </div>
                <span class="int-badge ${cfg.cls}">
                    <i class="fas ${cfg.icon}"></i> ${cfg.label}
                </span>
                <div class="int-detail">${api.detail}</div>
                ${api.model ? `<div class="int-detail" style="margin-top:.2rem;color:#94a3b8">Modèle : ${api.model}</div>` : ''}
                ${api.status === 'missing' || api.status === 'warning' ?
                    `<a href="/admin?module=parametres" class="hub-btn hub-btn--sm" style="margin-top:.7rem;display:inline-flex">
                        <i class="fas fa-gear"></i> Configurer
                    </a>` : ''}
            </div>`;
        });
    }

    // Chargement auto au démarrage
    loadHealth();
    </script>
    <?php
}
