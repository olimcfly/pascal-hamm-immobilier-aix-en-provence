<?php include __DIR__ . '/_noah_styles.php'; ?>
<div class="hub-page">

<header class="hub-hero">
    <div class="hub-hero-badge"><i class="fas fa-briefcase"></i> Construire — Noah IA</div>
    <h1>Formulation d'Offre</h1>
    <p>Construisez votre pitch commercial en 3 versions adaptées à votre persona cible.</p>
</header>

<div class="hub-narrative">
    <article class="hub-narrative-card hub-narrative-card--motivation">
        <h3><i class="fas fa-bolt" style="color:#f59e0b"></i> Le constat</h3>
        <p>Un discours commercial flou fait fuir les clients. Sans offre claire, impossible de signer en premier rendez-vous.</p>
    </article>
    <article class="hub-narrative-card hub-narrative-card--resultat">
        <h3><i class="fas fa-check-circle" style="color:#10b981"></i> Ce que vous obtenez</h3>
        <p>3 versions de votre pitch adaptées à votre persona — courte, détaillée, et en réponse aux objections courantes.</p>
    </article>
    <article class="hub-narrative-card hub-narrative-card--action">
        <h3><i class="fas fa-triangle-exclamation" style="color:#ef4444"></i> Conseil</h3>
        <p>Commencez par votre persona le plus rentable. Adaptez ensuite le pitch aux autres profils.</p>
    </article>
</div>

<div style="--tool-color:#27ae60">

    <a href="?module=construire" class="noah-back">
        <i class="fas fa-arrow-left"></i> Retour à Construire
    </a>

    <div class="noah-tool-header">
        <div class="noah-tool-icon" style="background:#eafaf1; color:#27ae60">
            <i class="fas fa-briefcase"></i>
        </div>
        <div>
            <h2 class="noah-tool-title">Offre Conseiller — Formulation</h2>
            <p class="noah-tool-sub">Construisez votre pitch commercial en 3 versions adaptées à votre persona</p>
        </div>
        <span class="noah-tool-badge">Noah IA</span>
    </div>

    <div class="noah-form-card">
        <form id="form-offre">
            <input type="hidden" name="tool" value="offre">
            <div class="noah-form-grid">
                <div class="noah-field">
                    <label class="noah-label">Votre métier</label>
                    <input class="noah-input" type="text" name="metier" placeholder="ex : agent immobilier" required>
                </div>
                <div class="noah-field">
                    <label class="noah-label">Persona ciblé</label>
                    <input class="noah-input" type="text" name="persona" placeholder="ex : vendeurs pressés" required>
                </div>
                <div class="noah-field">
                    <label class="noah-label">Objectif du client</label>
                    <input class="noah-input" type="text" name="objectif_client" placeholder="ex : vendre vite et au bon prix" required>
                </div>
                <div class="noah-field">
                    <label class="noah-label">Vos points forts</label>
                    <input class="noah-input" type="text" name="points_forts" placeholder="ex : réactivité, réseau local, photos pro" required>
                </div>
            </div>
            <button class="noah-submit" type="submit">
                <i class="fas fa-wand-magic-sparkles"></i> Formuler mon offre avec Noah
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
<script>initNoahForm('form-offre', '#27ae60');</script>
