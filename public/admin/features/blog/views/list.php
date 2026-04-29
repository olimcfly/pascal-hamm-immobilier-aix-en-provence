<?php

declare(strict_types=1);

/** @var BlogService|null $blogService */
if (!isset($blogService) || !$blogService instanceof BlogService) {
    require_once __DIR__ . '/../../../seo/services/BlogService.php';

    if (!isset($pdo) || !$pdo instanceof PDO) {
        $dbConfig = require __DIR__ . '/../../../../config/database.php';
        $dsn = sprintf(
            'mysql:host=%s;dbname=%s;charset=%s',
            $dbConfig['host'] ?? 'localhost',
            $dbConfig['dbname'] ?? '',
            $dbConfig['charset'] ?? 'utf8mb4'
        );

        $pdo = new PDO(
            $dsn,
            (string)($dbConfig['user'] ?? ''),
            (string)($dbConfig['pass'] ?? ''),
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]
        );
    }

    $blogService = new BlogService($pdo);
}

$filters = [
    'status' => (string)($_GET['status'] ?? ''),
];
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 20;
$data = $blogService->getDashboardData($filters, $page, $perPage);
$articles = $data['articles'] ?? [];
$pagination = $data['pagination'] ?? ['page' => 1, 'total_pages' => 1];

$h = static fn (?string $value): string => htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
?>

<section class="legacy-card legacy-card--tight">
    <div class="legacy-card-head">
        <h2>Articles du blog</h2>
        <a class="legacy-btn legacy-btn--primary" href="/admin?module=blog&amp;action=edit">Nouvel article</a>
    </div>

    <div class="legacy-card-body">
        <div class="legacy-table-wrap">
        <table class="legacy-table">
            <thead>
                <tr>
                    <th>Titre</th>
                    <th>Statut</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($articles === []): ?>
                    <tr>
                        <td colspan="4" class="legacy-empty">Aucun article trouvé.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($articles as $article): ?>
                        <tr>
                            <td>
                                <strong><?= $h($article['title'] ?? '') ?></strong>
                                <?php if (!empty($article['slug'])): ?>
                                    <div class="text-muted">/blog/<?= $h($article['slug']) ?></div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php
                                $status = (string)($article['status'] ?? 'draft');
                                $badgeClass = match ($status) {
                                    'published' => 'legacy-badge--green',
                                    'draft' => 'legacy-badge--gray',
                                    'scheduled' => 'legacy-badge--orange',
                                    default => 'legacy-badge--blue',
                                };
                                ?>
                                <span class="legacy-badge <?= $badgeClass ?>"><?= $h(ucfirst($status)) ?></span>
                            </td>
                            <td>
                                <?php
                                $updatedAt = (string)($article['updated_at'] ?? '');
                                echo $updatedAt !== '' ? $h(date('d/m/Y H:i', strtotime($updatedAt))) : '—';
                                ?>
                            </td>
                            <td>
                                <div class="legacy-actions">
                                <a class="legacy-btn legacy-btn--ghost" href="/admin?module=blog&amp;action=edit&amp;id=<?= (int)($article['id'] ?? 0) ?>">Éditer</a>
                                <a class="legacy-btn legacy-btn--secondary" href="/admin?module=blog&amp;action=delete&amp;id=<?= (int)($article['id'] ?? 0) ?>" onclick="return confirm('Supprimer cet article ?');">Supprimer</a>
                                <?php if (($article['status'] ?? '') !== 'published'): ?>
                                    <a class="legacy-btn legacy-btn--primary" href="/admin?module=blog&amp;action=publish&amp;id=<?= (int)($article['id'] ?? 0) ?>">Publier</a>
                                <?php else: ?>
                                    <span class="legacy-badge legacy-badge--green">Publié</span>
                                <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
        </div>
    </div>

    <?php if ((int)($pagination['total_pages'] ?? 1) > 1): ?>
        <div class="legacy-actions" style="margin-top:16px;">
            <?php for ($i = 1; $i <= (int)$pagination['total_pages']; $i++): ?>
                <a class="legacy-btn legacy-btn--ghost" href="?module=blog&amp;action=list&amp;page=<?= $i ?><?= $filters['status'] !== '' ? '&amp;status=' . urlencode($filters['status']) : '' ?>" <?= $i === (int)$pagination['page'] ? 'aria-current="page"' : '' ?>>
                    <?= $i ?>
                </a>
            <?php endfor; ?>
        </div>
    <?php endif; ?>
</section>
