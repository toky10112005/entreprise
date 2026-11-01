USE entreprise;

-- ------------------------------------------------------------
-- 1. Départements (sans responsable pour l'instant)
-- ------------------------------------------------------------
INSERT INTO departements (nom) VALUES
('Informatique'),
('Marketing'),
('Commercial');

-- ------------------------------------------------------------
-- 2. Managers (3 par département)
-- ------------------------------------------------------------
-- Mot de passe : même hash bcrypt que celui de l'ancien utilisateur de test
--MDP=tokybiby



INSERT INTO utilisateurs (nom, prenom, email, mot_de_passe, role, departement_id, poste, date_embauche) VALUES
-- Informatique
('Rakoto',      'Andry',   'andry.rakoto@example.com',      '$2y$10$xLbEhkqYmjDcxIC9fBDmp.rZOWZn/pXQVMUHpQnimWiJnemg7kYTG', 'manager', (SELECT id FROM departements WHERE nom = 'Informatique'), 'Manager Développement',    '2020-01-15'),
('Rasoanaivo',  'Hery',    'hery.rasoanaivo@example.com',   '$2y$10$xLbEhkqYmjDcxIC9fBDmp.rZOWZn/pXQVMUHpQnimWiJnemg7kYTG', 'manager', (SELECT id FROM departements WHERE nom = 'Informatique'), 'Manager Infrastructure',   '2020-03-01'),
('Randria',     'Voahangy','voahangy.randria@example.com',  '$2y$10$xLbEhkqYmjDcxIC9fBDmp.rZOWZn/pXQVMUHpQnimWiJnemg7kYTG', 'manager', (SELECT id FROM departements WHERE nom = 'Informatique'), 'Manager Support',          '2021-06-01'),
-- Marketing
('Ravelo',      'Tiana',   'tiana.ravelo@example.com',      '$2y$10$xLbEhkqYmjDcxIC9fBDmp.rZOWZn/pXQVMUHpQnimWiJnemg7kYTG', 'manager', (SELECT id FROM departements WHERE nom = 'Marketing'),    'Manager Communication',    '2019-09-01'),
('Andrianina',  'Mialy',   'mialy.andrianina@example.com',  '$2y$10$xLbEhkqYmjDcxIC9fBDmp.rZOWZn/pXQVMUHpQnimWiJnemg7kYTG', 'manager', (SELECT id FROM departements WHERE nom = 'Marketing'),    'Manager Digital',          '2020-11-01'),
('Rabe',        'Fanilo',  'fanilo.rabe@example.com',       '$2y$10$xLbEhkqYmjDcxIC9fBDmp.rZOWZn/pXQVMUHpQnimWiJnemg7kYTG', 'manager', (SELECT id FROM departements WHERE nom = 'Marketing'),    'Manager Création',         '2021-02-15'),
-- Commercial
('Razafy',      'Lanto',   'lanto.razafy@example.com',      '$2y$10$xLbEhkqYmjDcxIC9fBDmp.rZOWZn/pXQVMUHpQnimWiJnemg7kYTG', 'manager', (SELECT id FROM departements WHERE nom = 'Commercial'),   'Manager Ventes',           '2019-05-01'),
('Rakotomalala','Njaka',   'njaka.rakotomalala@example.com','$2y$10$xLbEhkqYmjDcxIC9fBDmp.rZOWZn/pXQVMUHpQnimWiJnemg7kYTG', 'manager', (SELECT id FROM departements WHERE nom = 'Commercial'),   'Manager Grands comptes',   '2020-08-01'),
('Andriamanana','Soa',     'soa.andriamanana@example.com',  '$2y$10$xLbEhkqYmjDcxIC9fBDmp.rZOWZn/pXQVMUHpQnimWiJnemg7kYTG', 'manager', (SELECT id FROM departements WHERE nom = 'Commercial'),   'Manager Service client',   '2021-10-01');

-- ------------------------------------------------------------
-- 3. RH et Direction
-- ------------------------------------------------------------
INSERT INTO utilisateurs (nom, prenom, email, mot_de_passe, role, poste, date_embauche) VALUES
('Rasolofo',   'Hanta',  'hanta.rasolofo@example.com',  '$2y$10$xLbEhkqYmjDcxIC9fBDmp.rZOWZn/pXQVMUHpQnimWiJnemg7kYTG', 'rh',        'Responsable RH',     '2018-01-10'),
('Ramanantsoa','Mamy',   'mamy.ramanantsoa@example.com','$2y$10$xLbEhkqYmjDcxIC9fBDmp.rZOWZn/pXQVMUHpQnimWiJnemg7kYTG', 'direction', 'Directeur Général',  '2017-01-02');

-- ------------------------------------------------------------
-- 4. Responsable principal de chaque département (premier manager)
-- ------------------------------------------------------------
UPDATE departements d
JOIN utilisateurs u ON u.email = 'andry.rakoto@example.com'
SET d.manager_id = u.id
WHERE d.nom = 'Informatique';

UPDATE departements d
JOIN utilisateurs u ON u.email = 'tiana.ravelo@example.com'
SET d.manager_id = u.id
WHERE d.nom = 'Marketing';

UPDATE departements d
JOIN utilisateurs u ON u.email = 'lanto.razafy@example.com'
SET d.manager_id = u.id
WHERE d.nom = 'Commercial';