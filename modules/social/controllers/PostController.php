<?php

declare(strict_types=1);

final class PostController
{
    public function __construct(
        private PostRepository $postRepository,
        private StrategyService $strategyService
    ) {
    }

    public function handle(string $action): void
    {
        verifyCsrf();
        $userId = socialUserId();

        if ($action === 'save-post') {
            $id = (int) ($_POST['id'] ?? 0);

            $niveauRaw = trim((string) ($_POST['niveau'] ?? ''));
            $niveauVal = in_array($niveauRaw, ['n1','n2','n3','n4','n5'], true) ? $niveauRaw : null;

            $formatRaw = trim((string) ($_POST['image_format'] ?? 'feed'));
            $formatVal = in_array($formatRaw, ['feed', 'story'], true) ? $formatRaw : 'feed';
            $svgRaw    = (string) ($_POST['image_svg'] ?? '');
            $svgClean  = $svgRaw !== '' ? social_sanitize_svg($svgRaw) : null;

            $payload = [
                'sequence_id'    => (int) ($_POST['sequence_id'] ?? 0),
                'titre'          => trim((string) ($_POST['titre'] ?? 'Publication')),
                'contenu'        => trim((string) ($_POST['contenu'] ?? '')),
                'reseaux'        => $_POST['reseaux'] ?? ['facebook'],
                'statut'         => trim((string) ($_POST['statut'] ?? 'brouillon')),
                'planifie_at'    => trim((string) ($_POST['planifie_at'] ?? '')),
                'niveau'         => $niveauVal,
                'ordre_sequence' => (int) ($_POST['ordre_sequence'] ?? 0) ?: null,
                'image_svg'      => $svgClean,
                'image_format'   => $formatVal,
            ];

            if ($payload['contenu'] === '') {
                flash('error', 'Le contenu du post est obligatoire.');
                redirect('/admin?module=social&action=' . ($id > 0 ? 'post-edit' : 'post-form') . ($id > 0 ? '&id=' . $id : ''));
            }

            if ($id > 0) {
                $this->postRepository->update($id, $userId, $payload);
            } else {
                $id = $this->postRepository->create($userId, $payload);
            }

            $this->strategyService->storeSnapshot($id, $payload);
            redirect('/admin?module=social&action=post&id=' . $id);
        }

        if ($action === 'delete-post') {
            $id = (int) ($_POST['id'] ?? 0);
            $this->postRepository->delete($id, $userId);
            redirect('/admin?module=social&action=journal');
        }

        redirect('/admin?module=social&action=sequences-list');
    }
}
