<?php

declare(strict_types=1);

/**
 * Scénarios prioritaires 1:1 (form_trigger → séquence auto active).
 * delay_after_previous : jours après l’e-mail précédent (e-mail 1 = 0, ignoré à l’envoi du premier).
 * Limitation moteur : pas d’horaires (H−3) ; la séquence « RDV » approche le calendrier en jours calendaires.
 */
$sig = "\n\nCordialement,\n{advisor_name}\n— {city}";

return [
    'estimation-rapport' => [
        'name' => 'Rapport d’estimation demandé',
        'objective' => 'Transformer une demande de rapport en rendez-vous',
        'persona' => 'Propriétaire ou porteur de projet',
        'description' => 'Déclenchée lorsque le visiteur demande l’envoi du rapport d’estimation (coordonnées laissées). J+0 à J+10.',
        'emails' => [
            [
                'subject' => 'Votre rapport est en préparation',
                'body' => 'Bonjour {first_name},'
                    . "\n\n" . 'J’ai bien reçu votre demande d’envoi de rapport. Il est en cours de préparation avec les éléments que vous nous avez transmis.'
                    . "\n\n" . 'Vous le recevrez sur votre adresse e-mail. Si un point manque, répondez simplement à ce message : c’est le plus rapide pour avancer sereinement.'
                    . $sig,
                'delay_after_previous' => 0,
            ],
            [
                'subject' => 'Ce que l’estimation en ligne ne voit pas',
                'body' => 'Bonjour {first_name},'
                    . "\n\n" . 'Une estimation automatique s’appuie sur des moyennes et des critères généraux. Le prix réel se joue souvent sur des détails : état, luminosité, étage, charges, DPE, agencement, travaux restant à prévoir, concurrence sur votre secteur à ' . '{city}.'
                    . "\n\n" . 'C’est précisément ce qu’un échange ciblé permet d’éclaircir, sans remplacer votre rapport, mais en le mettant en perspective.'
                    . $sig,
                'delay_after_previous' => 1,
            ],
            [
                'subject' => 'Les 5 éléments qui peuvent changer le prix réel',
                'body' => 'Bonjour {first_name},'
                    . "\n\n" . 'Pour nombre de biens, l’écart entre « fourchette web » et prix obtenu vient d’au moins un de ces leviers : l’emplacement et l’ensoleillement, l’état général, la copropriété et le plan de charges, la taxe et les rénovations récentes, l’attractivité du bien pour les visiteurs (photos, première impression), la stratégie de mise en prix et d’accompagnement.'
                    . "\n\n" . 'Si l’un d’eux s’applique à vous, c’est un signal utile pour la suite de votre démarche.'
                    . $sig,
                'delay_after_previous' => 2,
            ],
            [
                'subject' => 'Voulez-vous une lecture personnalisée ?',
                'body' => 'Bonjour {first_name},'
                    . "\n\n" . 'Votre situation est propre : la question n’est plus « chiffre moyen », mais « chiffre juste pour VOTRE bien, à ce moment, avec VOTRE calendrier ». Une lecture ciblée — par téléphone ou en rendez-vous — permet souvent d’y répondre en une fois.'
                    . "\n\n" . 'Souhaitez-vous m’indiquer deux créneaux de disponibilité ? Je vous proposerai le plus adapté.'
                    . $sig,
                'delay_after_previous' => 2,
            ],
            [
                'subject' => 'Dernière relance douce',
                'body' => 'Bonjour {first_name},'
                    . "\n\n" . 'Ce message clôt cette mini-série : je ne souhaite pas vous solliciter inutilement. Si le rapport vous suffit pour l’instant, gardez-le. Si un point bloque, ou si vous hésitez sur la prochaine étape (vente, travaux, calendrier), il suffit de répondre à ce mail : je vous fais un retour direct.'
                    . $sig,
                'delay_after_previous' => 5,
            ],
        ],
    ],
    'avis-valeur' => [
        'name' => 'Avis de valeur demandé',
        'objective' => 'Transformer une demande qualifiée en rendez-vous vendeur',
        'persona' => 'Propriétaire vendeur ou en réflexion',
        'description' => 'Déclenchée après demande d’avis de valeur formalisée. J+0 à J+7.',
        'emails' => [
            [
                'subject' => 'Votre demande d’avis de valeur est bien reçue',
                'body' => 'Bonjour {first_name},'
                    . "\n\n" . 'Merci pour votre demande. Je l’intègre en tenant compte du marché récent, des ventes comparables quand elles existent, et de la spécificité de votre bien autour de ' . '{city}.'
                    . "\n\n" . 'Vous aurez une base exploitable, avec les limites propres à tout avis : il reste un outil, pas un substitut à la visite si vous vous projetez en vente.'
                    . $sig,
                'delay_after_previous' => 0,
            ],
            [
                'subject' => 'Pourquoi un avis de valeur ne se limite pas au prix au m²',
                'body' => 'Bonjour {first_name},'
                    . "\n\n" . 'Le m² donne un ordre de grandeur ; la valeur d’un logement, elle, tient compte de l’état, de l’étage, de l’exposition, des charges, parfois du règlement de copropriété, de la visibilité sur Internet (photos, adresse) et de la date à laquelle on souhaite vendre.'
                    . "\n\n" . 'C’est pour cela qu’un avis sérieux s’accompagne d’explications, pas seulement d’un chiffre arrondi.'
                    . $sig,
                'delay_after_previous' => 1,
            ],
            [
                'subject' => 'Prix affiché, prix vendu : ce qui fait la différence',
                'body' => 'Bonjour {first_name},'
                    . "\n\n" . 'Sur le marché, l’annonce n’est qu’une intention : le résultat s’inscrit en signature, souvent avec un écart. La stratégie (prix, présentation, suivi) influence directement le prix effectivement obtenu.'
                    . "\n\n" . 'Si vous vendez, c’est sur ce fil que se joue l’arbitrage le plus payant : pas seulement « le bon prix », mais le bon enchaînement d’actions.'
                    . $sig,
                'delay_after_previous' => 2,
            ],
            [
                'subject' => 'Votre bien mérite une stratégie, pas juste un chiffre',
                'body' => 'Bonjour {first_name},'
                    . "\n\n" . 'Chaque bien a une story : vétusté, travaux, niche de demandeurs, calendrier fiscal ou familial. Un chiffre figé ne remplace jamais ce cadrage. Si un projet concret s’ouvre, un échange 20–30 minutes aligne souvent chiffre, calendrier et moyen de mise en marché — sans engagement si vous n’y êtes pas prêt.'
                    . $sig,
                'delay_after_previous' => 2,
            ],
            [
                'subject' => 'Souhaitez-vous aller plus loin ?',
                'body' => 'Bonjour {first_name},'
                    . "\n\n" . 'Si vous voulez faire un pas supplémentaire, indiquez-moi si vous ciblez surtout : une vente dans les 3 mois, du 3 au 6 mois, ou plus loin, « selon l’opportunité ». Cela m’aide à vous proposer la forme d’accompagnement la plus juste.'
                    . $sig,
                'delay_after_previous' => 2,
            ],
        ],
    ],
    'estimation-resultat' => [
        'name' => 'Résultat estimation + coordonnées',
        'objective' => 'Relancer les personnes qui ont vu une estimation et donné leurs coordonnées ensuite',
        'persona' => 'Propriétaire engagé',
        'description' => 'Déclenchée lorsque l’utilisateur a consulté le résultat et laissé ses coordonnées. J+0 à J+12.',
        'emails' => [
            [
                'subject' => 'Votre première estimation est disponible',
                'body' => 'Bonjour {first_name},'
                    . "\n\n" . 'Merci pour les éléments transmis. Votre estimation est (ou a été) accessible en ligne : elle donne un premier repère, à affiner en fonction de ce que l’on ne « voit » pas sur un formulaire (état intérieur, vues, copropriété, travaux, etc.).'
                    . "\n\n" . 'Gardez ce fil pour vos questions : c’est le canal le plus direct pour être répondu en priorité.'
                    . $sig,
                'delay_after_previous' => 0,
            ],
            [
                'subject' => 'Pourquoi deux biens similaires peuvent avoir deux prix différents',
                'body' => 'Bonjour {first_name},'
                    . "\n\n" . 'Même surface, même secteur, et pourtant des résultats de vente qui divergent. Souvent, l’explication tient à un mélange de micro-critères (état, bruit, lumière, réticence d’un type d’acheteur) et de macro-conditions (offre, saison, taux) à ' . '{city}.'
                    . "\n\n" . 'C’est pour cela qu’on raisonne en fourchette et en sensibilité, plus qu’en un seul chiffre figé pour deux biens théoriquement « jumeaux ».'
                    . $sig,
                'delay_after_previous' => 1,
            ],
            [
                'subject' => 'Les acheteurs ne paient pas seulement des mètres carrés',
                'body' => 'Bonjour {first_name},'
                    . "\n\n" . 'Un acheteur compare toujours des usages : lumière, plan, calme, atelier, extérieur, coût annuel, travaux, impression au premier regard. Tout cela pèse dans l’enveloppe d’achat, au-delà du m² moyen du quartier.'
                    . "\n\n" . 'Si vous vendez, mettre l’intention du bien au centre du message a souvent autant d’effet qu’un recadrage de prix sur le seul m².'
                    . $sig,
                'delay_after_previous' => 2,
            ],
            [
                'subject' => 'Et si on regardait votre projet plus sérieusement ?',
                'body' => 'Bonjour {first_name},'
                    . "\n\n" . 'Vous avez déjà un premier chiffre. La question suivante, c’est celle de votre vitesse (vente, année cible) et de votre contrainte (relogement, financement, travaux). Un court entretien permet d’enchaîner chiffre, calendrier et moyen de mise en marché sans se disperser.'
                    . "\n\n" . 'Souhaitez-vous m’en dire un peu plus par retour de mail ? Deux mots sur votre timing suffisent souvent à relancer l’échange de façon concrète.'
                    . $sig,
                'delay_after_previous' => 2,
            ],
            [
                'subject' => 'Votre estimation est-elle toujours d’actualité ?',
                'body' => 'Bonjour {first_name},'
                    . "\n\n" . 'Les marchés bougent, comme vos projets. Si votre situation a changé, ou si vous hésitez sur la prochaine étape, dites-moi en une phrase où vous en êtes : je m’adapte, sans recharger une estimation complète inutilement si l’on peut répondre autrement.'
                    . $sig,
                'delay_after_previous' => 7,
            ],
        ],
    ],
    'guide-offert' => [
        'name' => 'Guide offert',
        'objective' => 'Nourrir un prospect froid ou tiède',
        'persona' => 'Visiteur en phase de maturation',
        'description' => 'Déclenchée après opt-in / téléchargement de guide. J+0 à J+21.',
        'emails' => [
            [
                'subject' => 'Voici votre guide',
                'body' => 'Bonjour {first_name},'
                    . "\n\n" . 'Voici, comme promis, l’accès à votre guide. Conservez-le : les chapitres sur le calendrier et la fixation d’intention (vendre, acheter, estimer) sont souvent ceux qu’on relit le plus en premier.'
                    . "\n\n" . 'Si une question reste en suspens, répondez sur ce fil : c’est le bon canal pour aller du général à votre cas.'
                    . $sig,
                'delay_after_previous' => 0,
            ],
            [
                'subject' => 'La première erreur des propriétaires vendeurs',
                'body' => 'Bonjour {first_name},'
                    . "\n\n" . 'La première erreur, c’est de croire que le marché s’impose d’en haut, sans rôle de la présentation, du suivi, du prix initial et du calendrier. Le marché, c’est aussi la façon dont on raconte le bien et dont on gère la suite des visites.'
                    . $sig,
                'delay_after_previous' => 2,
            ],
            [
                'subject' => 'Comment savoir si c’est le bon moment pour vendre ?',
                'body' => 'Bonjour {first_name},'
                    . "\n\n" . 'Le « bon moment » mélange variables personnelles (besoin, financement) et de marché (offre, taux, saisonnalité à {city}).'
                    . "\n\n" . 'Quand l’intention se précise, un échange 15–20 minutes vaut toutes les généralités : il suffit d’en indiquer le souhait ici, sans engagement.'
                    . $sig,
                'delay_after_previous' => 2,
            ],
            [
                'subject' => 'Ce qui bloque souvent une vente',
                'body' => 'Bonjour {first_name},'
                    . "\n\n" . 'Ce qui freine, ce n’est pas toujours le prix affiché : c’est l’incompréhension (copro, charges, DPE, travaux), l’hésitation sur le prochain logement, ou l’agenda mal calé. Identifiez lequel domine, et on traite cela en priorité plutôt que d’enchaîner des ajustements de prix inutiles.'
                    . $sig,
                'delay_after_previous' => 3,
            ],
            [
                'subject' => 'Besoin d’un regard extérieur ?',
                'body' => 'Bonjour {first_name},'
                    . "\n\n" . 'Si le guide a ouvert des questions, un regard extérieur structurant aide souvent à trancher : pas pour vous convaincre d’agir, mais pour voir clair sur les options. Répondez en quelques mots : vente, achat, estimation, autre, et l’échéance que vous avez en tête.'
                    . $sig,
                'delay_after_previous' => 3,
            ],
            [
                'subject' => 'Je ne veux pas vous relancer inutilement',
                'body' => 'Bonjour {first_name},'
                    . "\n\n" . 'Ce fil se termine ici. Si le guide vous suffit pour l’instant, gardez-le. Si un jour le projet s’accélère, un simple message sur cette conversation suffit pour relancer. Bonne continuation dans vos décisions autour de ' . '{city}.'
                    . $sig,
                'delay_after_previous' => 11,
            ],
        ],
    ],
    'prendre-rendez-vous' => [
        'name' => 'Prise de rendez-vous',
        'objective' => 'Confirmer, préparer et relancer après l’entretien',
        'persona' => 'Prospect au rendez-vous',
        'description' => 'Déclenchée après le formulaire « Prendre rendez-vous ». Les messages espacent les envois en jours calendaires. Les relances H−3 / J−1 en relatif au créneau ne sont pas automatisables au quart d’heure près (cron journalier) : adaptez le timing si besoin côté message direct.',
        'emails' => [
            [
                'subject' => 'Votre rendez-vous est confirmé',
                'body' => 'Bonjour {first_name},'
                    . "\n\n" . 'Votre demande de rendez-vous est bien reçue. Vous avez une confirmation côté organisation ; ce message tient lieu d’appui, avec les prochaines étapes pour que notre échange soit utile dès le premier contact.'
                    . $sig,
                'delay_after_previous' => 0,
            ],
            [
                'subject' => 'Préparer notre échange',
                'body' => 'Bonjour {first_name},'
                    . "\n\n" . 'Pour que le temps passé ensemble soit le plus concret possible, pensez à noter : votre objectif principal (vendre, acheter, estimer, financer), l’adresse du bien s’il existe déjà, et les deux questions que vous voulez qu’on tranche en priorité. Le jour J, rapprochez-vous d’un rappel manuel (agenda) si vous avez un créneau fixé — le rappel automatique par e-mail ne colle pas exactement à l’heure faute de pilotage H−1/H−3 dans cet outil.'
                    . $sig,
                'delay_after_previous' => 1,
            ],
            [
                'subject' => 'Rappel de votre rendez-vous',
                'body' => 'Bonjour {first_name},'
                    . "\n\n" . 'Petit rappel amical avant notre prochaine rencontre : même lieu / même modalité que convenue (agence, visite, visio, téléphone), sauf autre consigne reçue de votre part. Si un imprévu survient, un court message ici suffit pour reprogrammer — mieux vaut un créneau calé qu’un rendez-vous subi.'
                    . $sig,
                'delay_after_previous' => 1,
            ],
            [
                'subject' => 'Merci pour notre échange',
                'body' => 'Bonjour {first_name},'
                    . "\n\n" . 'Merci pour l’échange. Si un point a été laissé ouvert (document, chiffre, calendrier), je vous l’adresse prochainement ou indiquez-moi simplement quoi en priorité. C’est le bon fil pour conclure les suites naturelles.'
                    . $sig,
                'delay_after_previous' => 1,
            ],
            [
                'subject' => 'Souhaitez-vous avancer ?',
                'body' => 'Bonjour {first_name},'
                    . "\n\n" . 'Après notre entretien, on peut figer : une suite claire (visite, mandat, recherche, financement) ou, au contraire, mettre le projet en pause. Dans les deux cas, dites-moi en une phrase comment vous voulez avancer, ou si le silence vaut clôture pour l’instant : je m’y plie, sans relance inutile.'
                    . $sig,
                'delay_after_previous' => 2,
            ],
        ],
    ],
    'contact' => [
        'name' => 'Contact générique',
        'objective' => 'Ne jamais laisser un contact sans suite',
        'persona' => 'Visiteur du site',
        'description' => 'Déclenchée après le formulaire contact. J+0 à J+7.',
        'emails' => [
            [
                'subject' => 'Votre message est bien reçu',
                'body' => 'Bonjour {first_name},'
                    . "\n\n" . 'Merci pour votre message. Il est bien reçu et pris en compte ; je m’en occupe directement, en général sous 24h ouvrées, sauf période de forte affluence (je vous le signale le cas échéant).'
                    . $sig,
                'delay_after_previous' => 0,
            ],
            [
                'subject' => 'Votre projet concerne plutôt vendre, acheter ou estimer ?',
                'body' => 'Bonjour {first_name},'
                    . "\n\n" . 'Pour cadrer le bon interlocuteur et la bonne réponse, dites-moi en une phrase si votre sujet est plutôt : vendre, acheter, estimer, ou un mélange (par exemple estimer pour vendre plus tard). Un délai cible, même flou, aide aussi (ce trimestre, fin d’année, etc.).'
                    . $sig,
                'delay_after_previous' => 1,
            ],
            [
                'subject' => 'Voici comment nous pouvons vous aider',
                'body' => 'Bonjour {first_name},'
                    . "\n\n" . 'Selon votre besoin, je peux : pour une vente, cadrer le prix, la stratégie et le calendrier ; pour un achat, affiner la zone et le budget cohérents ; pour une estimation, faire le lien entre chiffre en ligne et chiffre de marché. Indiquez ce qui manque aujourd’hui à votre réflexion, et j’y réponds de façon directe sur ce fil.'
                    . $sig,
                'delay_after_previous' => 2,
            ],
            [
                'subject' => 'Toujours besoin d’aide ?',
                'body' => 'Bonjour {first_name},'
                    . "\n\n" . 'Dernier message de cette mini-série : si vous avez obtenu ce qu’il fallait, vous pouvez ignorer ce mail. S’il reste un flou, une question, ou un obstacle (planning, financement, hésitation), répondez ici : je reçois, je traite, sans automatisme inutile derrière ce message-là.'
                    . $sig,
                'delay_after_previous' => 4,
            ],
        ],
    ],
    'financement' => [
        'name' => 'Financement',
        'objective' => 'Qualifier les acheteurs et sécuriser les projets',
        'persona' => 'Acheteur en phase de cadrage',
        'description' => 'Déclenchée après le formulaire financement. J+0 à J+5.',
        'emails' => [
            [
                'subject' => 'Votre demande financement est bien reçue',
                'body' => 'Bonjour {first_name},'
                    . "\n\n" . 'J’ai bien reçu les éléments liés à votre demande de financement. Ils aident à situer l’enveloppe réaliste (apport, mensualité cible, secteur) avant de cadrer la recherche de bien autour de ' . '{city}.'
                    . $sig,
                'delay_after_previous' => 0,
            ],
            [
                'subject' => 'Pourquoi valider son financement avant de visiter',
                'body' => 'Bonjour {first_name},'
                    . "\n\n" . 'Valider tôt son accord de principe ou, au minimum, l’enveloppe et le reste à vivre, évite de visiter des biens inaccessibles ou, inversement, de sous-évaluer sa capacité. C’est le meilleur moyen d’arbitrer sereinement entre plusieurs annonces et de répondre vite quand le bon profil se présente.'
                    . $sig,
                'delay_after_previous' => 1,
            ],
            [
                'subject' => 'Un projet bien financé se négocie mieux',
                'body' => 'Bonjour {first_name},'
                    . "\n\n" . 'Un dossier propre, une trajectoire d’emprunt comprise et un calendrier clair rassurent non seulement la banque, mais aussi le vendeur si vous négociez en situation concurrentielle. Le financement n’est pas qu’un taux : c’est de la clarté d’exécution pour tout le reste de la transaction.'
                    . $sig,
                'delay_after_previous' => 2,
            ],
            [
                'subject' => 'Souhaitez-vous être accompagné ?',
                'body' => 'Bonjour {first_name},'
                    . "\n\n" . 'Si vous souhaitez aller plus loin sur le montage (montant, apport, durée) ou l’enchaînement avec la visite, indiquez-le en répondant à ce message, avec l’échéance visée. Je vous oriente vers l’ajustement le plus cohérent par rapport à votre recherche, sans en faire trop tôt, ni trop tard.'
                    . $sig,
                'delay_after_previous' => 2,
            ],
        ],
    ],
];
