<?php
declare(strict_types=1);

$slug = trim((string) ($slug ?? ''));
$bienTitre = '';
if ($slug !== '') {
    try {
        $st = db()->prepare('SELECT titre FROM biens WHERE slug = :s LIMIT 1');
        $st->execute([':s' => $slug]);
        $row = $st->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            $bienTitre = (string) ($row['titre'] ?? '');
        }
    } catch (Throwable) {
    }
}

$pageTitle = 'Merci pour votre demande';
$metaDesc  = 'Votre message a bien été transmis. Nous vous recontactons très prochainement.';
$extraCss  = ['/assets/css/bien-detail.css'];
?>
<section class="section bien-merci">
    <div class="container" style="max-width: 40rem; text-align: center; padding: 3rem 1rem;">
        <div class="form-success" style="display: inline-block; text-align: left; max-width: 100%; padding: 2rem;">
            <i class="fas fa-check-circle" style="font-size: 2.5rem; color: var(--clr-primary, #c9a227);"></i>
            <h1 style="margin: 1rem 0 0.75rem; font-size: 1.75rem;">Merci, votre demande est bien enregistrée</h1>
            <?php if ($bienTitre !== ''): ?>
                <p style="margin: 0 0 1rem; color: var(--clr-text-muted, #64748b);">
                    Concernant : <strong><?= htmlspecialchars($bienTitre) ?></strong>
                </p>
            <?php endif; ?>
            <p style="margin: 0 0 1.5rem; line-height: 1.6;">
                <?= htmlspecialchars(defined('ADVISOR_NAME') ? ADVISOR_NAME : 'Notre équipe') ?> a reçu votre message
                et vous recontacte sous <strong>24 h</strong> en général (jours ouvrés).
                Vous pouvez aussi nous joindre par téléphone si votre demande est urgente.
            </p>
            <div style="display: flex; flex-wrap: wrap; gap: 0.75rem; justify-content: center;">
                <?php if ($slug !== ''): ?>
                    <a class="btn btn--outline" href="<?= htmlspecialchars(url('biens/' . $slug)) ?>">
                        <i class="fas fa-arrow-left"></i> Retour à la fiche
                    </a>
                <?php endif; ?>
                <a class="btn btn--primary" href="<?= htmlspecialchars(url('biens')) ?>">
                    Voir nos biens
                </a>
            </div>
        </div>
    </div>
</section>
