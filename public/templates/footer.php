<?php
$advisorName    = 'Pascal Hamm';
$advisorTitle   = 'Expert Immobilier 360°';
$advisorTagline = 'Expert immobilier indépendant dans le Pays d\'Aix. Accompagnement premium en vente, achat et financement, avec méthode et discrétion.';
?>

<footer class="site-footer">
    <div class="container footer__grid">

        <!-- Identité -->
        <div class="footer__col footer__brand">
            <a href="/" class="footer__logo">
                <span aria-hidden="true">🏡</span>
                <span>
                    <strong>Pascal Hamm</strong><br>
                    <em><?= e($advisorTitle) ?></em>
                </span>
            </a>
            <p class="footer__tagline"><?= e($advisorTagline) ?></p>
            <div class="footer__social">
                <a href="#" class="social-link" aria-label="Facebook" rel="noopener noreferrer">
                    <svg viewBox="0 0 24 24" width="20" fill="currentColor"><path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"/></svg>
                </a>
                <a href="#" class="social-link" aria-label="LinkedIn" rel="noopener noreferrer">
                    <svg viewBox="0 0 24 24" width="20" fill="currentColor"><path d="M16 8a6 6 0 0 1 6 6v7h-4v-7a2 2 0 0 0-2-2 2 2 0 0 0-2 2v7h-4v-7a6 6 0 0 1 6-6zM2 9h4v12H2z"/><circle cx="4" cy="4" r="2"/></svg>
                </a>
                <a href="#" class="social-link" aria-label="Instagram" rel="noopener noreferrer">
                    <svg viewBox="0 0 24 24" width="20" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="2" width="20" height="20" rx="5" ry="5"/><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"/><line x1="17.5" y1="6.5" x2="17.51" y2="6.5"/></svg>
                </a>
            </div>
        </div>

        <div class="footer__col">
            <h3 class="footer__title">Découvrir</h3>
            <ul class="footer__links">
                <li><a href="/biens">Nos biens</a></li>
                <li><a href="/guide-local">Secteurs</a></li>
                <li><a href="/acheter">Acheter</a></li>
            </ul>
        </div>

        <div class="footer__col">
            <h3 class="footer__title">Vendre</h3>
            <ul class="footer__links">
                <li><a href="/vendre">Vendre un bien</a></li>
                <li><a href="/estimation-gratuite">Estimation gratuite</a></li>
                <li><a href="/ressources/guide-vendeur">Méthode de vente</a></li>
            </ul>
        </div>

        <div class="footer__col">
            <h3 class="footer__title">Acheter</h3>
            <ul class="footer__links">
                <li><a href="/acheter">Acheter un bien</a></li>
                <li><a href="/financement">Financement</a></li>
                <li><a href="/contact?sujet=Recherche+personnalisee">Recherche personnalisée</a></li>
            </ul>
        </div>

        <div class="footer__col">
            <h3 class="footer__title">Entreprise</h3>
            <ul class="footer__links">
                <li><a href="/a-propos">À propos</a></li>
                <li><a href="/avis">Avis clients</a></li>
                <li><a href="/contact">Contact</a></li>
            </ul>
        </div>

    </div>

    <div class="footer__bottom">
        <div class="container footer__bottom-inner">
            <p>
                &copy; <?= date('Y') ?> Pascal Hamm — Tous droits réservés
                <?= defined('APP_SIRET') && APP_SIRET ? ' — SIRET&nbsp;: ' . e(APP_SIRET) : '' ?>.
            </p>
            <nav aria-label="Liens légaux">
                <a href="/mentions-legales">Mentions légales</a>
                <a href="/politique-confidentialite">Politique de confidentialité</a>
                <a href="/plan-du-site">Plan du site</a>
            </nav>
        </div>
    </div>
</footer>
