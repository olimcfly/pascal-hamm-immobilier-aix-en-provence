<?php

declare(strict_types=1);

final class OnboardingController
{
    private const STEP_LABELS = [
        1 => 'Qui tu es',
        2 => 'Pour qui tu travailles',
        3 => 'Ton offre',
        4 => 'Ta zone',
        5 => 'Ton objectif prioritaire',
        6 => 'Récapitulatif',
    ];

    private const STEP_KEYS = [
        1 => 'identity',
        2 => 'target',
        3 => 'offer',
        4 => 'territory',
        5 => 'goal',
    ];

    public function __construct(private OnboardingService $service)
    {
    }

    public function handle(): array
    {
        $user = Auth::user();
        if ($user === null) {
            throw new RuntimeException('Utilisateur non authentifié.');
        }

        $session = $this->service->getOrCreateSession((int) $user['id']);
        $sessionId = (int) ($session['id'] ?? 0);
        if ($sessionId <= 0) {
            throw new RuntimeException('Impossible de démarrer la session onboarding.');
        }

        $bundle = $this->service->getSessionBundle($sessionId);
        $session = $bundle['session'];
        $answersByStep = $bundle['answers'];
        $blueprint = $bundle['blueprint'];

        $currentStep = $this->resolveCurrentStep((int) ($session['current_step'] ?? 1));
        $requestedStep = $this->resolveCurrentStep((int) ($_GET['step'] ?? $currentStep));
        $step = $requestedStep;

        $errors = [];

        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
            verifyCsrf();
            $intent = (string) ($_POST['intent'] ?? 'save_step');

            if ($intent === 'complete') {
                $this->service->completeSession($sessionId);
                Session::flash('success', 'Onboarding finalisé. Votre blueprint est prêt pour le Sprint 2.');
                redirect('/admin?module=onboarding&step=6');
            }

            $postedStep = $this->resolveCurrentStep((int) ($_POST['step'] ?? $step));
            $stepKey = self::STEP_KEYS[$postedStep] ?? null;

            if ($stepKey !== null) {
                $payload = $this->extractStepPayload($postedStep, $_POST);
                $errors = $this->validateStep($postedStep, $payload);

                if ($errors === []) {
                    $navigation = (string) ($_POST['navigation'] ?? 'next');
                    $nextStep = $this->nextStep($postedStep, $navigation);
                    $this->service->saveStep($sessionId, $stepKey, $payload, min(5, max(1, $nextStep)));

                    if ($navigation === 'save') {
                        Session::flash('success', 'Étape enregistrée. Vous pourrez reprendre plus tard.');
                        redirect('/admin?module=onboarding&step=' . $postedStep);
                    }

                    if ($navigation === 'prev') {
                        redirect('/admin?module=onboarding&step=' . max(1, $postedStep - 1));
                    }

                    if ($postedStep >= 5) {
                        redirect('/admin?module=onboarding&step=6');
                    }

                    redirect('/admin?module=onboarding&step=' . ($postedStep + 1));
                }

                $answersByStep[$stepKey] = $payload;
                $blueprint = (new BlueprintService())->compile($answersByStep);
                $step = $postedStep;
            }
        }

        $inProgress = ((string) ($session['status'] ?? 'draft')) !== 'completed';
        $canResume = $inProgress && ((int) ($session['current_step'] ?? 1) > 1);

        return [
            'step' => $step,
            'labels' => self::STEP_LABELS,
            'stepKeys' => self::STEP_KEYS,
            'session' => $session,
            'answers' => $answersByStep,
            'errors' => $errors,
            'blueprint' => $blueprint,
            'canResume' => $canResume,
            'resumeStep' => (int) ($session['current_step'] ?? 1),
        ];
    }

    private function resolveCurrentStep(int $step): int
    {
        if ($step < 1) {
            return 1;
        }

        if ($step > 6) {
            return 6;
        }

        return $step;
    }

    private function nextStep(int $currentStep, string $navigation): int
    {
        if ($navigation === 'prev') {
            return $currentStep - 1;
        }

        if ($navigation === 'save') {
            return $currentStep;
        }

        return $currentStep + 1;
    }

    private function extractStepPayload(int $step, array $input): array
    {
        if ($step === 1) {
            return [
                'name' => trim((string) ($input['name'] ?? '')),
                'brand' => trim((string) ($input['brand'] ?? '')),
                'role' => trim((string) ($input['role'] ?? '')),
                'tone' => trim((string) ($input['tone'] ?? '')),
            ];
        }

        if ($step === 2) {
            return [
                'persona' => trim((string) ($input['persona'] ?? '')),
                'pain' => trim((string) ($input['pain'] ?? '')),
                'desire' => trim((string) ($input['desire'] ?? '')),
            ];
        }

        if ($step === 3) {
            return [
                'type' => trim((string) ($input['type'] ?? '')),
                'promise' => trim((string) ($input['promise'] ?? '')),
                'differentiator' => trim((string) ($input['differentiator'] ?? '')),
                'timeline' => trim((string) ($input['timeline'] ?? '')),
            ];
        }

        if ($step === 4) {
            return [
                'city' => trim((string) ($input['city'] ?? '')),
                'districts' => trim((string) ($input['districts'] ?? '')),
                'radius_km' => (int) ($input['radius_km'] ?? 0),
                'market_type' => trim((string) ($input['market_type'] ?? '')),
            ];
        }

        if ($step === 5) {
            return [
                'primary_goal' => trim((string) ($input['primary_goal'] ?? '')),
                'primary_channel' => trim((string) ($input['primary_channel'] ?? '')),
                'budget_monthly' => trim((string) ($input['budget_monthly'] ?? '')),
                'outputs' => array_values(array_filter(array_map('strval', (array) ($input['outputs'] ?? [])))),
            ];
        }

        return [];
    }

    private function validateStep(int $step, array $payload): array
    {
        $errors = [];

        if ($step === 1) {
            if ($payload['name'] === '') {
                $errors[] = 'Le nom professionnel est requis.';
            }
            if ($payload['role'] === '') {
                $errors[] = 'Le rôle métier est requis.';
            }
        }

        if ($step === 2) {
            if ($payload['persona'] === '') {
                $errors[] = 'Le persona principal est requis.';
            }
            if ($payload['pain'] === '') {
                $errors[] = 'Le problème principal est requis.';
            }
        }

        if ($step === 3) {
            if ($payload['type'] === '' || $payload['promise'] === '') {
                $errors[] = 'Le type d\'offre et la promesse sont requis.';
            }
        }

        if ($step === 4 && $payload['city'] === '') {
            $errors[] = 'La ville principale est requise.';
        }

        if ($step === 5) {
            if ($payload['primary_goal'] === '') {
                $errors[] = 'L\'objectif prioritaire est requis.';
            }
            if ($payload['primary_channel'] === '') {
                $errors[] = 'Le canal d\'acquisition prioritaire est requis.';
            }
        }

        return $errors;
    }
}
