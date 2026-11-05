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