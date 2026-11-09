-- =============================================================
-- AJOUT BLOC 2 : Détails des Postes (Fiches de poste)
-- =============================================================

CREATE TABLE `details_poste` (
    `id_detail_poste` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `poste_id` INT UNSIGNED NOT NULL,
    `salaire_min` DECIMAL(15, 2) NULL,
    `salaire_max` DECIMAL(15, 2) NULL,
    `objectifs` TEXT NULL COMMENT 'KPI ou objectifs principaux du poste',
    `valeur` VARCHAR(100) NULL COMMENT 'Indicateur ou valeur de référence',
    `mode_de_travail` VARCHAR(50) NULL COMMENT 'Ex: Présentiel, Télétravail, Hybride',
    `competences_techniques` TEXT NULL COMMENT 'Compétences techniques requises',
    `formation` TEXT NULL COMMENT 'Diplômes ou formations exigés',
    PRIMARY KEY (`id_detail_poste`),
    UNIQUE KEY `uk_details_poste_poste` (`poste_id`),
    CONSTRAINT `fk_details_poste_poste`
        FOREIGN KEY (`poste_id`) REFERENCES `postes` (`id`)
        ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT `chk_salaire_min_max` CHECK (`salaire_min` IS NULL OR `salaire_max` IS NULL OR `salaire_min` <= `salaire_max`),
    CONSTRAINT `chk_salaire_min_positif` CHECK (`salaire_min` IS NULL OR `salaire_min` >= 0),
    CONSTRAINT `chk_salaire_max_positif` CHECK (`salaire_max` IS NULL OR `salaire_max` >= 0)
) ENGINE=InnoDB;