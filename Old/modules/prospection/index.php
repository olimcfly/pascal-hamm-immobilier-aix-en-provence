<?php

declare(strict_types=1);

// Autoload des dépendances du module
require_once MODULES_PATH . '/prospection/repositories/ProspectRepository.php';
require_once MODULES_PATH . '/prospection/repositories/CampaignRepository.php';
require_once MODULES_PATH . '/prospection/repositories/SequenceRepository.php';
require_once MODULES_PATH . '/prospection/services/ProspectionMailer.php';
require_once MODULES_PATH . '/prospection/services/ProspectService.php';
require_once MODULES_PATH . '/prospection/services/CampaignService.php';
require_once MODULES_PATH . '/prospection/services/SequenceService.php';
require_once MODULES_PATH . '/prospection/services/ProspectionSeeder.php';

// -----------------------------------------------------------------------
// Traitement POST — avant tout rendu HTML (pattern identique à convertir/index.php)
// -----------------------------------------------------------------------
prospection_handle_post();

// -----------------------------------------------------------------------
// Dispatch AJAX avant tout rendu HTML
// -----------------------------------------------------------------------
if (isset($_GET['ajax'])) {
    $ajaxAction = preg_replace('/[^a-z0-9_-]/', '', (string)($_GET['ajax'] ?? ''));
    $ajaxPath   = MODULES_PATH . "/prospection/ajax/{$ajaxAction}.php";
    if (is_file($ajaxPath)) {
        require $ajaxPath;
        exit;
    }
    http_response_code(404);
    header('Content-Type: application/json');
    echo json_encode(['ok' => false, 'error' => 'Action inconnue.']);
    exit;
}

// -----------------------------------------------------------------------
// Instanciation des services (partagés dans le module)
// -----------------------------------------------------------------------
function prospection_make_services(): array
{
    $db     = \Database::getInstance();
    $userId = (int) (Auth::user()['id'] ?? 0);

    $prospectRepo  = new ProspectRepository($db);
    $campaignRepo  = new CampaignRepository($db);
    $seqRepo       = new SequenceRepository($db);

    return [
        'prospect' => new ProspectService($prospectRepo, $userId),
        'campaign' => new CampaignService($campaignRepo, $seqRepo, $userId),
        'sequence' => new SequenceService($seqRepo, $campaignRepo, $userId),
        'userId'   => $userId,
    ];
}

// -----------------------------------------------------------------------
// Point d'entrée principal — appelé par admin/index.php
// -----------------------------------------------------------------------
function renderContent(): void
{
    $action     = $_GET['action']     ?? 'dashboard';
    $action     = preg_replace('/[^a-z0-9_-]/', '', strtolower($action));
    $campaignId = isset($_GET['campaign_id']) ? (int) $_GET['campaign_id'] : 0;
    $contactId  = isset($_GET['contact_id'])  ? (int) $_GET['contact_id']  : 0;

    $svc = prospection_make_services();

    echo match ($action) {
        'contacts'        => prospection_render('contacts/list',      $svc),
        'contact-new'     => prospection_render('contacts/form',      $svc, ['contact' => null]),
        'contact-edit'    => prospection_render('contacts/form',      $svc, ['contact' => $svc['prospect']->getById($contactId)]),
        'contact-import'  => prospection_render('contacts/import',    $svc),
        'campaigns'       => prospection_render('campaigns/list',     $svc),
        'campaign-new'    => prospection_render('campaigns/form',     $svc, ['campaign' => null]),
        'campaign-edit'   => prospection_render('campaigns/form',     $svc, ['campaign' => $svc['campaign']->getById($campaignId)]),
        'campaign-detail' => prospection_render('campaigns/detail',   $svc, ['campaign' => $svc['campaign']->getDetailData($campaignId)]),
        'sequence'        => prospection_render('sequences/editor',   $svc, ['campaign_id' => $campaignId, 'steps' => $svc['sequence']->getSteps($campaignId)]),
        'activity'        => prospection_render('activity/log',       $svc, ['activity' => $svc['sequence']->getRecentActivity(100)]),
        default           => prospection_render('dashboard',          $svc),
    };
}

// -----------------------------------------------------------------------
// Rendu d'une vue partielle
// -----------------------------------------------------------------------
function prospection_render(string $view, array $svc, array $extra = []): string
{
    $viewPath = MODULES_PATH . "/prospection/views/{$view}.php";
    if (!is_file($viewPath)) {
        return '<div class="alert alert-danger">Vue introuvable : ' . e($view) . '</div>';
    }

    // Variables disponibles dans les vues
    $prospectService  = $svc['prospect'];
    $campaignService  = $svc['campaign'];
    $sequenceService  = $svc['sequence'];
    $currentUserId    = $svc['userId'];

    extract($extra);

    ob_start();
    require $viewPath;
    return ob_get_clean();
}

// -----------------------------------------------------------------------
// Traitement des formulaires POST
// -----------------------------------------------------------------------
function prospection_handle_post(): void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        return;
    }

    verifyCsrf($_POST['csrf_token'] ?? '');

    $svc    = prospection_make_services();
    $action = $_POST['action'] ?? '';

    switch ($action) {

        // ---- Contacts ----
        case 'contact_save':
            $id    = (int) ($_POST['id'] ?? 0);
            $input = array_intersect_key($_POST, array_flip(['first_name','last_name','email','phone','company','city','source','notes','status','tags']));

            if ($id > 0) {
                $result = $svc['prospect']->update($id, $input);
                if ($result['ok']) {
                    Session::flash('success', 'Contact mis à jour.');
                    redirect('/admin?module=prospection&action=contacts');
                } else {
                    Session::flash('error', implode(' ', $result['errors']));
                    redirect('/admin?module=prospection&action=contact-edit&contact_id=' . $id);
                }
            } else {
                $result = $svc['prospect']->create($input);
                if ($result['ok']) {
                    Session::flash('success', 'Contact ajouté.');
                    redirect('/admin?module=prospection&action=contacts');
                } else {
                    Session::flash('error', implode(' ', $result['errors']));
                    redirect('/admin?module=prospection&action=contact-new');
                }
            }
            break;

        case 'contact_delete':
            $id = (int) ($_POST['id'] ?? 0);
            $svc['prospect']->delete($id);
            Session::flash('success', 'Contact supprimé.');
            redirect('/admin?module=prospection&action=contacts');
            break;

        case 'contact_import_csv':
            $file = $_FILES['csv_file'] ?? null;
            if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
                Session::flash('error', 'Erreur lors de l\'upload du fichier.');
                redirect('/admin?module=prospection&action=contact-import');
                break;
            }

            // Validation type
            $mime = mime_content_type($file['tmp_name']);
            $allowedMimes = ['text/plain','text/csv','application/csv','application/vnd.ms-excel'];
            if (!in_array($mime, $allowedMimes, true) && !str_ends_with(strtolower($file['name']), '.csv')) {
                Session::flash('error', 'Seuls les fichiers CSV sont acceptés.');
                redirect('/admin?module=prospection&action=contact-import');
                break;
            }

            $result = $svc['prospect']->importCsv($file['tmp_name'], 'csv');
            $msg    = "Import terminé : {$result['imported']} contact(s) importé(s), {$result['skipped']} ignoré(s).";
            if (!empty($result['errors'])) {
                $msg .= ' Problèmes : ' . implode(' | ', array_slice($result['errors'], 0, 3));
            }

            Session::flash('success', $msg);
            redirect('/admin?module=prospection&action=contacts');
            break;

        // ---- Campagnes ----
        case 'campaign_save':
            $id    = (int) ($_POST['id'] ?? 0);
            $input = array_intersect_key($_POST, array_flip(['name','description','objective','status']));

            if ($id > 0) {
                $result = $svc['campaign']->update($id, $input);
                if ($result['ok']) {
                    Session::flash('success', 'Campagne mise à jour.');
                    redirect('/admin?module=prospection&action=campaign-detail&campaign_id=' . $id);
                } else {
                    Session::flash('error', implode(' ', $result['errors']));
                    redirect('/admin?module=prospection&action=campaign-edit&campaign_id=' . $id);
                }
            } else {
                $result = $svc['campaign']->create($input);
                if ($result['ok']) {
                    // Si demandé, créer la séquence démo
                    if (!empty($_POST['seed_demo'])) {
                        $svc['sequence']->seedDemoSequence($result['id']);
                    }
                    Session::flash('success', 'Campagne créée.');
                    redirect('/admin?module=prospection&action=campaign-detail&campaign_id=' . $result['id']);
                } else {
                    Session::flash('error', implode(' ', $result['errors']));
                    redirect('/admin?module=prospection&action=campaign-new');
                }
            }
            break;

        case 'campaign_delete':
            $id = (int) ($_POST['id'] ?? 0);
            $svc['campaign']->delete($id);
            Session::flash('success', 'Campagne supprimée.');
            redirect('/admin?module=prospection&action=campaigns');
            break;

        case 'campaign_enroll':
            $campaignId = (int) ($_POST['campaign_id'] ?? 0);
            $contactIds = $_POST['contact_ids'] ?? [];
            if (!is_array($contactIds)) {
                $contactIds = array_filter(array_map('intval', explode(',', (string) $contactIds)));
            }
            $result = $svc['campaign']->enrollContacts($campaignId, $contactIds);
            Session::flash('success', $result['enrolled'] . ' contact(s) ajouté(s) à la campagne.');
            redirect('/admin?module=prospection&action=campaign-detail&campaign_id=' . $campaignId);
            break;

        case 'campaign_unenroll':
            $campaignId = (int) ($_POST['campaign_id'] ?? 0);
            $contactId  = (int) ($_POST['contact_id']  ?? 0);
            $svc['campaign']->unenrollContact($campaignId, $contactId);
            redirect('/admin?module=prospection&action=campaign-detail&campaign_id=' . $campaignId);
            break;

        case 'campaign_mark_replied':
            $campaignId = (int) ($_POST['campaign_id'] ?? 0);
            $contactId  = (int) ($_POST['contact_id']  ?? 0);
            $svc['campaign']->markReplied($campaignId, $contactId);
            Session::flash('success', 'Contact marqué comme ayant répondu. Séquence arrêtée.');
            redirect('/admin?module=prospection&action=campaign-detail&campaign_id=' . $campaignId);
            break;

        // ---- Séquences ----
        case 'step_save':
            $campaignId = (int) ($_POST['campaign_id'] ?? 0);
            $stepId     = (int) ($_POST['step_id']     ?? 0);
            $input      = array_intersect_key($_POST, array_flip(['subject','body_text','delay_days','is_active']));

            if ($stepId > 0) {
                $result = $svc['sequence']->updateStep($campaignId, $stepId, $input);
            } else {
                $result = $svc['sequence']->addStep($campaignId, $input);
            }

            if ($result['ok']) {
                Session::flash('success', 'Étape sauvegardée.');
            } else {
                Session::flash('error', implode(' ', $result['errors']));
            }
            redirect('/admin?module=prospection&action=sequence&campaign_id=' . $campaignId);
            break;

        case 'step_delete':
            $campaignId = (int) ($_POST['campaign_id'] ?? 0);
            $stepId     = (int) ($_POST['step_id']     ?? 0);
            $svc['sequence']->deleteStep($campaignId, $stepId);
            Session::flash('success', 'Étape supprimée.');
            redirect('/admin?module=prospection&action=sequence&campaign_id=' . $campaignId);
            break;

        // ---- Démo / Seeder ----
        case 'run_seeder':
            $seeder = new ProspectionSeeder(\Database::getInstance(), $svc['userId']);
            $result = $seeder->run();
            if ($result['already_existed']) {
                Session::flash('success', 'La campagne de démo existe déjà. Ouvrez-la pour lancer la simulation.');
                redirect('/admin?module=prospection&action=campaign-detail&campaign_id=' . $result['campaign_id']);
            } else {
                Session::flash('success',
                    "Démo créée : {$result['contacts_created']} contacts tests inscrits. Lancez la simulation depuis le détail de la campagne."
                );
                redirect('/admin?module=prospection&action=campaign-detail&campaign_id=' . $result['campaign_id']);
            }
            break;

        // ---- Simulation manuelle ----
        case 'run_simulation':
            $campaignId = (int)($_POST['campaign_id'] ?? 0);
            $result     = $svc['sequence']->simulateFullCycle($campaignId);
            Session::flash('success',
                "Simulation terminée : {$result['total']} contact(s) simulé(s). Les statuts ont été mis à jour."
            );
            redirect('/admin?module=prospection&action=campaign-detail&campaign_id=' . $campaignId);
            break;
    }
}
