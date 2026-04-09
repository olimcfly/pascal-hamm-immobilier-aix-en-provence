<?php
$pageTitle = 'CRM Leads';
$pageDescription = 'Centralisez, priorisez et convertissez vos leads en actions concrètes.';

$view = isset($_GET['view']) ? strtolower((string)$_GET['view']) : 'cards';
$view = in_array($view, ['cards', 'pipeline'], true) ? $view : 'cards';

$pipeline = isset($_GET['pipeline']) ? strtolower((string)$_GET['pipeline']) : '';
$pipelines = LeadService::stageMatrix();
if ($pipeline !== '' && !isset($pipelines[$pipeline])) {
    $pipeline = '';
}

$leads = LeadService::list($pipeline ? ['pipeline' => $pipeline] : []);

$now = new DateTimeImmutable('now');
$todayStart = $now->setTime(0, 0, 0);
$todayEnd = $now->setTime(23, 59, 59);

$closedStages = ['converti', 'perdu', 'archive', 'inactif', 'traite'];
$rdvStages = ['rdv_a_planifier', 'rdv_propose'];
$hotIntentKeywords = ['urgent', 'vite', 'rapid', 'achat', 'vendre', 'rdv', 'visite', 'estimer', 'financement'];

$stats = [
    'active' => 0,
    'hot' => 0,
    'rdv' => 0,
    'cold' => 0,
    'today_todo' => 0,
];

$rankedLeads = [];

foreach ($leads as $lead) {
    $stage = strtolower((string)($lead['stage'] ?? 'nouveau'));
    $intent = strtolower((string)($lead['intent'] ?? ''));
    $priority = strtolower((string)($lead['priority'] ?? 'normal'));

    $isClosed = in_array($stage, $closedStages, true);
    $isRdv = in_array($stage, $rdvStages, true);
    $isHot = $priority === 'haute' || $isRdv || $stage === 'a_traiter';

    foreach ($hotIntentKeywords as $keyword) {
        if (str_contains($intent, $keyword)) {
            $isHot = true;
            break;
        }
    }

    if (!$isClosed) {
        $stats['active']++;
    }

    if ($isHot && !$isClosed) {
        $stats['hot']++;
    }

    if ($isRdv) {
        $stats['rdv']++;
    }

    if ($isClosed || (!$isHot && !$isRdv)) {
        $stats['cold']++;
    }

    $createdAt = null;
    if (!empty($lead['created_at'])) {
        try {
            $createdAt = new DateTimeImmutable((string)$lead['created_at']);
        } catch (Throwable) {
            $createdAt = null;
        }
    }

    $needsActionToday = !$isClosed && (
        $isRdv
        || $stage === 'a_traiter'
        || ($createdAt instanceof DateTimeImmutable && $createdAt >= $todayStart && $createdAt <= $todayEnd)
    );

    if ($needsActionToday) {
        $stats['today_todo']++;
    }

    $urgencyScore = 0;
    $urgencyScore += $priority === 'haute' ? 30 : ($priority === 'normal' ? 10 : 5);
    $urgencyScore += $isRdv ? 30 : 0;
    $urgencyScore += $stage === 'a_traiter' ? 20 : 0;
    $urgencyScore += $isHot ? 25 : 0;

    $lead['_is_hot'] = $isHot;
    $lead['_is_closed'] = $isClosed;
    $lead['_is_rdv'] = $isRdv;
    $lead['_needs_action_today'] = $needsActionToday;
    $lead['_urgency'] = $urgencyScore;
    $rankedLeads[] = $lead;
}

usort($rankedLeads, static fn(array $a, array $b): int => ($b['_urgency'] ?? 0) <=> ($a['_urgency'] ?? 0));
$priorityLeads = array_values(array_filter($rankedLeads, static fn(array $lead): bool => !empty($lead['_needs_action_today']) || !empty($lead['_is_hot'])));
$priorityLeads = array_slice($priorityLeads, 0, 5);

$smartMessage = 'Pipeline stable : continuez le suivi.';
if ($stats['rdv'] > 0) {
    $smartMessage = $stats['rdv'] . ' RDV à planifier aujourd\'hui.';
} elseif ($stats['today_todo'] > 0) {
    $smartMessage = $stats['today_todo'] . ' leads à traiter aujourd\'hui.';
} elseif ($stats['hot'] > 0) {
    $smartMessage = $stats['hot'] . ' leads chauds à contacter en priorité.';
}

$stageColor = static function (string $stage): string {
    if (in_array($stage, ['rdv_a_planifier', 'a_traiter', 'perdu'], true)) {
        return 'danger';
    }
    if (in_array($stage, ['a_qualifier', 'en_discussion', 'nurturing', 'rdv_propose', 'en_cours', 'a_relancer'], true)) {
        return 'follow';
    }
    return 'ok';
};

$intentLevel = static function (array $lead): string {
    if (!empty($lead['_is_hot'])) {
        return 'Forte';
    }
    if (!empty($lead['_is_closed'])) {
        return 'Faible';
    }
    return 'Moyenne';
};

$stageCountByPipeline = [];
foreach ($pipelines as $source => $stages) {
    foreach ($stages as $stage) {
        $stageCountByPipeline[$source][$stage] = 0;
    }
}

foreach ($leads as $lead) {
    $source = (string)($lead['pipeline'] ?? LeadService::SOURCE_AUTRE);
    $stage = (string)($lead['stage'] ?? 'nouveau');
    if (!isset($stageCountByPipeline[$source])) {
        $stageCountByPipeline[$source] = [];
    }
    if (!isset($stageCountByPipeline[$source][$stage])) {
        $stageCountByPipeline[$source][$stage] = 0;
    }
    $stageCountByPipeline[$source][$stage]++;
}

function renderContent(): void
{
    global $view, $pipeline, $pipelines, $leads, $stats, $smartMessage, $priorityLeads, $stageColor, $intentLevel, $stageCountByPipeline;
    ?>
    <style>
        .crm-shell{color:#e2e8f0;background:#0f172a;padding:1rem 0 2.5rem;}
        .crm-shell a{color:inherit;text-decoration:none;}
        .crm-header{background:linear-gradient(135deg,rgba(15,23,42,.95),rgba(30,41,59,.9));border:1px solid #1f2937;border-radius:16px;padding:1.15rem;box-shadow:0 12px 35px rgba(2,6,23,.35);display:flex;align-items:flex-start;justify-content:space-between;gap:1rem;flex-wrap:wrap;}
        .crm-header h1{margin:0;font-size:1.35rem;color:#f8fafc;}
        .crm-header p{margin:.35rem 0 0;color:#94a3b8;}
        .crm-smart{padding:.65rem .9rem;background:rgba(16,185,129,.12);border:1px solid rgba(16,185,129,.45);border-radius:999px;color:#bbf7d0;font-weight:700;font-size:.88rem;}

        .crm-kpis{display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:.85rem;margin:1rem 0;}
        .crm-kpi{background:#111827;border:1px solid #1f2937;border-radius:16px;padding:1rem;box-shadow:0 8px 30px rgba(0,0,0,.28);}
        .crm-kpi__label{display:block;color:#94a3b8;font-size:.82rem;margin-bottom:.35rem;}
        .crm-kpi__value{display:block;font-size:1.7rem;font-weight:800;color:#f8fafc;}
        .crm-kpi.danger{border-color:rgba(239,68,68,.45);box-shadow:0 8px 26px rgba(239,68,68,.2);}
        .crm-kpi.follow{border-color:rgba(249,115,22,.45);box-shadow:0 8px 26px rgba(249,115,22,.17);}
        .crm-kpi.ok{border-color:rgba(34,197,94,.35);box-shadow:0 8px 26px rgba(34,197,94,.14);}

        .crm-toolbar{display:flex;justify-content:space-between;gap:.75rem;flex-wrap:wrap;margin:0 0 1rem;}
        .crm-pill{display:inline-flex;align-items:center;gap:.35rem;padding:.5rem .85rem;border-radius:999px;border:1px solid #374151;background:#111827;color:#cbd5e1;font-weight:700;font-size:.85rem;}
        .crm-pill.active{background:#2563eb;border-color:#2563eb;color:#eff6ff;}

        .crm-priority{background:#111827;border:1px solid #1f2937;border-radius:16px;padding:1rem;box-shadow:0 8px 30px rgba(0,0,0,.25);margin:0 0 1rem;}
        .crm-priority h2{margin:0 0 .7rem;font-size:1.05rem;color:#f8fafc;}
        .crm-priority-list{display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:.75rem;}
        .crm-priority-card{background:#0b1220;border:1px solid #243041;border-radius:14px;padding:.8rem;}
        .crm-priority-card strong{color:#f8fafc;display:block;margin-bottom:.3rem;}

        .crm-actions{display:flex;gap:.45rem;flex-wrap:wrap;margin-top:.6rem;}
        .crm-btn{display:inline-flex;align-items:center;justify-content:center;padding:.46rem .7rem;border-radius:10px;background:#1e293b;border:1px solid #334155;color:#e2e8f0;font-size:.78rem;font-weight:700;}
        .crm-btn:hover{background:#334155;}

        .crm-cards{display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:.8rem;}
        .crm-lead{background:#111827;border:1px solid #1f2937;border-radius:16px;padding:.95rem;box-shadow:0 8px 30px rgba(2,6,23,.28);}
        .crm-lead__head{display:flex;justify-content:space-between;gap:.5rem;align-items:flex-start;}
        .crm-badge{display:inline-flex;padding:.22rem .6rem;border-radius:999px;font-size:.72rem;font-weight:800;}
        .crm-badge.danger{background:rgba(239,68,68,.2);color:#fca5a5;}
        .crm-badge.follow{background:rgba(249,115,22,.2);color:#fdba74;}
        .crm-badge.ok{background:rgba(34,197,94,.2);color:#86efac;}
        .crm-meta{color:#94a3b8;font-size:.8rem;}

        .crm-pipeline-grid{display:grid;gap:1rem;}
        .crm-pipeline{background:#111827;border:1px solid #1f2937;border-radius:16px;padding:1rem;overflow:auto;}
        .crm-pipeline h3{margin:0 0 .75rem;color:#f8fafc;}
        .crm-track{display:flex;gap:.65rem;min-width:max-content;}
        .crm-stage{width:170px;flex:0 0 auto;background:#0b1220;border:1px dashed #334155;border-radius:14px;padding:.65rem;}
        .crm-stage strong{display:block;color:#f8fafc;font-size:.86rem;}
        .crm-stage small{display:block;color:#94a3b8;font-size:.75rem;margin-top:.25rem;}

        .crm-floating{position:fixed;right:1rem;bottom:1rem;display:none;gap:.5rem;z-index:40;}
        .crm-fab{width:52px;height:52px;border-radius:999px;background:#2563eb;border:1px solid #60a5fa;display:flex;align-items:center;justify-content:center;color:#fff;box-shadow:0 10px 30px rgba(37,99,235,.35);font-size:1rem;}

        @media (max-width: 720px){
            .crm-shell{padding-bottom:5rem;}
            .crm-cards{grid-template-columns:1fr;}
            .crm-priority-list{grid-template-columns:1fr;}
            .crm-floating{display:flex;}
            .crm-header{padding:1rem;}
            .crm-kpi__value{font-size:1.45rem;}
        }
    </style>

    <div class="crm-shell">
        <div class="crm-header">
            <div>
                <h1><i class="fas fa-bolt"></i> CRM Leads • Cockpit</h1>
                <p><?= e($stats['active']) ?> opportunités actives • <?= e($stats['today_todo']) ?> leads à traiter aujourd'hui</p>
            </div>
            <div class="crm-smart"><?= e($smartMessage) ?></div>
        </div>

        <section class="crm-kpis">
            <article class="crm-kpi ok"><span class="crm-kpi__label">Opportunités actives</span><span class="crm-kpi__value"><?= e((string)$stats['active']) ?></span></article>
            <article class="crm-kpi danger"><span class="crm-kpi__label">Leads chauds</span><span class="crm-kpi__value"><?= e((string)$stats['hot']) ?></span></article>
            <article class="crm-kpi follow"><span class="crm-kpi__label">RDV à planifier</span><span class="crm-kpi__value"><?= e((string)$stats['rdv']) ?></span></article>
            <article class="crm-kpi ok"><span class="crm-kpi__label">Leads froids</span><span class="crm-kpi__value"><?= e((string)$stats['cold']) ?></span></article>
        </section>

        <section class="crm-priority">
            <h2>À faire maintenant</h2>
            <div class="crm-priority-list">
                <?php if (!$priorityLeads): ?>
                    <div class="crm-meta">Aucune urgence immédiate. Le pipeline est propre.</div>
                <?php endif; ?>
                <?php foreach ($priorityLeads as $lead): ?>
                    <?php $fullName = trim(($lead['first_name'] ?? '') . ' ' . ($lead['last_name'] ?? '')) ?: 'Lead sans nom'; ?>
                    <article class="crm-priority-card">
                        <strong><?= e($fullName) ?></strong>
                        <div class="crm-meta"><?= e(LeadService::sourceLabel((string)($lead['source_type'] ?? 'autre'))) ?> • <?= e(LeadService::stageLabel((string)($lead['stage'] ?? 'nouveau'))) ?></div>
                        <div class="crm-meta">Intention <?= e($intentLevel($lead)) ?></div>
                        <div class="crm-actions">
                            <?php if (!empty($lead['phone'])): ?>
                                <a class="crm-btn" href="tel:<?= e((string)$lead['phone']) ?>"><i class="fas fa-phone"></i>&nbsp;Appeler</a>
                            <?php endif; ?>
                            <a class="crm-btn" href="mailto:<?= e((string)($lead['email'] ?? '')) ?>"><i class="fas fa-envelope"></i>&nbsp;Message</a>
                            <a class="crm-btn" href="#"><i class="fas fa-calendar"></i>&nbsp;Planifier RDV</a>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        </section>

        <div class="crm-toolbar">
            <div style="display:flex;gap:.5rem;flex-wrap:wrap;">
                <a class="crm-pill <?= $view === 'cards' ? 'active' : '' ?>" href="?module=capturer&view=cards<?= $pipeline ? '&pipeline=' . urlencode($pipeline) : '' ?>">Vue Cards</a>
                <a class="crm-pill <?= $view === 'pipeline' ? 'active' : '' ?>" href="?module=capturer&view=pipeline<?= $pipeline ? '&pipeline=' . urlencode($pipeline) : '' ?>">Pipeline visuel</a>
            </div>
            <div style="display:flex;gap:.5rem;flex-wrap:wrap;">
                <a class="crm-pill <?= $pipeline === '' ? 'active' : '' ?>" href="?module=capturer&view=<?= urlencode($view) ?>">Tous les pipelines</a>
                <?php foreach (array_keys($pipelines) as $pipe): ?>
                    <a class="crm-pill <?= $pipeline === $pipe ? 'active' : '' ?>" href="?module=capturer&view=<?= urlencode($view) ?>&pipeline=<?= urlencode($pipe) ?>"><?= e(LeadService::sourceLabel($pipe)) ?></a>
                <?php endforeach; ?>
            </div>
        </div>

        <?php if ($view === 'cards'): ?>
            <section class="crm-cards">
                <?php if (!$leads): ?>
                    <article class="crm-lead"><div class="crm-meta">Aucun lead capturé pour l'instant.</div></article>
                <?php endif; ?>
                <?php foreach ($leads as $lead): ?>
                    <?php
                    $fullName = trim(($lead['first_name'] ?? '') . ' ' . ($lead['last_name'] ?? '')) ?: 'Lead sans nom';
                    $stage = (string)($lead['stage'] ?? 'nouveau');
                    $tone = $stageColor($stage);
                    ?>
                    <article class="crm-lead">
                        <div class="crm-lead__head">
                            <div>
                                <strong><?= e($fullName) ?></strong>
                                <div class="crm-meta"><?= e(LeadService::sourceLabel((string)($lead['source_type'] ?? 'autre'))) ?></div>
                            </div>
                            <span class="crm-badge <?= e($tone) ?>"><?= e(LeadService::stageLabel($stage)) ?></span>
                        </div>
                        <p class="crm-meta" style="margin:.45rem 0 .6rem;">Intention : <?= e($intentLevel($lead)) ?><?= !empty($lead['intent']) ? ' • ' . e((string)$lead['intent']) : '' ?></p>
                        <div class="crm-meta"><?= e((string)($lead['email'] ?? '')) ?></div>
                        <div class="crm-meta"><?= e((string)($lead['phone'] ?? '')) ?></div>
                        <div class="crm-actions">
                            <?php if (!empty($lead['phone'])): ?>
                                <a class="crm-btn" href="tel:<?= e((string)$lead['phone']) ?>">Appeler</a>
                            <?php endif; ?>
                            <a class="crm-btn" href="mailto:<?= e((string)($lead['email'] ?? '')) ?>">Message</a>
                            <a class="crm-btn" href="#">Planifier RDV</a>
                        </div>
                    </article>
                <?php endforeach; ?>
            </section>
        <?php else: ?>
            <section class="crm-pipeline-grid">
                <?php foreach ($pipelines as $source => $stages): ?>
                    <?php if ($pipeline !== '' && $pipeline !== $source) {
                        continue;
                    } ?>
                    <article class="crm-pipeline">
                        <h3><?= e(LeadService::sourceLabel($source)) ?> <small class="crm-meta">(drag & drop ready)</small></h3>
                        <div class="crm-track">
                            <?php foreach ($stages as $stage): ?>
                                <?php $tone = $stageColor((string)$stage); ?>
                                <div class="crm-stage">
                                    <span class="crm-badge <?= e($tone) ?>"><?= e(LeadService::stageLabel((string)$stage)) ?></span>
                                    <strong><?= e((string)($stageCountByPipeline[$source][$stage] ?? 0)) ?> lead(s)</strong>
                                    <small>Statut <?= e($tone === 'danger' ? 'Urgent' : ($tone === 'follow' ? 'Suivi' : 'OK')) ?></small>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </article>
                <?php endforeach; ?>
            </section>
        <?php endif; ?>

        <div class="crm-floating" aria-hidden="true">
            <a class="crm-fab" href="tel:+33400000000" title="Appeler"><i class="fas fa-phone"></i></a>
            <a class="crm-fab" href="mailto:contact@pascal-hamm-immobilier-aix-en-provence.fr" title="Envoyer un message"><i class="fas fa-comment-dots"></i></a>
        </div>
    </div>
    <?php
}
