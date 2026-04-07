<?php
$pageTitle = 'Immobilier Aix-en-Provence | Bastides, Villas & Viager Éthique - Pascal Hamm';
$metaDesc = 'Découvrez les bastides provençales, villas contemporaines et solutions de viager éthique à Aix-en-Provence avec Pascal Hamm, expert immobilier 360°.';
$metaKeywords = 'immobilier Aix-en-Provence, bastide provençale, viager éthique, expert immobilier 360°, villa contemporaine Provence';

// Préremplissage sécurisé des champs de recherche (GET) pour améliorer l'UX.
$searchQuery = htmlspecialchars((string) ($_GET['query'] ?? ''), ENT_QUOTES, 'UTF-8');
$searchType = htmlspecialchars((string) ($_GET['type'] ?? ''), ENT_QUOTES, 'UTF-8');
$searchBudget = htmlspecialchars((string) ($_GET['budget'] ?? ''), ENT_QUOTES, 'UTF-8');

// Fallback local : dans votre implémentation finale, hydratez via repository + requêtes préparées PDO.
$latest_properties = $latest_properties ?? [
    [
        'id' => 101,
        'title' => 'Bastide avec piscine et oliveraie',
        'image' => '/assets/images/featured1.jpg',
        'alt_text' => 'Bastide provençale avec piscine à Aix-en-Provence',
        'type' => 'Exclusif',
        'price' => 980000,
        'city' => 'Aix-en-Provence',
        'rooms' => 5,
        'area' => 240,
        'expiry_date' => '2026-05-31',
    ],
    [
        'id' => 102,
        'title' => 'Villa contemporaine vue Sainte-Victoire',
        'image' => '/assets/images/featured2.jpg',
        'alt_text' => 'Villa contemporaine avec terrasse et vue',
        'type' => 'Villa',
        'price' => 1450000,
        'city' => 'Le Tholonet',
        'rooms' => 4,
        'area' => 210,
        'expiry_date' => '2026-05-14',
    ],
    [
        'id' => 103,
        'title' => 'Viager éthique au cœur du Pays d\'Aix',
        'image' => '/assets/images/property2.jpg',
        'alt_text' => 'Maison en viager éthique en Provence',
        'type' => 'Viager',
        'price' => 347000,
        'city' => 'Ventabren',
        'rooms' => 3,
        'area' => 140,
        'expiry_date' => '2026-04-30',
    ],
];
$total_properties = $total_properties ?? 127;

// Hook CRM : cet endpoint peut être remplacé par un connecteur interne (HubSpot, Pipedrive, etc.).
$crmEndpoint = '/send-contact';
?>

<style>
/* Cohérence avec l'existant + styles homepage neuromarketing */
.hero-section { background: linear-gradient(rgba(0,0,0,.5), rgba(0,0,0,.5)), url('/images/background-provence.jpg') center/cover no-repeat; color: #fff; min-height: 70vh; display: flex; align-items: center; }
.home-section { padding: 4rem 0; }
.home-container { width: min(1200px, 92%); margin: 0 auto; }
.text-shadow { text-shadow: 0 2px 12px rgba(0,0,0,.35); }
.cta-button { background: #d4a762; color: #fff; padding: 12px 24px; border-radius: 4px; border: 0; display: inline-block; text-decoration: none; font-weight: 600; transition: transform .2s ease, box-shadow .2s ease, background .2s ease; }
.cta-button:hover, .cta-button:focus-visible { transform: scale(1.05); box-shadow: 0 8px 25px rgba(0,0,0,.18); }
.cta-button.secondary { background: transparent; border: 1px solid #d4a762; color: #d4a762; }
.search-grid, .properties-grid, .story-grid, .services-grid, .contact-content, .footer-grid { display: grid; gap: 1rem; }
.search-grid { grid-template-columns: 1fr; background: rgba(255,255,255,.12); padding: 1rem; border-radius: 10px; backdrop-filter: blur(4px); }
.search-grid input, .search-grid select, .contact-form input, .contact-form select, .contact-form textarea { width: 100%; border: 1px solid #ddd; border-radius: 6px; padding: .75rem; }
.properties-grid { grid-template-columns: 1fr; }
.property-card { background: #fff; border-radius: 12px; overflow: hidden; box-shadow: 0 2px 12px rgba(0,0,0,.08); position: relative; }
.property-card:hover { box-shadow: 0 10px 30px rgba(0,0,0,.12); }
.property-badge { position: absolute; top: .75rem; left: .75rem; background: #d4a762; color: #fff; padding: .25rem .65rem; border-radius: 999px; z-index: 2; font-size: .85rem; }
.property-card img { width: 100%; height: 220px; object-fit: cover; }
.property-body { padding: 1rem; }
.benefits li::marker { content: '✔ '; color: #2ecc71; }
.story-image img { width: 100%; border-radius: 10px; }
.service-card, .testimonial-card, .faq-item { background: #fff; border-radius: 10px; padding: 1.25rem; box-shadow: 0 2px 10px rgba(0,0,0,.06); }
.testimonial-card { background: #f9f9f9; border-left: 4px solid #d4a762; }
.contact { background: #0f172a; color: #fff; }
.contact .cta-button[type='submit'], .contact-form .submit-btn { background: #2ecc71; }
.footer { background: #111827; color: #fff; }
.footer a { color: #f8d7a2; }
.faq-answer[hidden] { display: none; }

@media (min-width: 768px) {
  .search-grid { grid-template-columns: repeat(2, 1fr); }
  .properties-grid { grid-template-columns: repeat(2, 1fr); }
  .story-grid, .contact-content { grid-template-columns: 1.15fr .85fr; }
  .services-grid { grid-template-columns: repeat(2, 1fr); }
  .footer-grid { grid-template-columns: repeat(2, 1fr); }
}
@media (min-width: 1024px) {
  .search-grid { grid-template-columns: 2fr 1fr 1fr auto; }
  .properties-grid { grid-template-columns: repeat(3, 1fr); }
  .services-grid { grid-template-columns: repeat(3, 1fr); }
  .footer-grid { grid-template-columns: 1.2fr 1fr 1fr 1fr; }
}
</style>

<header>
    <section class="hero-section" aria-labelledby="hero-title">
        <div class="home-container">
            <h1 id="hero-title" class="text-shadow">Votre rêve immobilier en Provence commence ici</h1>
            <p class="text-shadow">Bastides, villas, viagers éthiques… Je transforme vos projets en réalité, sans stress et avec expertise.</p>

            <form id="hero-search" action="/search" method="GET" aria-label="Recherche de biens en Provence">
                <div class="search-grid">
                    <input type="text" name="query" value="<?= $searchQuery ?>" placeholder="Ex: Bastide avec piscine à Aix-en-Provence" aria-label="Recherche de biens">
                    <select name="type" aria-label="Type de bien recherché">
                        <option value="">Type de bien</option>
                        <option value="bastide" <?= $searchType === 'bastide' ? 'selected' : '' ?>>Bastide provençale</option>
                        <option value="villa" <?= $searchType === 'villa' ? 'selected' : '' ?>>Villa contemporaine</option>
                        <option value="viager" <?= $searchType === 'viager' ? 'selected' : '' ?>>Viager</option>
                    </select>
                    <select name="budget" aria-label="Budget">
                        <option value="">Budget</option>
                        <option value="300000-500000" <?= $searchBudget === '300000-500000' ? 'selected' : '' ?>>300k - 500k €</option>
                        <option value="500000-1000000" <?= $searchBudget === '500000-1000000' ? 'selected' : '' ?>>500k - 1M €</option>
                    </select>
                    <button type="submit" class="cta-button">Rechercher</button>
                </div>
            </form>

            <div style="display:flex;gap:.75rem;flex-wrap:wrap;margin-top:1rem">
                <a id="cta-primary-a" href="/search" class="cta-button">🔍 Trouvez votre bien idéal</a>
                <a id="cta-primary-b" href="/search?utm_ab=variantB" class="cta-button" hidden>🔍 Démarrer ma recherche exclusive</a>
                <a href="#contact" class="cta-button secondary">📞 Parlez à un expert</a>
            </div>
        </div>
    </section>
</header>

<main>
    <section class="home-section opportunities" aria-labelledby="opportunities-heading">
        <div class="home-container">
            <h2 id="opportunities-heading">Dernières opportunités en Provence</h2>
            <p class="subtitle">Ces biens ne resteront pas longtemps sur le marché. Découvrez-les avant qu'il ne soit trop tard.</p>

            <div class="properties-grid">
                <?php foreach ($latest_properties as $property): ?>
                    <article class="property-card" itemscope itemtype="https://schema.org/RealEstateListing">
                        <meta itemprop="name" content="<?= htmlspecialchars($property['title'], ENT_QUOTES, 'UTF-8') ?>">
                        <img src="<?= htmlspecialchars($property['image'], ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($property['alt_text'], ENT_QUOTES, 'UTF-8') ?>" loading="lazy" width="640" height="420">
                        <div class="property-badge"><?= htmlspecialchars($property['type'], ENT_QUOTES, 'UTF-8') ?></div>
                        <div class="property-body">
                            <h3 itemprop="name"><?= htmlspecialchars($property['title'], ENT_QUOTES, 'UTF-8') ?></h3>
                            <p class="price" itemprop="offers" itemscope itemtype="https://schema.org/Offer">
                                <span itemprop="priceCurrency" content="EUR">€</span>
                                <span itemprop="price"><?= number_format((float) $property['price'], 0, ',', ' ') ?></span>
                                <meta itemprop="priceValidUntil" content="<?= htmlspecialchars($property['expiry_date'], ENT_QUOTES, 'UTF-8') ?>">
                            </p>
                            <p class="location" itemprop="address" itemscope itemtype="https://schema.org/PostalAddress">
                                <span itemprop="addressLocality"><?= htmlspecialchars($property['city'], ENT_QUOTES, 'UTF-8') ?></span>
                            </p>
                            <ul class="features">
                                <li><span itemprop="numberOfRooms"><?= (int) $property['rooms'] ?></span> chambres</li>
                                <li><span itemprop="floorSize" itemscope itemtype="https://schema.org/QuantitativeValue"><span itemprop="value"><?= (int) $property['area'] ?></span> <span itemprop="unitText">m²</span></span></li>
                            </ul>
                            <div class="cta-container">
                                <a href="/biens/<?= (int) $property['id'] ?>" class="cta-button">🔥 Offre exclusive</a>
                                <small class="urgency">Disponible jusqu'au <?= date('d/m', strtotime((string) $property['expiry_date'])) ?></small>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>

            <p style="margin-top:1rem"><a href="/biens" class="cta-button secondary">Voir tous les biens (<?= (int) $total_properties ?>)</a></p>
        </div>
    </section>

    <section class="home-section storytelling" aria-labelledby="story-heading">
        <div class="home-container story-content">
            <h2 id="story-heading">Pourquoi choisir un expert 360° pour votre projet immobilier ?</h2>
            <p class="intro">Imaginez : vous rêvez d'une bastide provençale avec vue sur le Luberon, mais vous ne savez pas par où commencer...</p>
            <div class="story-grid">
                <div class="story-text">
                    <p>Les agences classiques vous font perdre du temps, les annonces en ligne sont floues, et les démarches administratives vous donnent mal à la tête.</p>
                    <p>C'est pour cela que j'ai créé <strong>BeckHamm Real Estate</strong> : une approche <strong>sans stress, transparente et sur mesure</strong>.</p>
                    <ul class="benefits">
                        <li><strong>Recherche ciblée</strong> (y compris des biens non publiés)</li>
                        <li><strong>Négociation optimisée</strong> (économie moyenne de 10-15%)</li>
                        <li><strong>Financement sur mesure</strong> avec partenaires courtiers</li>
                        <li><strong>Gestion complète</strong> : notaire, travaux, mise en location</li>
                    </ul>
                    <div class="cta-container" style="display:flex;gap:.75rem;flex-wrap:wrap">
                        <a href="/services" class="cta-button">📩 Recevoir mon guide gratuit</a>
                        <a href="#contact" class="cta-button secondary">🗓️ Réserver un appel découverte</a>
                    </div>
                </div>
                <figure class="story-image">
                    <img src="/images/pascal-hamm-story.jpg" alt="Pascal Hamm en Provence avec un client" loading="lazy" width="700" height="500">
                    <figcaption>Pascal Hamm accompagnant un client dans une bastide provençale.</figcaption>
                </figure>
            </div>
        </div>
    </section>

    <section class="home-section services" aria-labelledby="services-heading">
        <div class="home-container">
            <h2 id="services-heading">Votre projet immobilier, géré de A à Z – Sans stress ni tracas</h2>
            <div class="services-grid">
                <article class="service-card">
                    <div class="service-icon" aria-hidden="true">🔑</div>
                    <h3>Immobilier clef en main Aix-en-Provence</h3>
                    <p>Je m'occupe de tout : recherche, visites, négociation, signature.</p>
                    <p class="proof"><strong>90%</strong> de mes clients signent en moins de 2 mois.</p>
                    <a href="/services/clef-en-main" class="cta-button">🔑 Découvrir le Clef en Main</a>
                </article>
                <article class="service-card">
                    <div class="service-icon" aria-hidden="true">💰</div>
                    <h3>Financement immobilier sur mesure</h3>
                    <p>Accès à des prêts avantageux, même pour les dossiers complexes.</p>
                    <p class="proof">Taux négociés jusqu'à <strong>-0,5%</strong> vs le marché.</p>
                    <a href="/services/financement" class="cta-button">💰 Obtenir une simulation gratuite</a>
                </article>
                <article class="service-card">
                    <div class="service-icon" aria-hidden="true">🏆</div>
                    <h3>Viager éthique Aix-en-Provence</h3>
                    <p>Montages sécurisés pour vendeurs et acquéreurs, en toute transparence.</p>
                    <a href="/services/viager" class="cta-button">🏆 En savoir plus sur le viager</a>
                </article>
            </div>
        </div>
    </section>

    <section class="home-section testimonials" aria-labelledby="testimonials-heading">
        <div class="home-container">
            <h2 id="testimonials-heading">Ils ont réalisé leur projet immobilier avec nous</h2>
            <div class="testimonials-slider">
                <article class="testimonial-card">
                    <div class="testimonial-header" style="display:flex;gap:.75rem;align-items:center">
                        <img src="/images/clients/jean-marie.jpg" alt="Jean et Marie D." loading="lazy" width="72" height="72" style="border-radius:50%;object-fit:cover">
                        <div>
                            <h4>Jean &amp; Marie D.</h4>
                            <p>Acheteurs d'une bastide provençale</p>
                        </div>
                    </div>
                    <blockquote>
                        <p>🏡 Grâce à Pascal, nous avons acheté notre bastide à Lourmarin 12% en dessous du prix du marché.</p>
                    </blockquote>
                    <div class="testimonial-footer" style="display:flex;gap:.75rem;flex-wrap:wrap">
                        <a href="/avis" class="cta-button secondary">📢 Voir tous les avis</a>
                        <a href="#" class="cta-button">🎥 Voir la vidéo témoignage</a>
                    </div>
                </article>
            </div>
        </div>
    </section>

    <section id="contact" class="home-section contact" aria-labelledby="contact-heading">
        <div class="home-container contact-content">
            <div class="contact-text">
                <h2 id="contact-heading">Contact expert immobilier Aix-en-Provence</h2>
                <p>Un expert vous répond sous 24h pour vous aider à concrétiser votre rêve.</p>
                <p><strong>📞 Téléphone :</strong> <a href="tel:+33667198366">+33 6 67 19 83 66</a></p>
                <p><strong>✉️ Email :</strong> <a href="mailto:pascal.hamm@expfrance.fr">pascal.hamm@expfrance.fr</a></p>
                <p><strong>📍 Adresse :</strong> Aix-en-Provence, Pays d'Aix</p>
            </div>

            <form id="contact-form" action="<?= htmlspecialchars($crmEndpoint, ENT_QUOTES, 'UTF-8') ?>" method="POST" class="contact-form" aria-label="Formulaire de contact">
                <input type="hidden" name="crm_source" value="homepage-2026-v2">
                <input type="hidden" name="ab_variant" id="ab_variant" value="A">
                <div class="form-group">
                    <label for="name">Votre nom *</label>
                    <input type="text" id="name" name="name" required aria-required="true" maxlength="120">
                </div>
                <div class="form-group">
                    <label for="email">Votre email *</label>
                    <input type="email" id="email" name="email" required aria-required="true" maxlength="190">
                </div>
                <div class="form-group">
                    <label for="phone">Votre téléphone</label>
                    <input type="tel" id="phone" name="phone" placeholder="+33 6 12 34 56 78">
                </div>
                <div class="form-group">
                    <label for="project">Type de projet *</label>
                    <select id="project" name="project" required aria-required="true">
                        <option value="">Sélectionnez...</option>
                        <option value="achat">Achat</option>
                        <option value="vente">Vente</option>
                        <option value="viager">Viager</option>
                        <option value="investissement">Investissement</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="message">Votre message *</label>
                    <textarea id="message" name="message" rows="4" required aria-required="true" placeholder="Décrivez votre projet en 2-3 lignes..."></textarea>
                </div>
                <!-- Hook anti-spam : remplacer par hCaptcha/Turnstile côté serveur. -->
                <input type="text" name="website" tabindex="-1" autocomplete="off" aria-hidden="true" style="position:absolute;left:-9999px">
                <div class="form-group checkbox">
                    <input type="checkbox" id="privacy" name="privacy" required aria-required="true">
                    <label for="privacy">J'accepte la <a href="/confidentialite">politique de confidentialité</a> *</label>
                </div>
                <button type="submit" class="cta-button submit-btn">🚀 Envoyer ma demande</button>
                <p class="guarantee">✅ Réponse sous 24h ou votre étude gratuite !</p>
            </form>
        </div>
    </section>

    <section class="home-section faq" aria-labelledby="faq-heading">
        <div class="home-container">
            <h2 id="faq-heading">Questions fréquentes sur l'immobilier en Provence</h2>
            <div class="faq-container">
                <article class="faq-item">
                    <h3>
                        <button class="faq-question" aria-expanded="false" aria-controls="faq1">Comment fonctionne le viager éthique ?</button>
                    </h3>
                    <div id="faq1" class="faq-answer" hidden>
                        <p>Le viager éthique permet une acquisition sécurisée, avec un cadre contractuel clair et un accompagnement juridique complet.</p>
                        <a href="/viager-ethique" class="cta-button secondary">En savoir plus</a>
                    </div>
                </article>
                <article class="faq-item">
                    <h3>
                        <button class="faq-question" aria-expanded="false" aria-controls="faq2">Quels sont les avantages d'un expert immobilier 360° ?</button>
                    </h3>
                    <div id="faq2" class="faq-answer" hidden>
                        <p>Vous centralisez recherche, négociation, financement et accompagnement notarial avec un interlocuteur unique.</p>
                    </div>
                </article>
            </div>
        </div>
    </section>
</main>

<footer class="footer home-section">
    <div class="home-container">
        <div class="footer-grid">
            <div class="footer-logo">
                <img src="/images/logo-beckhamm.svg" alt="BeckHamm Real Estate" width="150" loading="lazy">
                <p>Expert immobilier indépendant dans le Pays d'Aix, spécialisé en biens d'exception, estimation et viager éthique.</p>
            </div>
            <div class="footer-links">
                <h4>Services</h4>
                <ul>
                    <li><a href="/services/clef-en-main">Immobilier Clef en Main</a></li>
                    <li><a href="/services/viager">Viager Éthique</a></li>
                    <li><a href="/services/financement">Solutions de Financement</a></li>
                </ul>
            </div>
            <div class="footer-links">
                <h4>Informations</h4>
                <ul>
                    <li><a href="/a-propos">À propos de Pascal</a></li>
                    <li><a href="/blog">Blog immobilier Provence</a></li>
                    <li><a href="/guide-local">Guide local Aix-en-Provence</a></li>
                    <li><a href="/avis">Avis clients</a></li>
                </ul>
            </div>
            <div class="footer-contact">
                <h4>Contact</h4>
                <p>📍 Aix-en-Provence, Pays d'Aix</p>
                <p>📞 <a href="tel:+33667198366">06 67 19 83 66</a></p>
                <p>✉️ <a href="mailto:pascal.hamm@expfrance.fr">pascal.hamm@expfrance.fr</a></p>
            </div>
        </div>
        <div class="footer-bottom" style="margin-top:1rem;border-top:1px solid rgba(255,255,255,.15);padding-top:1rem">
            <p>© <?= date('Y') ?> Pascal Hamm — Tous droits réservés — SIRET : 12345678901234</p>
        </div>
    </div>
</footer>

<script type="application/ld+json">
<?= json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'RealEstateAgent',
    'name' => 'Pascal Hamm - BeckHamm Real Estate',
    'description' => 'Expert immobilier indépendant spécialisé dans les biens d\'exception en Provence et en Espagne.',
    'address' => [
        '@type' => 'PostalAddress',
        'streetAddress' => 'Aix-en-Provence, Pays d\'Aix',
        'addressLocality' => 'Aix-en-Provence',
        'addressRegion' => 'Provence-Alpes-Côte d\'Azur',
        'postalCode' => '13100',
        'addressCountry' => 'FR',
    ],
    'telephone' => '+33667198366',
    'email' => 'pascal.hamm@expfrance.fr',
    'url' => 'https://www.pascal-hamm-immobilier.fr',
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>
</script>
<script type="application/ld+json">
<?= json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'FAQPage',
    'mainEntity' => [
        [
            '@type' => 'Question',
            'name' => 'Comment fonctionne le viager éthique ?',
            'acceptedAnswer' => [
                '@type' => 'Answer',
                'text' => 'Le viager éthique est une solution encadrée avec accompagnement juridique et financier personnalisé.',
            ],
        ],
        [
            '@type' => 'Question',
            'name' => 'Quels sont les avantages d\'acheter avec un expert 360° ?',
            'acceptedAnswer' => [
                '@type' => 'Answer',
                'text' => 'Vous centralisez recherche, négociation, financement et suivi administratif avec un interlocuteur unique.',
            ],
        ],
    ],
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>
</script>
<script>
// Hook A/B testing basique (peut être branché à Google Optimize, AB Tasty, etc.).
(function() {
  const variant = Math.random() < 0.5 ? 'A' : 'B';
  const a = document.getElementById('cta-primary-a');
  const b = document.getElementById('cta-primary-b');
  const field = document.getElementById('ab_variant');
  if (variant === 'B' && a && b) {
    a.hidden = true;
    b.hidden = false;
  }
  if (field) field.value = variant;
})();

document.querySelectorAll('.faq-question').forEach((button) => {
  button.addEventListener('click', () => {
    const answer = document.getElementById(button.getAttribute('aria-controls'));
    const expanded = button.getAttribute('aria-expanded') === 'true';
    button.setAttribute('aria-expanded', expanded ? 'false' : 'true');
    if (answer) {
      answer.hidden = expanded;
    }
  });
});
</script>
