<?php
$sequences      = $sequences ?? [];
$postBySequence = $postBySequence ?? [];
?>
<div class="social-sequences-list">

    <?php if ($sequences === []): ?>
    <div class="s-empty-card">
        <h3>Aucune séquence</h3>
        <p>Créez votre première séquence de posts pour commencer à générer des contacts vendeurs sur les réseaux sociaux.</p>
        <a href="/admin?module=social&action=post-form" class="s-btn-new" style="margin:0 auto;">
            <i class="fas fa-plus"></i> Créer une séquence
        </a>
    </div>
    <?php endif; ?>

    <?php foreach ($sequences as $sequence): ?>
        <?php include __DIR__ . '/_row.php'; ?>
    <?php endforeach; ?>

</div>
</div><!-- /.social-wrap — ouvert dans _header.php -->
