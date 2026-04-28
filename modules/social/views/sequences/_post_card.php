<?php
$compact = ! empty($GLOBALS['social_post_card_compact']);

$postId    = (int) ($post['id'] ?? 0);
$titre     = (string) ($post['titre'] ?? 'Publication');
$contenu   = (string) ($post['contenu'] ?? '');
$statut    = (string) ($post['statut'] ?? 'brouillon');
$reseaux   = json_decode((string) ($post['reseaux'] ?? '[]'), true) ?: [];
$niveau    = (string) ($post['niveau'] ?? '');
$ordre     = (int) ($post['ordre_sequence'] ?? ($i + 1));
$planifieAt= (string) ($post['planifie_at'] ?? '');

$niveauHuman = [
    'n1' => 'Prise de conscience',
    'n2' => 'Problème identifié',
    'n3' => 'Solutions envisagées',
    'n4' => 'Décision / comparaison',
    'n5' => 'Passage à l’action',
];
$niveauLine = $niveau !== '' ? ($niveauHuman[$niveau] ?? strtoupper($niveau)) : 'Niveau non défini';

/* Publication */
$scheduleMain = 'À planifier';
$scheduleSub  = '';
if ($planifieAt !== '') {
    $ts = strtotime($planifieAt);
    if ($ts) {
        $scheduleMain = date('d/m/Y · H:i', $ts);
        $scheduleSub  = 'Planifié';
    }
} elseif ($statut === 'publie') {
    $pub = (string) ($post['publie_at'] ?? '');
    if ($pub !== '') {
        $ts = strtotime($pub);
        $scheduleMain = $ts ? date('d/m/Y · H:i', $ts) : $pub;
        $scheduleSub  = 'Publié';
    } else {
        $scheduleMain = 'Publié';
        $scheduleSub  = '';
    }
}

$netMap = [
    'facebook'           => ['Facebook', 'fab fa-facebook-f', '#1877F2'],
    'instagram'          => ['Instagram', 'fab fa-instagram', '#E1306C'],
    'linkedin'           => ['LinkedIn', 'fab fa-linkedin-in', '#0A66C2'],
    'google_my_business' => ['Google Business', 'fab fa-google', '#34A853'],
];

$statutChip = [
    'brouillon' => ['Brouillon', 'spc-st--draft'],
    'planifie'  => ['Planifié', 'spc-st--plan'],
    'publie'    => ['Publié', 'spc-st--ok'],
    'erreur'    => ['Erreur', 'spc-st--err'],
];
$st = $statutChip[$statut] ?? ['—', 'spc-st--draft'];

$barClass = $niveau ? 'bar-' . $niveau : 'bar-none';
$numClass = $niveau ? 'num-' . $niveau : 'num-none';
$nbClass  = $niveau ? 'nb-'  . $niveau : '';

$postDetailUrl = function_exists('admin_url')
    ? admin_url(['module' => 'social', 'action' => 'post', 'id' => $postId])
    : ('/admin/?module=social&action=post&id=' . (int) $postId);

$preview = trim($contenu !== '' ? $contenu : $titre);
if (function_exists('mb_substr')) {
    $preview = mb_substr($preview, 0, 220);
} else {
    $preview = substr($preview, 0, 220);
}
$fullLen = function_exists('mb_strlen')
    ? mb_strlen((string) ($contenu ?: $titre))
    : strlen((string) ($contenu ?: $titre));
if ($fullLen > 220) {
    $preview .= '…';
}
?>
<a href="<?= htmlspecialchars($postDetailUrl, ENT_QUOTES, 'UTF-8') ?>"
   class="seq-post-card <?= $compact ? 'seq-post-card--compact' : 'seq-post-card--xl' ?>">
    <div class="post-niveau-bar <?= htmlspecialchars($barClass, ENT_QUOTES, 'UTF-8') ?>"></div>

    <div class="seq-post-xl-head">
        <div class="seq-post-xl-order">
            <span class="post-num <?= htmlspecialchars($numClass, ENT_QUOTES, 'UTF-8') ?>"><?= (int) $ordre ?></span>
            <?php if ($niveau !== ''): ?>
                <span class="post-niveau-badge <?= htmlspecialchars($nbClass, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars(strtoupper($niveau), ENT_QUOTES, 'UTF-8') ?></span>
            <?php endif; ?>
        </div>
        <span class="seq-post-xl-chip <?= htmlspecialchars($st[1], ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($st[0], ENT_QUOTES, 'UTF-8') ?></span>
    </div>

    <div class="seq-post-xl-nlabel"><?= htmlspecialchars($niveauLine, ENT_QUOTES, 'UTF-8') ?></div>

    <div class="seq-post-xl-nets">
        <?php foreach ($reseaux as $r): ?>
            <?php $nInfo = $netMap[$r] ?? [ucfirst((string) $r), 'fas fa-share-alt', '#64748b']; ?>
            <span class="seq-post-xl-net">
                <i class="<?= htmlspecialchars($nInfo[1], ENT_QUOTES, 'UTF-8') ?>" style="color:<?= htmlspecialchars($nInfo[2], ENT_QUOTES, 'UTF-8') ?>;"></i>
                <?= htmlspecialchars($nInfo[0], ENT_QUOTES, 'UTF-8') ?>
            </span>
        <?php endforeach; ?>
        <?php if ($reseaux === []): ?>
            <span class="seq-post-xl-net seq-post-xl-net--muted">Aucun réseau</span>
        <?php endif; ?>
    </div>

    <div class="seq-post-xl-body"><?= htmlspecialchars($preview, ENT_QUOTES, 'UTF-8') ?></div>

    <div class="seq-post-xl-sched">
        <div class="seq-post-xl-sched-icon"><i class="far fa-clock"></i></div>
        <div class="seq-post-xl-sched-text">
            <span class="seq-post-xl-sched-label"><?= $scheduleSub !== '' ? htmlspecialchars($scheduleSub, ENT_QUOTES, 'UTF-8') : 'Calendrier' ?></span>
            <span class="seq-post-xl-sched-main"><?= htmlspecialchars($scheduleMain, ENT_QUOTES, 'UTF-8') ?></span>
        </div>
    </div>

    <div class="seq-post-xl-foot">
        <span>Ouvrir la fiche</span>
        <i class="fas fa-chevron-right"></i>
    </div>
</a>
