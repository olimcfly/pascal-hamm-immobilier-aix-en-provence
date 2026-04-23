<?php
// partials/header.php — Topbar admin unifiée
if (!defined('IMMO_LOCAL')) { die('Accès direct interdit'); }

$user        = Auth::user() ?? [];
$userName    = $user['name']  ?? $_SESSION['user_name']  ?? 'Admin';
$userEmail   = $user['email'] ?? $_SESSION['user_email'] ?? '';
$userRole    = $user['role']  ?? $_SESSION['user_role']  ?? 'user';
$notifCount  = $notifCount ?? (function_exists('getUnreadNotificationsCount') ? getUnreadNotificationsCount() : 0);
$breadcrumb  = $breadcrumb ?? '';
?>
<header class="topbar" role="banner">

    <div class="topbar-left">
        <button type="button"
                class="sidebar-toggle"
                id="sidebar-toggle"
                aria-label="Basculer le menu"
                title="Menu">
            <i class="fas fa-bars" aria-hidden="true"></i>
        </button>

        <?php if ($breadcrumb): ?>
            <nav class="topbar-breadcrumb" aria-label="Fil d'Ariane">
                <?= $breadcrumb ?>
            </nav>
        <?php endif; ?>
    </div>

    <div class="topbar-center">
        <div class="topbar-search">
            <i class="fas fa-search" aria-hidden="true"></i>
            <input type="search"
                   id="global-search"
                   placeholder="Rechercher… (Ctrl+K)"
                   aria-label="Recherche globale">
            <kbd class="search-shortcut">⌘K</kbd>
        </div>
    </div>

    <div class="topbar-right">

        <a href="/" target="_blank" rel="noopener"
           class="btn btn-sm btn-outline"
           title="Voir le site public">
            <i class="fas fa-globe" aria-hidden="true"></i>
            <span class="btn-label">Site public</span>
        </a>

        <button type="button"
                class="topbar-icon-btn"
                id="topbar-notif"
                aria-label="Notifications"
                title="Notifications">
            <i class="fas fa-bell" aria-hidden="true"></i>
            <?php if ($notifCount > 0): ?>
                <span class="notif-badge" aria-label="<?= (int)$notifCount ?> notifications non lues">
                    <?= $notifCount > 99 ? '99+' : (int)$notifCount ?>
                </span>
            <?php endif; ?>
        </button>

        <div class="topbar-user" id="topbar-user-menu">
            <button type="button" class="topbar-user-btn" aria-haspopup="true" aria-expanded="false">
                <span class="user-avatar">
                    <?= strtoupper(substr($userName, 0, 1)) ?>
                </span>
                <span class="user-name"><?= htmlspecialchars($userName) ?></span>
                <i class="fas fa-chevron-down" aria-hidden="true"></i>
            </button>
        </div>

    </div>

</header>
