<?php

declare(strict_types=1);

require_once __DIR__ . '/repositories/OnboardingRepository.php';
require_once __DIR__ . '/services/BlueprintService.php';
require_once __DIR__ . '/services/OnboardingService.php';
require_once __DIR__ . '/controllers/OnboardingController.php';

$pageTitle = 'Onboarding';
$pageDescription = 'Activez votre système immobilier en 5 étapes guidées';

$onboardingController = new OnboardingController(
    new OnboardingService(
        new OnboardingRepository(),
        new BlueprintService()
    )
);

$onboardingViewData = $onboardingController->handle();

// Charge les modules stratégiques pour l'écran post-onboarding
$_stratModules = require __DIR__ . '/../../admin/data/modules.php';
$_stratKeys    = ['construire', 'attirer', 'capturer', 'convertir', 'optimiser'];
$onboardingViewData['stratModules'] = array_map(
    static fn(string $k) => $_stratModules[$k] ?? [],
    array_combine($_stratKeys, $_stratKeys)
);

function renderContent(): void
{
    global $onboardingViewData;

    $viewData = $onboardingViewData;
    require __DIR__ . '/views/wizard.php';
}
