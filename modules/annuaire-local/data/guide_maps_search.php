<?php
/**
 * Raccourcis de recherche Google Maps par catégorie (données par déploiement).
 * Laissez ville / postal vides pour utiliser la première ville active (nom + code postal) en base.
 * Dans les URL, __VILLE__ et __CP__ sont remplacés automatiquement (voir accueil.php).
 *
 * @return array{ville?: string, slug?: string, postal?: string, categories: list<array{slug: string, title: string, icon: string, items: list<array{name: string, gmb: string, web: string}>}>}
 */
return [
    'ville'   => '',
    'slug'    => '',
    'postal'  => '',
    'categories' => [
        [
            'slug'  => 'boulangerie',
            'title' => 'Boulangerie',
            'icon'  => 'fa-bread-slice',
            'items' => [
                ['name' => 'Boulangeries artisanales', 'gmb' => 'https://www.google.com/maps/search/Boulangerie+__VILLE__+__CP__', 'web' => ''],
            ],
        ],
        [
            'slug'  => 'restauration',
            'title' => 'Restauration',
            'icon'  => 'fa-utensils',
            'items' => [
                ['name' => 'Restaurants', 'gmb' => 'https://www.google.com/maps/search/Restaurant+__VILLE__+__CP__', 'web' => ''],
                ['name' => 'Cuisine du monde', 'gmb' => 'https://www.google.com/maps/search/Restaurant+__VILLE__', 'web' => ''],
            ],
        ],
        [
            'slug'  => 'cafe-bar',
            'title' => 'Café / bar',
            'icon'  => 'fa-mug-hot',
            'items' => [
                ['name' => 'Cafés & bars', 'gmb' => 'https://www.google.com/maps/search/Caf%C3%A9+bar+__VILLE__', 'web' => ''],
            ],
        ],
        [
            'slug'  => 'epicerie',
            'title' => 'Alimentation',
            'icon'  => 'fa-basket-shopping',
            'items' => [
                ['name' => 'Épiceries & produits locaux', 'gmb' => 'https://www.google.com/maps/search/%C3%89picerie+__VILLE__+__CP__', 'web' => ''],
            ],
        ],
        [
            'slug'  => 'mode',
            'title' => 'Mode & beauté',
            'icon'  => 'fa-shirt',
            'items' => [
                ['name' => 'Mode & accessoires', 'gmb' => 'https://www.google.com/maps/search/Magasin+de+mode+__VILLE__', 'web' => ''],
            ],
        ],
        [
            'slug'  => 'coiffure-beaute',
            'title' => 'Coiffure / esthétique',
            'icon'  => 'fa-cut',
            'items' => [
                ['name' => 'Coiffeurs', 'gmb' => 'https://www.google.com/maps/search/Coiffeur+__VILLE__+__CP__', 'web' => ''],
                ['name' => 'Instituts de beauté', 'gmb' => 'https://www.google.com/maps/search/Institut+de+beaut%C3%A9+__VILLE__', 'web' => ''],
            ],
        ],
        [
            'slug'  => 'sante',
            'title' => 'Santé',
            'icon'  => 'fa-heart-pulse',
            'items' => [
                ['name' => 'Pharmacies', 'gmb' => 'https://www.google.com/maps/search/Pharmacie+__VILLE__+__CP__', 'web' => ''],
            ],
        ],
        [
            'slug'  => 'artisanat',
            'title' => 'Artisanat & dépannage',
            'icon'  => 'fa-hammer',
            'items' => [
                ['name' => 'Artisans du bâtiment', 'gmb' => 'https://www.google.com/maps/search/Artisan+__VILLE__', 'web' => ''],
            ],
        ],
        [
            'slug'  => 'services',
            'title' => 'Services',
            'icon'  => 'fa-screwdriver-wrench',
            'items' => [
                ['name' => 'Garages automobiles', 'gmb' => 'https://www.google.com/maps/search/Garage+automobile+__VILLE__', 'web' => ''],
            ],
        ],
        [
            'slug'  => 'loisirs-culture',
            'title' => 'Loisirs & culture',
            'icon'  => 'fa-masks-theater',
            'items' => [
                ['name' => 'Sorties & culture', 'gmb' => 'https://www.google.com/maps/search/Cin%C3%A9ma+__VILLE__', 'web' => ''],
            ],
        ],
    ],
];
