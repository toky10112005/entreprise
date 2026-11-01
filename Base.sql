CREATE DATABASE IF NOT EXISTS entreprise
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE entreprise;

-- ------------------------------------------------------------
-- 1. Départements et utilisateurs
-- ------------------------------------------------------------

CREATE TABLE departements (
  id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nom         VARCHAR(100) NOT NULL UNIQUE,
  manager_id  INT UNSIGNED NULL,
  created_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE utilisateurs (
  id             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nom            VARCHAR(100) NOT NULL,
  prenom         VARCHAR(100) NOT NULL,
  email          VARCHAR(150) NOT NULL UNIQUE,
  mot_de_passe   VARCHAR(255) NOT NULL,
  role           ENUM('candidat','employe','manager','rh','finance','direction') NOT NULL,
  departement_id INT UNSIGNED NULL,
  telephone      VARCHAR(30) NULL,
  poste          VARCHAR(100) NULL,
  date_embauche  DATE NULL,
  actif          TINYINT(1) NOT NULL DEFAULT 1,
  created_at     TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at     TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_user_dept FOREIGN KEY (departement_id) REFERENCES departements(id)
) ENGINE=InnoDB;

ALTER TABLE departements
  ADD CONSTRAINT fk_dept_manager FOREIGN KEY (manager_id) REFERENCES utilisateurs(id);

-- ------------------------------------------------------------
-- 2. Postes (grille salariale)
-- ------------------------------------------------------------
CREATE TABLE poste (
  id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nom         VARCHAR(255) NOT NULL,
  salaire_min DECIMAL(10,2) NOT NULL,
  salaire_max DECIMAL(10,2) NOT NULL
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- 3. Besoins en effectif
-- ------------------------------------------------------------
CREATE TABLE besoins (
  id                INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  reference         VARCHAR(20) NOT NULL UNIQUE,
  departement_id    INT UNSIGNED NOT NULL,
  manager_id        INT UNSIGNED NOT NULL,
  poste_id          INT UNSIGNED NOT NULL,
  intitule_poste    VARCHAR(150) NOT NULL,
  nb_postes         SMALLINT UNSIGNED NOT NULL DEFAULT 1,
  type_contrat      ENUM('CDI','CDD','CE') NOT NULL,
  motif             ENUM('manque_effectif','remplacement','nouveau_projet') NOT NULL,
  niveau_experience VARCHAR(50) NULL,
  date_prise_poste  DATE NULL,
  missions          TEXT NULL,
  competences       TEXT NULL,
  urgence           ENUM('normal','urgent') NOT NULL DEFAULT 'normal',
  statut            ENUM('brouillon','envoye_rh','envoye_finance','envoye_direction',
                         'valide','refuse','annonce_publiee') NOT NULL DEFAULT 'brouillon',
  created_at        TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at        TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_besoin_dept    FOREIGN KEY (departement_id) REFERENCES departements(id),
  CONSTRAINT fk_besoin_manager FOREIGN KEY (manager_id)     REFERENCES utilisateurs(id),
  CONSTRAINT fk_besoins_poste  FOREIGN KEY (poste_id)       REFERENCES poste(id),
  INDEX idx_besoin_statut (statut)
) ENGINE=InnoDB;

CREATE TABLE besoin_validations (
  id             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  besoin_id      INT UNSIGNED NOT NULL,
  etape          ENUM('rh','finance','direction') NOT NULL,
  utilisateur_id INT UNSIGNED NOT NULL,
  decision       ENUM('transmis','budget_adequat','budget_inadequat','valide','refuse','renvoye') NOT NULL,
  commentaire    TEXT NULL,
  date_action    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_bv_besoin FOREIGN KEY (besoin_id)      REFERENCES besoins(id) ON DELETE CASCADE,
  CONSTRAINT fk_bv_user   FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs(id)
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- 4. BUDGET : exercices + mouvements (grand livre simplifié)
-- ------------------------------------------------------------
-- Un exercice = une année budgétaire.
CREATE TABLE exercices (
  id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  annee       YEAR NOT NULL UNIQUE,
  statut      ENUM('ouvert','cloture') NOT NULL DEFAULT 'ouvert',
  created_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Chaque ligne = une entrée ou une sortie d'argent.
-- departement_id NULL = caisse centrale de l'entreprise.
-- Le solde n'est jamais stocké : il se calcule (entrées - sorties).
CREATE TABLE budget_mouvements (
  id             BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  exercice_id    INT UNSIGNED NOT NULL,
  departement_id INT UNSIGNED NULL,
  sens           ENUM('entree','sortie') NOT NULL,
  categorie      ENUM('dotation_initiale',     -- budget voté en début d'année (entrée caisse centrale)
                      'allocation',            -- caisse centrale -> département
                      'rallonge',              -- budget supplémentaire
                      'virement_interne',      -- département -> département
                      'salaire_recrutement',   -- coût d'un besoin validé
                      'autre') NOT NULL,
  montant        DECIMAL(15,2) NOT NULL,
  libelle        VARCHAR(200) NOT NULL,
  statut         ENUM('engage','realise','annule') NOT NULL DEFAULT 'realise',
  virement_ref   CHAR(36) NULL,                -- même valeur sur les 2 lignes d'un virement
  besoin_id      INT UNSIGNED NULL,            -- lien facultatif avec un besoin
  cree_par       INT UNSIGNED NOT NULL,
  date_mouvement DATE NOT NULL,
  created_at     TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at     TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT chk_montant_positif CHECK (montant > 0),
  CONSTRAINT fk_mv_exercice FOREIGN KEY (exercice_id)    REFERENCES exercices(id),
  CONSTRAINT fk_mv_dept     FOREIGN KEY (departement_id) REFERENCES departements(id),
  CONSTRAINT fk_mv_besoin   FOREIGN KEY (besoin_id)      REFERENCES besoins(id) ON DELETE SET NULL,
  CONSTRAINT fk_mv_user     FOREIGN KEY (cree_par)       REFERENCES utilisateurs(id),
  INDEX idx_mv_dept (exercice_id, departement_id),
  INDEX idx_mv_ref (virement_ref)
) ENGINE=InnoDB;

-- Solde par département (et caisse centrale) et par exercice
CREATE VIEW v_budget_departement AS
SELECT e.annee,
       m.departement_id,
       COALESCE(d.nom, 'Caisse centrale')                                        AS departement,
       SUM(CASE WHEN m.sens = 'entree' THEN m.montant ELSE 0 END)                AS total_entrees,
       SUM(CASE WHEN m.sens = 'sortie' AND m.statut = 'engage'  THEN m.montant ELSE 0 END) AS sorties_engagees,
       SUM(CASE WHEN m.sens = 'sortie' AND m.statut = 'realise' THEN m.montant ELSE 0 END) AS sorties_realisees,
       SUM(CASE WHEN m.sens = 'entree' THEN m.montant ELSE -m.montant END)       AS solde_disponible
FROM budget_mouvements m
JOIN exercices e ON e.id = m.exercice_id
LEFT JOIN departements d ON d.id = m.departement_id
WHERE m.statut <> 'annule'
GROUP BY e.annee, m.departement_id, d.nom;

-- Budget global de l'entreprise (somme de tout) par exercice
CREATE VIEW v_budget_global AS
SELECT annee,
       SUM(total_entrees)     AS total_entrees,
       SUM(sorties_engagees)  AS sorties_engagees,
       SUM(sorties_realisees) AS sorties_realisees,
       SUM(solde_disponible)  AS solde_disponible
FROM v_budget_departement
GROUP BY annee;

-- ------------------------------------------------------------
-- 5. Annonces
-- ------------------------------------------------------------
CREATE TABLE annonces (
  id               INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  besoin_id        INT UNSIGNED NOT NULL,
  rh_id            INT UNSIGNED NOT NULL,
  titre            VARCHAR(150) NOT NULL,
  lieu             VARCHAR(100) NULL,
  description      TEXT NOT NULL,
  profil_recherche TEXT NULL,
  afficher_salaire TINYINT(1) NOT NULL DEFAULT 0,
  date_limite      DATE NULL,
  statut           ENUM('brouillon','publiee','cloturee') NOT NULL DEFAULT 'brouillon',
  date_publication DATETIME NULL,
  CONSTRAINT fk_annonce_besoin FOREIGN KEY (besoin_id) REFERENCES besoins(id),
  CONSTRAINT fk_annonce_rh     FOREIGN KEY (rh_id)     REFERENCES utilisateurs(id),
  INDEX idx_annonce_statut (statut)
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- 6. Candidats et candidatures
-- ------------------------------------------------------------
CREATE TABLE candidats (
  id        INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nom       VARCHAR(100) NOT NULL,
  prenom    VARCHAR(100) NOT NULL,
  email     VARCHAR(150) NOT NULL,
  telephone VARCHAR(30) NULL,
  UNIQUE KEY uq_candidat_email (email)
) ENGINE=InnoDB;

CREATE TABLE candidatures (
  id               INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  annonce_id       INT UNSIGNED NOT NULL,
  candidat_id      INT UNSIGNED NOT NULL,
  cv_path          VARCHAR(255) NOT NULL,
  lettre_path      VARCHAR(255) NULL,
  message          TEXT NULL,
  consentement     TINYINT(1) NOT NULL DEFAULT 0,
  statut           ENUM('nouvelle','acceptee','refusee','entretien_planifie',
                        'retenue','non_retenue','embauche') NOT NULL DEFAULT 'nouvelle',
  decision_rh_id   INT UNSIGNED NULL,
  date_decision    DATETIME NULL,
  date_candidature TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_candidature (annonce_id, candidat_id),
  CONSTRAINT fk_cand_annonce  FOREIGN KEY (annonce_id)     REFERENCES annonces(id),
  CONSTRAINT fk_cand_candidat FOREIGN KEY (candidat_id)    REFERENCES candidats(id),
  CONSTRAINT fk_cand_rh       FOREIGN KEY (decision_rh_id) REFERENCES utilisateurs(id),
  INDEX idx_cand_statut (statut)
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- 7. Entretien unique (manager + direction)
-- ------------------------------------------------------------
CREATE TABLE entretiens (
  id             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  candidature_id INT UNSIGNED NOT NULL UNIQUE,
  manager_id     INT UNSIGNED NOT NULL,
  direction_id   INT UNSIGNED NOT NULL,
  date_entretien DATETIME NOT NULL,
  lieu           VARCHAR(150) NULL,
  statut         ENUM('planifie','realise','annule') NOT NULL DEFAULT 'planifie',
  avis           ENUM('favorable','defavorable') NULL,
  commentaire    TEXT NULL,
  CONSTRAINT fk_ent_cand      FOREIGN KEY (candidature_id) REFERENCES candidatures(id),
  CONSTRAINT fk_ent_manager   FOREIGN KEY (manager_id)     REFERENCES utilisateurs(id),
  CONSTRAINT fk_ent_direction FOREIGN KEY (direction_id)   REFERENCES utilisateurs(id)
) ENGINE=InnoDB;

CREATE TABLE entretien_notes (
  id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  entretien_id INT UNSIGNED NOT NULL,
  critere      ENUM('technique','experience','communication','motivation') NOT NULL,
  note         TINYINT UNSIGNED NOT NULL,
  remarque     VARCHAR(255) NULL,
  UNIQUE KEY uq_note (entretien_id, critere),
  CONSTRAINT chk_note CHECK (note BETWEEN 1 AND 5),
  CONSTRAINT fk_note_ent FOREIGN KEY (entretien_id) REFERENCES entretiens(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- 8. Contrat et négociation (direction)
-- ------------------------------------------------------------
CREATE TABLE contrats (
  id                 INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  candidature_id     INT UNSIGNED NOT NULL UNIQUE,
  direction_id       INT UNSIGNED NOT NULL,
  type_contrat       ENUM('CDI','CDD','Stage') NOT NULL,
  date_debut         DATE NULL,
  periode_essai_mois TINYINT UNSIGNED NULL,
  salaire_initial    DECIMAL(12,2) NOT NULL,
  salaire_final      DECIMAL(12,2) NULL,
  avantages          TEXT NULL,
  statut             ENUM('en_negociation','signe','sans_accord') NOT NULL DEFAULT 'en_negociation',
  date_signature     DATE NULL,
  employe_id         INT UNSIGNED NULL,
  CONSTRAINT fk_ctr_cand      FOREIGN KEY (candidature_id) REFERENCES candidatures(id),
  CONSTRAINT fk_ctr_direction FOREIGN KEY (direction_id)   REFERENCES utilisateurs(id),
  CONSTRAINT fk_ctr_employe   FOREIGN KEY (employe_id)     REFERENCES utilisateurs(id)
) ENGINE=InnoDB;

CREATE TABLE negociations (
  id                     INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  contrat_id             INT UNSIGNED NOT NULL,
  tour                   SMALLINT UNSIGNED NOT NULL,
  proposition_entreprise DECIMAL(12,2) NOT NULL,
  reponse_candidat       DECIMAL(12,2) NULL,
  note                   TEXT NULL,
  date_tour              TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_tour (contrat_id, tour),
  CONSTRAINT fk_neg_contrat FOREIGN KEY (contrat_id) REFERENCES contrats(id) ON DELETE CASCADE
) ENGINE=InnoDB;