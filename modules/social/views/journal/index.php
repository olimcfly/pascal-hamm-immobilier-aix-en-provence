<?php
$posts       = $posts ?? [];
$stats       = $stats ?? ['planifie' => 0, 'publie' => 0, 'brouillon' => 0, 'erreur' => 0];
$weekData    = $weekData ?? ['days' => [], 'monthLabel' => '', 'weekOffset' => 0];
$postsByDate = $postsByDate ?? [];

$weekOffset  = (int) ($weekData['weekOffset'] ?? 0);
$monthLabel  = (string) ($weekData['monthLabel'] ?? '');
$days        = $weekData['days'] ?? [];

$netColors = [
    'facebook'           => '#1877F2',
    'instagram'          => '#E1306C',
    'linkedin'           => '#0A66C2',
    'google_my_business' => '#34A853',
];

$totalPosts = count($posts);
$netCounts  = [];
foreach ($posts as $p) {
    foreach (json_decode((string) ($p['reseaux'] ?? '[]'), true) ?: [] as $r) {
        $netCounts[$r] = ($netCounts[$r] ?? 0) + 1;
    }
}

$today = date('Y-m-d');

$__admin = static function (array $q): string {
    return function_exists('admin_url') ? admin_url($q) : ('/admin/?' . http_build_query($q, '', '&', PHP_QUERY_RFC3986));
};

$__jourLink = static function (array $extra = []) use ($__admin, $weekOffset): string {
    $p = array_merge(['module' => 'social', 'action' => 'journal'], $extra);
    if ($weekOffset !== 0 && ! isset($p['week'])) {
        $p['week'] = $weekOffset;
    }

    return $__admin($p);
};
?>

<style>
.jour-v2-page { display:grid; gap:20px; padding-bottom:28px; max-width:1200px; margin:0 auto; }

.jour-v2-hero {
    background:linear-gradient(135deg,#0f2237 0%,#1a3a5c 100%);
    border-radius:16px;
    padding:28px 26px;
    color:#fff;
    box-shadow:0 4px 20px rgba(15,34,55,.18);
}
.jour-v2-hero-badge {
    display:inline-block;
    background:rgba(201,168,76,.2);
    color:#c9a84c;
    font-size:11px;
    font-weight:700;
    letter-spacing:.08em;
    text-transform:uppercase;
    padding:4px 12px;
    border-radius:20px;
    margin-bottom:12px;
    border:1px solid rgba(201,168,76,.35);
}
.jour-v2-hero h1 {
    margin:0 0 10px;
    font-size:clamp(22px,3vw,28px);
    font-weight:700;
    color:#fff;
    line-height:1.25;
}
.jour-v2-hero p {
    margin:0;
    font-size:15px;
    color:rgba(255,255,255,.72);
    line-height:1.65;
    max-width:640px;
}

.jour-v2-stats {
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(140px,1fr));
    gap:12px;
}
.jour-v2-stat {
    background:#fff;
    border-radius:12px;
    padding:16px 18px;
    border:1px solid #e5e7eb;
    box-shadow:0 1px 6px rgba(0,0,0,.06);
}
.jour-v2-stat strong {
    display:block;
    font-size:1.35rem;
    font-weight:800;
    color:#0f172a;
}
.jour-v2-stat small {
    font-size:.82rem;
    color:#64748b;
}

.jour-v2-net-tabs {
    display:flex;
    flex-wrap:wrap;
    gap:8px;
    align-items:center;
}
.jour-v2-net-tabs a {
    display:inline-flex;
    align-items:center;
    gap:8px;
    font-size:.82rem;
    font-weight:600;
    padding:8px 14px;
    border-radius:999px;
    text-decoration:none;
    background:#fff;
    border:1px solid #e2e8f0;
    color:#475569;
}
.jour-v2-net-tabs a.is-active {
    background:#1a3c5e;
    border-color:#1a3c5e;
    color:#fff;
}
.jour-v2-net-dot {
    width:8px;
    height:8px;
    border-radius:50%;
}

.jour-v2-week {
    background:#fff;
    border:1px solid #e5e7eb;
    border-radius:14px;
    padding:16px 18px;
    box-shadow:0 1px 6px rgba(0,0,0,.06);
}
.jour-v2-week-head {
    display:flex;
    align-items:center;
    justify-content:space-between;
    margin-bottom:14px;
}
.jour-v2-week-head span {
    font-weight:700;
    color:#1e293b;
}
.jour-v2-week-arrows {
    display:flex;
    gap:6px;
}
.jour-v2-week-arrows a {
    width:36px;
    height:36px;
    display:flex;
    align-items:center;
    justify-content:center;
    border-radius:8px;
    background:#f1f5f9;
    color:#1a3c5e;
    text-decoration:none;
}
.jour-v2-week-arrows a:hover { background:#e2e8f0; }

.jour-v2-days {
    display:grid;
    grid-template-columns:repeat(7,1fr);
    gap:8px;
}
@media (max-width:700px) {
    .jour-v2-days { grid-template-columns:repeat(4,1fr); }
}
.jour-v2-day {
    text-align:center;
    padding:10px 6px;
    border-radius:10px;
    background:#f8fafc;
    border:1px solid #f1f5f9;
}
.jour-v2-day .jn { font-size:.72rem; color:#64748b; text-transform:uppercase; }
.jour-v2-day .jd {
    font-size:1.1rem;
    font-weight:800;
    color:#0f172a;
    margin:4px 0;
}
.jour-v2-day .jd.is-today {
    background:#c9a84c;
    color:#10253c;
    border-radius:8px;
    padding:4px 8px;
    display:inline-block;
}
.jour-v2-dots { display:flex; justify-content:center; gap:3px; flex-wrap:wrap; margin-top:6px; }
.jour-v2-dots span {
    width:6px;
    height:6px;
    border-radius:50%;
}

.jour-v2-empty {
    background:#fff;
    border:1px dashed #cbd5e1;
    border-radius:14px;
    padding:40px 24px;
    text-align:center;
    color:#64748b;
}
.jour-v2-empty h3 { margin:0 0 10px; color:#334155; font-size:1.1rem; }
.jour-v2-btn-gold {
    display:inline-flex;
    align-items:center;
    gap:8px;
    margin-top:16px;
    padding:10px 18px;
    border-radius:10px;
    background:#c9a84c;
    color:#10253c;
    font-weight:700;
    text-decoration:none;
}
.jour-v2-btn-gold:hover { background:#b8962d; }

.jour-v2-date-sep {
    display:flex;
    align-items:center;
    gap:12px;
    margin:22px 0 14px;
}
.jour-v2-date-sep .line { flex:1; height:1px; background:#e2e8f0; }
.jour-v2-date-sep .lbl {
    font-size:.78rem;
    font-weight:700;
    text-transform:uppercase;
    letter-spacing:.06em;
    color:#94a3b8;
    white-space:nowrap;
}

.social-fab.jour-v2-fab {
    position:fixed;
    bottom:28px;
    right:28px;
    width:52px;
    height:52px;
    border-radius:50%;
    background:#c9a84c;
    color:#10253c;
    display:flex;
    align-items:center;
    justify-content:center;
    font-size:1.25rem;
    box-shadow:0 8px 24px rgba(15,34,55,.2);
    text-decoration:none;
    z-index:40;
}
.social-fab.jour-v2-fab:hover { background:#b8962d; color:#10253c; }
</style>

<div class="jour-v2-page">

    <header class="jour-v2-hero">
        <span class="jour-v2-hero-badge"><i class="fas fa-newspaper"></i> Journal social</span>
        <h1>Vos publications et leur statut</h1>
        <p>Vue chronologique des posts planifiés et publiés — même esprit visuel que la page Commencer : lisible, une seule colonne d’actions.</p>
    </header>

    <div class="jour-v2-stats">
        <div class="jour-v2-stat"><strong><?= (int) $stats['planifie'] ?></strong><small>Planifiées</small></div>
        <div class="jour-v2-stat"><strong style="color:#16a34a"><?= (int) $stats['publie'] ?></strong><small>Publiées</small></div>
        <div class="jour-v2-stat"><strong style="color:#d97706"><?= (int) $stats['brouillon'] ?></strong><small>Brouillons</small></div>
        <div class="jour-v2-stat"><strong style="color:<?= (int) $stats['erreur'] > 0 ? '#dc2626' : '#64748b' ?>"><?= (int) $stats['erreur'] ?></strong><small>Échecs</small></div>
    </div>

    <div class="jour-v2-net-tabs">
        <a href="<?= htmlspecialchars($__jourLink([]), ENT_QUOTES, 'UTF-8') ?>" class="<?= empty($_GET['network']) ? 'is-active' : '' ?>">
            <span class="jour-v2-net-dot" style="background:#c9a84c"></span>
            Tous (<?= (int) $totalPosts ?>)
        </a>
        <?php foreach ([
            'facebook'           => ['label' => 'Facebook', 'color' => '#1877F2'],
            'instagram'          => ['label' => 'Instagram','color' => '#E1306C'],
            'linkedin'           => ['label' => 'LinkedIn', 'color' => '#0A66C2'],
            'google_my_business' => ['label' => 'GMB',      'color' => '#34A853'],
        ] as $net => $info):
            if (empty($netCounts[$net])) {
                continue;
            }
            $isNet = isset($_GET['network']) && (string) $_GET['network'] === $net;
            ?>
        <a href="<?= htmlspecialchars($__jourLink(['network' => $net]), ENT_QUOTES, 'UTF-8') ?>" class="<?= $isNet ? 'is-active' : '' ?>">
            <span class="jour-v2-net-dot" style="background:<?= htmlspecialchars($info['color'], ENT_QUOTES, 'UTF-8') ?>"></span>
            <?= htmlspecialchars($info['label']) ?> (<?= (int) $netCounts[$net] ?>)
        </a>
        <?php endforeach; ?>
    </div>

    <div class="jour-v2-week">
        <div class="jour-v2-week-head">
            <span><?= htmlspecialchars($monthLabel, ENT_QUOTES, 'UTF-8') ?></span>
            <div class="jour-v2-week-arrows">
                <a href="<?= htmlspecialchars($__jourLink(['week' => $weekOffset - 1]), ENT_QUOTES, 'UTF-8') ?>" title="Semaine précédente"><i class="fas fa-chevron-left"></i></a>
                <a href="<?= htmlspecialchars($__jourLink(['week' => $weekOffset + 1]), ENT_QUOTES, 'UTF-8') ?>" title="Semaine suivante"><i class="fas fa-chevron-right"></i></a>
            </div>
        </div>
        <div class="jour-v2-days">
            <?php foreach ($days as $day): ?>
            <div class="jour-v2-day">
                <div class="jn"><?= htmlspecialchars((string) ($day['dayName'] ?? ''), ENT_QUOTES, 'UTF-8') ?></div>
                <div class="jd<?= ! empty($day['isToday']) ? ' is-today' : '' ?>"><?= (int) ($day['day'] ?? 0) ?></div>
                <div class="jour-v2-dots">
                    <?php foreach ($day['posts'] ?? [] as $dp):
                        $dpNets = json_decode((string) ($dp['reseaux'] ?? '[]'), true) ?: [];
                        $dotColor = $netColors[$dpNets[0] ?? ''] ?? '#94a3b8';
                        ?>
                    <span style="background:<?= htmlspecialchars($dotColor, ENT_QUOTES, 'UTF-8') ?>"></span>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <?php if (empty($postsByDate)): ?>
    <div class="jour-v2-empty">
        <h3>Journal vide</h3>
        <p>Programmez une publication pour voir la timeline ici.</p>
        <a href="<?= htmlspecialchars($__admin(['module' => 'social', 'action' => 'post-form']), ENT_QUOTES, 'UTF-8') ?>" class="jour-v2-btn-gold">
            <i class="fas fa-plus"></i> Créer une publication
        </a>
    </div>
    <?php else: ?>

    <?php foreach ($postsByDate as $dateKey => $datePosts): ?>
        <?php
        $ts = strtotime($dateKey);
        $frDays   = ['Dimanche','Lundi','Mardi','Mercredi','Jeudi','Vendredi','Samedi'];
        $frMonths = ['jan','fév','mars','avr','mai','juin','juil','août','sep','oct','nov','déc'];
        $dayName  = $frDays[(int) date('w', $ts)];
        $dayNum   = (int) date('j', $ts);
        $monName  = $frMonths[(int) date('n', $ts) - 1];

        $isToday     = ($dateKey === $today);
        $isTomorrow  = ($dateKey === date('Y-m-d', strtotime('+1 day')));
        $isYesterday = ($dateKey === date('Y-m-d', strtotime('-1 day')));

        if ($isToday) {
            $sepLabel = "Aujourd'hui — {$dayName} {$dayNum} {$monName}";
        } elseif ($isTomorrow) {
            $sepLabel = "Demain — {$dayName} {$dayNum} {$monName}";
        } elseif ($isYesterday) {
            $sepLabel = "Hier — {$dayName} {$dayNum} {$monName}";
        } else {
            $sepLabel = "{$dayName} {$dayNum} {$monName}";
        }
        ?>

        <div class="jour-v2-date-sep">
            <span class="line"></span>
            <span class="lbl"><?= htmlspecialchars($sepLabel, ENT_QUOTES, 'UTF-8') ?></span>
            <span class="line"></span>
        </div>

        <div class="journal-day-group">
            <?php foreach ($datePosts as $post): ?>
                <?php include __DIR__ . '/_post_item.php'; ?>
            <?php endforeach; ?>
        </div>
    <?php endforeach; ?>

    <?php endif; ?>

</div><!-- /.jour-v2-page -->

</div><!-- /.social-wrap — ouvert dans _header.php -->

<a href="<?= htmlspecialchars($__admin(['module' => 'social', 'action' => 'post-form']), ENT_QUOTES, 'UTF-8') ?>" class="social-fab jour-v2-fab" title="Nouvelle publication">
    <i class="fas fa-plus"></i>
</a>
