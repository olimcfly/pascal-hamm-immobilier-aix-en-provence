<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();

    $email     = trim((string) ($_POST['email'] ?? ''));
    $firstName = trim((string) ($_POST['first_name'] ?? ''));
    $address   = trim((string) ($_POST['address'] ?? ''));

    if ($email !== '' && $firstName !== '' && $address !== '' 
        && filter_var($email, FILTER_VALIDATE_EMAIL)) {
        LeadService::capture([
            'source_type'      => LeadService::SOURCE_ESTIMATION,
            'pipeline'         => LeadService::SOURCE_ESTIMATION,
            'stage'            => 'avis_valeur',
            'priority'         => 'haute',
            'first_name'       => $firstName,
            'last_name'        => trim((string) ($_POST['last_name'] ?? '')),
            'email'            => $email,
            'phone'            => trim((string) ($_POST['phone'] ?? '')),
            'intent'           => 'Demande d\'avis de valeur',
            'property_type'    => trim((string) ($_POST['property_type'] ?? '')),
            'property_address' => $address,
            'consent'          => !empty($_POST['consent']),
        ]);

        redirect('/merci');
    }
}

$pageTitle = 'Avis de valeur gratuit — Estimation de votre bien | Pascal Hamm Immobilier';
$metaDesc  = 'Obtenez un avis de valeur gratuit et personnalisé pour votre bien immobilier. 
              Pascal Hamm, expert indépendant, analyse votre bien et vous remet une estimation détaillée.';
$extraCss  = ['/assets/css/estimation.css'];
?>

<section class="hero hero--light" aria-labelledby="avis-valeur-hero">
    <div class="container">
        <div class="hero__content">
            <span class="section-label hero__label">Avis de valeur</span>
            <h1 id="avis-valeur-hero">Quelle est la valeur réelle de votre bien ?</h1>
            <p class="hero__subtitle">
                Obtenez une estimation précise et gratuite, réalisée par un expert 
                immobilier indépendant qui connaît parfaitement le marché aixois.
            </p>
        </div>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="estimation-layout">

            <!-- ── Colonne gauche : Pourquoi + Sidebar ── -->
            <div class="estimation-sidebar">

                <!-- Bloc "Pourquoi" -->
                <div class="why-estimate">
                    <h3>Pourquoi demander un avis de valeur ?</h3>
                    <p class="why-intro">
                        Un avis de valeur vous permet de connaître le prix réel 
                        du marché avant de vendre ou d'acheter, et d'éviter 
                        les mauvaises surprises.
                    </p>
                    <?php foreach ([
                        ['🎯', 'Estimation personnalisée',    'Basée sur les ventes récentes du secteur'],
                        ['🔍', 'Analyse complète',            'Atouts et points d\'amélioration de votre bien'],
                        ['💡', 'Prix de vente optimal',       'Conseil sur la stratégie de mise en vente'],
                        ['⚡', 'Résultat sous 48h',           'Sans engagement, entièrement gratuit'],
                    ] as [$icon, $title, $desc]): ?>
                    <div class="why-item">
                        <span class="why-icon"><?= $icon ?></span>
                        <div class="why-text">
                            <strong><?= e($title) ?></strong>
                            <p><?= e($desc) ?></p>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <!-- Bloc conseiller -->
                <div class="advisor-box">
                    <div class="advisor-avatar">👤</div>
                    <h4>Pascal Hamm</h4>
                    <p class="role">Expert immobilier indépendant · Aix-en-Provence</p>
                    <p style="font-size:.875rem;color:var(--clr-text-muted)">
                        Plus de 20 ans d'expérience sur le marché aixois. 
                        Votre avis de valeur sera réalisé personnellement par Pascal.
                    </p>
                </div>

            </div>

            <!-- ── Colonne droite : Formulaire ── -->
            <div class="estimation-form">
                <h2>Demander mon avis de valeur gratuit</h2>
                <p class="lead">Remplissez le formulaire ci-dessous, nous vous recontactons sous 48h.</p>

                <form method="POST" novalidate>
                    <?= csrfField() ?>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label" for="first_name">Prénom *</label>
                            <input 
                                class="form-control" 
                                id="first_name"
                                name="first_name" 
                                type="text"
                                autocomplete="given-name"
                                required>
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="last_name">Nom</label>
                            <input 
                                class="form-control" 
                                id="last_name"
                                name="last_name"
                                type="text"
                                autocomplete="family-name">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label" for="email">Email *</label>
                            <input 
                                class="form-control" 
                                id="email"
                                name="email" 
                                type="email"
                                autocomplete="email"
                                required>
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="phone">Téléphone</label>
                            <input 
                                class="form-control" 
                                id="phone"
                                name="phone" 
                                type="tel"
                                autocomplete="tel">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="address">Adresse du bien *</label>
                        <input 
                            class="form-control" 
                            id="address"
                            name="address" 
                            type="text"
                            placeholder="Ex : 12 rue Mirabeau, Aix-en-Provence"
                            autocomplete="street-address"
                            required>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label" for="property_type">Type de bien *</label>
                            <select class="form-control" id="property_type" name="property_type" required>
                                <option value="">Sélectionner</option>
                                <option value="appartement">Appartement</option>
                                <option value="maison">Maison</option>
                                <option value="villa">Villa</option>
                                <option value="terrain">Terrain</option>
                                <option value="local-commercial">Local commercial</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="surface">Surface (m²)</label>
                            <input 
                                class="form-control" 
                                id="surface"
                                name="surface" 
                                type="number" 
                                min="10"
                                placeholder="Ex : 85">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-check">
                            <input type="checkbox" name="consent" required>
                            <span>
                                J'accepte que mes données soient traitées pour recevoir mon avis de valeur.
                                <a href="<?= url('/politique-confidentialite') ?>">Politique de confidentialité</a>
                            </span>
                        </label>
                    </div>

                    <button class="btn btn--primary btn--full" type="submit">
                        Recevoir mon avis de valeur gratuit
                    </button>
                </form>
            </div>

        </div>
    </div>
</section>
