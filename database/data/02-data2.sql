-- =============================================================
-- 1. Insertion des Statuts de Référence (Bloc 2)
-- =============================================================
INSERT INTO workflow_decisions
    (statut_demande_recrutement_actuel_id, statut_demande_recrutement_suivant_id, role_decideur_id)
VALUES
    (5, 7, 3);
    
INSERT INTO statuts_demandes_recrutement (libelle, delai_modif) VALUES
('en_recrutement',0);

 INSERT INTO `Besoins` (`id`, `date_soumission_demande`, `manager_id`, `statut_actuel_id`) VALUES
(3, '2026-06-01 08:30:00', 1, 5); -- Statut 5 = 'valide'

INSERT INTO `details_besoins` (`id`, `besoins_id`, `poste_id`, `effectif`, `motif_recrutement_id`, `date_delai_recrutement`, `commentaire`, `budget`) VALUES
(3, 3, 1, 2, 1, '2026-09-01', 'Besoin de renforcer l''équipe web pour le nouveau projet WanderMada.', 3500000.00);

INSERT INTO `statut_offre` (`id_statut_offre`, `libelle`) VALUES
(1, 'Brouillon'),
(2, 'clôturé');

INSERT INTO `statut_candidat` (`id_statut_candidat`, `libelle`) VALUES
(1, 'en attente'),
(2, 'retenu'),
(3, 'rejeté');

INSERT INTO `statut_candidature` (`id_statut_candidature`, `libelle`) VALUES
(1, 'en attente'),
(2, 'miandry entretien'),
(3, 'rejeté');

INSERT INTO `status_entretien` (`id_statut_entretien`, `libelle`) VALUES
(1, 'en attente'),
(2, 'validé'),
(3, 'annulé');




