<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();

    $email  = trim((string)($_POST['email'] ?? ''));
    $prenom = trim((string)($_POST['prenom'] ?? ''));

    if ($email !== '' && $prenom !== '' && filter_var($email, FILTER_VALIDATE_EMAIL) && !empty($_POST['rgpd'])) {
        LeadService::capture([
            'source_type' => LeadService::SOURCE_FINANCEMENT,
            'pipeline'    => LeadService::SOURCE_FINANCEMENT,
            'stage'       => 'nouveau',
            'first_name'  => $prenom,
            'last_name'   => trim((string)($_POST['nom'] ?? '')),
            'email'       => $email,
            'phone'       => trim((string)($_POST['telephone'] ?? '')),
            'intent'      => 'Acheter avant vendre',
            'property_type' => trim((string)($_POST['type_bien_a_vendre'] ?? '')),
            'notes'       => trim((string)($_POST['message'] ?? '')),
            'consent'     => true,
            'metadata'    => [
                'origin_path' => $_SERVER['REQUEST_URI'] ?? '/financement/acheter-avant-vendre',
                'type_de_demande' => trim((string)($_POST['type_de_demande'] ?? 'acheter_avant_vendre')),
                'ville_actuelle' => trim((string)($_POST['ville_actuelle'] ?? '')),
                'estimation_bien_actuel' => trim((string)($_POST['estimation_bien_actuel'] ?? '')),
                'projet_achat' => trim((string)($_POST['projet_achat'] ?? '')),
                'budget_estime' => trim((string)($_POST['budget_estime'] ?? '')),
                'apport_personnel' => trim((string)($_POST['apport_personnel'] ?? '')),
                'delai_souhaite' => trim((string)($_POST['delai_souhaite'] ?? '')),
                'situation_bien_actuel' => trim((string)($_POST['situation_bien_actuel'] ?? '')),
            ],
        ]);

        redirect('/merci');
    }
}

$pageTitle = 'Acheter avant vendre à Aix-en-Provence | Financement immobilier — Pascal Hamm';
$metaDesc  = 'Acheter avant vendre : clarifiez votre capacité de financement, sécurisez votre transition immobilière et avancez avec une stratégie sereine à Aix-en-Provence.';
$canonical = (defined('APP_URL') ? APP_URL : 'https://pascalhamm.fr') . '/financement/acheter-avant-vendre';
$extraCss  = ['/assets/css/financement.css'];
?>

<div class="page-header financement-hero" id="acheter-avant-vendre">
    <div class="container">
        <nav class="breadcrumb" aria-label="Fil d'Ariane">
            <a href="/">Accueil</a><a href="/financement">Financement</a><span>Acheter avant vendre</span>
        </nav>
        <h1>Acheter avant vendre : avancez sereinement dans votre transition immobilière</h1>
        <p>
            Vous souhaitez acheter un nouveau bien avant la vente de votre logement actuel ?
            Je vous aide à clarifier votre projet, sécuriser votre financement immobilier et
            coordonner chaque étape sans précipitation.
        </p>
        <div class="hero-actions">
            <a href="#formulaire-acheter-avant-vendre" class="btn btn--accent btn--lg">Demander un accompagnement</a>
            <a href="/contact?sujet=Financement+immobilier" class="btn btn--outline btn--lg">Parler à un conseiller</a>
        </div>
        <p class="hero-reassurance">Échange confidentiel • réponse rapide • sans engagement</p>
    </div>
</div>

<section class="section">
    <div class="container narrow">
        <span class="section-label">Comprendre votre situation</span>
        <h2 class="section-title">Le problème que rencontrent beaucoup de propriétaires</h2>
        <p class="section-subtitle">
            Entre la peur de vendre trop vite et la crainte de rater une opportunité d'achat immobilier,
            l'incertitude peut bloquer tout le projet. Cette situation est fréquente à Aix-en-Provence
            et dans le Pays d'Aix, surtout sur des marchés réactifs.
        </p>
        <div class="grid-2">
            <article class="premium-card"><h3>Peur de ne pas vendre à temps</h3><p>Vous craignez de vous retrouver avec deux biens ou un calendrier impossible à tenir.</p></article>
            <article class="premium-card"><h3>Peur de manquer le bon achat</h3><p>Une maison intéressante apparaît, mais votre vente immobilière n'est pas finalisée.</p></article>
            <article class="premium-card"><h3>Capacité financière floue</h3><p>Difficile de savoir précisément ce que vous pouvez acheter sans visibilité sur votre vente.</p></article>
            <article class="premium-card"><h3>Charge mentale trop élevée</h3><p>Ordre des étapes, délais, financement : tout semble complexe quand on avance seul.</p></article>
        </div>
    </div>
</section>

<section class="section section--alt">
    <div class="container narrow">
        <span class="section-label">Pédagogie</span>
        <h2 class="section-title">Acheter avant vendre : est-ce possible ?</h2>
        <p>
            Oui, dans certains cas. Tout dépend de votre situation, du bien visé, du niveau de sécurité
            que vous souhaitez et du montage de financement immobilier adapté.
            Mon rôle est d'étudier votre projet immobilier avec méthode, sans promesse irréaliste,
            pour éviter toute décision précipitée.
        </p>
        <ul class="check-list">
            <li>Chaque dossier est étudié individuellement : revenus, timing, valeur du bien actuel, objectifs.</li>
            <li>Il existe plusieurs stratégies selon votre profil et votre tolérance au risque.</li>
            <li>L'objectif est de vous apporter de la clarté avant engagement.</li>
            <li>Un accompagnement immobilier permet de coordonner achat, vente et financement.</li>
        </ul>
    </div>
</section>

<section class="section">
    <div class="container">
        <span class="section-label">Cas fréquents</span>
        <h2 class="section-title">Dans quels cas cette solution peut être pertinente ?</h2>
        <div class="grid-3">
            <?php foreach ([
                ['Famille', 'Vous avez besoin d\'un logement plus grand, mais vous voulez vendre dans de bonnes conditions.'],
                ['Changement de vie', 'Mutation, séparation, retour dans la région : le timing devient une priorité.'],
                ['Opportunité', 'Un bien rare se présente et vous souhaitez vous positionner sans avancer dans le flou.'],
                ['Vente non précipitée', 'Vous souhaitez éviter de brader votre bien actuel pour respecter un calendrier.'],
                ['Montée en gamme', 'Vous vendez un appartement pour acheter une maison avec une stratégie structurée.'],
                ['Transition locale', 'Projet dans le Pays d\'Aix avec besoin d\'aligner délais, financement et déménagement.'],
            ] as [$title, $text]): ?>
            <article class="premium-card">
                <h3><?= e($title) ?></h3>
                <p><?= e($text) ?></p>
            </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="section section--alt">
    <div class="container narrow">
        <span class="section-label">Checklist</span>
        <h2 class="section-title">Ce qu'il faut clarifier avant d'avancer</h2>
        <p class="section-subtitle">On ne vous pousse pas à agir dans l'urgence : on structure votre décision.</p>
        <div class="check-grid">
            <?php foreach ([
                'Valeur estimée de votre bien actuel',
                'Niveau d\'avancement de la vente immobilière',
                'Budget cible du futur achat',
                'Apport personnel réellement mobilisable',
                'Capacité de financement estimée',
                'Délais réalistes entre achat et vente',
                'Niveau de sécurité souhaité',
            ] as $item): ?>
            <div class="check-item">✓ <?= e($item) ?></div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="section">
    <div class="container">
        <span class="section-label">Accompagnement</span>
        <h2 class="section-title">Comment je vous accompagne</h2>
        <div class="steps-grid">
            <article><h3>1. Analyse</h3><p>Étude de votre situation actuelle et de vos priorités.</p></article>
            <article><h3>2. Cadrage</h3><p>Définition d'un projet cohérent entre achat immobilier, vente immobilière et budget.</p></article>
            <article><h3>3. Stratégie</h3><p>Choix du meilleur timing et des options de financement pertinentes pour votre profil.</p></article>
            <article><h3>4. Coordination</h3><p>Accompagnement global avec les interlocuteurs utiles, en gardant une vision d'ensemble.</p></article>
        </div>
    </div>
</section>

<section class="section section--alt">
    <div class="container">
        <span class="section-label">Bénéfices concrets</span>
        <h2 class="section-title">Ce que vous gagnez</h2>
        <div class="benefits">
            <p>✔ Plus de visibilité sur la faisabilité de votre projet immobilier</p>
            <p>✔ Moins de stress dans votre transition immobilière</p>
            <p>✔ Des décisions mieux séquencées, sans précipitation</p>
            <p>✔ Une meilleure coordination achat, vente et financement</p>
            <p>✔ Un interlocuteur unique pour rester clair et efficace</p>
        </div>
    </div>
</section>

<section class="section">
    <div class="container narrow">
        <span class="section-label">Parcours simple</span>
        <h2 class="section-title">Comment ça fonctionne ?</h2>
        <ol class="process-list">
            <li><strong>Vous décrivez votre situation</strong> en 2 minutes via le formulaire.</li>
            <li><strong>Votre projet est étudié</strong> avec une lecture claire des contraintes et leviers.</li>
            <li><strong>Vous êtes recontacté rapidement</strong> pour un échange personnalisé.</li>
            <li><strong>Vous avancez avec une feuille de route</strong> structurée et rassurante.</li>
        </ol>
    </div>
</section>

<section class="section section--alt" id="formulaire-acheter-avant-vendre">
    <div class="container">
        <div class="form-wrap">
            <div>
                <span class="section-label">Demande de financement</span>
                <h2 class="section-title">Parlons de votre projet acheter avant vendre</h2>
                <p class="section-subtitle">Réponse rapide • sans engagement • échange confidentiel</p>
            </div>

            <form action="<?= e($_SERVER['REQUEST_URI'] ?? '/financement/acheter-avant-vendre') ?>" method="POST" class="premium-form" novalidate>
                <?= csrfField() ?>
                <input type="hidden" name="type_de_demande" value="acheter_avant_vendre">

                <div class="form-row">
                    <div class="form-group"><label class="form-label" for="fav-prenom">Prénom *</label><input class="form-control" type="text" id="fav-prenom" name="prenom" required></div>
                    <div class="form-group"><label class="form-label" for="fav-nom">Nom *</label><input class="form-control" type="text" id="fav-nom" name="nom" required></div>
                </div>

                <div class="form-row">
                    <div class="form-group"><label class="form-label" for="fav-email">Email *</label><input class="form-control" type="email" id="fav-email" name="email" required></div>
                    <div class="form-group"><label class="form-label" for="fav-telephone">Téléphone</label><input class="form-control" type="tel" id="fav-telephone" name="telephone"></div>
                </div>

                <div class="form-row">
                    <div class="form-group"><label class="form-label" for="fav-ville">Ville actuelle</label><input class="form-control" type="text" id="fav-ville" name="ville_actuelle"></div>
                    <div class="form-group"><label class="form-label" for="fav-type">Type de bien à vendre</label><input class="form-control" type="text" id="fav-type" name="type_bien_a_vendre" placeholder="Appartement, maison..."></div>
                </div>

                <div class="form-row">
                    <div class="form-group"><label class="form-label" for="fav-estimation">Estimation connue du bien actuel</label><input class="form-control" type="text" id="fav-estimation" name="estimation_bien_actuel" placeholder="Ex. 480 000 €"></div>
                    <div class="form-group"><label class="form-label" for="fav-budget">Budget estimé du futur achat</label><input class="form-control" type="text" id="fav-budget" name="budget_estime"></div>
                </div>

                <div class="form-row">
                    <div class="form-group"><label class="form-label" for="fav-apport">Apport personnel</label><input class="form-control" type="text" id="fav-apport" name="apport_personnel"></div>
                    <div class="form-group"><label class="form-label" for="fav-delai">Délai souhaité</label><input class="form-control" type="text" id="fav-delai" name="delai_souhaite" placeholder="Ex. 3 à 6 mois"></div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="fav-situation">Situation du bien actuel</label>
                    <select class="form-control" id="fav-situation" name="situation_bien_actuel">
                        <option value="pas_encore_en_vente">Pas encore en vente</option>
                        <option value="en_estimation">En estimation</option>
                        <option value="deja_en_vente">Déjà en vente</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label" for="fav-projet">Projet d'achat recherché</label>
                    <textarea class="form-control" id="fav-projet" name="projet_achat" rows="3" placeholder="Type de bien, secteur, surface, priorités..."></textarea>
                </div>

                <div class="form-group">
                    <label class="form-label" for="fav-message">Message libre</label>
                    <textarea class="form-control" id="fav-message" name="message" rows="4" placeholder="Précisez vos contraintes et vos questions."></textarea>
                </div>

                <div class="form-group form-group--checkbox">
                    <label class="checkbox-label">
                        <input type="checkbox" name="rgpd" required>
                        <span>J'accepte la <a href="/politique-confidentialite" target="_blank">politique de confidentialité</a> et le traitement de ma demande.</span>
                    </label>
                </div>

                <button type="submit" class="btn btn--primary btn--lg btn--full">Être recontacté pour mon projet</button>
            </form>
        </div>
    </div>
</section>

<section class="section" id="faq-financement">
    <div class="container narrow">
        <span class="section-label">FAQ</span>
        <h2 class="section-title">Questions fréquentes : acheter avant vendre</h2>
        <div class="faq-list">
            <?php foreach ([
                ['Peut-on acheter avant d\'avoir vendu son bien ?', 'Oui, dans certains cas. Cela dépend de votre situation financière, de la valeur de votre bien actuel et de la stratégie de financement retenue.'],
                ['Comment savoir si mon projet est réaliste ?', 'Une analyse de votre capacité de financement, de votre calendrier et de vos objectifs permet de valider la faisabilité avant de vous engager.'],
                ['Faut-il d\'abord faire estimer mon bien actuel ?', 'C\'est fortement recommandé. Une estimation du bien réaliste donne une base fiable pour construire votre projet achat + vente.'],
                ['Est-ce risqué d\'acheter avant de vendre ?', 'Le risque existe surtout quand le projet est mal préparé. Un accompagnement immobilier structuré permet de le réduire nettement.'],
                ['Comment mieux coordonner achat, vente et financement ?', 'En travaillant avec un plan clair : priorités, délais cibles, scénarios de sécurité et points de décision à chaque étape.'],
                ['Puis-je être accompagné si mon bien n\'est pas encore sur le marché ?', 'Oui. C\'est souvent le meilleur moment pour préparer votre transition immobilière et éviter les décisions dans l\'urgence.'],
                ['Quels éléments préparer avant un premier échange ?', 'Votre situation actuelle, vos objectifs, une estimation de votre bien, votre budget cible et votre délai souhaité.'],
                ['En combien de temps puis-je être recontacté ?', 'En général sous 24h ouvrées, avec un premier échange orienté clarté et faisabilité.'],
            ] as [$q, $a]): ?>
            <article class="faq-item">
                <h3><?= e($q) ?></h3>
                <p><?= e($a) ?></p>
            </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="cta-banner">
    <div class="container">
        <h2>Sécurisez votre projet d'achat avant vente dès maintenant</h2>
        <p>Clarifiez votre stratégie en quelques minutes, puis avancez avec une feuille de route fiable.</p>
        <div class="cta-banner__actions">
            <a href="#formulaire-acheter-avant-vendre" class="btn btn--accent btn--lg">Demander un accompagnement</a>
        </div>
    </div>
</section>

<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "FAQPage",
  "mainEntity": [
    {"@type": "Question", "name": "Peut-on acheter avant d'avoir vendu son bien ?", "acceptedAnswer": {"@type": "Answer", "text": "Oui, dans certains cas selon votre situation financière, la valeur estimée de votre bien actuel et la stratégie de financement retenue."}},
    {"@type": "Question", "name": "Comment savoir si mon projet est réaliste ?", "acceptedAnswer": {"@type": "Answer", "text": "Une analyse de votre capacité de financement, de votre calendrier et de vos objectifs permet de valider la faisabilité avant engagement."}},
    {"@type": "Question", "name": "Faut-il d'abord faire estimer mon bien actuel ?", "acceptedAnswer": {"@type": "Answer", "text": "Oui, une estimation fiable du bien actuel est une base essentielle pour structurer un projet acheter avant vendre."}},
    {"@type": "Question", "name": "Est-ce risqué d'acheter avant de vendre ?", "acceptedAnswer": {"@type": "Answer", "text": "Le risque augmente surtout sans préparation. Un accompagnement structuré permet de réduire fortement l'incertitude."}},
    {"@type": "Question", "name": "Puis-je être accompagné si mon bien n'est pas encore sur le marché ?", "acceptedAnswer": {"@type": "Answer", "text": "Oui, c'est souvent le meilleur moment pour anticiper et coordonner vente, achat et financement."}},
    {"@type": "Question", "name": "En combien de temps puis-je être recontacté ?", "acceptedAnswer": {"@type": "Answer", "text": "En général sous 24h ouvrées."}}
  ]
}
</script>
