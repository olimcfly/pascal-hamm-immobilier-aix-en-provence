<?php

declare(strict_types=1);

function renderHelpIndexPage(HelpCenterService $service, string $context): void
{
    $query = trim((string) ($_GET['q'] ?? ''));
    $category = trim((string) ($_GET['category'] ?? ''));
    $articles = $service->searchArticles($query, $category, $context, 50);
    $categories = $service->getCategories();
    ?>
    <div class="hub-page">

    <header class="hub-hero">
        <div class="hub-hero-badge"><i class="fas fa-circle-question"></i> Aide</div>
        <h1>Centre d'aide</h1>
        <p>Trouvez rapidement la bonne action selon votre module actif.</p>
    </header>

    <div class="aide-info-wrap">
        <button class="aide-info-btn" type="button" aria-label="Comment fonctionne ce module ?">
            <i class="fas fa-circle-info"></i> Comment fonctionne ce centre ?
        </button>
        <div class="aide-info-tooltip" role="tooltip">
            <div class="aide-info-row"><i class="fas fa-magnifying-glass" style="color:#3b82f6"></i><div><strong>Recherche contextuelle</strong><br>Les articles sont filtrés selon votre module actif pour afficher les réponses les plus pertinentes en premier.</div></div>
            <div class="aide-info-row"><i class="fas fa-check-circle" style="color:#10b981"></i><div><strong>Articles pratiques</strong><br>Chaque article inclut des actions directes vers le module concerné — pas juste de la documentation, mais de l'aide concrète.</div></div>
            <div class="aide-info-row"><i class="fas fa-robot" style="color:#f59e0b"></i><div><strong>Chat IA disponible</strong><br>Si vous ne trouvez pas votre réponse ici, l'assistant IA peut répondre à vos questions en langage naturel.</div></div>
        </div>
    </div>
    <style>
    .aide-info-wrap{position:relative;display:inline-block;margin-bottom:1.25rem;}
    .aide-info-btn{background:none;border:1px solid #e2e8f0;border-radius:6px;padding:.4rem .85rem;font-size:.85rem;color:#64748b;cursor:pointer;display:inline-flex;align-items:center;gap:.45rem;transition:background .15s,color .15s;}
    .aide-info-btn:hover{background:#f1f5f9;color:#334155;}
    .aide-info-tooltip{display:none;position:absolute;top:calc(100% + 8px);left:0;z-index:200;background:#fff;border:1px solid #e2e8f0;border-radius:10px;box-shadow:0 8px 24px rgba(0,0,0,.1);padding:1rem 1.1rem;width:400px;max-width:90vw;}
    .aide-info-tooltip.is-open{display:block;}
    .aide-info-row{display:flex;gap:.75rem;align-items:flex-start;padding:.55rem 0;font-size:.84rem;line-height:1.45;color:#374151;}
    .aide-info-row+.aide-info-row{border-top:1px solid #f1f5f9;}
    .aide-info-row>i{margin-top:2px;flex-shrink:0;width:16px;text-align:center;}
    </style>
    <script>
    (function(){var b=document.querySelector('.aide-info-btn'),t=document.querySelector('.aide-info-tooltip');if(!b||!t)return;b.addEventListener('click',function(e){e.stopPropagation();t.classList.toggle('is-open');});document.addEventListener('click',function(){t.classList.remove('is-open');});})();
    </script>

    <div style="background:#fff;border:1px solid var(--hub-border,#e2e8f0);border-radius:var(--hub-radius,16px);padding:1.25rem 1.4rem;box-shadow:var(--hub-shadow-sm)">
        <form method="get" class="help-search">
            <input type="hidden" name="module" value="aide">
            <input type="text" name="q" value="<?= htmlspecialchars($query) ?>" placeholder="Rechercher un article d'aide…" style="flex:1;min-width:220px;padding:.7rem .9rem;border:1px solid #cbd5e1;border-radius:10px;font-size:.92rem">
            <input type="hidden" name="context" value="<?= htmlspecialchars($context) ?>">
            <button type="submit" class="hub-btn hub-btn--gold"><i class="fas fa-magnifying-glass"></i> Rechercher</button>
        </form>
        <?php if ($context !== ''): ?>
            <p style="margin:.6rem 0 0;font-size:.85rem;color:#64748b">Contexte détecté : <strong style="color:#0f172a"><?= htmlspecialchars($context) ?></strong></p>
        <?php endif; ?>
    </div>

    <section class="help-categories" aria-label="Catégories">
        <a href="/admin?module=aide&amp;context=<?= urlencode($context) ?>" class="help-chip <?= $category === '' ? 'is-active' : '' ?>">Toutes</a>
        <?php foreach ($categories as $cat): ?>
            <a href="/admin?module=aide&amp;category=<?= urlencode($cat) ?>&amp;context=<?= urlencode($context) ?>" class="help-chip <?= $category === $cat ? 'is-active' : '' ?>"><?= htmlspecialchars(ucfirst($cat)) ?></a>
        <?php endforeach; ?>
    </section>

    <section class="help-grid" id="help-grid" aria-live="polite">
        <?php if ($articles === []): ?>
            <div class="help-empty">Aucun article trouvé pour cette recherche.</div>
        <?php endif; ?>

        <?php foreach ($articles as $article): ?>
            <article class="help-card" data-category="<?= htmlspecialchars((string) ($article['category'] ?? '')) ?>">
                <div class="help-card-head">
                    <span class="help-badge"><?= htmlspecialchars(strtoupper((string) ($article['category'] ?? 'general'))) ?></span>
                    <small><?= (int) ($article['views_count'] ?? 0) ?> vue(s)</small>
                </div>
                <h3><?= htmlspecialchars((string) ($article['title'] ?? "Article d'aide")) ?></h3>
                <p><?= htmlspecialchars((string) ($article['excerpt'] ?? '')) ?></p>
                <div class="help-actions">
                    <a class="help-btn" href="/admin?module=aide&amp;action=article&amp;id=<?= urlencode((string) ($article['slug'] ?? $article['id'] ?? '')) ?>&amp;context=<?= urlencode($context) ?>">Lire</a>
                    <?php if (!empty($article['cta_url']) && !empty($article['cta_label'])): ?>
                        <a class="help-link" href="<?= htmlspecialchars((string) $article['cta_url']) ?>"><?= htmlspecialchars((string) $article['cta_label']) ?></a>
                    <?php endif; ?>
                </div>
            </article>
        <?php endforeach; ?>
    </section>

    <style>
        .help-hero{background:#fff;border-radius:14px;padding:1.5rem;border:1px solid #e5e7eb;margin-bottom:1rem}
        .help-hero h1{margin:0 0 .5rem;color:#1f2937;font-size:1.45rem}
        .help-hero p{margin:.3rem 0;color:#6b7280}
        .help-search{display:flex;gap:.6rem;flex-wrap:wrap;margin-top:1rem}
        .help-search input{flex:1;min-width:220px;padding:.7rem .9rem;border:1px solid #d1d5db;border-radius:10px}
        .help-btn{display:inline-flex;align-items:center;justify-content:center;background:#1f3a5f;color:#fff;padding:.65rem .95rem;border-radius:10px;text-decoration:none;border:none;cursor:pointer}
        .help-context{margin-top:.8rem;font-size:.9rem}
        .help-categories{display:flex;gap:.5rem;flex-wrap:wrap;margin-bottom:1rem}
        .help-chip{padding:.4rem .8rem;background:#eef2ff;color:#1e3a8a;border-radius:999px;text-decoration:none;font-size:.86rem}
        .help-chip.is-active{background:var(--hub-navy,#0f2237);color:#fff}
        .help-grid{display:grid;gap:1rem;grid-template-columns:repeat(auto-fit,minmax(260px,1fr))}
        .help-card{background:#fff;border:1px solid var(--hub-border,#e2e8f0);border-radius:var(--hub-radius-md,14px);padding:1rem 1.2rem;box-shadow:var(--hub-shadow-sm,0 1px 8px rgba(15,23,42,.06))}
        .help-card h3{font-size:1rem;margin:.45rem 0;color:#0f172a;font-weight:700}
        .help-card p{font-size:.88rem;color:#4b5563;line-height:1.55}
        .help-card-head{display:flex;justify-content:space-between;align-items:center}
        .help-badge{font-size:.68rem;background:#f1f5f9;color:#475569;padding:.2rem .55rem;border-radius:999px;letter-spacing:.04em;text-transform:uppercase;font-weight:700}
        .help-actions{display:flex;justify-content:space-between;align-items:center;margin-top:.9rem}
        .help-link{font-size:.85rem;color:#1e40af;text-decoration:none}
        .help-empty{background:#fff;padding:1.2rem;border:1px dashed #d1d5db;border-radius:12px;color:#6b7280}
        .help-search{display:flex;gap:.6rem;flex-wrap:wrap}
    </style>
    </div><!-- /.hub-page -->
    <?php
}
