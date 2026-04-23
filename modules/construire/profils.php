<?php include __DIR__ . '/_noah_styles.php'; ?>
<div class="hub-page">

<header class="hub-hero">
    <div class="hub-hero-badge"><i class="fas fa-users"></i> Construire — Noah IA</div>
    <h1>Profils Clients</h1>
    <p>Identifiez vos 3 profils clients prioritaires sur votre territoire avec NeuroPersona.</p>
</header>

<div class="hub-narrative">
    <article class="hub-narrative-card hub-narrative-card--motivation">
        <h3><i class="fas fa-bolt" style="color:#f59e0b"></i> Le constat</h3>
        <p>S'adresser à tout le monde revient à ne parler à personne. Un message ciblé convertit 5x mieux.</p>
    </article>
    <article class="hub-narrative-card hub-narrative-card--resultat">
        <h3><i class="fas fa-check-circle" style="color:#10b981"></i> Ce que ça débloque</h3>
        <p>3 profils clients détaillés avec leurs motivations, freins et canaux préférés — prêts à utiliser dans vos campagnes.</p>
    </article>
    <article class="hub-narrative-card hub-narrative-card--action">
        <h3><i class="fas fa-triangle-exclamation" style="color:#ef4444"></i> À éviter</h3>
        <p>Ne créez pas 10 personas. Concentrez-vous sur les 3 profils qui génèrent 80% de vos mandats.</p>
    </article>
</div>

<div style="--tool-color:#3498db">

    <a href="?module=construire" class="noah-back">
        <i class="fas fa-arrow-left"></i> Retour à Construire
    </a>

    <div class="noah-tool-header">
        <div class="noah-tool-icon" style="background:#e3f2fd; color:#3498db">
            <i class="fas fa-brain"></i>
        </div>
        <div>
            <h2 class="noah-tool-title">NeuroPersona — Profils Clients</h2>
            <p class="noah-tool-sub">Identifiez vos 3 profils clients prioritaires sur votre territoire</p>
        </div>
        <span class="noah-tool-badge">Noah IA</span>
    </div>

    <div class="noah-form-card">
        <form id="form-profils">
            <input type="hidden" name="tool" value="profils">
            <div class="noah-form-grid">
                <div class="noah-field full">
                    <label class="noah-label">Votre activité</label>
                    <input class="noah-input" type="text" name="activite" placeholder="ex : conseiller en immobilier indépendant depuis 3 ans" required>
                </div>
                <div class="noah-field">
                    <label class="noah-label">Zone géographique</label>
                    <input class="noah-input" type="text" name="zone" placeholder="ex : Aix-en-Provence Sud" required>
                </div>
                <div class="noah-field">
                    <label class="noah-label">Objectif mensuel</label>
                    <input class="noah-input" type="text" name="objectif" placeholder="ex : 3 mandats par mois" required>
                </div>
            </div>
            <button class="noah-submit" type="submit">
                <i class="fas fa-wand-magic-sparkles"></i> Identifier mes profils clients avec Noah
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
<script>initNoahForm('form-profils', '#3498db');</script>
