# Module Prospection Email B2B

Module MVC SaaS complet pour gérer la prospection email B2B sans mélanger scraping brut, base contacts et envoi direct.

## Arborescence

- `controllers/`: orchestration métier globale.
- `models/`: accès DB (contacts, campagnes, séquences).
- `services/`: ingestion, validation, segmentation/campagnes, moteur de séquences, conversations.
- `views/`: interface introductive MERE, mobile-first.
- `api/`: endpoints JSON (contacts et campagnes).
- `sql/schema.sql`: DDL des tables `prospect_*`.

## Parcours fonctionnel

1. **Collecte** : ajout manuel, import CSV (mapping), buffering scraping.
2. **Validation** : statuts `missing|invalid_format|duplicate|pending_review|valid|blacklisted`.
3. **Segmentation** : filtrage des contacts validés pour campagnes.
4. **Campagnes** : objectif, boîte d'envoi, volume journalier, lancement.
5. **Séquences** : étapes, délai, variables dynamiques, arrêt auto sur réponse.
6. **Conversations** : création thread, historique, arrêt séquence, mise à jour statut contact.
