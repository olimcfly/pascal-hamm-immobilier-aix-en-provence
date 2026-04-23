# Référentiel officiel — Pages modules CRM IMMO LOCAL+

> Standard de référence pour toutes les nouvelles pages modules.
> Basé sur les meilleures pages existantes : `attirer`, `seo`, `construire`, `capture`.
> CSS system : `hub-unified.css` (déjà chargé dans le layout).

---

## 1. STRUCTURE STANDARD D'UNE PAGE MODULE

```
hub-page
├── hub-hero            ← header foncé + badge doré + titre + sous-titre
├── hub-narrative       ← 3 cards pédagogiques (Réalité / Résultat / À éviter)
├── [KPIs si pertinent] ← grille de chiffres clés
├── contenu principal   ← hub-modules-grid, hub-pillars, tableaux, formulaires
└── hub-final-cta       ← étape suivante, lien vers action principale
```

**Règle** : toute page module commence par `.hub-page` comme wrapper.
**Règle** : les pages d'action (formulaires, tables, CMS) gardent la même structure hero + narrative, puis leur contenu fonctionnel.

---

## 2. COMPOSANTS AUTORISÉS ET RECOMMANDÉS

### Header de page
```html
<header class="hub-hero">
    <div class="hub-hero-badge"><i class="fas fa-[icon]"></i> Catégorie</div>
    <h1>Titre du module</h1>
    <p>Sous-titre explicatif orienté bénéfice (max 1-2 lignes).</p>
</header>
```

### Bloc pédagogique (narrative)
```html
<div class="hub-narrative">
    <article class="hub-narrative-card hub-narrative-card--motivation">
        <h3><i class="fas fa-bolt" style="color:#f59e0b"></i> Le constat</h3>
        <p>...</p>
    </article>
    <article class="hub-narrative-card hub-narrative-card--resultat">
        <h3><i class="fas fa-check-circle" style="color:#10b981"></i> Le résultat visé</h3>
        <p>...</p>
    </article>
    <article class="hub-narrative-card hub-narrative-card--action">
        <h3><i class="fas fa-triangle-exclamation" style="color:#ef4444"></i> Le risque à éviter</h3>
        <p>...</p>
    </article>
</div>
```
Variantes de bordure : `--motivation` (orange), `--explanation` (bleu), `--resultat` (vert), `--action` (rouge).

### Grille de modules
```html
<div class="hub-modules-grid">
    <a href="..." class="hub-module-card">
        <div class="hub-module-card-head">
            <div class="hub-module-card-icon" style="background:#eff6ff;color:#1d4ed8">
                <i class="fas fa-[icon]"></i>
            </div>
            <h3>Nom de l'action</h3>
            <span class="hub-state hub-state--available">Disponible</span>
        </div>
        <p>Description courte orientée bénéfice.</p>
        <small class="hub-module-card-action"><i class="fas fa-arrow-right"></i> Accéder</small>
    </a>
</div>
```

### Boutons
```html
<!-- Principal (doré) -->
<a href="..." class="hub-btn hub-btn--gold"><i class="fas fa-plus"></i> Action principale</a>

<!-- Secondaire (foncé) -->
<button class="hub-btn"><i class="fas fa-save"></i> Enregistrer</button>

<!-- Ghost (clair) -->
<button class="hub-btn" style="background:#f1f5f9;color:#334155;">Annuler</button>

<!-- Petit -->
<button class="hub-btn hub-btn--sm">...</button>
```

### Empty state standard
```html
<div style="padding:3rem 1rem;text-align:center;color:#94a3b8">
    <i class="fas fa-inbox fa-2x" style="opacity:.25;display:block;margin-bottom:.6rem"></i>
    <div style="font-size:.88rem">Aucun élément pour le moment.</div>
    <div style="font-size:.82rem;margin-top:.3rem">
        <a href="..." class="hub-btn hub-btn--sm hub-btn--gold" style="margin-top:.8rem">Créer le premier</a>
    </div>
</div>
```

### CTA de fin / étape suivante
```html
<div class="hub-final-cta">
    <div>
        <h2>Prochaine étape</h2>
        <p>Texte d'accompagnement, 1-2 lignes maximum.</p>
    </div>
    <a href="..." class="hub-btn hub-btn--gold"><i class="fas fa-arrow-right"></i> Continuer</a>
</div>
```

### Progress bar
```html
<div class="hub-progress">
    <div class="hub-progress-head">
        <strong>Votre progression</strong>
        <span>2 / 5 étapes complétées</span>
    </div>
    <div class="hub-progress-track">
        <div class="hub-progress-bar" style="width:40%"></div>
    </div>
</div>
```

---

## 3. RÈGLES DE HIÉRARCHIE VISUELLE

| Niveau | Élément | Style |
|--------|---------|-------|
| H1 | Titre du module | Dans `.hub-hero`, couleur blanc |
| H2 | Titre de section | `font-size:1.1-1.2rem; color:#0f172a` |
| H3 | Titre de carte | `font-size:1rem; color:#0f172a` ou `var(--hub-navy)` |
| Corps | Texte description | `color:#4b5563; font-size:.88-.93rem; line-height:1.55` |
| Méta | Infos secondaires | `color:#64748b; font-size:.78-.82rem` |

**Règle** : jamais plus de 3 niveaux de titres visibles simultanément.
**Règle** : les H1 n'existent que dans le `.hub-hero`.

---

## 4. RÈGLES D'ESPACEMENT

| Contexte | Valeur |
|----------|--------|
| Gap entre sections principales | `1.2rem` (var `--hub-gap`) |
| Padding carte | `1rem 1.1rem` (compact) ou `1.25rem 1.4rem` (confort) |
| Gap grille modules | `1rem` |
| Gap narrative | `.9rem` |
| Border-radius carte | `16px` (var `--hub-radius`) |
| Border-radius éléments secondaires | `14px` (var `--hub-radius-md`) ou `12px` (var `--hub-radius-sm`) |

---

## 5. TON ÉDITORIAL DE MODULE

**Principe** : parler à l'utilisateur comme un coach, pas comme un développeur.

| ✅ À faire | ❌ À éviter |
|-----------|-----------|
| "Gérez vos contacts de prospection" | "CRUD contacts table" |
| "Chaque visiteur qui remplit le formulaire arrive ici" | "Données du formulaire stockées en base" |
| "Répondez rapidement — un lead contacté dans l'heure convertit 7x mieux" | "Status: nouveau / en_cours / traite" |
| "Prochaine étape : créer votre première campagne" | (pas de guidage) |

**Structure narrative recommandée (méthode MERE)** :
- **Constat** : La réalité actuelle sans l'outil
- **Résultat** : Ce que l'utilisateur obtient avec
- **À éviter** : L'erreur fréquente à ne pas commettre

---

## 6. MOBILE-FIRST

```css
/* Base : 1 colonne sur mobile */
.mon-grid { display: grid; gap: 1.2rem; }

/* Tablette (768px) : 2 colonnes */
@media (min-width: 768px) {
    .mon-grid { grid-template-columns: repeat(2, 1fr); }
}

/* Desktop (1100px) : 3-4 colonnes */
@media (min-width: 1100px) {
    .mon-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); }
}
```

**Règle** : les tableaux avec `min-width` doivent être dans un wrapper `overflow-x:auto`.
**Règle** : les grilles 2-colonnes de formulaires doivent passer en 1 colonne sous 700px.
**Règle** : le hero `hub-hero` passe de `padding:1.5rem 1.15rem` à `padding:2rem 2.1rem` au-delà de 768px (déjà géré dans hub-unified.css).

---

## 7. PATTERN INTRODUCTION PÉDAGOGIQUE

Chaque page module doit expliquer sa raison d'être en 3 points.
Format recommandé : 3 cards `.hub-narrative-card` dans une `.hub-narrative`.

| Card | Couleur | Icône recommandée | Contenu |
|------|---------|-------------------|---------|
| Constat / Réalité | Orange `#f59e0b` | `fa-bolt` | Pourquoi ce problème existe |
| Résultat / Solution | Vert `#10b981` | `fa-check-circle` | Ce que l'outil apporte concrètement |
| Risque / Attention | Rouge `#ef4444` | `fa-triangle-exclamation` | L'erreur à ne pas faire |

Variante : remplacer "Risque" par "Conseil" ou "Comment utiliser" si plus adapté.

---

## 8. PATTERN EMPTY STATE

```html
<div style="padding:3rem 1rem;text-align:center;color:#94a3b8">
    <i class="fas fa-[icon-contextuel] fa-2x" style="opacity:.25;display:block;margin-bottom:.6rem"></i>
    <div style="font-size:.88rem;font-weight:600">Aucun [élément] pour le moment.</div>
    <div style="font-size:.82rem;margin-top:.3rem">[Explication en 1 ligne de ce qui apparaîtra ici.]</div>
    <!-- Optionnel : CTA direct -->
    <a href="..." class="hub-btn hub-btn--gold hub-btn--sm" style="margin-top:1rem">
        <i class="fas fa-plus"></i> Créer le premier
    </a>
</div>
```

Icônes contextuelles recommandées : `fa-inbox` (messages/logs), `fa-users` (contacts), `fa-bullhorn` (campagnes), `fa-file-lines` (pages), `fa-handshake` (partenaires).

---

## 9. CHARTE VISUELLE — VARIABLES CSS DISPONIBLES

```css
var(--hub-navy)         /* #0f2237 — fond hero */
var(--hub-navy-mid)     /* #1a3a5c */
var(--hub-gold)         /* #c9a84c — accent gold, boutons principaux */
var(--hub-surface)      /* #ffffff */
var(--hub-border)       /* #e2e8f0 */
var(--hub-border-soft)  /* #ecf0f6 */
var(--hub-bg-soft)      /* #fbfdff */
var(--hub-text)         /* #111827 */
var(--hub-text-muted)   /* #64748b */
var(--hub-text-body)    /* #4b5563 */
var(--hub-shadow-sm)    /* 0 1px 8px rgba(15,23,42,.08) */
var(--hub-radius)       /* 16px */
var(--hub-radius-md)    /* 14px */
var(--hub-radius-sm)    /* 12px */
var(--hub-radius-btn)   /* 9px */
var(--hub-gap)          /* 1.2rem */
```

---

## 10. CHECKLIST POUR TOUTE NOUVELLE PAGE MODULE

- [ ] `$pageTitle` défini en haut du fichier
- [ ] `renderContent(): void` (jamais `string`)
- [ ] POST handling au **niveau fichier** (avant `renderContent()`) si redirections nécessaires
- [ ] Wrapper `.hub-page` en premier élément rendu
- [ ] `.hub-hero` avec badge + H1 + sous-titre
- [ ] `.hub-narrative` avec 3 cards contextuelles
- [ ] Empty states sur tous les contenus conditionnels
- [ ] Tables avec wrapper `overflow-x:auto`
- [ ] Boutons via `.hub-btn` (pas inline styles arbitraires)
- [ ] Responsive testé : mobile (1 col), tablette (2 col), desktop (3-4 col)
- [ ] Aucun `require_once layout.php` dans le module (interdit)
- [ ] Routes `/admin?module=` (jamais `/admin/index.php?module=`)

---

## 11. EXEMPLES DE SECTIONS TYPES

### Section avec piliers (3 colonnes)
```html
<div class="hub-pillars">
    <div class="hub-pillar">
        <h2><i class="fas fa-[icon]" style="color:#3b82f6"></i> Titre</h2>
        <p>Sous-titre</p>
        <div class="hub-items">
            <div class="hub-item">
                <div class="hub-item-head">
                    <h3>Sous-élément</h3>
                    <span class="hub-state hub-state--available">Disponible</span>
                </div>
                <p>Description</p>
                <a href="..." class="hub-btn hub-btn--sm">Accéder</a>
            </div>
        </div>
    </div>
</div>
```

### KPIs (grille)
```html
<div style="display:grid;grid-template-columns:repeat(2,1fr);gap:.85rem">
    <!-- Sur ≥680px : repeat(4,1fr) via media query -->
    <div style="background:#fff;border:1px solid var(--hub-border);border-radius:var(--hub-radius-md);padding:1rem 1.2rem;box-shadow:var(--hub-shadow-sm)">
        <div style="font-size:.72rem;text-transform:uppercase;letter-spacing:.06em;color:#64748b;font-weight:700">Label</div>
        <div style="font-size:2rem;font-weight:800;color:#0f172a;line-height:1">42</div>
        <div style="font-size:.78rem;color:#64748b;margin-top:.15rem">sous-label</div>
    </div>
</div>
```

---

*Dernière mise à jour : 2026-04-11 — Standard établi à partir de l'audit complet des 27 modules.*
