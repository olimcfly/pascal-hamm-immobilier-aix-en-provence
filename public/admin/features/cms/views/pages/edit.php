<?php
$pageTitle = ucfirst(str_replace('-', ' ', $page_slug));
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier <?= htmlspecialchars($pageTitle) ?> | CMS</title>
    <link rel="stylesheet" href="/admin/assets/css/legacy-harmonize.css">
    <style>
        body.legacy-standalone { margin: 0; }
        .legacy-standalone-shell { max-width: 1100px; margin: 0 auto; padding: 2rem; }
        .legacy-card + .legacy-card { margin-top: 0; }
    </style>
</head>
<body class="legacy-standalone">
<main class="legacy-standalone-shell">
    <header class="legacy-hero">
        <h1>Modifier la page <?= htmlspecialchars($pageTitle) ?></h1>
        <p>Éditez les contenus du CMS avec une mise en page alignée sur le reste de l'administration.</p>
    </header>

    <?php if (!empty($_GET['success'])): ?>
        <div class="legacy-alert legacy-alert--success">Les modifications ont été enregistrées.</div>
    <?php endif; ?>

    <form method="POST" action="/admin/cms/save" class="legacy-form-grid">
        <input type="hidden" name="page_slug" value="<?= htmlspecialchars($page_slug) ?>">

        <?php foreach ($sections as $sectionKey => $sectionConfig): ?>
            <section class="legacy-form-section">
                <h3><?= htmlspecialchars($sectionConfig['title'] ?? ucfirst($sectionKey)) ?></h3>

                <?php foreach (($sectionConfig['fields'] ?? []) as $fieldKey => $fieldConfig): ?>
                    <div class="legacy-form-group">
                        <label class="legacy-label" for="<?= htmlspecialchars($sectionKey . '_' . $fieldKey) ?>">
                            <?= htmlspecialchars($fieldConfig['label'] ?? ucfirst($fieldKey)) ?>
                        </label>
                        <?php if (($fieldConfig['type'] ?? 'text') === 'textarea'): ?>
                            <textarea
                                class="legacy-textarea"
                                id="<?= htmlspecialchars($sectionKey . '_' . $fieldKey) ?>"
                                name="sections[<?= htmlspecialchars($sectionKey) ?>][<?= htmlspecialchars($fieldKey) ?>]"
                            ><?= htmlspecialchars($pageData[$sectionKey][$fieldKey] ?? '') ?></textarea>
                        <?php else: ?>
                            <input
                                class="legacy-input"
                                type="text"
                                id="<?= htmlspecialchars($sectionKey . '_' . $fieldKey) ?>"
                                name="sections[<?= htmlspecialchars($sectionKey) ?>][<?= htmlspecialchars($fieldKey) ?>]"
                                value="<?= htmlspecialchars($pageData[$sectionKey][$fieldKey] ?? '') ?>"
                            >
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </section>
        <?php endforeach; ?>

        <div class="legacy-form-actions">
            <button type="submit" class="legacy-btn legacy-btn--primary">Enregistrer</button>
            <a class="legacy-btn legacy-btn--secondary" href="/admin?module=cms">Retour</a>
        </div>
    </form>
</main>
</body>
</html>
