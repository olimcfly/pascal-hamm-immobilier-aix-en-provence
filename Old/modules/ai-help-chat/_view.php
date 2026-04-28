<?php

declare(strict_types=1);

function renderAiHelpChatIndex(AiHelpChatService $service): void
{
    $user = Auth::user() ?? ['role' => 'guest'];
    $role = (string) ($user['role'] ?? 'guest');
    $allowed = $service->canUserUseChat($role);
    $settings = $service->getSettings();
    ?>
    <div class="hub-page">

    <header class="hub-hero">
        <div class="hub-hero-badge"><i class="fas fa-comments"></i> Aide IA</div>
        <h1>Chat d’aide IA</h1>
        <p>Assistant contextuel connecté au centre d’aide et aux ressources internes du CRM.</p>
    </header>

    <div class="hub-narrative">
        <article class="hub-narrative-card hub-narrative-card--explanation">
            <h3><i class="fas fa-plug" style="color:#3b82f6"></i> Connexion IA</h3>
            <p>Le chat utilise votre configuration IA active pour répondre à vos questions en langage naturel.</p>
        </article>
        <article class="hub-narrative-card hub-narrative-card--resultat">
            <h3><i class="fas fa-check-circle" style="color:#10b981"></i> Sources disponibles</h3>
            <p>Centre d’aide, guides internes, documentation modules — les réponses sont contextualisées à votre situation.</p>
        </article>
        <article class="hub-narrative-card hub-narrative-card--motivation">
            <h3><i class="fas fa-bolt" style="color:#f59e0b"></i> Statut actuel</h3>
            <p><?= $allowed ? ‘Le chat est disponible pour votre rôle.’ : ‘Le chat n\’est pas disponible pour votre rôle actuel.’ ?> Rôle : <strong><?= htmlspecialchars($role) ?></strong></p>
        </article>
    </div>

    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:1rem">
        <div style="background:#fff;border:1px solid var(--hub-border,#e2e8f0);border-radius:var(--hub-radius-md,14px);padding:1.1rem 1.25rem;box-shadow:var(--hub-shadow-sm)">
            <h3 style="margin:0 0 .5rem;font-size:.95rem;color:#0f172a;font-weight:700"><i class="fas fa-robot" style="color:#f59e0b;margin-right:.4rem"></i>Assistant</h3>
            <p style="font-size:.88rem;color:#4b5563;margin:.3rem 0"><?= htmlspecialchars((string) ($settings[‘assistant_name’] ?? ‘Assistant Aide IA’)) ?></p>
            <small style="font-size:.78rem;color:#64748b">Mode : <?= htmlspecialchars((string) ($settings[‘response_mode’] ?? ‘guide’)) ?> · Ton : <?= htmlspecialchars((string) ($settings[‘tone’] ?? ‘professionnel’)) ?></small>
        </div>
        <div style="background:#fff;border:1px solid var(--hub-border,#e2e8f0);border-radius:var(--hub-radius-md,14px);padding:1.1rem 1.25rem;box-shadow:var(--hub-shadow-sm)">
            <h3 style="margin:0 0 .5rem;font-size:.95rem;color:#0f172a;font-weight:700"><i class="fas fa-database" style="color:#3b82f6;margin-right:.4rem"></i>Sources</h3>
            <p style="font-size:.88rem;color:#4b5563;margin:.3rem 0">Centre d’aide, guides internes, docs modules, vidéos futures.</p>
            <small style="font-size:.78rem;color:#64748b">Suggestions contextuelles activables depuis la configuration.</small>
        </div>
    </div>

    <?php if (aiHelpChatCanConfigure()): ?>
    <div class="hub-final-cta">
        <div>
            <h2>Configuration avancée</h2>
            <p>Personnalisez le comportement, le ton et les sources de l’assistant.</p>
        </div>
        <a href="/admin?module=ai-help-chat&action=settings" class="hub-btn hub-btn--gold"><i class="fas fa-gear"></i> Ouvrir la configuration</a>
    </div>
    <?php endif; ?>

    </div><!-- /.hub-page -->
    <?php
}
