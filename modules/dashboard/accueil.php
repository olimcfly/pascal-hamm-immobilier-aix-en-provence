<?php
$pageTitle = "Tableau de bord";
$pageDescription = "Vue d'ensemble de votre activité immobilière";

function renderContent() {
    $pdo = db();

    // ── KPIs ─────────────────────────────────────────────────────
    $biens_actifs = (int) $pdo->query("SELECT COUNT(*) FROM biens WHERE statut = 'Disponible'")->fetchColumn();
    $biens_total  = (int) $pdo->query("SELECT COUNT(*) FROM biens")->fetchColumn();

    $leads_mois   = (int) $pdo->query(
        "SELECT COUNT(*) FROM crm_leads WHERE created_at >= DATE_FORMAT(NOW(),'%Y-%m-01')"
    )->fetchColumn();
    $leads_total  = (int) $pdo->query("SELECT COUNT(*) FROM crm_leads")->fetchColumn();

    $msgs_nonlus  = (int) $pdo->query(
        "SELECT COUNT(*) FROM contact_messages WHERE direction = 'in' AND created_at >= DATE_FORMAT(NOW(),'%Y-%m-01')"
    )->fetchColumn();

    $estims_mois  = (int) $pdo->query(
        "SELECT COUNT(*) FROM estimation_requests WHERE created_at >= DATE_FORMAT(NOW(),'%Y-%m-01')"
    )->fetchColumn();

    $rdv_mois     = (int) $pdo->query(
        "SELECT COUNT(*) FROM estimation_rdv WHERE created_at >= DATE_FORMAT(NOW(),'%Y-%m-01')"
    )->fetchColumn();

    // ── Données utilisateur ───────────────────────────────────────
    $user     = Auth::user();
    $prenom   = $user['firstname'] ?? explode(' ', APP_NAME)[0] ?? 'Pascal';

    $jours_fr = ['Dimanche','Lundi','Mardi','Mercredi','Jeudi','Vendredi','Samedi'];
    $mois_fr  = ['janvier','février','mars','avril','mai','juin','juillet','août','septembre','octobre','novembre','décembre'];
    $date_str = $jours_fr[date('w')] . ' ' . date('j') . ' ' . $mois_fr[(int)date('n') - 1] . ' ' . date('Y');

    // ── Message contextuel ────────────────────────────────────────
    $heure = (int) date('G');
    $salut = $heure < 12 ? 'Bonjour' : ($heure < 18 ? 'Bon après-midi' : 'Bonsoir');

    $urgence = '';
    if ($msgs_nonlus > 0) {
        $urgence = $msgs_nonlus === 1
            ? '<strong>1 message reçu</strong> ce mois — à traiter.'
            : '<strong>' . $msgs_nonlus . ' messages reçus</strong> ce mois — à traiter.';
    } elseif ($leads_mois > 0) {
        $urgence = $leads_mois === 1
            ? '<strong>1 nouveau lead</strong> ce mois — à qualifier.'
            : '<strong>' . $leads_mois . ' nouveaux leads</strong> ce mois — à qualifier.';
    } else {
        $urgence = 'Aucun lead ni message en attente. Bon moment pour prospecter.';
    }

    ?>
    <style>
    .db-welcome {
        background: linear-gradient(135deg, #0f2237 0%, #1a3a5c 100%);
        border-radius: 16px;
        padding: 28px 32px;
        color: #fff;
        margin-bottom: 28px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 20px;
        box-shadow: 0 4px 20px rgba(15,34,55,.18);
    }
    .db-welcome-left { flex: 1; }
    .db-welcome-salut { font-size: 13px; color: rgba(255,255,255,.55); font-weight: 500; letter-spacing: .04em; text-transform: uppercase; margin-bottom: 6px; }
    .db-welcome-name  { font-size: 26px; font-weight: 700; color: #fff; margin-bottom: 10px; line-height: 1.2; }
    .db-welcome-msg   { font-size: 14px; color: rgba(255,255,255,.75); line-height: 1.6; }
    .db-welcome-msg strong { color: #c9a84c; }
    .db-welcome-date  {
        text-align: right; color: rgba(255,255,255,.4); font-size: 12px;
        white-space: nowrap; flex-shrink: 0;
    }
    .db-welcome-date-day { font-size: 36px; font-weight: 700; color: rgba(255,255,255,.15); line-height: 1; display: block; }

    .db-kpi-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 16px;
        margin-bottom: 28px;
    }
    .db-kpi {
        background: #fff;
        border-radius: 12px;
        padding: 20px 22px;
        box-shadow: 0 1px 6px rgba(0,0,0,.07);
        border-left: 4px solid #e8ecf0;
        transition: transform .15s, box-shadow .15s;
        text-decoration: none;
        display: block;
    }
    .db-kpi:hover { transform: translateY(-2px); box-shadow: 0 4px 16px rgba(0,0,0,.1); }
    .db-kpi.accent-blue   { border-color: #3498db; }
    .db-kpi.accent-gold   { border-color: #c9a84c; }
    .db-kpi.accent-green  { border-color: #27ae60; }
    .db-kpi.accent-orange { border-color: #e67e22; }
    .db-kpi.accent-red    { border-color: #e74c3c; }
    .db-kpi-icon  { font-size: 22px; margin-bottom: 10px; }
    .db-kpi-val   { font-size: 32px; font-weight: 700; color: #2c3e50; line-height: 1; margin-bottom: 4px; }
    .db-kpi-label { font-size: 12px; color: #8a95a3; font-weight: 500; text-transform: uppercase; letter-spacing: .04em; }
    .db-kpi-sub   { font-size: 11px; color: #b0bac5; margin-top: 4px; }

    .db-actions {
        background: #fff;
        border-radius: 12px;
        padding: 22px 24px;
        box-shadow: 0 1px 6px rgba(0,0,0,.07);
        margin-bottom: 24px;
    }
    .db-actions-title { font-size: 13px; font-weight: 600; color: #8a95a3; text-transform: uppercase; letter-spacing: .06em; margin-bottom: 14px; }
    .db-actions-list  { display: flex; flex-wrap: wrap; gap: 10px; }
    .db-action-btn {
        display: inline-flex; align-items: center; gap: 7px;
        padding: 9px 16px; border-radius: 8px; font-size: 13px; font-weight: 500;
        text-decoration: none; transition: background .15s, color .15s;
        border: 1.5px solid #e8ecf0; color: #4a5568; background: #f8fafc;
    }
    .db-action-btn:hover { background: #0f2237; color: #fff; border-color: #0f2237; }
    .db-action-btn.primary { background: #c9a84c; color: #0f2237; border-color: #c9a84c; font-weight: 700; }
    .db-action-btn.primary:hover { background: #b8943d; border-color: #b8943d; }

    @media (max-width: 600px) {
        .db-welcome { flex-direction: column; }
        .db-welcome-date { text-align: left; }
        .db-kpi-grid { grid-template-columns: repeat(2, 1fr); }
    }
    </style>

    <!-- Bandeau de bienvenue -->
    <div class="db-welcome">
        <div class="db-welcome-left">
            <div class="db-welcome-salut"><?= $salut ?></div>
            <div class="db-welcome-name"><?= htmlspecialchars($prenom) ?> — votre journée en un coup d'œil</div>
            <div class="db-welcome-msg"><?= $urgence ?></div>
        </div>
        <div class="db-welcome-date">
            <span class="db-welcome-date-day"><?= date('j') ?></span>
            <?= htmlspecialchars($date_str) ?>
        </div>
    </div>

    <!-- KPIs -->
    <div class="db-kpi-grid">

        <a href="/admin/?module=biens" class="db-kpi accent-blue">
            <div class="db-kpi-icon">🏠</div>
            <div class="db-kpi-val"><?= $biens_actifs ?></div>
            <div class="db-kpi-label">Biens actifs</div>
            <div class="db-kpi-sub"><?= $biens_total ?> au total</div>
        </a>

        <a href="/admin/?module=capturer" class="db-kpi <?= $leads_mois > 0 ? 'accent-gold' : 'accent-orange' ?>">
            <div class="db-kpi-icon">🎯</div>
            <div class="db-kpi-val"><?= $leads_mois ?></div>
            <div class="db-kpi-label">Leads ce mois</div>
            <div class="db-kpi-sub"><?= $leads_total ?> au total</div>
        </a>

        <a href="/admin/?module=biens" class="db-kpi <?= $msgs_nonlus > 0 ? 'accent-gold' : 'accent-green' ?>">
            <div class="db-kpi-icon">💬</div>
            <div class="db-kpi-val"><?= $msgs_nonlus ?></div>
            <div class="db-kpi-label">Messages reçus</div>
            <div class="db-kpi-sub">Ce mois</div>
        </a>

        <a href="/admin/?module=estimation" class="db-kpi accent-gold">
            <div class="db-kpi-icon">📊</div>
            <div class="db-kpi-val"><?= $estims_mois ?></div>
            <div class="db-kpi-label">Estimations ce mois</div>
            <div class="db-kpi-sub"><?= $rdv_mois ?> RDV générés</div>
        </a>

    </div>

    <!-- Actions rapides -->
    <div class="db-actions">
        <div class="db-actions-title">Actions rapides</div>
        <div class="db-actions-list">
            <a href="/admin/?module=biens&action=nouveau" class="db-action-btn primary">
                <i class="fas fa-plus"></i> Ajouter un bien
            </a>
            <a href="/admin/?module=capturer" class="db-action-btn">
                <i class="fas fa-user-plus"></i> Nouveau lead
            </a>
            <a href="/admin/?module=biens" class="db-action-btn">
                <i class="fas fa-list"></i> Voir les biens
            </a>
            <a href="/admin/?module=seo" class="db-action-btn">
                <i class="fas fa-chart-line"></i> Visibilité SEO
            </a>
            <a href="/admin/?module=social" class="db-action-btn">
                <i class="fas fa-share-nodes"></i> Réseaux sociaux
            </a>
        </div>
    </div>
    <?php
}
