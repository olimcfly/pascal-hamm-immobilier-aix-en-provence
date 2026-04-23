<?php
require_once __DIR__ . '/includes/GmbService.php';

$user    = Auth::user();
$service = new GmbService((int) ($user['id'] ?? 0));
$avis    = $service->avis();
?>
<style>
.gmb-avis-list{display:grid;gap:1rem}
.gmb-avis-item{background:#fff;border:1px solid var(--hub-border,#e2e8f0);border-radius:var(--hub-radius-md,14px);padding:1.1rem 1.25rem;box-shadow:var(--hub-shadow-sm)}
.gmb-avis-head{display:flex;align-items:center;justify-content:space-between;margin-bottom:.5rem}
.gmb-avis-head strong{font-size:.95rem;color:#0f172a}
.gmb-avis-head span{color:#f59e0b;letter-spacing:.05em}
.gmb-avis-item p{font-size:.88rem;color:#4b5563;line-height:1.55;margin:.4rem 0}
.gmb-avis-item small{font-size:.78rem;color:#94a3b8}
.gmb-avis-item textarea{width:100%;margin-top:.75rem;padding:.6rem .75rem;border:1px solid #cbd5e1;border-radius:10px;font-size:.88rem;resize:vertical;min-height:70px;box-sizing:border-box}
.gmb-avis-actions{display:flex;justify-content:flex-end;margin-top:.6rem}
</style>

<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1rem;flex-wrap:wrap;gap:.5rem">
    <h2 style="margin:0;font-size:1rem;font-weight:700;color:#0f172a"><i class="fas fa-star" style="color:#f59e0b;margin-right:.4rem"></i>Avis clients (<?= count($avis) ?>)</h2>
    <button class="hub-btn hub-btn--sm" data-action="get-avis"><i class="fas fa-rotate"></i> Actualiser</button>
</div>

<div class="gmb-avis-list">
    <?php if (!$avis): ?>
        <div style="padding:3rem 1rem;text-align:center;color:#94a3b8">
            <i class="fas fa-star fa-2x" style="opacity:.2;display:block;margin-bottom:.6rem"></i>
            <div style="font-size:.88rem;font-weight:600">Aucun avis synchronisé pour le moment.</div>
            <div style="font-size:.82rem;margin-top:.3rem">Cliquez sur "Actualiser" pour synchroniser vos avis Google.</div>
        </div>
    <?php endif; ?>

    <?php foreach ($avis as $row): ?>
        <article class="gmb-avis-item" data-avis-id="<?= (int) $row['id'] ?>">
            <div class="gmb-avis-head">
                <strong><?= htmlspecialchars($row['auteur'] ?: 'Client Google') ?></strong>
                <span><?= str_repeat('★', (int) $row['note']) ?><?= str_repeat('☆', 5 - (int) $row['note']) ?></span>
            </div>
            <p><?= nl2br(htmlspecialchars((string) $row['commentaire'])) ?></p>
            <small><?= htmlspecialchars((string) $row['avis_at']) ?></small>
            <textarea class="reply-input" placeholder="Répondre à cet avis..."><?= htmlspecialchars((string) ($row['reponse'] ?? '')) ?></textarea>
            <div class="gmb-avis-actions">
                <button class="hub-btn hub-btn--sm hub-btn--gold" data-action="reply-avis"><i class="fas fa-reply"></i> Publier la réponse</button>
            </div>
        </article>
    <?php endforeach; ?>
</div>
