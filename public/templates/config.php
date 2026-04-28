<?php
// config.php

// Configuration par défaut
$defaultConfig = [
    'pageTitle' => 'Titre par défaut',
    'metaDesc' => 'Description par défaut',
    'metaKeywords' => 'mots-clés, par, défaut',
    'extraCss' => ['/public/assets/css/style.css'],
    'extraJs' => []
];

// Configurations spécifiques aux pages
$pageConfigs = [
    'biens' => [
        'pageTitle' => 'Nos biens immobiliers à Aix-en-Provence — Pascal Hamm | Vente & Location',
        'metaDesc' => 'Découvrez notre sélection de biens immobiliers à Aix-en-Provence et dans les environs : appartements, maisons, terrains et biens de prestige.',
        'metaKeywords' => 'biens immobiliers Aix-en-Provence, appartements à vendre Aix, maisons Aix, immobilier Pays d\'Aix, acheter à Aix, location Aix',
        'extraCss' => ['/public/assets/css/style.css'],
        'extraJs' => []
    ],
    'contact' => [
        'pageTitle' => 'Contactez-nous — Pascal Hamm | Immobilier à Aix-en-Provence',
        'metaDesc' => 'Contactez-nous pour votre projet immobilier sur Aix-en-Provence et les environs : vente, achat, estimation et accompagnement sur mesure.',
        'metaKeywords' => 'contact immobilier Aix-en-Provence, agent immobilier Aix, Pascal Hamm contact, conseiller immobilier Aix',
        'extraCss' => ['/public/assets/css/style.css'],
        'extraJs' => []
    ],
    'estimation' => [
        'pageTitle' => 'Estimation gratuite à Aix-en-Provence — Pascal Hamm',
        'metaDesc' => 'Obtenez une estimation gratuite de votre bien immobilier à Aix-en-Provence et dans les environs.',
        'metaKeywords' => 'estimation immobilière Aix-en-Provence, estimation gratuite Aix, avis de valeur Aix, Pascal Hamm estimation',
        'extraCss' => ['/public/assets/css/style.css'],
        'extraJs' => []
    ],
    'guide' => [
        'pageTitle' => 'Guide immobilier Aix-en-Provence — Pascal Hamm',
        'metaDesc' => 'Découvrez notre guide immobilier pour mieux vendre, acheter et comprendre le marché sur Aix-en-Provence et les environs.',
        'metaKeywords' => 'guide immobilier Aix, vendre à Aix-en-Provence, acheter à Aix, marché immobilier Aix, Pascal Hamm guide',
        'extraCss' => ['/public/assets/css/style.css'],
        'extraJs' => []
    ],
    'secteurs' => [
        'pageTitle' => 'Secteurs immobiliers autour d\'Aix-en-Provence — Pascal Hamm',
        'metaDesc' => 'Découvrez les secteurs, quartiers et villes autour d\'Aix-en-Provence pour votre projet immobilier.',
        'metaKeywords' => 'secteurs immobiliers Aix, quartiers Aix-en-Provence, villes autour d\'Aix, immobilier Pays d\'Aix',
        'extraCss' => ['/public/assets/css/style.css'],
        'extraJs' => []
    ],
    'home' => [
        'pageTitle' => 'Immobilier à Aix-en-Provence — Pascal Hamm | Vente, Achat, Estimation',
        'metaDesc' => 'Bienvenue sur le site de Pascal Hamm, votre conseiller immobilier sur Aix-en-Provence et les environs pour vendre, acheter ou faire estimer votre bien.',
        'metaKeywords' => 'immobilier Aix-en-Provence, conseiller immobilier Aix, vente Aix, achat Aix, estimation Aix, Pascal Hamm',
        'extraCss' => ['/public/assets/css/style.css'],
        'extraJs' => []
    ]
];

function getPageConfig($pageName) {
    global $defaultConfig, $pageConfigs;

    // Retourner la configuration par défaut si la page n'est pas trouvée
    if (!isset($pageConfigs[$pageName])) {
        return $defaultConfig;
    }

    // Fusionner la configuration par défaut avec la configuration spécifique à la page
    return array_merge($defaultConfig, $pageConfigs[$pageName]);
}