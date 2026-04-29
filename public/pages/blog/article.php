<?php
require_once ROOT_PATH . '/core/helpers/articles.php';

$slug = trim((string) ($slug ?? ''));
$dbArticle = null;

try {
    $dbArticle = get_article_by_slug($slug);
} catch (\Throwable $e) {
    $dbArticle = null;
}

$fallbackArticles = [
    'negociation-immobiliere' => [
        'title' => 'Comment bien négocier un prix immobilier ?',
        'excerpt' => 'Méthode concrète pour négocier un achat ou une vente immobilière sans fragiliser le dossier : prix, arguments, timing, preuves de marché et posture.',
        'content_html' => <<<'HTML'
<p>Une bonne négociation immobilière ne consiste pas à demander une remise au hasard. Elle repose sur une lecture précise du bien, du marché local, du niveau de concurrence et de la situation du vendeur ou de l'acheteur. A Aix-en-Provence et dans le Pays d'Aix, cette préparation est déterminante : certains biens partent vite, d'autres restent affichés plusieurs mois parce que le prix, la présentation ou la stratégie ne sont pas alignés.</p>

<h2>1. Commencer par le prix réel du marché</h2>
<p>Avant toute offre, il faut distinguer trois prix : le prix affiché, le prix espéré et le prix probable de transaction. Le prix affiché reflète souvent une intention. Le prix probable dépend des ventes comparables, de l'état du bien, de l'adresse exacte, de la rareté, des travaux et du contexte de demande.</p>
<p>Une négociation solide s'appuie donc sur des éléments vérifiables : ventes récentes, prix au mètre carré, délais de commercialisation, niveau de prestations, diagnostics, charges, exposition, étage, extérieur, stationnement et qualité de l'immeuble ou du quartier.</p>

<h2>2. Identifier les vrais leviers de négociation</h2>
<p>Tous les défauts ne justifient pas une baisse de prix. Un mur à repeindre n'a pas le même poids qu'une toiture à reprendre, un DPE faible, une absence d'ascenseur, une copropriété fragile ou des travaux votés. L'objectif est de hiérarchiser les arguments pour ne présenter que ceux qui ont une valeur économique réelle.</p>
<ul>
    <li><strong>Travaux mesurables :</strong> demander des devis ou chiffrer une enveloppe crédible.</li>
    <li><strong>Comparables vendus :</strong> montrer des références proches, pas des moyennes trop générales.</li>
    <li><strong>Délai de vente :</strong> un bien affiché depuis longtemps ouvre souvent plus de marge.</li>
    <li><strong>Financement solide :</strong> un dossier acheteur fiable peut compenser une offre plus basse.</li>
</ul>

<h2>3. Eviter l'offre trop agressive</h2>
<p>Une offre très basse peut fermer la discussion, surtout si le bien est récent sur le marché ou si plusieurs acheteurs sont positionnés. La bonne approche consiste à formuler une offre argumentée, polie et cohérente. Le vendeur doit comprendre que le prix proposé n'est pas une provocation, mais la conséquence d'une analyse.</p>
<p>Dans certains cas, il vaut mieux négocier moins fortement le prix mais sécuriser les conditions : délai de signature, absence de condition suspensive trop longue, accord bancaire avancé, souplesse sur la date de libération ou engagement clair sur le calendrier.</p>

<h2>4. Côté vendeur : préparer la négociation avant les visites</h2>
<p>Le vendeur qui attend la première offre pour réfléchir à sa marge de négociation arrive souvent en position faible. Avant la mise en ligne, il faut définir un prix de présentation, un prix plancher, les arguments de défense et les concessions acceptables.</p>
<p>La préparation passe aussi par la qualité du dossier : diagnostics prêts, charges connues, procès-verbaux de copropriété disponibles, factures de travaux, plans et informations fiscales. Plus le dossier est clair, moins l'acheteur dispose de zones d'incertitude pour faire baisser le prix.</p>

<h2>5. Ne pas négocier uniquement le prix</h2>
<p>Une transaction immobilière ne se résume pas au montant final. Les délais, les conditions suspensives, les meubles inclus, les petits travaux avant signature, la date de remise des clés ou la prise en charge de certains documents peuvent aussi faire partie de la discussion.</p>
<p>La meilleure négociation est celle qui permet aux deux parties de signer sereinement. Un accord trop dur peut créer de la défiance et fragiliser la vente jusqu'au compromis, voire jusqu'à l'acte authentique.</p>

<h2>6. Se faire accompagner pour garder la bonne distance</h2>
<p>L'immobilier est rarement neutre émotionnellement. Un acheteur peut avoir peur de laisser passer le bien. Un vendeur peut défendre un prix lié à son histoire personnelle. Un intermédiaire local aide à remettre la discussion sur des faits, à doser la posture et à préserver la relation entre les parties.</p>
<p>A Aix-en-Provence, les écarts de prix peuvent être importants d'une rue à l'autre. Une négociation pertinente doit donc combiner les chiffres, la connaissance terrain et la réalité du moment : nombre d'acheteurs actifs, rareté du type de bien, saisonnalité et tension du secteur.</p>

<h2>En résumé</h2>
<p>Pour bien négocier un prix immobilier, il faut arriver préparé, argumenter avec des preuves, rester crédible et ne pas confondre fermeté avec brutalité. Le bon prix n'est pas seulement celui que l'on souhaite obtenir : c'est celui qui permet à la transaction d'aboutir dans de bonnes conditions.</p>
HTML,
        'cover_image' => '',
        'author_name' => defined('ADVISOR_NAME') ? ADVISOR_NAME : 'Pascal Hamm',
        'published_at' => '2026-04-29',
        'updated_at' => '2026-04-29',
        'category' => 'Conseils immobiliers',
        'tags' => ['négociation immobilière', 'achat immobilier', 'vente immobilière', 'Aix-en-Provence'],
        'meta_title' => 'Comment bien négocier un prix immobilier ?',
        'meta_desc' => 'Conseils concrets pour négocier un prix immobilier avec méthode : arguments, comparables, offre, timing et stratégie à Aix-en-Provence.',
    ],
];

if ($dbArticle !== null) {
    $article = [
        'title' => $dbArticle['titre'] ?? $dbArticle['seo_title'] ?? 'Article',
        'excerpt' => $dbArticle['meta_desc'] ?? '',
        'content_html' => $dbArticle['contenu'] ?? '',
        'cover_image' => $dbArticle['image'] ?? '',
        'author_name' => defined('ADVISOR_NAME') ? ADVISOR_NAME : 'Pascal Hamm',
        'published_at' => $dbArticle['date_publication'] ?? $dbArticle['created_at'] ?? '',
        'updated_at' => $dbArticle['updated_at'] ?? '',
        'category' => $dbArticle['type'] ?? 'Blog immobilier',
        'tags' => array_filter([
            $dbArticle['primary_keyword'] ?? null,
            ...json_decode((string) ($dbArticle['secondary_keywords_json'] ?? '[]'), true) ?: [],
        ]),
    ];
} elseif (isset($fallbackArticles[$slug])) {
    $article = $fallbackArticles[$slug];
} else {
    http_response_code(404);
    page('pages/404');
    return;
}

$pageTitle = (string) ($article['meta_title'] ?? $article['title']);
$metaDesc = (string) ($article['meta_desc'] ?? $article['excerpt'] ?? '');
$canonical = APP_URL . '/blog/' . rawurlencode($slug);
$ogType = 'article';
$extraCss = ['/assets/css/blog-article.css'];

require ROOT_PATH . '/public/templates/pages/blog-single.php';
