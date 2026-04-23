<?php include __DIR__ . '/_noah_styles.php'; ?>
<div class="hub-page">

<header class="hub-hero">
    <div class="hub-hero-badge"><i class="fas fa-anchor"></i> Construire — Noah IA</div>
    <h1>Ancrage Territorial</h1>
    <p>Créez votre phrase d'impact différenciante sur votre marché local — en quelques secondes avec Noah.</p>
</header>

<div class="hub-narrative">
    <article class="hub-narrative-card hub-narrative-card--motivation">
        <h3><i class="fas fa-bolt" style="color:#f59e0b"></i> Le problème</h3>
        <p>Sans ancrage clair, vous ressemblez à tous les autres agents. Le visiteur ne comprend pas pourquoi vous et pas un autre.</p>
    </article>
    <article class="hub-narrative-card hub-narrative-card--resultat">
        <h3><i class="fas fa-check-circle" style="color:#10b981"></i> Ce que ça produit</h3>
        <p>Une phrase d'accroche mémorable qui positionne votre expertise locale en 10 secondes — utilisable partout.</p>
    </article>
    <article class="hub-narrative-card hub-narrative-card--action">
        <h3><i class="fas fa-triangle-exclamation" style="color:#ef4444"></i> Conseil</h3>
        <p>Testez 2-3 variantes et choisissez celle qui sonne "vous" — authentique avant d'être parfait.</p>
    </article>
</div>

<div style="--tool-color:#e74c3c">

    <a href="?module=construire" class="noah-back">
        <i class="fas fa-arrow-left"></i> Retour à Construire
    </a>

    <div class="noah-tool-header">
        <div class="noah-tool-icon" style="background:#fdedec; color:#e74c3c">
            <i class="fas fa-anchor"></i>
        </div>
        <div>
            <h2 class="noah-tool-title">Méthode ANCRE+ — Positionnement</h2>
            <p class="noah-tool-sub">Générez 3 formulations d'accroche percutantes pour votre positionnement conseiller</p>
        </div>
        <span class="noah-tool-badge">Noah IA</span>
    </div>

    <div class="noah-form-card">
        <form id="form-ancre">
            <input type="hidden" name="tool" value="positionnement">
            <div class="noah-form-grid">
                <div class="noah-field">
                    <label class="noah-label">Votre métier</label>
                    <input class="noah-input" type="text" name="metier" placeholder="ex : agent immobilier indépendant" required>
                </div>
                <div class="noah-field">
                    <label class="noah-label">Zone géographique</label>
                    <input class="noah-input" type="text" name="zone" placeholder="ex : Aix-en-Provence Métropole" required>
                </div>
                <div class="noah-field">
                    <label class="noah-label">Type de clients</label>
                    <input class="noah-input" type="text" name="persona" placeholder="ex : primo-accédants 30-45 ans" required>
                </div>
                <div class="noah-field">
                    <label class="noah-label">Objectif principal</label>
                    <input class="noah-input" type="text" name="objectif" placeholder="ex : générer des mandats vendeurs" required>
                </div>
            </div>
            <button class="noah-submit" type="submit">
                <i class="fas fa-wand-magic-sparkles"></i> Générer mon positionnement avec Noah
            </button>
            <div class="noah-result-box">
                <div class="noah-result-label"><i class="fas fa-sparkles"></i> Résultat Noah IA</div>
                <div class="noah-result-content"></div>
            </div>
            <div class="noah-error-box"></div>
        </form>
    </div>

</div><!-- /tool-color wrapper -->
</div><!-- /.hub-page -->
<script>initNoahForm('form-ancre', '#e74c3c');</script>
