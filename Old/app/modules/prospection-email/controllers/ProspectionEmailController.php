<?php

declare(strict_types=1);

require_once __DIR__ . '/../models/ProspectContactRepository.php';
require_once __DIR__ . '/../models/ProspectCampaignRepository.php';
require_once __DIR__ . '/../models/ProspectSequenceRepository.php';
require_once __DIR__ . '/../services/ContactIngestionService.php';
require_once __DIR__ . '/../services/ContactValidationService.php';
require_once __DIR__ . '/../services/CampaignService.php';
require_once __DIR__ . '/../services/SequenceEngineService.php';
require_once __DIR__ . '/../services/ConversationService.php';

class ProspectionEmailController
{
    private ContactIngestionService $ingestionService;
    private ContactValidationService $validationService;
    private CampaignService $campaignService;
    private SequenceEngineService $sequenceService;
    private ConversationService $conversationService;

    public function __construct(private PDO $db)
    {
        $contactRepo = new ProspectContactRepository($db);

        $this->ingestionService = new ContactIngestionService($contactRepo);
        $this->validationService = new ContactValidationService($contactRepo, $db);
        $this->campaignService = new CampaignService(new ProspectCampaignRepository($db), $contactRepo);
        $this->sequenceService = new SequenceEngineService(new ProspectSequenceRepository($db));
        $this->conversationService = new ConversationService($db);
    }

    public function index(): void
    {
        $pageTitle = 'Prospection Email B2B';
        require __DIR__ . '/../views/index.php';
    }

    public function createCampaign(array $payload): array
    {
        return $this->campaignService->createCampaign($payload);
    }

    public function createSequence(int $campaignId, string $name, array $steps): array
    {
        return $this->sequenceService->createSequence($campaignId, $name, $steps);
    }

    public function registerInboundReply(array $payload): void
    {
        $this->conversationService->registerInboundReply($payload);
    }

    public function validationWorkflow(int $contactId, string $newStatus, string $action, ?string $notes = null): bool
    {
        return $this->validationService->validateContact($contactId, $newStatus, $action, $notes);
    }

    public function getIngestionService(): ContactIngestionService
    {
        return $this->ingestionService;
    }
}
