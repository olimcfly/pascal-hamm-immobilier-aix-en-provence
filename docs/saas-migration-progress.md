# Migration SaaS multi-tenant — suivi

**Avancement global : ~90 %** (implémentation livrée ; tests manuels + exécution migration sur votre MySQL à confirmer).

---

## Étape 1 — Audit technique (avant modification)

### Authentification

| Élément | Détail |
|--------|--------|
| Session admin | `admin/session-helper.php` — `startAdminSession()`, cookie `pascalhamm_sess`, durée 8 h. |
| Session app publique | `core/Session.php` + `core/bootstrap.php` — nom `SESSION_NAME` / `app_session`. |
| Classe `Auth` | `core/Auth.php` — `login`, `logout`, `check`, `isAdmin`, `user`, `attempt`, rôles `admin` / `superadmin` (ENUM étendu en prod via migrations). |
| Login admin effectif | `admin/login.php` et `public/admin/login.php` — PDO direct sur `users`, fallback `ADMIN_EMAIL` / hash dans `includes/config.php`. |
| Garde d’entrée admin | `admin/index.php` / `public/admin/index.php` — `isAdminLoggedIn()` sur `user_id` + `user_role`. |

### Table utilisateurs

- `users` (schéma initial `database/migrations/001_init.sql`, rôle étendu `036_superuser_account.sql`) : `id`, `email`, `password`, `name`, `role`, `is_active`, timestamps.

### Données « client » repérées (hors CRM migré dans cette phase)

| Domaine | Tables / fichiers indicatifs |
|--------|------------------------------|
| Contacts legacy | `contacts` (`001_init.sql`) |
| Leads CRM | `crm_leads`, `crm_lead_interactions` (`007_crm_leads.sql`, `LeadService.php`) |
| Settings | `settings`, `settings_history`, `settings_templates` (`004_settings_centralization.sql`) |
| Pages / CMS | `pages`, `cms_pages`, etc. |
| Blog | `blog_articles`, `blog_silos` (`009_blog_schema.sql`) |
| Biens | `biens`, `bien_photos` |
| Secteurs / zones | `zones`, `villes`, `quartiers` (`026_secteurs_villes_quartiers.sql`) |
| Séquences e-mail | `email_sequences`, `email_sequence_subscriptions` (`028_email_sequences.sql`) |
| CRM séquences | `crm_sequences`, `crm_sequence_enrollments` (`015_sequences_crm.sql`) |
| Formulaires / funnels | `funnels`, champs UTM sur `crm_leads` (`013_funnels.sql`) |

**Risque identifié :** tant que seul le CRM est filtré par `organization_id`, les autres domaines restent globaux — comportement attendu pour cette itération.

---

## Étape 2 — Base de données

**Fichiers :**

- `database/migrations/041_saas_multitenant_core.sql` — schéma aligné produit : `organizations` (`status` inclut `trial` / `cancelled`, `plan_code`, `owner_user_id`), `memberships` (`role` owner/admin/editor/viewer, `status` active/invited/disabled), `tenant_settings` (id + `LONGTEXT`), `tenant_features` (`limit_value`), `usage_counters` (`usage_key`, `period_start` / `period_end`), graine `legacy-default`, CRM `organization_id`.
- `database/migrations/043_saas_schema_align_v2.sql` — **à exécuter si une ancienne variante de 041** (avec `is_default`, anciennes `tenant_*` / `usage_counters`) a déjà été appliquée : bascule sans perte de données là où c’est possible.

**Fixture optionnelle :** `database/migrations/042_saas_fixture_second_org.example.sql`

**Commandes types :**

```bash
mysql -u USER -p DB_NAME < database/migrations/041_saas_multitenant_core.sql
# Si ancienne 041 déjà en prod :
mysql -u USER -p DB_NAME < database/migrations/043_saas_schema_align_v2.sql
```

---

## Étape 3 — TenantContext

**Fichier :** `core/TenantContext.php`

- `activeOrganizationId()`, `organizationRole()`, `isSuperadmin()`, `viewingAllOrganizations()`.
- `crmLeadsTenantPredicate()`, `appendCrmLeadTenantFilter()`, `canAccessCrmLeadRow()`.
- `leadCaptureOrganizationId()` — org `is_default` pour les formulaires publics.
- `setViewAllOrganizations()` — réservé superadmin.

---

## Étape 4 — Session après login

**Fichier :** `core/helpers/saas_session.php`

- `saas_hydrate_organization_session(PDO)` — remplit `active_organization_id`, `organization_role`, `tenant_view_all` (superadmin).
- `saas_ensure_tenant_session(PDO)` — si org absente en session, ré-hydrate.

**Modifications :**

- `admin/login.php`, `public/admin/login.php` — appel hydrate après chaque connexion réussie.
- `admin/index.php`, `public/admin/index.php` — `saas_ensure_tenant_session(db())` ; `?tenant_scope=all|active` (superadmin) pour voir toutes les orgs ou filtrer sur l’org active.

**Session :** `user_id`, `user_role`, `user_email`, `user_name`, `active_organization_id`, `organization_role`, `tenant_view_all`.

**`core/Auth.php` :** `user()` enrichi ; `login()` accepte des clés optionnelles org (usage futur).

---

## Étape 5 — CRM / leads

**`core/services/LeadService.php`**

- Insertion avec `organization_id` (surcharge possible via `$payload['organization_id']`, sinon `TenantContext::leadCaptureOrganizationId()`).
- `list`, `updateRdvStatus`, `logInteraction`, `latestInteractionsByLead` respectent le tenant.
- `ensureOrganizationColumn()` si migration pas encore appliquée (mode tolérant).

**Modules :**

- `modules/contacts/accueil.php` — liste filtrée ; POST `delete_lead` + CSRF ; bouton Supprimer.
- `modules/contacts/vue.php` — détail uniquement si lead dans le tenant.
- `modules/crm-hub/accueil.php` — comptages par source filtrés.
- `modules/capturer/accueil.php` — via `LeadService::list` (déjà filtré).
- `modules/optimiser/analytics.php` — série leads filtrée.

**Non migré dans cette passe :** `MonthlyReportService` (cron / sans session tenant), `SequenceCrmService::getLead` (usage interne), messagerie — à traiter plus tard.

---

## Étape 6 — Tests (checklist)

À exécuter sur un environnement où `041` est appliquée :

| Test | Attendu |
|------|---------|
| Connexion admin classique | Session avec `active_organization_id` renseigné. |
| Connexion superadmin | Idem ; `tenant_view_all` initialisé à `false`. |
| `/admin/?module=contacts` | Liste = leads de l’org active uniquement. |
| Création lead (formulaire site ou script) | `organization_id` = org par défaut. |
| Détail / modification (capturer, vue contact) | Uniquement si lead dans l’org. |
| Suppression depuis liste contacts | DELETE avec prédicat tenant. |
| Utilisateur org A vs lead org B | Pas d’accès (sauf superadmin + `?tenant_scope=all`). |

**Tests automatisés :** non exécutés dans cet environnement (pas de MySQL de test branché ici). **À compléter chez vous.**

---

## Étape 7 — Fichiers modifiés / ajoutés

**Ajoutés**

- `database/migrations/041_saas_multitenant_core.sql`
- `database/migrations/042_saas_fixture_second_org.example.sql`
- `core/TenantContext.php`
- `core/helpers/saas_session.php`
- `docs/saas-migration-progress.md`

**Modifiés**

- `core/Auth.php`
- `core/bootstrap.php`
- `core/services/LeadService.php`
- `admin/index.php`, `public/admin/index.php`
- `admin/login.php`, `public/admin/login.php`
- `modules/contacts/accueil.php`, `modules/contacts/vue.php`
- `modules/crm-hub/accueil.php`
- `modules/optimiser/analytics.php`

---

## Prochaines étapes suggérées

1. Appliquer `041` sur staging puis production (sauvegarde BDD avant).
2. UI de sélection d’organisation (multi-membres) + invitations.
3. Rattacher `settings`, `biens`, `cms_*`, etc. à `organization_id`.
4. Facturation / quotas via `usage_counters` + `tenant_features`.
5. Filtrer rapports cron (`MonthlyReportService`) par org ou par utilisateur → org.

---

## Risques résiduels

- **Migration non appliquée :** filtres CRM sur `organization_id` peuvent échouer ou retourner vide si la colonne n’existe pas (mitigation partielle : `ensureOrganizationColumn`).
- **Login config `user_id = 1` :** doit exister dans `users` + `memberships` pour une hydratation cohérente (sinon org vide jusqu’à correction des données).
- **Superadmin « voir tout » :** uniquement via `?tenant_scope=all` (éviter l’exposition accidentelle en production).
