# Audit d'intégration — Module Guide Local / Partenaires

## A. Existant réutilisable
- Entrée front centralisée dans `public/index.php` + layout unique `public/templates/layout.php`.
- Routing maison (`core/Router.php`) avec patterns dynamiques `{slug}`.
- Paramètres de zone et API Google Maps déjà disponibles via `settings` (`zone_lat`, `zone_lng`, `zone_rayon_km`, `api_google_maps`).
- Service et patterns Google Maps déjà utilisés dans `modules/estimation/accueil.php`.
- Back-office modulaire (`public/admin/index.php`) avec chargement automatique de `modules/<module>/accueil.php`.
- Sidebar admin inclut déjà l’entrée `partenaires`.
- SEO/sitemap centralisé dans `public/sitemap.php`.

## B. Manques identifiés
- Aucune route publique active pour `/guide-local` malgré des liens existants.
- Aucun module admin `modules/partenaires/` (menu visible mais non fonctionnel).
- Table `guide_local` existante mais orientée quartiers/communes, pas partenaires géolocalisés.
- Aucun endpoint AJAX de filtrage rayon côté front.

## C. Créations / adaptations proposées
- Créer un service dédié `LocalPartnerService` (CRUD + filtrage rayon Haversine + bounding box).
- Ajouter tables `local_partners` et `local_partner_categories`.
- Activer routes front `/guide-local`, `/guide-local/{slug}`, `/api/guide-local/partners`.
- Créer module admin `modules/partenaires/accueil.php`.
- Convertir la page guide local en vue carte + filtres rayon + cards partenaires.
- Étendre sitemap pour indexer les fiches partenaires.

## D. Points à ne pas casser
- Layout global et conventions CSS existantes.
- Routage principal front/admin.
- Sitemap existant et pages SEO déjà publiées.
- Auth admin basée sur `Auth::check()`.

## E. Intégration retenue
- Greffe dans la structure actuelle (pas de nouvelle architecture parallèle).
- Réutilisation des settings existants pour le point central de rayon.
- Filtrage distance SQL performant (bounding box + Haversine).
- Admin intégré dans le hub existant via module `partenaires`.

## Choix du point central
- Point central recommandé: `zone_lat` / `zone_lng` configurés en back-office, avec fallback sur Aix-en-Provence.
- Ce choix est cohérent avec le reste du site (zones métier déjà pilotées via settings).

## SEO
- URL indexables: `/guide-local` + `/guide-local/{slug}`.
- Entrées ajoutées au sitemap XML existant.
- Meta title/meta desc gérées dans les vues en cohérence avec le layout principal.
