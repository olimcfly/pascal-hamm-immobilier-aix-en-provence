<?php

declare(strict_types=1);

$pageTitle = 'Générateur de contenu';
$pageDescription = 'Créez du contenu basé sur votre positionnement';

function renderContent(): void {
    ?>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        .generator-container {
            max-width: 900px;
            margin: 0 auto;
        }

        .generator-header {
            background: linear-gradient(135deg, #0f2237 0%, #1a3a5c 100%);
            border-radius: 16px;
            padding: 36px 40px;
            color: #fff;
            margin-bottom: 32px;
            box-shadow: 0 4px 20px rgba(15,34,55,.18);
        }

        .generator-header h1 {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 12px;
        }

        .generator-header p {
            font-size: 15px;
            color: rgba(255,255,255,.7);
            line-height: 1.65;
        }

        .generator-positioning-summary {
            background: #f8fbff;
            border: 2px solid #dbeafe;
            border-radius: 12px;
            padding: 24px;
            margin-bottom: 32px;
        }

        .generator-summary-title {
            font-size: 14px;
            font-weight: 700;
            color: #3b82f6;
            text-transform: uppercase;
            letter-spacing: .07em;
            margin-bottom: 16px;
        }

        .generator-summary-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 16px;
        }

        .generator-summary-item {
            padding: 12px;
            background: #fff;
            border-radius: 8px;
            border-left: 3px solid #3b82f6;
        }

        .generator-summary-label {
            font-size: 12px;
            font-weight: 700;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: .05em;
            margin-bottom: 4px;
        }

        .generator-summary-value {
            font-size: 14px;
            font-weight: 600;
            color: #1e293b;
        }

        .generator-content-types {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 14px;
            margin-bottom: 32px;
        }

        .generator-content-type {
            background: #fff;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            padding: 20px;
            cursor: pointer;
            transition: all 0.2s ease;
            text-align: center;
        }

        .generator-content-type:hover {
            border-color: #c9a84c;
            background: #fffaf0;
        }

        .generator-content-type.selected {
            border-color: #10b981;
            background: #f0fdf4;
        }

        .generator-content-type-icon {
            font-size: 32px;
            margin-bottom: 12px;
            display: block;
        }

        .generator-content-type-name {
            font-size: 14px;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 4px;
        }

        .generator-content-type-desc {
            font-size: 12px;
            color: #64748b;
        }

        .generator-results {
            display: grid;
            grid-template-columns: 1fr;
            gap: 24px;
            margin-top: 32px;
        }

        .generator-content-card {
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 1px 6px rgba(0,0,0,.07);
        }

        .generator-content-header {
            background: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
            padding: 16px 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
        }

        .generator-content-number {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: #dbeafe;
            color: #3b82f6;
            font-weight: 700;
            font-size: 14px;
        }

        .generator-content-title {
            font-size: 15px;
            font-weight: 700;
            color: #1e293b;
            flex: 1;
        }

        .generator-content-type-badge {
            display: inline-block;
            background: #f1f5f9;
            color: #64748b;
            padding: 4px 10px;
            border-radius: 6px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .05em;
        }

        .generator-content-body {
            padding: 20px;
            font-size: 14px;
            color: #475569;
            line-height: 1.7;
            white-space: pre-wrap;
            word-wrap: break-word;
        }

        .generator-content-footer {
            border-top: 1px solid #e2e8f0;
            padding: 12px 20px;
            display: flex;
            gap: 8px;
        }

        .generator-btn {
            padding: 8px 12px;
            border: none;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .generator-btn-copy {
            background: #f1f5f9;
            color: #334155;
            border: 1px solid #dbe2ea;
            flex: 1;
        }

        .generator-btn-copy:hover {
            background: #eef2f7;
        }

        .generator-btn-copy.copied {
            background: #dcfce7;
            color: #166534;
            border-color: #86efac;
        }

        .generator-btn-edit {
            background: #3b82f6;
            color: #fff;
            border: none;
        }

        .generator-btn-edit:hover {
            background: #2563eb;
        }

        .generator-actions {
            display: flex;
            gap: 12px;
            margin-top: 32px;
            flex-wrap: wrap;
        }

        .generator-btn-primary {
            padding: 12px 24px;
            background: #10b981;
            color: #fff;
            border: none;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .generator-btn-primary:hover {
            background: #059669;
        }

        .generator-btn-secondary {
            padding: 12px 24px;
            background: #f1f5f9;
            color: #334155;
            border: 1px solid #dbe2ea;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .generator-btn-secondary:hover {
            background: #eef2f7;
        }

        .generator-empty-state {
            text-align: center;
            padding: 60px 20px;
            background: #f8fafc;
            border-radius: 12px;
            border: 2px dashed #e2e8f0;
        }

        .generator-empty-state-icon {
            font-size: 48px;
            margin-bottom: 16px;
            opacity: 0.3;
        }

        .generator-empty-state-title {
            font-size: 18px;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 8px;
        }

        .generator-empty-state-text {
            font-size: 14px;
            color: #64748b;
            line-height: 1.6;
            max-width: 400px;
            margin: 0 auto;
        }

        .generator-loading {
            text-align: center;
            padding: 40px 20px;
        }

        .generator-spinner {
            display: inline-block;
            width: 40px;
            height: 40px;
            border: 4px solid #e2e8f0;
            border-top-color: #3b82f6;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        @media (max-width: 600px) {
            .generator-header {
                padding: 24px 20px;
            }

            .generator-content-types {
                grid-template-columns: 1fr;
            }

            .generator-summary-grid {
                grid-template-columns: 1fr;
            }

            .generator-actions {
                flex-direction: column;
            }

            .generator-btn-primary,
            .generator-btn-secondary {
                width: 100%;
                justify-content: center;
            }
        }
    </style>

    <div class="generator-container">
        <div class="generator-header">
            <h1>Générez votre contenu</h1>
            <p>Créez 5 contenus prêts à publier basés sur votre positionnement.</p>
        </div>

        <div id="mainContent">
            <!-- Section résumé du positionnement -->
            <div class="generator-positioning-summary" id="summarySection" style="display: none;">
                <div class="generator-summary-title">Votre positionnement</div>
                <div class="generator-summary-grid">
                    <div class="generator-summary-item">
                        <div class="generator-summary-label">Type de vendeur</div>
                        <div class="generator-summary-value" id="summaryVendeur">-</div>
                    </div>
                    <div class="generator-summary-item">
                        <div class="generator-summary-label">Problème principal</div>
                        <div class="generator-summary-value" id="summaryProbleme">-</div>
                    </div>
                    <div class="generator-summary-item">
                        <div class="generator-summary-label">Objectif</div>
                        <div class="generator-summary-value" id="summaryObjectif">-</div>
                    </div>
                    <div class="generator-summary-item">
                        <div class="generator-summary-label">Zone</div>
                        <div class="generator-summary-value" id="summaryZone">-</div>
                    </div>
                </div>
            </div>

            <!-- Section choix des types de contenu -->
            <div>
                <div style="margin-bottom: 16px;">
                    <h2 style="font-size: 18px; font-weight: 700; color: #0f172a; margin-bottom: 8px;">Sélectionnez les contenus à générer</h2>
                    <p style="font-size: 14px; color: #64748b;">Choisissez les 5 types de contenu que vous souhaitez créer.</p>
                </div>
                <div class="generator-content-types">
                    <div class="generator-content-type selected" data-type="titre">
                        <span class="generator-content-type-icon">📝</span>
                        <div class="generator-content-type-name">Titre accrocheur</div>
                        <div class="generator-content-type-desc">Pour une annonce ou article</div>
                    </div>
                    <div class="generator-content-type selected" data-type="description">
                        <span class="generator-content-type-icon">📋</span>
                        <div class="generator-content-type-name">Description courte</div>
                        <div class="generator-content-type-desc">Pour un profil ou réseau</div>
                    </div>
                    <div class="generator-content-type selected" data-type="article">
                        <span class="generator-content-type-icon">📚</span>
                        <div class="generator-content-type-name">Article conseil</div>
                        <div class="generator-content-type-desc">Éducatif et détaillé</div>
                    </div>
                    <div class="generator-content-type selected" data-type="reseaux">
                        <span class="generator-content-type-icon">💬</span>
                        <div class="generator-content-type-name">Message réseaux</div>
                        <div class="generator-content-type-desc">LinkedIn, Facebook, Instagram</div>
                    </div>
                    <div class="generator-content-type selected" data-type="email">
                        <span class="generator-content-type-icon">✉️</span>
                        <div class="generator-content-type-name">Email prospection</div>
                        <div class="generator-content-type-desc">Avec appel à l'action</div>
                    </div>
                </div>
            </div>

            <!-- Bouton générer -->
            <div style="margin-top: 32px; margin-bottom: 32px;">
                <button class="generator-btn-primary" id="generateBtn" onclick="generateContents()">
                    <i class="fas fa-sparkles"></i> Générer les contenus
                </button>
                <button class="generator-btn-secondary" onclick="history.back()">
                    <i class="fas fa-arrow-left"></i> Retour
                </button>
            </div>

            <!-- Section résultats -->
            <div id="resultsSection" style="display: none;">
                <div style="margin-bottom: 24px;">
                    <h2 style="font-size: 20px; font-weight: 700; color: #0f172a; margin-bottom: 8px;">Vos 5 contenus générés</h2>
                    <p style="font-size: 14px; color: #64748b;">Cliquez sur un contenu pour le copier ou le modifier.</p>
                </div>

                <div class="generator-results" id="resultsContainer">
                    <!-- Les contenus seront générés ici -->
                </div>

                <div class="generator-actions" style="margin-top: 40px; border-top: 1px solid #e2e8f0; padding-top: 32px;">
                    <button class="generator-btn-primary" onclick="downloadAllContents()">
                        <i class="fas fa-download"></i> Télécharger tous
                    </button>
                    <button class="generator-btn-secondary" onclick="location.reload()">
                        <i class="fas fa-redo"></i> Générer à nouveau
                    </button>
                    <button class="generator-btn-secondary" onclick="history.back()">
                        <i class="fas fa-arrow-left"></i> Retour
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
    (function () {
        const typeLabels = {
            'bien_ne_se_vend_pas': 'Bien qui ne se vend pas',
            'hesite_vendre': 'Personne qui hésite à vendre',
            'doit_vendre_vite': 'Personne qui doit vendre rapidement'
        };

        const problemeLabels = {
            'pas_visites': 'Pas de visites',
            'prix_complique': 'Prix compliqué',
            'trop_concurrence': 'Trop de concurrence',
            'hesitent': 'Hésitation'
        };

        const objectifLabels = {
            'vendre_vite': 'Vendre vite',
            'meilleur_prix': 'Vendre au meilleur prix',
            'rassure': 'Être rassuré',
            'comprendre': 'Comprendre'
        };

        // Récupérer les données du positionnement
        const data = JSON.parse(sessionStorage.getItem('positionnement_data') || '{}');

        if (Object.keys(data).length === 0) {
            document.getElementById('mainContent').innerHTML = `
                <div class="generator-empty-state">
                    <div class="generator-empty-state-icon">⚠️</div>
                    <div class="generator-empty-state-title">Aucun positionnement trouvé</div>
                    <div class="generator-empty-state-text">
                        Veuillez d'abord compléter le module de positionnement.
                    </div>
                    <button class="generator-btn-secondary" onclick="window.location.href='/admin/?module=positionnement'" style="margin-top: 20px;">
                        <i class="fas fa-arrow-left"></i> Aller au positionnement
                    </button>
                </div>
            `;
            return;
        }

        // Afficher le résumé
        document.getElementById('summarySection').style.display = 'block';
        document.getElementById('summaryVendeur').textContent = typeLabels[data.vendeur_type] || '-';
        document.getElementById('summaryProbleme').textContent = problemeLabels[data.probleme] || '-';
        document.getElementById('summaryObjectif').textContent = objectifLabels[data.objectif] || '-';
        document.getElementById('summaryZone').textContent = data.zone || '-';

        // Gestion de la sélection des types de contenu
        document.querySelectorAll('.generator-content-type').forEach(el => {
            el.addEventListener('click', function () {
                this.classList.toggle('selected');
            });
        });

        // Générer les contenus
        window.generateContents = function () {
            const selectedTypes = Array.from(document.querySelectorAll('.generator-content-type.selected'))
                .map(el => el.dataset.type);

            if (selectedTypes.length === 0) {
                alert('Sélectionnez au moins un type de contenu.');
                return;
            }

            // Afficher le loading
            document.getElementById('mainContent').innerHTML = `
                <div class="generator-loading">
                    <div class="generator-spinner"></div>
                    <p style="margin-top: 16px; color: #64748b;">Génération en cours...</p>
                </div>
            `;

            // Simuler une génération (remplacer par un appel API si nécessaire)
            setTimeout(() => {
                const contents = generateContentData(selectedTypes, data);
                displayResults(contents);
            }, 1500);
        };

        function generateContentData(types, data) {
            const zone = data.zone || 'votre région';
            const typeLabel = typeLabels[data.vendeur_type] || 'vendeur';
            const pensee = data.pensee || 'une préoccupation';

            const allContents = {
                titre: {
                    label: 'Titre accrocheur',
                    type: 'Titre',
                    content: `Votre bien ne se vend pas ? Découvrez pourquoi et comment y remédier à ${zone}`
                },
                description: {
                    label: 'Description courte',
                    type: 'Description',
                    content: `Je spécialise dans l'accompagnement des vendeurs qui rencontrent des difficultés. Si votre bien ne se vend pas, ce n'est pas forcément le prix. Parlons-en.`
                },
                article: {
                    label: 'Article conseil',
                    type: 'Article',
                    content: `Pourquoi votre bien ne se vend pas

Vous avez mis votre bien en vente et les visites ne viennent pas. C'est une situation frustrante, mais plus courante qu'on ne le pense.

Les véritables raisons

La plupart des conseillers immobiliers diraient que c'est une question de prix. Mais ce n'est que partiellement vrai.

Les vrais obstacles :
• Une présentation qui ne met pas en avant les atouts
• Un positionnement qui ne parle pas à votre acheteur idéal
• Une stratégie de prospection qui ne cible pas le bon marché

Comment y remédier

1. Définissez précisément qui achète votre bien (type d'acheteur)
2. Comprenez ce qui le préoccupe vraiment
3. Adaptez votre message à ses vrais besoins

À ${zone}, nous aidons les vendeurs à franchir cette étape critique. Vous n'êtes pas seul.`
                },
                reseaux: {
                    label: 'Message réseaux',
                    type: 'Réseaux sociaux',
                    content: `Votre bien ne se vend pas ? 📍 Ce n'est peut-être pas le prix.

Je travaille avec les vendeurs qui sont dans cette situation à ${zone}.

La première étape : comprendre pourquoi. Envoyez-moi un message, on en parle. 💬`
                },
                email: {
                    label: 'Email prospection',
                    type: 'Email',
                    content: `Sujet : Pourquoi votre bien ne se vend pas

Bonjour,

Si votre bien est en vente depuis un moment sans générer de visites, vous n'êtes pas seul. C'est une situation que je vois régulièrement à ${zone}.

Avant de baisser le prix, prenons 15 minutes pour en parler.

Souvent, la vraie raison n'est pas le prix, mais la manière dont votre bien est présenté et positionné.

Je peux vous aider à :
• Identifier précisément le problème
• Définir votre vrai marché
• Créer un plan d'action

Intéressé pour une discussion sans engagement ?

Cordialement,
[Votre nom]`
                }
            };

            return types.map((type, index) => ({
                number: index + 1,
                ...allContents[type]
            })).filter(Boolean);
        }

        function displayResults(contents) {
            document.getElementById('mainContent').innerHTML = `
                <div id="resultsSection" style="display: block;">
                    <div style="margin-bottom: 24px;">
                        <h2 style="font-size: 20px; font-weight: 700; color: #0f172a; margin-bottom: 8px;">Vos contenus générés</h2>
                        <p style="font-size: 14px; color: #64748b;">Cliquez sur un contenu pour le copier ou le modifier.</p>
                    </div>

                    <div class="generator-results" id="resultsContainer">
                        ${contents.map(content => `
                            <div class="generator-content-card">
                                <div class="generator-content-header">
                                    <div class="generator-content-number">${content.number}</div>
                                    <div class="generator-content-title">${content.label}</div>
                                    <div class="generator-content-type-badge">${content.type}</div>
                                </div>
                                <div class="generator-content-body">${content.content}</div>
                                <div class="generator-content-footer">
                                    <button class="generator-btn generator-btn-copy" onclick="copyToClipboard(this, '${escapeQuotes(content.content)}')">
                                        <i class="fas fa-copy"></i> Copier
                                    </button>
                                </div>
                            </div>
                        `).join('')}
                    </div>

                    <div class="generator-actions" style="margin-top: 40px; border-top: 1px solid #e2e8f0; padding-top: 32px;">
                        <button class="generator-btn-primary" onclick="downloadAllContents()">
                            <i class="fas fa-download"></i> Télécharger tous
                        </button>
                        <button class="generator-btn-secondary" onclick="location.reload()">
                            <i class="fas fa-redo"></i> Générer à nouveau
                        </button>
                        <button class="generator-btn-secondary" onclick="history.back()">
                            <i class="fas fa-arrow-left"></i> Retour
                        </button>
                    </div>
                </div>
            `;
        }

        window.copyToClipboard = function (btn, text) {
            navigator.clipboard.writeText(text).then(() => {
                const originalText = btn.innerHTML;
                btn.innerHTML = '<i class="fas fa-check"></i> Copié !';
                btn.classList.add('copied');
                setTimeout(() => {
                    btn.innerHTML = originalText;
                    btn.classList.remove('copied');
                }, 2000);
            });
        };

        window.downloadAllContents = function () {
            const contents = Array.from(document.querySelectorAll('.generator-content-body'))
                .map((el, i) => `${i + 1}. ${el.textContent.trim()}`)
                .join('\n\n---\n\n');

            const element = document.createElement('a');
            element.setAttribute('href', 'data:text/plain;charset=utf-8,' + encodeURIComponent(contents));
            element.setAttribute('download', 'contenus_positionnement.txt');
            element.style.display = 'none';
            document.body.appendChild(element);
            element.click();
            document.body.removeChild(element);
        };

        function escapeQuotes(str) {
            return str.replace(/'/g, "\\'").replace(/"/g, '\\"');
        }
    })();
    </script>
    <?php
}
