<?php

declare(strict_types=1);

final class SocialController
{
    public function __construct(
        private SequenceRepository $sequenceRepository,
        private PostRepository $postRepository,
        private StrategyService $strategyService
    ) {
    }

    public function sequencesList(): void
    {
        $userId = socialUserId();
        $filters = [
            'persona' => (string) ($_GET['persona'] ?? 'all'),
            'status'  => (string) ($_GET['status']  ?? 'all'),
        ];

        $sequences      = $this->sequenceRepository->findAllByUser($userId, $filters);
        $postBySequence = $this->postRepository->groupedBySequence($userId);

        $GLOBALS['social_use_commencer_nav'] = true;

        include __DIR__ . '/../views/layout/_header.php';
        include __DIR__ . '/../views/sequences/list.php';
    }

    public function sequences(): void
    {
        $userId  = socialUserId();
        $filters = [
            'persona' => (string) ($_GET['persona'] ?? 'all'),
            'status'  => (string) ($_GET['status']  ?? 'all'),
        ];

        $sequences      = $this->sequenceRepository->findAllByUser($userId, $filters);
        $postBySequence = $this->postRepository->groupedBySequence($userId);

        $selectedSeqId = (int) ($_GET['seq'] ?? 0);
        $sequenceIds   = array_map(static fn (array $s): int => (int) ($s['id'] ?? 0), $sequences);
        if ($selectedSeqId > 0 && ! in_array($selectedSeqId, $sequenceIds, true)) {
            $selectedSeqId = 0;
        }
        if ($selectedSeqId === 0 && $sequences !== []) {
            $selectedSeqId = (int) ($sequences[0]['id'] ?? 0);
        }

        $selectedSequence = null;
        foreach ($sequences as $s) {
            if ((int) ($s['id'] ?? 0) === $selectedSeqId) {
                $selectedSequence = $s;
                break;
            }
        }

        $selectedPosts = $postBySequence[$selectedSeqId] ?? [];

        $GLOBALS['social_use_commencer_nav'] = true;

        include __DIR__ . '/../views/layout/_header.php';
        include __DIR__ . '/../views/sequences/index.php';
    }

    public function journal(): void
    {
        $userId     = socialUserId();
        $weekOffset = (int) ($_GET['week'] ?? 0);

        $posts = $this->postRepository->findChronological($userId);
        $stats = $this->postRepository->getStats($userId);
        $weekData = $this->postRepository->getWeekData($userId, $weekOffset);

        // Grouper les posts par date (décroissant)
        $postsByDate = [];
        foreach ($posts as $post) {
            $ref     = $post['planifie_at'] ?? $post['publie_at'] ?? $post['created_at'] ?? '';
            $dateKey = $ref ? date('Y-m-d', strtotime($ref)) : date('Y-m-d');
            $postsByDate[$dateKey][] = $post;
        }
        krsort($postsByDate);

        $GLOBALS['social_use_commencer_nav'] = true;

        include __DIR__ . '/../views/layout/_header.php';
        include __DIR__ . '/../views/journal/index.php';
    }

    public function postDetail(int $postId): void
    {
        $userId = socialUserId();
        $post   = $this->postRepository->findById($postId, $userId);

        $GLOBALS['social_use_commencer_nav'] = true;

        include __DIR__ . '/../views/layout/_header.php';
        echo '<div class="social-post-shell">';
        include __DIR__ . '/../views/post/detail.php';
        echo '</div></div>';
    }

    public function postForm(int $postId = 0): void
    {
        $userId    = socialUserId();
        $post      = $postId > 0 ? $this->postRepository->findById($postId, $userId) : null;
        $sequences = $this->sequenceRepository->findAllByUser($userId, ['persona' => 'all', 'status' => 'all']);

        $GLOBALS['social_use_commencer_nav'] = true;

        include __DIR__ . '/../views/layout/_header.php';
        echo '<div class="social-post-shell">';
        include __DIR__ . '/../views/post/form.php';
        echo '</div></div>';
    }
}
