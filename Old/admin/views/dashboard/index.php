<div class="main-content">
    <!-- Page Header -->
    <div class="page-header">
        <div class="breadcrumb">
            <a href="#"><i class="fas fa-home"></i></a>
            <span class="breadcrumb-sep">/</span>
            <span>Tableau de bord</span>
        </div>
        <h1>
            <i class="fas fa-chart-line page-icon"></i>
            HUB <span class="page-title-accent">Tableau de bord</span>
        </h1>
        <p class="section-subtitle">Vue d'ensemble de votre activité immobilière</p>
    </div>

    <!-- Stats Cards Grid -->
    <div class="cards-container">
        <!-- Biens en gestion -->
        <div class="card" style="--card-accent: #3498db; --card-icon-bg: #e3f2fd;">
            <div class="card-header">
                <div class="card-icon">
                    <i class="fas fa-home"></i>
                </div>
                <div class="card-title">Biens en gestion</div>
            </div>
            <div class="card-value"><?= number_format($stats['biens'] ?? 0, 0, ',', ' ') ?></div>
            <div class="card-description">
                <span class="badge badge-success">
                    <i class="fas fa-arrow-up"></i> <?= $stats['biens_trend'] ?? 0 ?>%
                </span>
                <span class="text-muted">vs mois dernier</span>
            </div>
        </div>

        <!-- Leads ce mois -->
        <div class="card" style="--card-accent: #2ecc71; --card-icon-bg: #e8f5e9;">
            <div class="card-header">
                <div class="card-icon">
                    <i class="fas fa-phone"></i>
                </div>
                <div class="card-title">Leads ce mois</div>
            </div>
            <div class="card-value"><?= number_format($stats['leads'] ?? 0, 0, ',', ' ') ?></div>
            <div class="card-description">
                <span class="badge <?= ($stats['leads_trend'] ?? 0) >= 0 ? 'badge-success' : 'badge-danger' ?>">
                    <i class="fas <?= ($stats['leads_trend'] ?? 0) >= 0 ? 'fa-arrow-up' : 'fa-arrow-down' ?>"></i>
                    <?= abs($stats['leads_trend'] ?? 0) ?>%
                </span>
                <span class="text-muted">vs mois dernier</span>
            </div>
        </div>

        <!-- Taux de conversion -->
        <div class="card" style="--card-accent: #9b59b6; --card-icon-bg: #f3e5f5;">
            <div class="card-header">
                <div class="card-icon">
                    <i class="fas fa-percentage"></i>
                </div>
                <div class="card-title">Taux de conversion</div>
            </div>
            <div class="card-value"><?= number_format($stats['conversion_rate'] ?? 0, 1, ',', ' ') ?>%</div>
            <div class="card-description">
                <div class="tag">Objectif: <?= $stats['conversion_goal'] ?? '30' ?>%</div>
            </div>
        </div>

        <!-- Chiffre d'affaires -->
        <div class="card" style="--card-accent: #f39c12; --card-icon-bg: #fff3e0;">
            <div class="card-header">
                <div class="card-icon">
                    <i class="fas fa-euro-sign"></i>
                </div>
                <div class="card-title">Chiffre d'affaires</div>
            </div>
            <div class="card-value"><?= number_format($stats['ca'] ?? 0, 2, ',', ' ') ?> €</div>
            <div class="card-description">
                <span class="badge badge-success">
                    <i class="fas fa-arrow-up"></i> <?= $stats['ca_trend'] ?? 0 ?>%
                </span>
                <span class="text-muted">vs année dernière</span>
            </div>
        </div>
    </div>

    <!-- Main Dashboard Grid -->
    <div class="dashboard-grid mt-20">
        <div class="dash-cols">
            <!-- Colonne gauche -->
            <div class="dash-col-left">
                <!-- Activité récente -->
                <div class="card">
                    <div class="card-header justify-between">
                        <h2>Activité récente</h2>
                        <a href="#" class="card-action">
                            Voir tout <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                    <div class="card-body">
                        <?php if (empty($recentActivity ?? [])): ?>
                            <div class="empty">Aucune activité récente</div>
                        <?php else: ?>
                            <div class="data-table">
                                <table class="w-full">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Type</th>
                                            <th>Description</th>
                                            <th class="text-right">Statut</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($recentActivity ?? [] as $activity): ?>
                                        <tr>
                                            <td><?= date('d/m/Y H:i', strtotime($activity['date'])) ?></td>
                                            <td>
                                                <span class="badge <?= [
                                                    'lead' => 'badge-info',
                                                    'rdv' => 'badge-success',
                                                    'contrat' => 'badge-warning',
                                                    'visite' => 'badge-primary'
                                                ][$activity['type']] ?? 'badge-muted' ?>">
                                                    <?= ucfirst($activity['type']) ?>
                                                </span>
                                            </td>
                                            <td><?= htmlspecialchars($activity['description']) ?></td>
                                            <td class="text-right">
                                                <span class="badge <?= $activity['status'] === 'completed' ? 'badge-success' : 'badge-warning' ?>">
                                                    <?= $activity['status'] === 'completed' ? 'Terminé' : 'En cours' ?>
                                                </span>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Prochaines étapes -->
                <div class="card">
                    <div class="card-header">
                        <h2>Prochaines étapes</h2>
                    </div>
                    <div class="card-body">
                        <div class="quick-links">
                            <a href="#" class="quick-link flex items-center gap-8">
                                <i class="fas fa-plus text-primary"></i>
                                Ajouter un bien
                            </a>
                            <a href="#" class="quick-link flex items-center gap-8">
                                <i class="fas fa-calendar-plus text-success"></i>
                                Planifier un RDV
                            </a>
                            <a href="#" class="quick-link flex items-center gap-8">
                                <i class="fas fa-file-contract text-warning"></i>
                                Créer un contrat
                            </a>
                            <a href="#" class="quick-link flex items-center gap-8">
                                <i class="fas fa-chart-line text-purple-600"></i>
                                Voir les stats
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Colonne droite -->
            <div class="dash-col-right">
                <!-- Alertes -->
                <div class="card">
                    <div class="card-header">
                        <h2>Alertes</h2>
                        <?php if (!empty($alerts ?? [])): ?>
                            <span class="badge <?= count($alerts) > 2 ? 'badge-danger' : 'badge-warning' ?>">
                                <?= count($alerts) ?> alertes
                            </span>
                        <?php endif; ?>
                    </div>
                    <div class="card-body">
                        <?php if (empty($alerts ?? [])): ?>
                            <div class="empty">Aucune alerte pour le moment</div>
                        <?php else: ?>
                            <?php foreach ($alerts ?? [] as $alert): ?>
                            <div class="alert <?= [
                                'info' => 'alert-info',
                                'warning' => 'alert-warning',
                                'danger' => 'alert-danger'
                            ][$alert['type']] ?? 'alert-info' ?>">
                                <div class="flex items-center gap-8">
                                    <i class="fas <?= [
                                        'info' => 'fa-info-circle',
                                        'warning' => 'fa-exclamation-triangle',
                                        'danger' => 'fa-exclamation-circle'
                                    ][$alert['type']] ?? 'fa-info-circle' ?>"></i>
                                    <div>
                                        <div class="font-bold"><?= htmlspecialchars($alert['title']) ?></div>
                                        <div class="text-sm"><?= htmlspecialchars($alert['message']) ?></div>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Modules recommandés -->
                <div class="card modules-section">
                    <div class="card-header">
                        <h2>Modules recommandés</h2>
                        <p class="modules-subtitle">Pour améliorer votre productivité</p>
                    </div>
                    <div class="card-body">
                        <div class="modules-grid">
                            <div class="module-card">
                                <div class="module-name">ASSISTANT IA</div>
                                <div class="module-title">Optimisez votre temps avec l'IA</div>
                                <div class="module-block">
                                    <p>Génération automatique de descriptions</p>
                                    <p>Réponses intelligentes aux leads</p>
                                    <p>Analyse de marché</p>
                                </div>
                                <a href="#" class="btn btn-primary btn-sm mt-8">
                                    <i class="fas fa-robot"></i> Découvrir
                                </a>
                            </div>

                            <div class="module-card">
                                <div class="module-name">AUTOMATION</div>
                                <div class="module-title">Automatisez vos tâches répétitives</div>
                                <div class="module-block">
                                    <p>Envoi automatique de relances</p>
                                    <p>Suivi des leads sans effort</p>
                                    <p>Rappels intelligents</p>
                                </div>
                                <a href="#" class="btn btn-outline btn-sm mt-8">
                                    <i class="fas fa-cogs"></i> Configurer
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
