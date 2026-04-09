<?php

declare(strict_types=1);

require_once __DIR__ . '/repositories/ArticleRepository.php';
require_once __DIR__ . '/repositories/CampaignRepository.php';
require_once __DIR__ . '/repositories/PublicationRepository.php';
require_once __DIR__ . '/services/ArticleService.php';
require_once __DIR__ . '/services/CampaignService.php';
require_once __DIR__ . '/services/PublicationService.php';

$pageTitle       = 'Rédaction';
$pageDescription = 'Créez et gérez votre contenu éditorial';

$pdo    = db();
$user   = Auth::user();
$userId = (int)($user['id'] ?? 0);

// Website ID (fallback 1)
$websiteId = (int)($user['website_id'] ?? 1);

// Ensure schema
$schemaSql = file_get_contents(__DIR__ . '/sql/redaction.sql');
if ($schemaSql !== false) {
    foreach (array_filter(array_map('trim', explode(';', $schemaSql))) as $stmt) {
        if ($stmt !== '') {
            try { $pdo->exec($stmt); } catch (PDOException) {}
        }
    }
}

// Repos & services
$articleRepo  = new ArticleRepository($pdo);
$campaignRepo = new CampaignRepository($pdo);
$pubRepo      = new PublicationRepository($pdo);
$articleSvc   = new ArticleService($articleRepo);
$campaignSvc  = new CampaignService($campaignRepo, $articleRepo);
$pubSvc       = new PublicationService($pubRepo, $articleSvc);

$action = $_GET['action'] ?? 'hub';

// ─── API endpoints (JSON) ────────────────────────────────────────────────────

if ($action === 'api_search_articles') {
    header('Content-Type: application/json');
    $q = trim($_GET['q'] ?? '');
    echo json_encode($articleRepo->searchForLink($userId, $q));
    exit;
}

if ($action === 'api_generate_post') {
    header('Content-Type: application/json');
    $reseau  = $_POST['reseau'] ?? 'gmb';
    $article = [
        'titre'   => $_POST['titre']   ?? '',
        'intro'   => $_POST['intro']   ?? '',
        'contenu' => $_POST['contenu'] ?? '',
    ];
    echo json_encode(['contenu' => $articleSvc->generateSocialPost($article, $reseau)]);
    exit;
}

// ─── Mutations (POST) ────────────────────────────────────────────────────────

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'article_save') {
    $data = $_POST;

    // Handle maillage interne (JSON-encoded items)
    $mi = [];
    foreach ($data['maillage_interne'] ?? [] as $raw) {
        $decoded = json_decode($raw, true);
        if ($decoded) {
            $mi[] = $decoded;
        }
    }
    $data['maillage_interne'] = json_encode($mi, JSON_UNESCAPED_UNICODE);

    // Handle maillage externe
    $me = [];
    $anchors = $data['maillage_externe_anchor'] ?? [];
    $urls    = $data['maillage_externe_url']    ?? [];
    foreach ($anchors as $i => $anchor) {
        $url = $urls[$i] ?? '';
        if ($anchor && $url) {
            $me[] = ['anchor' => $anchor, 'url' => $url];
        }
    }
    $data['maillage_externe'] = json_encode($me, JSON_UNESCAPED_UNICODE);
    unset($data['maillage_externe_anchor'], $data['maillage_externe_url']);

    // Strip non-article fields
    $allowed = ['id','website_id','user_id','silo_id','type','titre','slug','seo_title','meta_desc','h1',
                'contenu','intro','conclusion','statut','index_status','persona_id','niveau_conscience',
                'date_publication','mot_cle_principal','mots_cles_lsi','maillage_interne','maillage_externe',
                'keywords_raw'];
    $clean = array_intersect_key($data, array_flip($allowed));
    $clean['user_id']    = $userId;
    $clean['website_id'] = $websiteId;

    // date_publication: empty → null
    if (empty($clean['date_publication'])) {
        unset($clean['date_publication']);
    }
    if ($clean['statut'] === 'publié' && empty($clean['date_publication'])) {
        $clean['date_publication'] = date('Y-m-d H:i:s');
    }

    $id = $articleSvc->save($clean, $userId, $websiteId);

    // Auto-generate social posts
    $reseaux = $data['auto_reseaux'] ?? [];
    if (!empty($reseaux)) {
        $article = $articleRepo->findById($id);
        if ($article) {
            foreach ($reseaux as $reseau) {
                $postContent = $data['post_' . $reseau] ?? $articleSvc->generateSocialPost($article, $reseau);
                $planifieAt  = !empty($data['post_' . $reseau . '_date'])
                    ? date('Y-m-d H:i:s', strtotime($data['post_' . $reseau . '_date']))
                    : null;
                $pubRepo->save([
                    'article_id'  => $id,
                    'user_id'     => $userId,
                    'reseau'      => $reseau,
                    'titre'       => mb_substr($article['titre'] ?? '', 0, 255),
                    'contenu'     => $postContent,
                    'statut'      => $planifieAt ? 'planifié' : 'draft',
                    'planifie_at' => $planifieAt,
                ]);
            }
        }
    }

    $_SESSION['rd_flash'] = ['type' => 'success', 'msg' => 'Article enregistré avec succès.'];
    header('Location: /admin?module=redaction&action=article_edit&id=' . $id);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'campaign_save') {
    $data = $_POST;
    $slots = [];
    foreach ($data['slots'] ?? [] as $s) {
        $slots[] = [
            'role'             => $s['role'] ?? 'conscience',
            'article_id'       => !empty($s['article_id']) ? (int)$s['article_id'] : null,
            'niveau_conscience' => !empty($s['niveau_conscience']) ? (int)$s['niveau_conscience'] : null,
        ];
    }
    $id = $campaignSvc->save([
        'id'          => !empty($data['id']) ? (int)$data['id'] : null,
        'nom'         => $data['nom'] ?? '',
        'description' => $data['description'] ?? null,
        'mot_cle'     => $data['mot_cle'] ?? null,
        'statut'      => $data['statut'] ?? 'draft',
    ], $slots, $userId, $websiteId);

    $_SESSION['rd_flash'] = ['type' => 'success', 'msg' => 'Campagne enregistrée.'];
    header('Location: /admin?module=redaction&action=campaign_edit&id=' . $id);
    exit;
}

// ─── Deletions ───────────────────────────────────────────────────────────────

if ($action === 'article_delete' && !empty($_GET['id'])) {
    $articleRepo->delete((int)$_GET['id']);
    header('Location: /admin?module=redaction&action=pool_articles');
    exit;
}

if ($action === 'campaign_delete' && !empty($_GET['id'])) {
    $campaignRepo->delete((int)$_GET['id']);
    header('Location: /admin?module=redaction&action=campaigns');
    exit;
}

if ($action === 'pub_delete' && !empty($_GET['id'])) {
    $pubRepo->delete((int)$_GET['id']);
    header('Location: /admin?module=redaction&action=pool_gmb');
    exit;
}

// ─── Views data ──────────────────────────────────────────────────────────────

function renderContent(): void
{
    global $action, $articleRepo, $campaignRepo, $pubRepo,
           $articleSvc, $campaignSvc, $pubSvc,
           $userId, $websiteId;

    // Hub
    if ($action === 'hub') {
        $counts        = $articleRepo->countByStatut($userId);
        $pubCounts     = $pubRepo->countByReseau($userId);
        $campaigns     = $campaignRepo->findAll($userId);
        $campaignsCount = count($campaigns);
        $recentArticles = $articleRepo->findAll($userId, []);
        $recentArticles = array_slice($recentArticles, 0, 10);
        require __DIR__ . '/views/hub.php';
        return;
    }

    // Article form
    if ($action === 'article_new' || $action === 'article_edit') {
        $article  = null;
        $keywords = [];
        if ($action === 'article_edit' && !empty($_GET['id'])) {
            $article  = $articleRepo->findById((int)$_GET['id']);
            $keywords = $articleRepo->getKeywords((int)$_GET['id']);
        }
        // Pre-fill type/niveau from query string for campaign linking
        if (!$article) {
            $article = [
                'type'             => $_GET['type'] ?? 'satellite',
                'niveau_conscience' => !empty($_GET['niveau_conscience']) ? (int)$_GET['niveau_conscience'] : null,
            ];
        }
        $allArticles = $articleRepo->findAll($userId, []);
        require __DIR__ . '/views/article_form.php';
        return;
    }

    // Pool articles
    if ($action === 'pool_articles') {
        $filters  = [
            'statut' => $_GET['statut'] ?? '',
            'type'   => $_GET['type']   ?? '',
            'q'      => $_GET['q']      ?? '',
        ];
        $articles = $articleRepo->findAll($userId, $filters);
        $counts   = $articleRepo->countByStatut($userId);
        require __DIR__ . '/views/pool_articles.php';
        return;
    }

    // Pool GMB / publications
    if ($action === 'pool_gmb') {
        $filters      = [
            'reseau' => $_GET['reseau'] ?? '',
            'statut' => $_GET['statut'] ?? '',
        ];
        $publications = $pubSvc->findAll($userId, $filters);
        $pubCounts    = $pubRepo->countByReseau($userId);
        require __DIR__ . '/views/pool_gmb.php';
        return;
    }

    // Journal
    if ($action === 'journal') {
        $journal = $pubSvc->getJournal($userId);
        require __DIR__ . '/views/journal.php';
        return;
    }

    // Campaign list
    if ($action === 'campaigns') {
        $campaigns = $campaignRepo->findAll($userId);
        require __DIR__ . '/views/campaigns_list.php';
        return;
    }

    // Campaign form
    if ($action === 'campaign_new' || $action === 'campaign_edit') {
        $campaign    = null;
        $slots       = [];
        if ($action === 'campaign_edit' && !empty($_GET['id'])) {
            $full     = $campaignSvc->getWithArticles((int)$_GET['id']);
            $campaign = $full;
            $slots    = $full ? $campaignSvc->buildSlots($full) : [];
        }
        if (!$campaign) {
            $campaign = [];
            $mockFull = ['articles' => []];
            $slots    = $campaignSvc->buildSlots($mockFull);
        }
        $allArticles = $articleRepo->findAll($userId, []);
        require __DIR__ . '/views/campaign_form.php';
        return;
    }

    // Default → hub
    $counts        = $articleRepo->countByStatut($userId);
    $pubCounts     = $pubRepo->countByReseau($userId);
    $campaigns     = $campaignRepo->findAll($userId);
    $campaignsCount = count($campaigns);
    $recentArticles = array_slice($articleRepo->findAll($userId, []), 0, 10);
    require __DIR__ . '/views/hub.php';
}
