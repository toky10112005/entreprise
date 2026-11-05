INSERT INTO departements (libelle) VALUES
('Informatique'),
('Ressources Humaines'),
('Marketing'),
('Finance'),
('Ventes');

INSERT INTO postes (departement_id, libelle) VALUES
(1, 'Développeur'),
(1, 'Administrateur Système'),
(2, 'Responsable RH'),
(2, 'Chargé de Recrutement'),
(3, 'Chef de Produit'),
(3, 'Responsable Marketing Digital'),
(4, 'Analyste Financier'),
(4, 'Comptable'),
(5, 'Commercial'),
(5, 'Responsable des Ventes');

INSERT INTO types_besoins (libelle) VALUES
('Création de poste'),
('Remplacement de poste');

INSERT INTO statuts_demandes_recrutement (libelle, delai_modif) VALUES
('Brouillon',0),
('envoye_finance',3),
('envoye_rh',3),
('envoye_direction',3),
('valide',0),
('refuse',0);

INSERT INTO roles (libelle) VALUES
('candidat'),
('manager'),
('rh'),
('finance'),
('direction');

INSERT INTO utilisateurs (role_id, nom, prenom, email, mot_de_passe, departement_id, role, telephone, actif) VALUES
(2, 'Doe', 'John', 'john@gmail.com', '123', 1, 'manager', '1234567890', 1);


INSERT INTO motifs_recrutement (libelle,type_besoin_id) VALUES
('Démission',1),
('Licenciement',1),
('Retraite',1),
('Création de poste',1),
('Autre',1);

INSERT INTO workflow_decisions
    (statut_demande_recrutement_actuel_id, statut_demande_recrutement_suivant_id, role_decideur_id)
VALUES
    (1, 3, 2);

-- =============================================================
-- 3. Insertion des Utilisateurs (Managers, RH, Direction)
-- =============================================================
INSERT INTO `utilisateurs` (`id`, `role_id`, `nom`, `prenom`, `email`, `mot_de_passe`, `departement_id`, `role`, `actif`) VALUES
(2, 2, 'Rasoa', 'Marie', 'marie.rasoa@entreprise.mg', '$2y$10$exemplehashmotdepasse', 2, 'rh', 1),
(3, 3, 'Rabe', 'Paul', 'paul.rabe@entreprise.mg', '$2y$10$exemplehashmotdepasse', 3, 'finance', 1),
(4, 4, 'Andria', 'Luc', 'luc.andria@entreprise.mg', '$2y$10$exemplehashmotdepasse', NULL, 'direction', 1);

 INSERT INTO `Besoins` (`id`, `date_soumission_demande`, `manager_id`, `statut_actuel_id`) VALUES
(1, '2026-06-01 08:30:00', 1, 5), -- Statut 5 = 'valide'
(2, '2026-06-05 10:15:00', 1, 3); -- Statut 3 = 'valide'

-- =============================================================
-- 9. Insertion des Détails des Besoins (Poste, effectif, budget, etc.)
-- =============================================================
INSERT INTO `details_besoins` (`id`, `besoins_id`, `poste_id`, `effectif`, `motif_recrutement_id`, `date_delai_recrutement`, `commentaire`, `budget`) VALUES
(1, 1, 1, 2, 1, '2026-09-01', 'Besoin de renforcer l''équipe web pour le nouveau projet WanderMada.', 3500000.00),
(2, 2, 2, 1, 2, '2026-10-15', 'Remplacement suite au départ imminent de l''administrateur système.', 2800000.00);

-- =============================================================
-- 10. Insertion de l'historique pour tracer la validation
-- =============================================================
INSERT INTO `historiques_statuts_demandes_recrutement` (`statut_id`, `besoins_id`, `date_insertion`, `decideur_id`, `commentaire`) VALUES
(1, 1, '2026-06-01 08:30:00', 1, 'Création de la demande par le manager'),
(5, 1, '2026-06-02 14:00:00', 2, 'Validation finale du besoin validée par les RH et la Direction'),
(1, 2, '2026-06-05 10:15:00', 1, 'Création du besoin de remplacement'),
(3, 2, '2026-06-06 09:30:00', 4, 'Validation de la direction accordée');