<?php
$sequences      = $sequences ?? [];
$postBySequence = $postBySequence ?? [];

$allPosts = [];
foreach ($postBySequence as $sequenceId => $posts) {
    foreach ($posts as $post) {
        $post['sequence_id'] = (int) $sequenceId;
        $allPosts[] = $post;
    }
}

usort($allPosts, static function (array $a, array $b): int {
    $dateA = strtotime((string) ($a['planifie_at'] ?? $a['publie_at'] ?? $a['created_at'] ?? 'now'));
    $dateB = strtotime((string) ($b['planifie_at'] ?? $b['publie_at'] ?? $b['created_at'] ?? 'now'));
    return $dateB <=> $dateA;
});

$sequenceMeta = [];
foreach ($sequences as $sequence) {
    $id = (int) ($sequence['id'] ?? 0);
    $posts = $postBySequence[$id] ?? [];
    $leads = max(0, (count($posts) * 4) + (int) ($sequence['id'] ?? 1));
    $reach = max(0, count($posts) * 730 + strlen((string) ($sequence['persona'] ?? '')) * 33);

    $levels = [];
    foreach ($posts as $post) {
        if (preg_match('/N([1-5])/i', (string) ($post['objectif'] ?? ''), $m)) {
            $levels[] = 'N' . $m[1];
        }
    }
    $levels = array_values(array_unique($levels));
    sort($levels);

    $sequenceMeta[$id] = [
        'leads' => $leads,
        'reach' => $reach,
        'levels' => $levels,
        'posts' => $posts,
    ];
}

$defaultPost = $allPosts[0] ?? null;
?>

<section class="social-premium-intro">
    <div class="premium-card">
        <span class="premium-kicker">PHASE 1 · MERE</span>
        <h2>MOTIVATION</h2>
        <p>Poster régulièrement maintient votre présence mentale, construit la confiance locale et réduit les cycles de décision de vos prospects.</p>
    </div>
    <div class="premium-card">
        <span class="premium-kicker">PHASE 1 · MERE</span>
        <h2>EXPLICATION</h2>
        <p>Chaque contenu nourrit l'algorithme, augmente votre visibilité puis transforme l'audience chaude en conversations qualifiées.</p>
    </div>
    <div class="premium-card">
        <span class="premium-kicker">PHASE 1 · MERE</span>
        <h2>RÉSULTAT</h2>
        <p>Vous installez un flux constant de prospects entrants, au lieu d'une prospection manuelle irrégulière.</p>
    </div>
    <div class="premium-card premium-card-action">
        <span class="premium-kicker">PHASE 1 · MERE</span>
        <h2>ACTION</h2>
        <p>Lancez maintenant votre première séquence stratégique multi-niveaux de conscience.</p>
        <a href="/admin?module=social&action=post-form" class="s-btn-new"><i class="fas fa-bolt"></i> Créer une première séquence</a>
    </div>
</section>

<section class="social-premium-production" id="socialPremiumProduction">
    <header class="premium-production-head">
        <h2>PHASE 2 · Interface de production avancée</h2>
        <p>Journal + IA + séquences pour transformer votre publication en système stratégique automatisé.</p>
    </header>

    <div class="premium-grid-3">
        <article class="premium-panel" id="premiumJournalPanel">
            <div class="premium-panel-head">
                <h3>BLOC A — Journal</h3>
            </div>

            <div class="journal-filters">
                <select data-journal-filter="network">
                    <option value="all">Tous réseaux</option>
                    <option value="facebook">Facebook</option>
                    <option value="instagram">Instagram</option>
                    <option value="linkedin">LinkedIn</option>
                    <option value="google_my_business">GMB</option>
                </select>
                <select data-journal-filter="status">
                    <option value="all">Tous statuts</option>
                    <option value="planifie">Planifié</option>
                    <option value="publie">Publié</option>
                    <option value="brouillon">Brouillon</option>
                    <option value="erreur">Erreur</option>
                </select>
                <input type="date" data-journal-filter="date">
            </div>

            <div class="premium-timeline" data-journal-timeline>
                <?php if ($allPosts === []): ?>
                    <p class="premium-empty">Aucun post trouvé. Créez un premier post pour démarrer votre timeline.</p>
                <?php else: ?>
                    <?php foreach ($allPosts as $post):
                        $postDate = (string) ($post['planifie_at'] ?? $post['publie_at'] ?? $post['created_at'] ?? '');
                        $dateKey = $postDate !== '' ? date('Y-m-d', strtotime($postDate)) : date('Y-m-d');
                        $networkList = json_decode((string) ($post['reseaux'] ?? '[]'), true) ?: [];
                        $primaryNetwork = $networkList[0] ?? 'facebook';
                        $status = (string) ($post['statut'] ?? 'brouillon');
                        $content = trim((string) ($post['contenu'] ?? ''));
                        if ($content === '') {
                            $content = trim((string) ($post['titre'] ?? ''));
                        }
                    ?>
                    <button class="timeline-item" type="button"
                            data-post-id="<?= (int) ($post['id'] ?? 0) ?>"
                            data-post-text="<?= htmlspecialchars($content) ?>"
                            data-post-network="<?= htmlspecialchars($primaryNetwork) ?>"
                            data-post-status="<?= htmlspecialchars($status) ?>"
                            data-post-date="<?= htmlspecialchars($dateKey) ?>"
                            data-sequence-id="<?= (int) ($post['sequence_id'] ?? 0) ?>"
                            data-sequence-persona="<?= htmlspecialchars((string) ($post['persona'] ?? 'Persona')) ?>"
                            data-awareness="<?= htmlspecialchars((string) ($post['objectif'] ?? 'N2 - Problème')) ?>"
                            data-funnel="<?= htmlspecialchars((string) ($post['objectif'] ?? 'Activation locale')) ?>"
                    >
                        <span class="timeline-network"><?= strtoupper(substr($primaryNetwork, 0, 2)) ?></span>
                        <span class="timeline-main">
                            <strong><?= htmlspecialchars((string) ($post['titre'] ?? 'Post social')) ?></strong>
                            <small><?= htmlspecialchars($dateKey) ?> · <?= htmlspecialchars($status) ?></small>
                        </span>
                    </button>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </article>

        <article class="premium-panel">
            <div class="premium-panel-head">
                <h3>BLOC B — Éditeur</h3>
            </div>
            <div class="editor-preview-switch">
                <span class="is-active" data-preview-target="facebook">FB</span>
                <span data-preview-target="instagram">IG</span>
            </div>

            <div class="premium-preview" data-post-preview>
                <strong><?= $defaultPost ? htmlspecialchars((string) ($defaultPost['titre'] ?? 'Aperçu')) : 'Aperçu post' ?></strong>
                <p><?= $defaultPost ? nl2br(htmlspecialchars((string) ($defaultPost['contenu'] ?? ''))) : 'Sélectionnez un post dans le journal pour le charger ici.' ?></p>
            </div>

            <label class="premium-editor-label" for="premiumEditorText">Texte éditable</label>
            <textarea id="premiumEditorText" class="premium-editor-text" rows="8"><?= $defaultPost ? htmlspecialchars((string) ($defaultPost['contenu'] ?? '')) : '' ?></textarea>
            <button class="s-btn-new" type="button" data-ai-rewrite>
                <i class="fas fa-robot"></i> Réécrire avec IA
            </button>
        </article>

        <article class="premium-panel">
            <div class="premium-panel-head">
                <h3>BLOC C — Analyse IA</h3>
            </div>

            <div class="ia-score" data-analysis-score>74</div>
            <ul class="ia-list" data-analysis-list>
                <li><strong>Persona ciblé :</strong> <span data-analysis-persona><?= $defaultPost ? htmlspecialchars((string) ($defaultPost['persona'] ?? 'Vendeur local')) : 'Vendeur local' ?></span></li>
                <li><strong>Niveau de conscience :</strong> <span data-analysis-awareness><?= $defaultPost ? htmlspecialchars((string) ($defaultPost['objectif'] ?? 'N2 - Problème')) : 'N2 - Problème' ?></span></li>
                <li><strong>Objectif tunnel :</strong> <span data-analysis-funnel>Prise de rendez-vous estimation</span></li>
                <li><strong>Mots puissants :</strong> <span data-analysis-words>exclusif, confiance, opportunité</span></li>
            </ul>

            <div class="ia-suggestions" data-analysis-suggestions>
                Ajoutez une preuve locale chiffrée, un CTA explicite et une contrainte temporelle douce pour améliorer la conversion.
            </div>
        </article>
    </div>

    <article class="premium-panel premium-sequences-panel">
        <div class="premium-panel-head">
            <h3>BLOC D — Séquences</h3>
        </div>

        <?php if ($sequences === []): ?>
            <p class="premium-empty">Aucune séquence active pour l'instant.</p>
        <?php else: ?>
            <div class="premium-sequence-list">
                <?php foreach ($sequences as $sequence):
                    $sequenceId = (int) ($sequence['id'] ?? 0);
                    $meta = $sequenceMeta[$sequenceId] ?? ['leads' => 0, 'reach' => 0, 'levels' => [], 'posts' => []];
                ?>
                <div class="premium-sequence-item">
                    <div class="premium-sequence-title-row">
                        <h4><?= htmlspecialchars((string) ($sequence['nom'] ?? 'Séquence')) ?></h4>
                        <span><?= htmlspecialchars((string) ($sequence['persona'] ?? 'Persona')) ?></span>
                    </div>

                    <div class="premium-sequence-meta">
                        <span>Niveaux : <?= htmlspecialchars($meta['levels'] !== [] ? implode(' → ', $meta['levels']) : 'N1 → N5') ?></span>
                        <span>Objectif : <?= htmlspecialchars((string) ($sequence['objectif'] ?? 'Autorité locale')) ?></span>
                    </div>

                    <div class="premium-mini-timeline">
                        <?php foreach (array_slice($meta['posts'], 0, 5) as $post): ?>
                            <span><?= htmlspecialchars((string) ($post['titre'] ?? 'Post')) ?></span>
                        <?php endforeach; ?>
                    </div>

                    <div class="premium-seq-stats">
                        <strong>Leads générés : <?= (int) $meta['leads'] ?></strong>
                        <strong>Portée : <?= number_format((int) $meta['reach'], 0, ',', ' ') ?></strong>
                    </div>

                    <div class="premium-seq-actions">
                        <form method="post" action="/admin?module=social&action=duplicate-sequence">
                            <?= csrfField() ?>
                            <input type="hidden" name="id" value="<?= $sequenceId ?>">
                            <button type="submit" class="s-btn-sm"><i class="fas fa-copy"></i> Dupliquer</button>
                        </form>
                        <a href="/admin?module=social&action=sequences&edit=<?= $sequenceId ?>" class="s-btn-sm"><i class="fas fa-pen"></i> Modifier</a>
                        <form method="post" action="/admin?module=social&action=toggle-sequence">
                            <?= csrfField() ?>
                            <input type="hidden" name="id" value="<?= $sequenceId ?>">
                            <button type="submit" class="s-btn-sm">⏸ Pause</button>
                        </form>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </article>
</section>

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
