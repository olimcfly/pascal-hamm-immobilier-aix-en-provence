<?php
$pageTitle = 'Immobilier Clef en Main & Financement - Pascal Hamm Immobilier';
$metaDesc = 'Pascal Hamm, votre allié 360° en immobilier clef en main : achat, vente, viager et financement. Spécialiste des biens d\'exception en Provence et Espagne.';
?>

<!-- ── Hero ────────────────────────────────────────────────── -->
<section class="hero hero--full">
    <div class="hero__bg" style="background-image:url('/assets/images/hero-provence.jpg')"></div>
    <div class="container">
        <div class="hero__content">
            <div class="hero__tagline" data-animate>
                <span>Rien ne se perd, tout se transforme</span>
                <h1>Immobilier Clef en Main<br>Partenaire en Financements</h1>
                <p>Pascal Hamm, votre allié 360° pour tous vos projets immobiliers</p>
            </div>

            <div class="hero__cta" data-animate>
                <a href="#contact" class="btn btn--accent btn--lg">Entrer en contact</a>
                <a href="#biens" class="btn btn--outline-white btn--lg">Trouvez le bien de vos rêves</a>
            </div>

            <div class="hero__social">
                <a href="https://www.facebook.com/people/Pascal-Hamm/" class="social-link" aria-label="Facebook">
                    <svg><use xlink:href="#icon-facebook"></use></svg>
                </a>
                <a href="https://www.instagram.com/pascal.hamm2025" class="social-link" aria-label="Instagram">
                    <svg><use xlink:href="#icon-instagram"></use></svg>
                </a>
                <a href="https://www.linkedin.com/in/pascal-hamm2025immobilierclefenmaincourtagefinancier" class="social-link" aria-label="LinkedIn">
                    <svg><use xlink:href="#icon-linkedin"></use></svg>
                </a>
            </div>
        </div>
    </div>
</section>

<!-- ── Search Bar ───────────────────────────────────────────── -->
<section class="search-bar">
    <div class="container">
        <form class="property-search" data-animate>
            <div class="search-group">
                <select name="transaction">
                    <option value="all">Acheter</option>
                    <option value="buy">Acheter</option>
                    <option value="rent">Louer</option>
                    <option value="viager">Viager</option>
                </select>
            </div>

            <div class="search-group">
                <select name="type">
                    <option value="all">Type de Bien</option>
                    <option value="house">Maison</option>
                    <option value="apartment">Appartement</option>
                    <option value="villa">Villa</option>
                    <option value="domain">Domaine</option>
                </select>
            </div>

            <div class="search-group">
                <input type="text" name="location" placeholder="Ville (ex: Aix-en-Provence)">
            </div>

            <div class="search-group">
                <select name="bedrooms">
                    <option value="all">Chambres</option>
                    <option value="1">1+</option>
                    <option value="2">2+</option>
                    <option value="3">3+</option>
                    <option value="4">4+</option>
                </select>
            </div>

            <div class="search-group">
                <select name="bathrooms">
                    <option value="all">Salles de Bains</option>
                    <option value="1">1+</option>
                    <option value="2">2+</option>
                    <option value="3">3+</option>
                </select>
            </div>

            <div class="search-group">
                <input type="number" name="min-area" placeholder="Min m²">
            </div>

            <div class="search-group price-range">
                <input type="number" name="min-price" placeholder="Prix min">
                <span>à</span>
                <input type="number" name="max-price" placeholder="Prix max">
            </div>

            <button type="submit" class="btn btn--accent">Rechercher</button>
        </form>
    </div>
</section>

<!-- ── Property Listings ────────────────────────────────────── -->
<section class="section" id="biens">
    <div class="container">
        <div class="section__header">
            <h2 class="section-title">Notre sélection de biens</h2>
            <div class="sort-options">
                <span>Trier par:</span>
                <select>
                    <option>Par défaut</option>
                    <option>Prix croissant</option>
                    <option>Prix décroissant</option>
                    <option>Surface</option>
                    <option>Date d'ajout</option>
                </select>
            </div>
        </div>

        <div class="property-grid">
            <!-- Property Card Example -->
            <div class="property-card" data-animate>
                <div class="property-badge">Viager</div>
                <div class="property-image">
                    <img src="/assets/images/property1.jpg" alt="Appartement T5 en viager">
                    <div class="property-price">€90 000 <span>1/6</span></div>
                </div>
                <div class="property-details">
                    <h3>VIAGER - APPARTEMENT T5</h3>
                    <p class="property-location">Limoges</p>
                    <div class="property-features">
                        <span>3 Chambres</span>
                        <span>98 m²</span>
                    </div>
                    <a href="#" class="btn btn--outline">Voir les détails</a>
                </div>
            </div>

            <!-- More property cards would be dynamically loaded -->
            <div class="property-card" data-animate>
                <div class="property-badge">Viager Éthique</div>
                <div class="property-image">
                    <img src="/assets/images/property2.jpg" alt="Viager Saint-Médard-en-Jalles">
                    <div class="property-price">€347 000 <span>1/13</span></div>
                </div>
                <div class="property-details">
                    <h3>Viager occupé sans rente</h3>
                    <p class="property-location">Saint-Médard-en-Jalles</p>
                    <div class="property-features">
                        <span>2 Chambres</span>
                        <span>1 Salle de bain</span>
                        <span>104.23 m²</span>
                    </div>
                    <a href="#" class="btn btn--outline">Voir les détails</a>
                </div>
            </div>
            <!-- End of example cards -->
        </div>

        <div class="pagination">
            <span>Affichage de 1-10 sur 3274 propriétés</span>
            <div class="pagination-controls">
                <button class="btn btn--outline">Précédent</button>
                <span>1</span>
                <span>2</span>
                <span>3</span>
                <span>4</span>
                <span>5</span>
                <span>...</span>
                <span>328</span>
                <button class="btn btn--outline">Suivant</button>
            </div>
        </div>
    </div>
</section>

<!-- ── About Section ────────────────────────────────────────── -->
<section class="section section--alt">
    <div class="container">
        <div class="grid-2">
            <div class="about-content" data-animate>
                <span class="section-label">Bienvenue chez Pascal Hamm Immobilier</span>
                <h2 class="section-title">Résidence de charme & prestige en Provence</h2>
                <p class="section-subtitle">
                    Spécialiste des biens d'exception en Provence et en Espagne, j'accompagne une clientèle exigeante à la recherche de propriétés uniques.
                </p>
                <p>
                    Bastides provençales, villas contemporaines, appartements de standing, domaines viticoles ou résidences en bord de mer - mon expertise couvre tous les types de biens d'exception.
                </p>
                <p>
                    Grâce à ma connaissance approfondie du marché local et à un réseau exclusif, je mets à votre service toute mon expertise pour concrétiser vos projets immobiliers, que vous souhaitiez acquérir, vendre ou investir.
                </p>
                <p>
                    <strong>Discrétion, écoute et accompagnement personnalisé</strong> sont au cœur de ma démarche - votre rêve devient réalité.
                </p>
            </div>
            <div class="about-image" data-animate>
                <img src="/assets/images/pascal-hamm-provence.jpg" alt="Pascal Hamm - Expert immobilier">
                <div class="signature">
                    <img src="/assets/images/signature.png" alt="Signature Pascal Hamm">
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ── Services 360° ────────────────────────────────────────── -->
<section class="section">
    <div class="container">
        <div class="section__header text-center" data-animate>
            <span class="section-label">Notre approche 360°</span>
            <h2 class="section-title">Immobilier Clef en Main & Solutions de Financement</h2>
            <p class="section-subtitle">
                Une expertise complète pour tous vos projets immobiliers
            </p>
        </div>

        <div class="services-grid">
            <div class="service-card" data-animate>
                <div class="service-icon">
                    <svg><use xlink:href="#icon-keys"></use></svg>
                </div>
                <h3>Immobilier Clef en Main</h3>
                <p>De la recherche à la signature, je gère l'intégralité de votre projet immobilier pour une expérience sans stress.</p>
            </div>

            <div class="service-card" data-animate>
                <div class="service-icon">
                    <svg><use xlink:href="#icon-finance"></use></svg>
                </div>
                <h3>Partenaire en Financements</h3>
                <p>Accès à des solutions de financement sur mesure et optimisées pour votre projet.</p>
            </div>

            <div class="service-card" data-animate>
                <div class="service-icon">
                    <svg><use xlink:href="#icon-viager"></use></svg>
                </div>
                <h3>Expertise Viager</h3>
                <p>Spécialiste du viager éthique avec des solutions adaptées à chaque situation.</p>
            </div>

            <div class="service-card" data-animate>
                <div class="service-icon">
                    <svg><use xlink:href="#icon-luxury"></use></svg>
                </div>
                <h3>Biens d'Exception</h3>
                <p>Accès à un portefeuille exclusif de propriétés haut de gamme en Provence et en Espagne.</p>
            </div>

            <div class="service-card" data-animate>
                <div class="service-icon">
                    <svg><use xlink:href="#icon-investment"></use></svg>
                </div>
                <h3>Investissement Immobilier</h3>
                <p>Conseil en investissement locatif et patrimonial avec optimisation fiscale.</p>
            </div>

            <div class="service-card" data-animate>
                <div class="service-icon">
                    <svg><use xlink:href="#icon-renovation"></use></svg>
                </div>
                <h3>Gestion de Projet</h3>
                <p>Accompagnement complet pour les projets de rénovation ou de construction.</p>
            </div>
        </div>
    </div>
</section>

<!-- ── Featured Properties ───────────────────────────────────── -->
<section class="section section--alt">
    <div class="container">
        <div class="section__header text-center" data-animate>
            <span class="section-label">Nos coups de cœur</span>
            <h2 class="section-title">Propriétés d'exception</h2>
        </div>

        <div class="featured-slider">
            <div class="featured-property" data-animate>
                <div class="featured-image">
                    <img src="/assets/images/featured1.jpg" alt="Propriété d'exception 1">
                </div>
                <div class="featured-content">
                    <h3>Bastide provençale avec vue sur Luberon</h3>
                    <p class="price">Prix sur demande</p>
                    <p class="location">Lourmarin (84)</p>
                    <div class="features">
                        <span>6 chambres</span>
                        <span>4 salles de bain</span>
                        <span>350 m²</span>
                        <span>2 ha de terrain</span>
                    </div>
                </div>
            </div>

            <div class="featured-property" data-animate>
                <div class="featured-image">
                    <img src="/assets/images/featured2.jpg" alt="Propriété d'exception 2">
                </div>
                <div class="featured-content">
                    <h3>Villa contemporaine en bord de mer</h3>
                    <p class="price">€2 850 000</p>
                    <p class="location">Cassis (13)</p>
                    <div class="features">
                        <span>4 chambres</span>
                        <span>3 salles de bain</span>
                        <span>220 m²</span>
                        <span>1 200 m² de terrain</span>
                    </div>
                </div>
            </div>

            <div class="featured-property" data-animate>
                <div class="featured-image">
                    <img src="/assets/images/featured3.jpg" alt="Propriété d'exception 3">
                </div>
                <div class="featured-content">
                    <h3>Domaine viticole en Côtes de Provence</h3>
                    <p class="price">Prix sur demande</p>
                    <p class="location">Les Arcs-sur-Argens (83)</p>
                    <div class="features">
                        <span>8 chambres</span>
                        <span>5 salles de bain</span>
                        <span>450 m²</span>
                        <span>15 ha de vignes</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ── Viager Section ────────────────────────────────────────── -->
<section class="section">
    <div class="container">
        <div class="section__header text-center" data-animate>
            <span class="section-label">Solutions innovantes</span>
            <h2 class="section-title">Le Viager Éthique by EXP REALTY</h2>
            <p class="section-subtitle">
                Une approche moderne et sécurisée du viager pour toutes les parties
            </p>
        </div>

        <div class="viager-grid">
            <div class="viager-card" data-animate>
                <div class="viager-icon">
                    <svg><use xlink:href="#icon-handshake"></use></svg>
                </div>
                <h3>Viager occupé sans rente</h3>
                <p>Solution 100% bouquet pour une acquisition immédiate sans paiement de rente.</p>
            </div>

            <div class="viager-card" data-animate>
                <div class="viager-icon">
                    <svg><use xlink:href="#icon-calculator"></use></svg>
                </div>
                <h3>Viager libre</h3>
                <p>Acquisition immédiate d'un bien libre d'occupation avec paiement différé.</p>
            </div>

            <div class="viager-card" data-animate>
                <div class="viager-icon">
                    <svg><use xlink:href="#icon-family"></use></svg>
                </div>
                <h3>Viager familial</h3>
                <p>Solutions adaptées pour les transmissions intrafamiliales.</p>
            </div>

            <div class="viager-card" data-animate>
                <div class="viager-icon">
                    <svg><use xlink:href="#icon-invest"></use></svg>
                </div>
                <h3>Viager investissement</h3>
                <p>Opportunités d'investissement avec rendement attractif.</p>
            </div>
        </div>

        <div class="viager-cta" data-animate>
            <p>Vous souhaitez en savoir plus sur le viager ou évaluer une opportunité ?</p>
            <a href="#contact" class="btn btn--accent">Demander une étude personnalisée</a>
        </div>
    </div>
</section>

<!-- ── Contact Section ───────────────────────────────────────── -->
<section class="section section--alt" id="contact">
    <div class="container">
        <div class="section__header text-center" data-animate>
            <span class="section-label">Contactez-nous</span>
            <h2 class="section-title">Je suis là pour vous aider</h2>
            <p class="section-subtitle">
                Contactez-moi dès aujourd'hui pour commencer une conversation sur votre projet immobilier.
            </p>
        </div>

        <div class="contact-grid">
            <div class="contact-info" data-animate>
                <div class="contact-method">
                    <div class="contact-icon">
                        <svg><use xlink:href="#icon-phone"></use></svg>
                    </div>
                    <h3>Téléphone</h3>
                    <p>+33 6 67 19 83 66</p>
                </div>

                <div class="contact-method">
                    <div class="contact-icon">
                        <svg><use xlink:href="#icon-whatsapp"></use></svg>
                    </div>
                    <h3>WhatsApp</h3>
                    <p>Disponible sur WhatsApp</p>
                </div>

                <div class="contact-method">
                    <div class="contact-icon">
                        <svg><use xlink:href="#icon-email"></use></svg>
                    </div>
                    <h3>E-mail</h3>
                    <p>pascal.hamm@expfrance.fr</p>
                </div>

                <div class="contact-social">
                    <h3>Réseaux sociaux</h3>
                    <div class="social-links">
                        <a href="https://www.facebook.com/people/Pascal-Hamm/" class="social-link" aria-label="Facebook">
                            <svg><use xlink:href="#icon-facebook"></use></svg>
                        </a>
                        <a href="https://www.instagram.com/pascal.hamm2025" class="social-link" aria-label="Instagram">
                            <svg><use xlink:href="#icon-instagram"></use></svg>
                        </a>
                        <a href="https://www.linkedin.com/in/pascal-hamm2025immobilierclefenmaincourtagefinancier" class="social-link" aria-label="LinkedIn">
                            <svg><use xlink:href="#icon-linkedin"></use></svg>
                        </a>
                    </div>
                </div>
            </div>

            <div class="contact-form" data-animate>
                <form id="contact-form">
                    <div class="form-group">
                        <input type="text" name="name" placeholder="Votre nom" required>
                    </div>
                    <div class="form-group">
                        <input type="email" name="email" placeholder="Votre email" required>
                    </div>
                    <div class="form-group">
                        <input type="tel" name="phone" placeholder="Votre téléphone">
                    </div>
                    <div class="form-group">
                        <select name="subject" required>
                            <option value="">Type de demande</option>
                            <option value="achat">Achat immobilier</option>
                            <option value="vente">Vente immobilière</option>
                            <option value="viager">Viager</option>
                            <option value="financement">Financement</option>
                            <option value="autre">Autre demande</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <textarea name="message" placeholder="Votre message" rows="5" required></textarea>
                    </div>
                    <button type="submit" class="btn btn--accent">Envoyer ma demande</button>
                </form>
            </div>
        </div>
    </div>
</section>

<!-- ── Testimonials ─────────────────────────────────────────── -->
<section class="section">
    <div class="container">
        <div class="section__header text-center" data-animate>
            <span class="section-label">Témoignages</span>
            <h2 class="section-title">Ce que disent nos clients</h2>
        </div>

        <div class="testimonial-slider">
            <div class="testimonial" data-animate>
                <div class="testimonial-content">
                    <p>"Pascal Hamm a su trouver la propriété de nos rêves en Provence. Son expertise et son réseau nous ont permis d'acquérir une bastide exceptionnelle à un prix très compétitif. Son accompagnement tout au long du processus a été exemplaire."</p>
                </div>
                <div class="testimonial-author">
                    <img src="/assets/images/client1.jpg" alt="Jean et Marie D.">
                    <div>
                        <h4>Jean et Marie D.</h4>
                        <p>Acheteurs d'une bastide provençale</p>
                    </div>
                </div>
            </div>

            <div class="testimonial" data-animate>
                <div class="testimonial-content">
                    <p>"Grâce à l'expertise de Pascal en viager, nous avons pu vendre notre bien dans des conditions optimales tout en sécurisant notre avenir. Une approche humaine et professionnelle que je recommande sans hésiter."</p>
                </div>
                <div class="testimonial-author">
                    <img src="/assets/images/client2.jpg" alt="Claire M.">
                    <div>
                        <h4>Claire M.</h4>
                        <p>Vendeuse en viager</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ── CTA Final ─────────────────────────────────────────────── -->
<section class="cta-final">
    <div class="container">
        <div class="cta-content" data-animate>
            <h2>Votre projet immobilier mérite une expertise 360°</h2>
            <p>Que vous cherchiez à acheter, vendre, investir ou optimiser votre patrimoine, Pascal Hamm et son équipe vous accompagnent avec professionnalisme et discrétion.</p>
            <a href="#contact" class="btn btn--accent btn--lg">Contactez-nous dès maintenant</a>
        </div>
    </div>
</section>

<!-- ── JSON-LD Schema ────────────────────────────────────────── -->
<?php
$jsonLd = json_encode([
    "@context" => "https://schema.org",
    "@type" => "RealEstateAgent",
    "name" => "BeckHamm Real Estate Properties",
    "description" => "Spécialiste des biens d'exception en Provence et en Espagne, accompagnement 360° en immobilier clef en main et solutions de financement.",
    "url" => "https://www.pascalhamm-immobilier.com",
    "telephone" => "+33667198366",
    "email" => "pascal.hamm@expfrance.fr",
    "address" => [
        "@type" => "PostalAddress",
        "streetAddress" => "Adresse à préciser",
        "addressLocality" => "Aix-en-Provence",
        "addressRegion" => "Provence-Alpes-Côte d'Azur",
        "postalCode" => "13100",
        "addressCountry" => "FR"
    ],
    "sameAs" => [
        "https://www.facebook.com/people/Pascal-Hamm/",
        "https://www.instagram.com/pascal.hamm2025",
        "https://www.linkedin.com/in/pascal-hamm2025immobilierclefenmaincourtagefinancier"
    ],
    "areaServed" => [
        "@type" => "Place",
        "name" => "Provence-Alpes-Côte d'Azur"
    ],
    "makesOffer" => [
        "@type" => "Offer",
        "name" => "Immobilier clef en main",
        "description" => "Accompagnement complet pour l'achat ou la vente de biens immobiliers"
    ],
    "hasOfferCatalog" => [
        "@type" => "OfferCatalog",
        "name" => "Services immobiliers",
        "itemListElement" => [
            [
                "@type" => "Offer",
                "name" => "Achat immobilier",
                "description" => "Recherche et acquisition de biens immobiliers"
            ],
            [
                "@type" => "Offer",
                "name" => "Vente immobilière",
                "description" => "Vente de biens avec stratégie optimisée"
            ],
            [
                "@type" => "Offer",
                "name" => "Viager éthique",
                "description" => "Solutions de viager pour vendeurs et acquéreurs"
            ],
            [
                "@type" => "Offer",
                "name" => "Financement immobilier",
                "description" => "Accompagnement dans la recherche de solutions de financement"
            ]
        ]
    ]
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
?>
<script type="application/ld+json"><?= $jsonLd ?></script>

<!-- ── Cookie Banner ─────────────────────────────────────────── -->
<div id="cookie-banner">
    <div class="cookie-content">
        <p>🍪 Ce site utilise des cookies pour améliorer votre expérience. En continuant à naviguer, vous acceptez leur utilisation.</p>
        <div class="cookie-actions">
            <button id="cookie-refuse" class="btn btn--outline">Refuser</button>
            <button id="cookie-accept" class="btn btn--accent">Accepter</button>
        </div>
    </div>
</div>
