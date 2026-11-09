-- =============================================================
-- Insertion des détails de postes (Fiches de poste & KPI)
-- =============================================================

INSERT INTO `details_poste` (
    `poste_id`, 
    `salaire_min`, 
    `salaire_max`, 
    `objectifs`, 
    `valeur`, 
    `mode_de_travail`, 
    `competences_techniques`, 
    `formation`
) VALUES
-- 1. Développeur (Informatique)
(
    1, 
    2000000.00, 
    4500000.00, 
    'Livrer des fonctionnalités stables dans les délais et maintenir une couverture de tests supérieure à 80%.', 
    'Velocity Sprint > 30 pts', 
    'Hybride', 
    'PHP (CodeIgniter 4 / Laravel), Vue.js 3, MySQL/PostgreSQL, Git, Architecture REST', 
    'Master ou Licence en Informatique / Génie Logiciel'
),

-- 2. Administrateur Système (Informatique)
(
    2, 
    2500000.00, 
    5000000.00, 
    'Garantir une haute disponibilité des serveurs (99.9%) et sécuriser les infrastructures cloud et locales.', 
    'Uptime 99.9%', 
    'Présentiel', 
    'Linux (Ubuntu/Debian), Docker, Apache/Nginx, Scripting Bash, Monitoring (Grafana/Prometheus), Networking', 
    'Bac+3/5 en Réseaux & Systèmes Informatiques'
),

-- 3. Responsable RH (Ressources Humaines)
(
    3, 
    3000000.00, 
    6000000.00, 
    'Optimiser la rétention des talents, piloter la gestion des carrières et assurer la conformité sociale.', 
    'Taux de turnover < 5%', 
    'Présentiel', 
    'Droit du travail, Gestion de la paie, Négociation sociale, SIRH, Conduite d entretiens', 
    'Master en Gestion des Ressources Humaines ou Management'
),

-- 4. Chargé de Recrutement (Ressources Humaines)
(
    4, 
    1800000.00, 
    3200000.00, 
    'Réduire le délai moyen de recrutement et constituer un vivier de candidats qualifiés.', 
    'Délai de pourvoi < 30 jours', 
    'Hybride', 
    'Sourcing LinkedIn, Conduite d entretiens structurés, Rédaction d offres, Gestion d ATS', 
    'Licence/Master en Psychologie du travail, RH ou Management'
),

-- 6. Responsable Marketing Digital (Marketing)
(
    6, 
    2500000.00, 
    4800000.00, 
    'Augmenter l engagement sur les réseaux sociaux et générer des leads qualifiés via les campagnes payantes.', 
    'Taux de conversion > 3.5%', 
    'Télétravail', 
    'SEO/SEA, Google Analytics, Meta Ads, Marketing d influence, Copywriting, Figma', 
    'Master en Marketing Digital ou Communication'
),

-- 8. Comptable (Finance)
(
    8, 
    1800000.00, 
    3500000.00, 
    'Assurer la tenue rigoureuse des comptes, les déclarations fiscales et le suivi de la trésorerie.', 
    'Zéro pénalité de retard', 
    'Présentiel', 
    'Comptabilité générale & analytique, ERP (Odoo/SAP), Excel avancé, Fiscalité locale', 
    'Licence/Master en Comptabilité, Contrôle & Audit (CCA)'
),

-- 9. Commercial (Ventes)
(
    9, 
    1500000.00, 
    4000000.00, 
    'Atteindre et dépasser les objectifs mensuels de prospection et de vente de services.', 
    'Chiffre d affaires mensuel > 15M MGA', 
    'Présentiel', 
    'Techniques de vente B2B, CRM (HubSpot/Salesforce), Négociation, Prospection téléphonique & terrain', 
    'Bac+2/3 en Commerce, Vente ou Gestion d Entreprise'
);

update Besoins set statut_actuel_id = 5 where id = 2;

