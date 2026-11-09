DROP DATABASE IF EXISTS `entreprise`;

CREATE DATABASE IF NOT EXISTS `entreprise` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE `entreprise`;

CREATE TABLE `departements` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `libelle` VARCHAR(150) NOT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_departements_libelle` (`libelle`)
) ENGINE=InnoDB;

CREATE TABLE `roles` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `libelle` VARCHAR(50) NOT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_roles_libelle` (`libelle`)
) ENGINE=InnoDB;

CREATE TABLE `postes` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `departement_id` INT UNSIGNED NOT NULL,
    `libelle` VARCHAR(150) NOT NULL,
    PRIMARY KEY (`id`),
    KEY `idx_postes_departement` (`departement_id`),
    CONSTRAINT `fk_postes_departement`
        FOREIGN KEY (`departement_id`) REFERENCES `departements` (`id`)
        ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE `types_besoins` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `libelle` VARCHAR(100) NOT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_types_besoins_libelle` (`libelle`)
) ENGINE=InnoDB;

CREATE TABLE `statuts_demandes_recrutement` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `libelle` VARCHAR(100) NOT NULL,
    `delai_modif` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Délai de modification ou de notification en jours',
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_statuts_demandes_libelle` (`libelle`)
) ENGINE=InnoDB;

CREATE TABLE `utilisateurs` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `role_id` INT UNSIGNED NULL,
    `nom` VARCHAR(100) NOT NULL,
    `prenom` VARCHAR(100) NOT NULL,
    `email` VARCHAR(255) NOT NULL,
    `mot_de_passe` VARCHAR(255) NOT NULL,
    `departement_id` INT UNSIGNED NULL,
    `role` VARCHAR(50) NOT NULL DEFAULT 'candidat',
    `telephone` VARCHAR(30) NULL,
    `actif` TINYINT(1) NOT NULL DEFAULT 1,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_utilisateurs_email` (`email`),
    KEY `idx_utilisateurs_role` (`role_id`),
    KEY `idx_utilisateurs_departement` (`departement_id`),
    CONSTRAINT `fk_utilisateurs_role`
        FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`)
        ON UPDATE CASCADE ON DELETE SET NULL,
    CONSTRAINT `fk_utilisateurs_departement`
        FOREIGN KEY (`departement_id`) REFERENCES `departements` (`id`)
        ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE `motifs_recrutement` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `libelle` VARCHAR(150) NOT NULL,
    `type_besoin_id` INT UNSIGNED NOT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_motifs_recrutement_libelle` (`libelle`),
    KEY `idx_motifs_type_besoin` (`type_besoin_id`),
    CONSTRAINT `fk_motifs_type_besoin`
        FOREIGN KEY (`type_besoin_id`) REFERENCES `types_besoins` (`id`)
        ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE `Besoins` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `date_soumission_demande` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `manager_id` INT UNSIGNED NOT NULL,
    `statut_actuel_id` INT UNSIGNED NOT NULL,
    PRIMARY KEY (`id`),
    KEY `idx_demandes_manager` (`manager_id`),
    KEY `idx_demandes_statut` (`statut_actuel_id`),
    CONSTRAINT `fk_demandes_manager`
        FOREIGN KEY (`manager_id`) REFERENCES `utilisateurs` (`id`)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT `fk_demandes_statut`
        FOREIGN KEY (`statut_actuel_id`) REFERENCES `statuts_demandes_recrutement` (`id`)
        ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE `details_besoins` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `besoins_id` INT UNSIGNED NOT NULL,
    `poste_id` INT UNSIGNED NOT NULL,
    `effectif` INT UNSIGNED NOT NULL DEFAULT 1,
    `motif_recrutement_id` INT UNSIGNED NOT NULL,
    `date_delai_recrutement` DATE NULL,
    `commentaire` TEXT NULL,
    `budget` DECIMAL(15, 2) NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_details_demande` (`besoins_id`),
    KEY `idx_details_poste` (`poste_id`),
    KEY `idx_details_motif` (`motif_recrutement_id`),
    CONSTRAINT `fk_details_demande`
        FOREIGN KEY (`besoins_id`) REFERENCES `Besoins` (`id`)
        ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT `fk_details_poste`
        FOREIGN KEY (`poste_id`) REFERENCES `postes` (`id`)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT `fk_details_motif`
        FOREIGN KEY (`motif_recrutement_id`) REFERENCES `motifs_recrutement` (`id`)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT `chk_details_effectif` CHECK (`effectif` > 0),
    CONSTRAINT `chk_details_budget` CHECK (`budget` IS NULL OR `budget` >= 0)
) ENGINE=InnoDB;

CREATE TABLE `workflow_decisions` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `statut_demande_recrutement_actuel_id` INT UNSIGNED NOT NULL,
    `statut_demande_recrutement_suivant_id` INT UNSIGNED NOT NULL,
    `role_decideur_id` INT UNSIGNED NOT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_workflow_transition`
        (`statut_demande_recrutement_actuel_id`, `statut_demande_recrutement_suivant_id`, `role_decideur_id`),
    KEY `idx_workflow_statut_suivant` (`statut_demande_recrutement_suivant_id`),
    KEY `idx_workflow_role` (`role_decideur_id`),
    CONSTRAINT `fk_workflow_statut_actuel`
        FOREIGN KEY (`statut_demande_recrutement_actuel_id`)
        REFERENCES `statuts_demandes_recrutement` (`id`)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT `fk_workflow_statut_suivant`
        FOREIGN KEY (`statut_demande_recrutement_suivant_id`)
        REFERENCES `statuts_demandes_recrutement` (`id`)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT `fk_workflow_role`
        FOREIGN KEY (`role_decideur_id`) REFERENCES `roles` (`id`)
        ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE `historiques_statuts_demandes_recrutement` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `statut_id` INT UNSIGNED NOT NULL,
    `besoins_id` INT UNSIGNED NOT NULL,
    `date_insertion` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `decideur_id` INT UNSIGNED NOT NULL,
    `commentaire` TEXT NULL,
    PRIMARY KEY (`id`),
    KEY `idx_historiques_statut` (`statut_id`),
    KEY `idx_historiques_demande` (`besoins_id`),
    KEY `idx_historiques_decideur` (`decideur_id`),
    CONSTRAINT `fk_historiques_statut`
        FOREIGN KEY (`statut_id`) REFERENCES `statuts_demandes_recrutement` (`id`)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT `fk_historiques_demande`
        FOREIGN KEY (`besoins_id`) REFERENCES `Besoins` (`id`)
        ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT `fk_historiques_decideur`
        FOREIGN KEY (`decideur_id`) REFERENCES `utilisateurs` (`id`)
        ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- BLOC 2 : Recrutement, Candidatures et Entretiens
-- ------------------------------------------------------------

-- 1. Tables de référence (Statuts)
CREATE TABLE `statut_offre` (
    `id_statut_offre` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `libelle` VARCHAR(100) NOT NULL,
    PRIMARY KEY (`id_statut_offre`),
    UNIQUE KEY `uk_statut_offre_libelle` (`libelle`)
) ENGINE=InnoDB;

CREATE TABLE `statut_candidat` (
    `id_statut_candidat` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `libelle` VARCHAR(100) NOT NULL,
    PRIMARY KEY (`id_statut_candidat`),
    UNIQUE KEY `uk_statut_candidat_libelle` (`libelle`)
) ENGINE=InnoDB;

CREATE TABLE `statut_candidature` (
    `id_statut_candidature` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `libelle` VARCHAR(100) NOT NULL,
    PRIMARY KEY (`id_statut_candidature`),
    UNIQUE KEY `uk_statut_candidature_libelle` (`libelle`)
) ENGINE=InnoDB;

CREATE TABLE `status_entretien` (
    `id_statut_entretien` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `libelle` VARCHAR(100) NOT NULL,
    PRIMARY KEY (`id_statut_entretien`),
    UNIQUE KEY `uk_status_entretien_libelle` (`libelle`)
) ENGINE=InnoDB;

-- 2. Offres d'emploi
CREATE TABLE `Offre` (
    `id_offre` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `besoin_id` INT UNSIGNED NOT NULL,
    `id_rh` INT UNSIGNED NOT NULL,
    `statut_offre_id` INT UNSIGNED NOT NULL,
    `date_insertion` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id_offre`),
    KEY `idx_offre_besoin` (`besoin_id`),
    KEY `idx_offre_rh` (`id_rh`),
    KEY `idx_offre_statut` (`statut_offre_id`),
    CONSTRAINT `fk_offre_besoin`
        FOREIGN KEY (`besoin_id`) REFERENCES `Besoins` (`id`)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT `fk_offre_rh`
        FOREIGN KEY (`id_rh`) REFERENCES `utilisateurs` (`id`)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT `fk_offre_statut`
        FOREIGN KEY (`statut_offre_id`) REFERENCES `statut_offre` (`id_statut_offre`)
        ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

-- 3. Candidats
CREATE TABLE `candidats` (
    `id_candidat` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `nom` VARCHAR(100) NOT NULL,
    `prenom` VARCHAR(100) NOT NULL,
    `email` VARCHAR(255) NOT NULL,
    `statut_candidat_id` INT UNSIGNED NOT NULL,
    `date_insertion` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id_candidat`),
    UNIQUE KEY `uk_candidats_email` (`email`),
    KEY `idx_candidat_statut` (`statut_candidat_id`),
    CONSTRAINT `fk_candidat_statut`
        FOREIGN KEY (`statut_candidat_id`) REFERENCES `statut_candidat` (`id_statut_candidat`)
        ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

-- 4. Historique du statut des candidats
CREATE TABLE `HistoriqueStatutCandidat` (
    `id_historique_statut_candidat` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `candidat_id` INT UNSIGNED NOT NULL,
    `statut_candidat_id` INT UNSIGNED NOT NULL,
    `date_insertion` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `commentaire` TEXT NULL,
    PRIMARY KEY (`id_historique_statut_candidat`),
    KEY `idx_hist_candidat` (`candidat_id`),
    KEY `idx_hist_candidat_statut` (`statut_candidat_id`),
    CONSTRAINT `fk_hist_candidat`
        FOREIGN KEY (`candidat_id`) REFERENCES `candidats` (`id_candidat`)
        ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT `fk_hist_candidat_statut`
        FOREIGN KEY (`statut_candidat_id`) REFERENCES `statut_candidat` (`id_statut_candidat`)
        ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

-- 5. Candidatures (Lien Offre <-> Candidat)
CREATE TABLE `candidatures` (
    `id_candidature` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `offre_id` INT UNSIGNED NOT NULL,
    `candidat_id` INT UNSIGNED NOT NULL,
    `cv` VARCHAR(255) NOT NULL,
    `lettre_motivation` VARCHAR(255) NULL,
    `statut_candidature_id` INT UNSIGNED NOT NULL, -- Corrigé en FK pour cohérence avec la table de référence
    `date_insertion` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `date_modification` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `commentaire` TEXT NULL,
    PRIMARY KEY (`id_candidature`),
    UNIQUE KEY `uk_candidature_offre_candidat` (`offre_id`, `candidat_id`),
    KEY `idx_candidature_offre` (`offre_id`),
    KEY `idx_candidature_candidat` (`candidat_id`),
    KEY `idx_candidature_statut` (`statut_candidature_id`),
    CONSTRAINT `fk_candidature_offre`
        FOREIGN KEY (`offre_id`) REFERENCES `Offre` (`id_offre`)
        ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT `fk_candidature_candidat`
        FOREIGN KEY (`candidat_id`) REFERENCES `candidats` (`id_candidat`)
        ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT `fk_candidature_statut`
        FOREIGN KEY (`statut_candidature_id`) REFERENCES `statut_candidature` (`id_statut_candidature`)
        ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

-- 6. Entretiens
CREATE TABLE `entretiens` (
    `id_entretien` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `candidature_id` INT UNSIGNED NOT NULL,
    `date_entretien` DATETIME NOT NULL,
    `statut_entretien_id` INT UNSIGNED NOT NULL, -- Corrigé en FK pour pointer vers status_entretien
    `commentaire` TEXT NULL,
    `rh_id` INT UNSIGNED NOT NULL,
    PRIMARY KEY (`id_entretien`),
    KEY `idx_entretien_candidature` (`candidature_id`),
    KEY `idx_entretien_statut` (`statut_entretien_id`),
    KEY `idx_entretien_rh` (`rh_id`),
    CONSTRAINT `fk_entretien_candidature`
        FOREIGN KEY (`candidature_id`) REFERENCES `candidatures` (`id_candidature`)
        ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT `fk_entretien_statut`
        FOREIGN KEY (`statut_entretien_id`) REFERENCES `status_entretien` (`id_statut_entretien`)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT `fk_entretien_rh`
        FOREIGN KEY (`rh_id`) REFERENCES `utilisateurs` (`id`)
        ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

-- 7. Contrats
CREATE TABLE `contrats` (
    `id_contrat` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `candidature_id` INT UNSIGNED NOT NULL,
    `date_debut` DATE NOT NULL,
    `date_fin` DATE NULL,
    `salaire` DECIMAL(15,2) NOT NULL,
    `commentaire` TEXT NULL,
    PRIMARY KEY (`id_contrat`),
    UNIQUE KEY `uk_contrat_candidature` (`candidature_id`),
    CONSTRAINT `fk_contrat_candidature`
        FOREIGN KEY (`candidature_id`) REFERENCES `candidatures` (`id_candidature`)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT `chk_contrat_salaire` CHECK (`salaire` >= 0)
) ENGINE=InnoDB;

-- 8. Historique du statut des candidatures
CREATE TABLE `historique_candidature` (
    `id_historique_candidature` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `candidature_id` INT UNSIGNED NOT NULL,
    `statut_candidature_id` INT UNSIGNED NOT NULL,
    `date_insertion` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `commentaire` TEXT NULL,
    PRIMARY KEY (`id_historique_candidature`),
    KEY `idx_hist_cand_candidature` (`candidature_id`),
    KEY `idx_hist_cand_statut` (`statut_candidature_id`),
    CONSTRAINT `fk_hist_cand_candidature`
        FOREIGN KEY (`candidature_id`) REFERENCES `candidatures` (`id_candidature`)
        ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT `fk_hist_cand_statut`
        FOREIGN KEY (`statut_candidature_id`) REFERENCES `statut_candidature` (`id_statut_candidature`)
        ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;