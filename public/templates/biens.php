<?php
// biens.php

// Définir les métadonnées spécifiques à la page
$pageTitle = 'Nos biens immobiliers à Aix-en-Provence — Pascal Hamm | Vente & Location';
$metaDesc = 'Découvrez notre sélection de biens immobiliers à Aix-en-Provence et dans les environs : appartements, maisons, immeubles, terrains et biens de prestige.';
$metaKeywords = 'biens immobiliers Aix-en-Provence, appartements à vendre Aix, maisons Aix, immobilier Pays d\'Aix, acheter à Aix, location Aix';

// Définir le template à utiliser
$template = 'biens';

// Inclure l'en-tête
require_once __DIR__ . '/../../templates/header.php';
?>

<!-- Contenu spécifique à la page -->
<section class="page-header">
    <div class="container">
        <h1 class="page-title">Nos biens immobiliers</h1>
        <p class="page-subtitle">À Aix-en-Provence et sur le Pays d’Aix (Bouches-du-Rhône)</p>
    </div>
</section>

<!-- Contenu principal de la page -->
<div class="container">
    <!-- Votre contenu ici -->
</div>

<?php
// Inclure le pied de page
require_once __DIR__ . '/../../templates/footer.php';
?>