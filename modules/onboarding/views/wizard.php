<?php

declare(strict_types=1);

$step = (int) ($viewData['step'] ?? 1);
$labels = $viewData['labels'] ?? [];
$stepKeys = $viewData['stepKeys'] ?? [];
$answers = $viewData['answers'] ?? [];
$errors = $viewData['errors'] ?? [];
$blueprint = $viewData['blueprint'] ?? [];
$canResume = (bool) ($viewData['canResume'] ?? false);
$resumeStep = (int) ($viewData['resumeStep'] ?? 1);
$session = $viewData['session'] ?? [];
$status = (string) ($session['status'] ?? 'draft');
$currentStep = max(1, min(5, (int) ($session['current_step'] ?? 1)));
$flash = Session::getFlash();

$activeKey = $stepKeys[$step] ?? '';
$activeAnswers = $activeKey !== '' ? ($answers[$activeKey] ?? []) : [];

$selectedOutputs = array_map('strval', (array) ($answers['goal']['outputs'] ?? []));
$stratModules   = $viewData['stratModules'] ?? [];

$_dashboard         = $viewData['dashboard'] ?? [];
$dashboardCompleted = (int) ($_dashboard['completed'] ?? 0);
$dashboardTotal     = (int) ($_dashboard['total'] ?? 0);
$dashboardProgress  = (int) ($_dashboard['global_progress'] ?? 0);
$dashboardItems     = $_dashboard['items'] ?? [];
$methodCards        = $viewData['methodCards'] ?? [];
?>
<style>
    .onboarding-shell { max-width: 1080px; margin: 0 auto; display:grid; gap:1rem; }
    .onboarding-card { background:#fff; border:1px solid #e7ecf3; border-radius:18px; padding:1.1rem 1.2rem; box-shadow:0 8px 30px rgba(17,24,39,.05); }
    .onboarding-header {
        background: radial-gradient(circle at top right, rgba(37, 99, 235, .12), rgba(15, 23, 42, .02) 35%), #fff;
    }
    .onboarding-header h1 { margin:0; font-size:1.55rem; color:#0f172a; }
    .onboarding-header p { margin:.35rem 0 0; color:#64748b; }
    .onboarding-summary-line { margin:.75rem 0 0; color:#334155; font-weight:600; }
    .onboarding-progress-wrap { margin-top:.85rem; }
    .onboarding-progress-meta { display:flex; justify-content:space-between; font-size:.8rem; color:#64748b; margin-bottom:.35rem; }
    .onboarding-progress-track { height:10px; background:#e2e8f0; border-radius:999px; overflow:hidden; }
    .onboarding-progress-bar { height:100%; border-radius:999px; background:linear-gradient(90deg,#2563eb,#0ea5e9); }
    .onboarding-stepper { display:grid; grid-template-columns:repeat(6,minmax(0,1fr)); gap:.5rem; }
    .onboarding-step { border:1px solid #dbe4ef; border-radius:999px; padding:.42rem .6rem; font-size:.78rem; text-align:center; color:#64748b; }
    .onboarding-step.active { border-color:#2563eb; background:#eff6ff; color:#1e40af; font-weight:600; }
    .onboarding-step.done { border-color:#22c55e; color:#166534; }
    .onboarding-dashboard-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(210px,1fr)); gap:.75rem; }
    .onboarding-phase-card { border:1px solid #dbe4ef; border-radius:14px; padding:.8rem; background:#fff; display:flex; flex-direction:column; gap:.55rem; box-shadow:0 4px 14px rgba(15,23,42,.03); }
    .onboarding-phase-top { display:flex; justify-content:space-between; gap:.5rem; align-items:flex-start; }
    .onboarding-phase-title { font-weight:700; color:#0f172a; font-size:.95rem; }
    .onboarding-phase-desc { font-size:.82rem; color:#64748b; line-height:1.45; min-height:2.3rem; }
    .onboarding-status-badge { display:inline-flex; align-items:center; gap:.25rem; border-radius:999px; font-size:.72rem; padding:.2rem .55rem; border:1px solid transparent; font-weight:700; white-space:nowrap; }
    .onboarding-status-badge.non_commence { border-color:#cbd5e1; color:#475569; background:#f8fafc; }
    .onboarding-status-badge.en_cours { border-color:#bfdbfe; color:#1d4ed8; background:#eff6ff; }
    .onboarding-status-badge.complete { border-color:#bbf7d0; color:#166534; background:#ecfdf3; }
    .onboarding-phase-progress { display:flex; align-items:center; justify-content:space-between; font-size:.75rem; color:#64748b; }
    .onboarding-phase-track { height:6px; background:#e2e8f0; border-radius:999px; overflow:hidden; margin-top:.28rem; }
    .onboarding-phase-fill { height:100%; background:linear-gradient(90deg,#2563eb,#0ea5e9); border-radius:999px; }
    .onboarding-phase-action { margin-top:auto; }
    .onboarding-phase-action a { display:inline-flex; align-items:center; gap:.35rem; text-decoration:none; font-weight:700; font-size:.78rem; color:#1d4ed8; }
    .onboarding-phase-action a:hover { text-decoration:underline; }
    .onboarding-method-title { margin:0 0 .2rem; font-size:1.15rem; color:#0f172a; }
    .onboarding-method-subtitle { margin:0 0 .7rem; font-size:.88rem; color:#64748b; }
    .onboarding-method-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(220px,1fr)); gap:.7rem; }
    .onboarding-method-card { border:1px solid #dbe4ef; border-radius:14px; padding:.85rem; background:#fcfdff; }
    .onboarding-method-card h3 { margin:0 0 .35rem; font-size:.9rem; color:#0f172a; }
    .onboarding-method-card p { margin:0; font-size:.82rem; color:#64748b; line-height:1.45; }
    .onboarding-grid { display:grid; grid-template-columns:1fr 1fr; gap:.8rem; }
    .onboarding-field label { display:block; font-weight:600; margin-bottom:.26rem; font-size:.9rem; }
    .onboarding-field input,.onboarding-field select,.onboarding-field textarea { width:100%; border:1px solid #cfd8e3; border-radius:12px; padding:.63rem .72rem; font:inherit; }
    .onboarding-field textarea { min-height:96px; resize:vertical; }
    .onboarding-actions { display:flex; justify-content:space-between; flex-wrap:wrap; gap:.6rem; margin-top:.9rem; }
    .btn { border:0; border-radius:10px; padding:.62rem .95rem; cursor:pointer; font-weight:600; }
    .btn-secondary { background:#e2e8f0; color:#0f172a; }
    .btn-primary { background:#2563eb; color:#fff; }
    .btn-ghost { background:#f8fafc; color:#334155; border:1px solid #cbd5e1; }
    .alert { padding:.72rem .88rem; border-radius:12px; }
    .alert-success { background:#ecfdf3; color:#166534; border:1px solid #bbf7d0; }
    .alert-error { background:#fef2f2; color:#991b1b; border:1px solid #fecaca; }
    .summary-grid { display:grid; grid-template-columns:1fr 1fr; gap:1rem; }
    .summary-box { border:1px solid #e2e8f0; border-radius:12px; padding:.75rem; background:#f8fafc; }
    .summary-box h3 { margin:0 0 .45rem; font-size:1rem; }
    .summary-box p { margin:.2rem 0; font-size:.9rem; }
    pre.blueprint { max-height:300px; overflow:auto; background:#0f172a; color:#e2e8f0; border-radius:12px; padding:.8rem; font-size:.78rem; }
    @media (max-width: 860px) {
        .onboarding-grid, .summary-grid, .onboarding-stepper { grid-template-columns:1fr; }
    }
</style>

<div class="onboarding-shell">
    <section class="onboarding-card onboarding-header">
        <h1>Onboarding d’activation</h1>
        <p>Répondez à quelques questions clés. Nous préparons un blueprint prêt pour les modules suivants.</p>

        <?php if ($canResume): ?>
            <p><a href="/admin?module=onboarding&amp;step=<?= (int) $resumeStep ?>">Reprendre votre session en cours (étape <?= (int) $resumeStep ?>/5)</a></p>
        <?php endif; ?>

        <p class="onboarding-summary-line">Complétez les étapes pour activer votre système de croissance.</p>
        <div class="onboarding-progress-wrap">
            <div class="onboarding-progress-meta">
                <span>Progression globale</span>
                <span><?= (int) $dashboardCompleted ?>/<?= (int) $dashboardTotal ?> étapes complétées (<?= (int) $dashboardProgress ?>%)</span>
            </div>
            <div class="onboarding-progress-track">
                <div class="onboarding-progress-bar" style="width: <?= (int) max(0, min(100, $dashboardProgress)) ?>%"></div>
            </div>
        </div>

        <div class="onboarding-stepper" aria-label="Progression onboarding">
            <?php foreach ($labels as $stepIndex => $stepLabel): ?>
                <?php
                $class = 'onboarding-step';
                if ($stepIndex === $step) {
                    $class .= ' active';
                } elseif ($stepIndex < $currentStep || ($status === 'completed' && $stepIndex <= 6)) {
                    $class .= ' done';
                }
                ?>
                <div class="<?= e($class) ?>"><?= (int) $stepIndex ?>/<?= $stepIndex < 6 ? '5' : '5+' ?> — <?= e((string) $stepLabel) ?></div>
            <?php endforeach; ?>
        </div>
    </section>

    <section class="onboarding-card">
        <h2 style="margin:0 0 .65rem;font-size:1.2rem;color:#0f172a;">Dashboard onboarding premium</h2>
        <div class="onboarding-dashboard-grid">
            <?php foreach ($dashboardItems as $item): ?>
                <?php
                $status = (string) ($item['state'] ?? 'non_commence');
                $statusText = $status === 'complete' ? 'Complété' : ($status === 'en_cours' ? 'En cours' : 'Non commencé');
                $completion = (int) ($item['completion'] ?? 0);
                ?>
                <article class="onboarding-phase-card">
                    <div class="onboarding-phase-top">
                        <div class="onboarding-phase-title"><?= e((string) ($item['title'] ?? 'Étape')) ?></div>
                        <span class="onboarding-status-badge <?= e($status) ?>"><?= e($statusText) ?></span>
                    </div>
                    <p class="onboarding-phase-desc"><?= e((string) ($item['description'] ?? '')) ?></p>
                    <div class="onboarding-phase-progress">
                        <span>Progression</span>
                        <span><?= (int) max(0, min(100, $completion)) ?>%</span>
                    </div>
                    <div class="onboarding-phase-track">
                        <div class="onboarding-phase-fill" style="width: <?= (int) max(0, min(100, $completion)) ?>%"></div>
                    </div>
                    <div class="onboarding-phase-action">
                        <a href="<?= e((string) ($item['route'] ?? '/admin?module=onboarding')) ?>">
                            <?= $status === 'complete' ? 'Revoir' : ($status === 'en_cours' ? 'Continuer' : 'Commencer') ?>
                            <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </section>

    <section class="onboarding-card">
        <h2 class="onboarding-method-title">Méthode / Stratégie</h2>
        <p class="onboarding-method-subtitle">Une logique simple pour savoir quoi faire, dans quel ordre, et pourquoi chaque étape compte.</p>
        <div class="onboarding-method-grid">
            <?php foreach ($methodCards as $card): ?>
                <article class="onboarding-method-card">
                    <h3><?= e((string) ($card['title'] ?? 'Étape')) ?></h3>
                    <p><?= e((string) ($card['text'] ?? '')) ?></p>
                </article>
            <?php endforeach; ?>
        </div>
    </section>

    <?php if (is_array($flash) && isset($flash['message'], $flash['type'])): ?>
        <div class="alert <?= $flash['type'] === 'success' ? 'alert-success' : 'alert-error' ?>"><?= e((string) $flash['message']) ?></div>
    <?php endif; ?>

    <?php if ($errors !== []): ?>
        <div class="alert alert-error">
            <?php foreach ($errors as $error): ?>
                <div>• <?= e((string) $error) ?></div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <?php if ($step <= 5): ?>
    <section class="onboarding-card">
        <form method="post" action="/admin?module=onboarding&amp;step=<?= (int) $step ?>">
            <?= csrfField() ?>
            <input type="hidden" name="intent" value="save_step">
            <input type="hidden" name="step" value="<?= (int) $step ?>">

            <div class="onboarding-grid">
                <?php if ($step === 1): ?>
                    <div class="onboarding-field"><label>Nom pro *</label><input required type="text" name="name" value="<?= e((string) ($activeAnswers['name'] ?? '')) ?>"></div>
                    <div class="onboarding-field"><label>Marque / enseigne</label><input type="text" name="brand" value="<?= e((string) ($activeAnswers['brand'] ?? '')) ?>"></div>
                    <div class="onboarding-field"><label>Rôle métier *</label><select name="role" required><?php foreach (['conseiller','agent','coach','mandataire','autre'] as $r): ?><option value="<?= e($r) ?>" <?= (($activeAnswers['role'] ?? '') === $r ? 'selected' : '') ?>><?= e(ucfirst($r)) ?></option><?php endforeach; ?></select></div>
                    <div class="onboarding-field"><label>Tonalité souhaitée (optionnel)</label><input type="text" name="tone" value="<?= e((string) ($activeAnswers['tone'] ?? '')) ?>"></div>
                <?php elseif ($step === 2): ?>
                    <div class="onboarding-field"><label>Persona principal *</label><input required type="text" name="persona" value="<?= e((string) ($activeAnswers['persona'] ?? '')) ?>"></div>
                    <div class="onboarding-field"><label>Problème principal *</label><input required type="text" name="pain" value="<?= e((string) ($activeAnswers['pain'] ?? '')) ?>"></div>
                    <div class="onboarding-field" style="grid-column:1/-1"><label>Désir / résultat recherché</label><textarea name="desire"><?= e((string) ($activeAnswers['desire'] ?? '')) ?></textarea></div>
                <?php elseif ($step === 3): ?>
                    <div class="onboarding-field"><label>Type d’offre *</label><input required type="text" name="type" value="<?= e((string) ($activeAnswers['type'] ?? '')) ?>"></div>
                    <div class="onboarding-field"><label>Promesse principale *</label><input required type="text" name="promise" value="<?= e((string) ($activeAnswers['promise'] ?? '')) ?>"></div>
                    <div class="onboarding-field"><label>Différenciateur</label><input type="text" name="differentiator" value="<?= e((string) ($activeAnswers['differentiator'] ?? '')) ?>"></div>
                    <div class="onboarding-field"><label>Délai / horizon</label><input type="text" name="timeline" value="<?= e((string) ($activeAnswers['timeline'] ?? '')) ?>"></div>
                <?php elseif ($step === 4): ?>
                    <div class="onboarding-field"><label>Ville principale *</label><input required type="text" name="city" value="<?= e((string) ($activeAnswers['city'] ?? '')) ?>"></div>
                    <div class="onboarding-field"><label>Rayon d'action (km)</label><input type="number" min="0" name="radius_km" value="<?= e((string) ($activeAnswers['radius_km'] ?? '')) ?>"></div>
                    <div class="onboarding-field"><label>Zones / quartiers ciblés</label><input type="text" name="districts" value="<?= e((string) ($activeAnswers['districts'] ?? '')) ?>" placeholder="Centre, Mazarin, Jas de Bouffan..."></div>
                    <div class="onboarding-field"><label>Type de marché local</label><input type="text" name="market_type" value="<?= e((string) ($activeAnswers['market_type'] ?? '')) ?>"></div>
                <?php elseif ($step === 5): ?>
                    <div class="onboarding-field"><label>Objectif prioritaire *</label><input required type="text" name="primary_goal" value="<?= e((string) ($activeAnswers['primary_goal'] ?? '')) ?>"></div>
                    <div class="onboarding-field"><label>Canal d'acquisition *</label><input required type="text" name="primary_channel" value="<?= e((string) ($activeAnswers['primary_channel'] ?? '')) ?>"></div>
                    <div class="onboarding-field"><label>Budget mensuel estimatif (€)</label><input type="number" min="0" step="10" name="budget_monthly" value="<?= e((string) ($activeAnswers['budget_monthly'] ?? '')) ?>"></div>
                    <div class="onboarding-field" style="grid-column:1/-1">
                        <label>Outputs souhaités</label>
                        <label><input type="checkbox" name="outputs[]" value="site" <?= in_array('site', $selectedOutputs, true) ? 'checked' : '' ?>> Site</label>
                        <label><input type="checkbox" name="outputs[]" value="funnel" <?= in_array('funnel', $selectedOutputs, true) ? 'checked' : '' ?>> Tunnel</label>
                        <label><input type="checkbox" name="outputs[]" value="seo" <?= in_array('seo', $selectedOutputs, true) ? 'checked' : '' ?>> SEO</label>
                        <label><input type="checkbox" name="outputs[]" value="content" <?= in_array('content', $selectedOutputs, true) ? 'checked' : '' ?>> Contenu</label>
                        <label><input type="checkbox" name="outputs[]" value="crm" <?= in_array('crm', $selectedOutputs, true) ? 'checked' : '' ?>> CRM</label>
                    </div>
                <?php endif; ?>
            </div>

            <div class="onboarding-actions">
                <div>
                    <?php if ($step > 1): ?>
                        <button class="btn btn-secondary" name="navigation" value="prev" type="submit">Retour</button>
                    <?php endif; ?>
                </div>
                <div>
                    <button class="btn btn-ghost" name="navigation" value="save" type="submit">Enregistrer</button>
                    <button class="btn btn-primary" name="navigation" value="next" type="submit"><?= $step < 5 ? 'Suivant' : 'Voir le récapitulatif' ?></button>
                </div>
            </div>
        </form>
    </section>
    <?php else: ?>

    <style>
    /* ===== ÉCRAN STRATÉGIE ===== */
    .str-wrap {
        --s-navy:    #1a3c5e;
        --s-navy-dk: #0f2237;
        --s-gold:    #c9a84c;
        --s-bg:      #f0f4f8;
        --s-white:   #ffffff;
        --s-border:  #dde3eb;
        --s-text:    #1e293b;
        --s-muted:   #64748b;
        --s-green:   #16a34a;
        --s-radius:  12px;
        font-family: 'Segoe UI', system-ui, sans-serif;
        color: var(--s-text);
        max-width: 860px;
        margin: 0 auto;
    }

    /* Intro recap */
    .str-intro {
        background: var(--s-white);
        border: 1px solid var(--s-border);
        border-radius: var(--s-radius);
        padding: 20px 24px;
        margin-bottom: 28px;
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 20px;
        flex-wrap: wrap;
    }
    .str-intro h1 {
        font-size: 1.3rem;
        font-weight: 800;
        color: var(--s-navy-dk);
        margin: 0 0 4px;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .str-intro h1 i { color: var(--s-gold); }
    .str-intro p { font-size: .875rem; color: var(--s-muted); margin: 0; }
    .str-intro-facts {
        display: flex;
        gap: 20px;
        flex-wrap: wrap;
        font-size: .82rem;
    }
    .str-fact { text-align: center; }
    .str-fact strong { display: block; font-size: 1.1rem; font-weight: 800; color: var(--s-navy); }
    .str-fact span { color: var(--s-muted); }

    /* Module card */
    .str-module {
        background: var(--s-white);
        border: 1px solid var(--s-border);
        border-radius: var(--s-radius);
        overflow: hidden;
        margin-bottom: 18px;
        box-shadow: 0 2px 8px rgba(26,60,94,.06);
    }

    /* Header foncé */
    .str-module-head {
        background: var(--s-navy-dk);
        padding: 14px 22px;
        display: flex;
        align-items: center;
        gap: 12px;
    }
    .str-module-head .str-num {
        width: 30px; height: 30px;
        background: var(--s-gold);
        color: var(--s-navy-dk);
        border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        font-weight: 800;
        font-size: .85rem;
        flex-shrink: 0;
    }
    .str-module-head h2 {
        font-size: 1rem;
        font-weight: 800;
        color: #ffffff;
        margin: 0;
        letter-spacing: .03em;
        text-transform: uppercase;
    }
    .str-module-head p {
        font-size: .78rem;
        color: #94a3b8;
        margin: 2px 0 0;
    }

    /* Corps */
    .str-module-body { padding: 20px 22px; display: grid; gap: 18px; }

    /* Blocs Pourquoi / Explication / Recette */
    .str-block-label {
        font-size: .7rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: .1em;
        color: var(--s-navy);
        margin-bottom: 6px;
        display: flex;
        align-items: center;
        gap: 6px;
    }
    .str-block-label i { color: var(--s-gold); font-size: .75rem; }
    .str-block-text {
        font-size: .875rem;
        color: var(--s-text);
        line-height: 1.65;
    }

    /* Recette steps */
    .str-recipe {
        list-style: none;
        padding: 0; margin: 0;
        display: grid;
        gap: 8px;
    }
    .str-recipe li {
        display: flex;
        align-items: flex-start;
        gap: 10px;
        font-size: .875rem;
        color: var(--s-text);
        line-height: 1.5;
    }
    .str-recipe-num {
        width: 22px; height: 22px;
        background: var(--s-navy);
        color: #fff;
        border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        font-size: .72rem;
        font-weight: 700;
        flex-shrink: 0;
        margin-top: 1px;
    }

    /* CTA bas */
    .str-cta {
        background: var(--s-navy-dk);
        border-radius: var(--s-radius);
        padding: 24px 28px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        flex-wrap: wrap;
        margin-top: 8px;
    }
    .str-cta-text h3 {
        font-size: 1.05rem;
        font-weight: 800;
        color: #fff;
        margin: 0 0 4px;
    }
    .str-cta-text p { font-size: .84rem; color: #94a3b8; margin: 0; }
    .str-btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 11px 22px;
        border-radius: 8px;
        font-size: .9rem;
        font-weight: 700;
        font-family: inherit;
        cursor: pointer;
        text-decoration: none;
        transition: opacity .15s, transform .1s;
        border: none;
    }
    .str-btn:hover { opacity: .88; transform: translateY(-1px); }
    .str-btn-gold { background: var(--s-gold); color: var(--s-navy-dk); }
    .str-btn-outline {
        background: transparent;
        border: 1.5px solid rgba(255,255,255,.25);
        color: #e2e8f0;
    }
    </style>

    <?php
    $moduleIcons = [
        'construire' => ['icon' => 'fas fa-layer-group', 'num' => 1],
        'attirer'    => ['icon' => 'fas fa-bullseye',    'num' => 2],
        'capturer'   => ['icon' => 'fas fa-inbox',       'num' => 3],
        'convertir'  => ['icon' => 'fas fa-handshake',   'num' => 4],
        'optimiser'  => ['icon' => 'fas fa-chart-line',  'num' => 5],
    ];
    ?>

    <div class="str-wrap">

        <!-- Intro avec données blueprint -->
        <div class="str-intro">
            <div>
                <h1><i class="fas fa-rocket"></i> Votre plan stratégique est prêt</h1>
                <p>Onboarding complété &mdash; voici votre feuille de route par module.</p>
            </div>
            <div class="str-intro-facts">
                <?php if ($answers['identity']['name'] ?? ''): ?>
                <div class="str-fact">
                    <strong><?= e((string)($answers['identity']['name'] ?? '')) ?></strong>
                    <span><?= e(ucfirst((string)($answers['identity']['role'] ?? ''))) ?></span>
                </div>
                <?php endif; ?>
                <?php if ($answers['territory']['city'] ?? ''): ?>
                <div class="str-fact">
                    <strong><?= e((string)($answers['territory']['city'] ?? '')) ?></strong>
                    <span>Zone principale</span>
                </div>
                <?php endif; ?>
                <?php if ($answers['goal']['primary_goal'] ?? ''): ?>
                <div class="str-fact">
                    <strong><?= e((string)($answers['goal']['primary_goal'] ?? '')) ?></strong>
                    <span>Objectif</span>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Un module par bloc -->
        <?php foreach ($stratModules as $slug => $mod):
            if (empty($mod['meta']['motivation'])) continue;
            $meta = $mod['meta'];
            $info = $moduleIcons[$slug] ?? ['icon' => 'fas fa-circle', 'num' => '?'];
        ?>
        <div class="str-module">
            <div class="str-module-head">
                <div class="str-num"><?= $info['num'] ?></div>
                <div>
                    <h2><i class="<?= e($info['icon']) ?>" style="margin-right:6px"></i><?= e($mod['title'] ?? strtoupper($slug)) ?></h2>
                    <p><?= e($mod['description'] ?? '') ?></p>
                </div>
            </div>
            <div class="str-module-body">

                <div>
                    <div class="str-block-label"><i class="fas fa-circle-question"></i> Pourquoi</div>
                    <p class="str-block-text"><?= e($meta['motivation']) ?></p>
                </div>

                <div>
                    <div class="str-block-label"><i class="fas fa-lightbulb"></i> Explication</div>
                    <p class="str-block-text"><?= e($meta['explanation']) ?></p>
                </div>

                <?php if (!empty($meta['recipe'])): ?>
                <div>
                    <div class="str-block-label"><i class="fas fa-list-check"></i> Recette</div>
                    <ol class="str-recipe">
                        <?php foreach ($meta['recipe'] as $i => $step): ?>
                            <li>
                                <span class="str-recipe-num"><?= $i + 1 ?></span>
                                <span><?= e($step) ?></span>
                            </li>
                        <?php endforeach; ?>
                    </ol>
                </div>
                <?php endif; ?>

            </div>
        </div>
        <?php endforeach; ?>

        <!-- CTA final -->
        <div class="str-cta">
            <div class="str-cta-text">
                <h3>Prêt à passer à l'action ?</h3>
                <p>Votre configuration est enregistrée. Commencez par le Module 1 : Construire.</p>
            </div>
            <div style="display:flex;gap:10px;flex-wrap:wrap">
                <a class="str-btn str-btn-outline" href="/admin?module=onboarding&amp;step=5">
                    <i class="fas fa-arrow-left"></i> Retour
                </a>
                <?php if ($status !== 'completed'): ?>
                <form method="post" action="/admin?module=onboarding&amp;step=6" style="display:inline">
                    <?= csrfField() ?>
                    <input type="hidden" name="intent" value="complete">
                    <button class="str-btn str-btn-gold" type="submit">
                        <i class="fas fa-check"></i> Valider &amp; commencer
                    </button>
                </form>
                <?php else: ?>
                <a class="str-btn str-btn-gold" href="/admin?module=construire">
                    <i class="fas fa-arrow-right"></i> Retour au plan d'action
                </a>
                <?php endif; ?>
            </div>
        </div>

    </div><!-- .str-wrap -->

    <?php endif; ?>
</div>
