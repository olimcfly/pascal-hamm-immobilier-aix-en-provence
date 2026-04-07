<?php
// public/pages/financement/financement.php
// Page "Financement" incluse par public/financement.php
// NE PAS mettre d'exit ou de require ici : ce fichier est destiné à être inclus dans layout.php

// Démarrer la session si nécessaire (évite les warnings quand on utilise $_SESSION)
if (session_status() !== PHP_SESSION_ACTIVE) {
    @session_start();
}

// Titre page (utilisé par layout.php)
$pageTitle = 'Financement - Pascal Hamm Immobilier - Aix-en-Provence';

// CSRF (pré-existant dans la session via bootstrap)
$csrfToken = $_SESSION['csrf_token'] ?? bin2hex(random_bytes(16));
$_SESSION['csrf_token'] = $csrfToken;

// Données SEO (peuvent être reprises/modifiées par layout.php)
$metaDescription = 'Accompagnement personnalisé pour votre financement immobilier à Aix-en-Provence. Simulation humaine, diagnostic rapide et exigence de taux adaptés à votre projet. Demandez un rendez-vous ou une étude de financement gratuite.';
$canonical = '/financement';

// Si ton layout utilise des variables spécifiques, expose-les aussi
$pageMetaDescription = $metaDescription;
$pageCanonical = $canonical;
?>

<section class="section hero-plain">
  <div class="container">
    <header class="page-header">
      <h1 class="title">Financement immobilier à Aix‑en‑Provence</h1>
      <p class="lead">Accompagnement complet pour obtenir le meilleur montage financier — pas de simulateur public : un conseil humain et une prise en charge administrative jusqu’à l’offre de prêt.</p>
    </header>

    <div class="grid two-cols">
      <div class="col">
        <h2>Pourquoi nous confier votre financement ?</h2>
        <ul>
          <li><strong>Conseil personnalisé</strong> : étude complète de votre dossier (revenus, apport, projet).</li>
          <li><strong>Optimisation</strong> : durée, assurance, garanties, et plan de financement adapté à votre situation.</li>
          <li><strong>Gain de temps</strong> : nous centralisons et suivons les demandes auprès des partenaires bancaires.</li>
          <li><strong>Accompagnement jusqu’à l’offre</strong> : relecture des propositions, suivi des conditions suspensives et coordination pour la signature.</li>
        </ul>

        <h3 id="acheter-avant-vendre">Acheter avant de vendre ?</h3>
        <p>Si vous souhaitez <strong>acheter avant de vendre</strong>, nous évaluons les solutions pertinences : prêt relais, double financement temporaire ou bridge bancaire. Chaque option a des impacts différents sur votre budget — nous vous présentons des scénarios clairs.</p>

        <h3>Ce que nous demandons pour lancer l’étude</h3>
        <ul>
          <li>Détails du projet (type de bien, montant estimé, adresse si connue, surface)</li>
          <li>Situation personnelle et professionnelle (statut, revenus, charges)</li>
          <li>Apport disponible et plan d’entrée</li>
          <li>Copies des trois dernières fiches de paie, dernier avis d’imposition, tableau d’amortissement éventuel</li>
        </ul>

        <p>Une fois ces éléments reçus, nous préparons une première étude et contactons nos partenaires pour obtenir des propositions adaptées.</p>
      </div>

      <div class="col">
        <h2>Nos services inclus</h2>
        <ul>
          <li>Analyse de faisabilité et calcules de capacité d'emprunt</li>
          <li>Négociation des meilleures conditions (taux, assurance, durée)</li>
          <li>Préparation du dossier et prise en charge administrative</li>
          <li>Accompagnement jusqu’à l’offre de prêt signée</li>
        </ul>

        <h3>Prise de rendez‑vous</h3>
        <p>Pour lancer une étude gratuite, prenez rendez‑vous en agence ou envoyez vos documents via notre formulaire sécurisé. Nous vous rappelons rapidement pour préciser le besoin et lancer les demandes.</p>

        <div class="cta">
          <a class="button primary" href="/contact">Demander une étude gratuite</a>
        </div>
      </div>
    </div>

    <h2>Ils nous ont fait confiance</h2>
    <div class="testimonials">
      <blockquote>
        « Très bon accompagnement pour l'obtention de notre prêt, rapide et clair. » — L. &amp; M., Aix‑en‑Provence
      </blockquote>
    </div>
  </div>
</section>

<section id="faq-financement" class="section faq">
  <div class="container">
    <h2>FAQ — Financement</h2>

    <div class="faq-item">
      <h3>Combien de temps pour obtenir une étude de financement ?</h3>
      <p>Après réception des éléments principaux, nous fournissons un état des lieux et des premières propositions sous 24 à 48h ouvrées.</p>
    </div>

    <div class="faq-item">
      <h3>Proposez-vous des prêts relais ?</h3>
      <p>Oui, nous étudions la pertinence d’un prêt relais selon votre projet et votre calendrier de vente.</p>
    </div>

    <div class="faq-item">
      <h3>Dois‑je avoir un apport ?</h3>
      <p>L’apport facilite l’octroi et les meilleures conditions, mais nous travaillons aussi des dossiers avec peu ou pas d’apport selon les profils et garanties.</p>
    </div>

    <div class="faq-item">
      <h3>Faites‑vous le montage avec des comparateurs automatisés ?</h3>
      <p>Non : nous privilégions le contact humain et la négociation directe avec nos partenaires bancaires pour obtenir des conditions sur‑mesure.</p>
    </div>
  </div>
</section>

<!-- JSON-LD FAQ pour SEO -->
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "FAQPage",
  "mainEntity": [
    {
      "@type": "Question",
      "name": "Combien de temps pour obtenir une étude de financement ?",
      "acceptedAnswer": {
        "@type": "Answer",
        "text": "Après réception des éléments principaux, nous fournissons un état des lieux et des premières propositions sous 24 à 48h ouvrées."
      }
    },
    {
      "@type": "Question",
      "name": "Proposez-vous des prêts relais ?",
      "acceptedAnswer": {
        "@type": "Answer",
        "text": "Oui, nous étudions la pertinence d’un prêt relais selon votre projet et votre calendrier de vente."
      }
    },
    {
      "@type": "Question",
      "name": "Dois-je avoir un apport ?",
      "acceptedAnswer": {
        "@type": "Answer",
        "text": "L’apport facilite l’octroi et les conditions, mais nous travaillons aussi des dossiers avec peu ou pas d’apport selon les profils et garanties."
      }
    }
  ]
}
</script>
