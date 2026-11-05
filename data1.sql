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