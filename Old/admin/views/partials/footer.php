<?php
// partials/footer.php — Pied de page admin
if (!defined('IMMO_LOCAL')) { die('Accès direct interdit'); }

$advisorDisplayName = $advisorDisplayName
    ?? trim((string) setting('advisor_firstname', '') . ' ' . (string) setting('advisor_lastname', ''))
    ?: (defined('ADVISOR_NAME') ? ADVISOR_NAME : (defined('APP_NAME') ? APP_NAME : 'IMMO LOCAL+'));

$appVersion = defined('APP_VERSION') ? APP_VERSION : '1.0.0';

$startTime = $_SERVER['REQUEST_TIME_FLOAT'] ?? $_SERVER['REQUEST_TIME'] ?? microtime(true);
$execTime  = round((microtime(true) - (float)$startTime) * 1000);
$memUsage  = round(memory_get_peak_usage(true) / 1024 / 1024, 1);
?>
<footer class="admin-footer" role="contentinfo">

    <div class="footer-left">
        <span class="footer-brand">
            <i class="fas fa-building footer-brand-icon" aria-hidden="true"></i>
            IMMO LOCAL<span class="brand-plus">+</span>
        </span>
        <span class="footer-sep" aria-hidden="true">·</span>
        <span class="footer-copy">
            &copy; <?= date('Y') ?>
            <?= htmlspecialchars($advisorDisplayName) ?> Immobilier.
            Tous droits réservés.
        </span>
    </div>

    <nav class="footer-center" aria-label="Liens utiles">
        <a href="/admin?module=aide" class="footer-link">Support</a>
        <span class="footer-sep" aria-hidden="true">·</span>
        <a href="/admin?module=aide&amp;section=doc" class="footer-link">Documentation</a>
        <span class="footer-sep" aria-hidden="true">·</span>
        <a href="/mentions-legales" target="_blank" rel="noopener" class="footer-link">Confidentialité</a>
    </nav>

    <div class="footer-right">
        <span class="footer-version" title="Version de l'application">
            <i class="fas fa-code-branch" aria-hidden="true"></i>
            v<?= htmlspecialchars($appVersion) ?>
        </span>
        <span class="footer-sep" aria-hidden="true">·</span>
        <span class="footer-perf" title="Performance serveur">
            <i class="fas fa-gauge-high" aria-hidden="true"></i>
            <?= $memUsage ?> Mo · <?= $execTime ?> ms
        </span>
    </div>

</footer>
