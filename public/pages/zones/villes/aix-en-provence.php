<?php
declare(strict_types=1);

$ville = [
    'nom'         => 'Aix-en-Provence',
    'type'        => 'ville',
    'prix'        => '4 800',
    'tendance'    => '+1,8%',
    'delai'       => '45 jours',
    'image'       => '/assets/images/hero-bg.jpg',
    'description' => 'Sous-préfecture des Bouches-du-Rhône, ville d’art, d’eau et de patrimoine, Aix-en-Provence concentre universités, tissu économique et une forte demande pour les appartements en centre et les maisons sur le Pays d’Aix.',
    'metaDesc'    => 'Immobilier à Aix-en-Provence (13090, 13080, 13100) : estimation, vente et achat avec Pascal Hamm. Conseil local sur le centre historique, les quartiers résidentiels et les communes alentour.',
    'marche'      => "Le marché aixois reste recherché : centre-ville (Cours Mirabeau, Mazarin, quartier d’Aix), zones résidentielles (Puyricard, les Milles, Luynes) et périphérie proche (Gardanne, Les Pennes-Mirabeau, Vitrolles, Bouc-Bel-Air) répondent à des profils différents. Les biens de qualité, bien positionnés et correctement estimés se vendent avec une liquidité solide, mais la comparaison s’appuie sur des critères de rue, d’exposition, d’état et de charge utile. Un accompagnement local permet d’arbitrer entre projets d’achat, de vente (y compris biens de standing et maisons d’architecte) et stratégies d’investissement locatif (étudiant, prime ou familial).",
    'faq'         => [
        ['q' => 'Quels sont les secteurs les plus demandés à Aix ?', 'a' => "Le centre historique, le quartier Mazarin et les rues proches des facultés concentrent une demande mixte (résidence et investisseur). En périphérie, Puyricard, Les Milles et certains secteurs pavillonnaires attirent les familles à la recherche d’espace et de jardin."],
        ['q' => 'Comment l’estimation se distingue-t-elle d’une moyenne web ?', 'a' => "L’estimation s’appuie sur des ventes récentes de biens comparables, le micro-secteur (rue, étage, luminosité, charges) et l’état effectif. Les indicateurs de masse n’informent pas sur la concurrence réelle du bien en ligne."],
    ],
];
require __DIR__ . '/../_ville-secteur.php';
