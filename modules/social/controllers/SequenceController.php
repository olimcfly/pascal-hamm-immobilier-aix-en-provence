<?php

declare(strict_types=1);

final class SequenceController
{
    public function __construct(
        private SequenceRepository $sequenceRepository,
        private PostRepository $postRepository
    ) {
    }

    public function handle(string $action): void
    {
        verifyCsrf();
        $userId = socialUserId();

        if ($action === 'save-sequence') {
            $id = (int) ($_POST['id'] ?? 0);
            $payload = [
                'nom' => trim((string) ($_POST['nom'] ?? 'Nouvelle séquence')),
                'persona' => trim((string) ($_POST['persona'] ?? 'Persona libre')),
                'zone' => trim((string) ($_POST['zone'] ?? setting('zone_city', 'Bordeaux'))),
                'statut' => trim((string) ($_POST['statut'] ?? 'active')),
                'objectif' => trim((string) ($_POST['objectif'] ?? 'N2 -> N3')),
            ];

            if ($id > 0) {
                $this->sequenceRepository->update($id, $userId, $payload);
            } else {
                $this->sequenceRepository->create($userId, $payload);
            }
        }

        if ($action === 'toggle-sequence') {
            $id = (int) ($_POST['id'] ?? 0);
            $this->sequenceRepository->togglePause($id, $userId);
        }

        $redirectSeq = (int) ($_POST['id'] ?? 0);

        if ($action === 'duplicate-sequence') {
            $id = (int) ($_POST['id'] ?? 0);
            $newId = $this->sequenceRepository->duplicate($id, $userId);
            if ($newId > 0) {
                $this->postRepository->duplicateForSequence($id, $newId, $userId);
                $redirectSeq = $newId;
            }
        }

        $url = function_exists('admin_url')
            ? admin_url(['module' => 'social', 'action' => 'sequences'] + ($redirectSeq > 0 ? ['seq' => $redirectSeq] : []))
            : ('/admin/?module=social&action=sequences' . ($redirectSeq > 0 ? '&seq=' . $redirectSeq : ''));
        redirect($url);
    }
}
