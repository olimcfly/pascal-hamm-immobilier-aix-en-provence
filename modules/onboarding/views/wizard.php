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
?>
<style>
    .onboarding-shell { max-width: 980px; margin: 0 auto; display:grid; gap:1rem; }
    .onboarding-card { background:#fff; border:1px solid #e7ecf3; border-radius:18px; padding:1.1rem 1.2rem; box-shadow:0 8px 30px rgba(17,24,39,.05); }
    .onboarding-header h1 { margin:0; font-size:1.5rem; }
    .onboarding-header p { margin:.35rem 0 0; color:#64748b; }
    .onboarding-stepper { display:grid; grid-template-columns:repeat(6,minmax(0,1fr)); gap:.5rem; }
    .onboarding-step { border:1px solid #dbe4ef; border-radius:999px; padding:.42rem .6rem; font-size:.78rem; text-align:center; color:#64748b; }
    .onboarding-step.active { border-color:#2563eb; background:#eff6ff; color:#1e40af; font-weight:600; }
    .onboarding-step.done { border-color:#22c55e; color:#166534; }
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
    <section class="onboarding-card">
        <h2>Récapitulatif de votre onboarding</h2>
        <div class="summary-grid">
            <div class="summary-box"><h3>Identité</h3><p><strong>Nom:</strong> <?= e((string) ($answers['identity']['name'] ?? '')) ?></p><p><strong>Marque:</strong> <?= e((string) ($answers['identity']['brand'] ?? '')) ?></p><p><strong>Rôle:</strong> <?= e((string) ($answers['identity']['role'] ?? '')) ?></p></div>
            <div class="summary-box"><h3>Cible</h3><p><strong>Persona:</strong> <?= e((string) ($answers['target']['persona'] ?? '')) ?></p><p><strong>Problème:</strong> <?= e((string) ($answers['target']['pain'] ?? '')) ?></p><p><strong>Désir:</strong> <?= e((string) ($answers['target']['desire'] ?? '')) ?></p></div>
            <div class="summary-box"><h3>Offre</h3><p><strong>Type:</strong> <?= e((string) ($answers['offer']['type'] ?? '')) ?></p><p><strong>Promesse:</strong> <?= e((string) ($answers['offer']['promise'] ?? '')) ?></p><p><strong>Différenciateur:</strong> <?= e((string) ($answers['offer']['differentiator'] ?? '')) ?></p></div>
            <div class="summary-box"><h3>Zone</h3><p><strong>Ville:</strong> <?= e((string) ($answers['territory']['city'] ?? '')) ?></p><p><strong>Quartiers:</strong> <?= e((string) ($answers['territory']['districts'] ?? '')) ?></p><p><strong>Rayon:</strong> <?= e((string) ($answers['territory']['radius_km'] ?? '0')) ?> km</p></div>
            <div class="summary-box"><h3>Objectif</h3><p><strong>Objectif:</strong> <?= e((string) ($answers['goal']['primary_goal'] ?? '')) ?></p><p><strong>Canal:</strong> <?= e((string) ($answers['goal']['primary_channel'] ?? '')) ?></p><p><strong>Budget:</strong> <?= e((string) ($answers['goal']['budget_monthly'] ?? '')) ?></p></div>
            <div class="summary-box" style="grid-column:1/-1"><h3>Blueprint JSON v1.0</h3><pre class="blueprint"><?= e((string) json_encode($blueprint, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)) ?></pre></div>
        </div>

        <div class="onboarding-actions">
            <a class="btn btn-secondary" href="/admin?module=onboarding&amp;step=5">Retour</a>
            <form method="post" action="/admin?module=onboarding&amp;step=6">
                <?= csrfField() ?>
                <input type="hidden" name="intent" value="complete">
                <button class="btn btn-primary" type="submit">Finaliser le Sprint 1</button>
            </form>
        </div>
    </section>
    <?php endif; ?>
</div>
