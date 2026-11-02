# Ajouter un système de filtre par rôle pour les utilisateurs:

- [x] Ajouter le Middleware côté CI4    OK
- [x] ajouter les router par filtre     OK
- [ ] Récupérer les Erreur(satatus 403) côté front et afficher le message d'erreur

> **Note:à savoir le concept de token utilisé ici.**
>
> Ici chaque utilisateur qui s'authentifie reçoit un token JWT (JSON Web Token) qui contient des informations sur l'utilisateur, y compris son rôle. Ce token est ensuite utilisé pour vérifier l'accès aux routes protégées par le filtre de rôle. Si un utilisateur tente d'accéder à une route pour laquelle il n'a pas les autorisations appropriées, le serveur renverra une réponse avec le statut 403 (Forbidden), indiquant que l'accès est refusé.

> Raha misy test ana Vue atao de le view/testconnex.vue kitihana f aza mamorina fichier instony

## I.Bloc 1:

## Backend (CodeIgniter 4)

### 1. Models

- DepartementModel (departements)
- PosteModel ($table = 'poste')
- BesoinModel (besoins)
- BesoinValidationModel (besoin_validations)
- BudgetMouvementModel (budget_mouvements)
- $allowedFields pour chaque model. Pour BesoinModel : reference, departement_id, manager_id, poste_id, intitule_poste, nb_postes, type_contrat, motif, niveau_experience, date_prise_poste, missions, competences, urgence, statut

#### Méthodes utiles :

- besoins d'un manager
- besoins par statut
- besoin avec jointures (département, poste, manager)
- historique de besoin_validations avec le nom du validateur
- lecture du solde d'un département depuis la vue v_budget_departement (colonne solde_disponible)

### 2. Authentification et rôles

- [ ] Installer php8.3-xml (sudo apt install php8.3-xml) puis composer require firebase/php-jwt
- [ ] JWT_SECRET dans .env
- [ ] Login : mettre sub et role dans le JWT, et refuser les comptes actif = 0
- [ ] RoleFilter + alias role dans Filters.php
- [ ] Le filtre expose l'id et le rôle de l'utilisateur au contrôleur
- [ ] register : ne jamais laisser le client choisir role (forcer candidat ou réserver la création aux admins/RH)
- [ ] Rôles autorisés sur les routes : manager, rh, finance, direction

### 3. Service de workflow (BesoinWorkflowService)

- [ ] Une méthode par action : envoyerAuRh, rhTransmet, rhRenvoie, rhRefuse, financeBudgetOk, financeBudgetKo, directionValide, directionRefuse
- [ ] Chaque méthode vérifie le statut courant (ex. rhTransmet seulement si envoye_rh), sinon erreur 409/422
- [ ] Chaque méthode est dans une transaction : changement de besoins.statut + insertion dans besoin_validations (avec utilisateur_id pris dans le token, jamais dans le body)
- [ ] Génération de reference unique (ex. BES-2026-0001)
- [ ] Contrôle côté finance : comparer le coût estimé (nb_postes × salaire_max du poste, ou règle propre) au solde_disponible du département
- [ ] À la validation Direction : insérer dans budget_mouvements une ligne sens = 'sortie', categorie = 'salaire_recrutement', statut = 'engage', besoin_id renseigné, cree_par = l'utilisateur direction, exercice_id de l'exercice ouvert (montant > 0, contrainte CHECK)

### 4. Contrôleurs et routes

- [ ] DepartementController::index et PosteController::index (listes pour le formulaire, accessibles à tout utilisateur connecté)
- [ ] BesoinController :
  - POST /api/besoins (manager) : crée en brouillon. manager_id et departement_id viennent du token / de l'utilisateur, pas du front
  - GET /api/besoins : manager = les siens ; rh = envoye_rh ; finance = envoye_finance ; direction = envoye_direction
  - GET /api/besoins/{id} : détail + historique des validations (vérifier le droit de lecture)
  - PUT /api/besoins/{id} : seulement en brouillon et par son propriétaire
  - DELETE /api/besoins/{id} : seulement en brouillon, par son propriétaire
  - POST /api/besoins/{id}/envoyer (manager)
- Actions de validation :
  - POST /api/besoins/{id}/rh/transmettre, /rh/renvoyer, /rh/refuser (rh)
  - POST /api/besoins/{id}/finance/valider-budget, /finance/refuser-budget (finance)
  - POST /api/besoins/{id}/direction/valider, /direction/refuser (direction)
- [ ] Groupes de routes avec ['filter' => 'role:manager'], role:rh, role:finance, role:direction (groupes avec un nom de préfixe)

### 5. Validation des données

- [ ] intitule_poste requis (max 150)
- [ ] poste_id doit exister dans poste
- [ ] nb_postes entier ≥ 1
- [ ] type_contrat dans CDI,CDD,CE
- [ ] motif dans manque_effectif,remplacement,nouveau_projet
- [ ] urgence dans normal,urgent
- [ ] date_prise_poste date valide et dans le futur
- [ ] commentaire obligatoire pour refuser et renvoyer
- [ ] Codes HTTP : 400/422 validation, 401 non connecté, 403 mauvais rôle ou pas propriétaire, 404 introuvable, 409 transition impossible

### 7. Test

## FRONT (Vue JS)

### 1. Base

- [ ] Axios (baseURL, ajout du Bearer), intercepteur 401 → login, 403 → message « Accès refusé »
- [ ] Store Pinia : user, token, role (en minuscules, comme en base), persistés dans localStorage
- [ ] Fichier de constantes pour les statuts, motifs, types de contrat et leurs libellés français

### 2. Navigation

- [ ] Page login
- [ ] Navigation guard avec meta.roles (['manager'], ['rh'], ['finance'], ['direction'])
- [ ] Menu selon le rôle (Manager : « Mes besoins » ; RH : « Besoins à traiter » ; Finance : « Contrôle budget » ; Direction : « À valider »)

### 3. Écrans Manager

- [ ] Formulaire « Nouveau besoin » : poste (liste poste), intitulé, nombre de postes, type de contrat, motif, niveau d'expérience, date de prise de poste, missions, compétences, urgence
- [ ] Département affiché mais non modifiable (il vient du compte)
- [ ] Liste « Mes besoins » avec badge de statut et filtre par statut
- [ ] Détail avec historique des validations
- [ ] Boutons Modifier / Supprimer / Envoyer au RH (visibles seulement en brouillon)
- [ ] Si un besoin est renvoyé (renvoye), afficher le commentaire du RH

### 4. Écrans RH

- [ ] Liste des besoins envoye_rh
- [ ] Détail avec boutons Transmettre, Renvoyer, Refuser et champ commentaire

### 5. Écrans Finance

- [ ] Liste des besoins envoye_finance
- [ ] Détail affichant la fourchette salariale du poste, le coût estimé et le solde du département
- [ ] Boutons Budget adéquat / Budget inadéquat avec commentaire

### 6. Écrans Direction

- [ ] Liste des besoins envoye_direction
- [ ] Détail complet avec tout l'historique (RH, Finance)
- [ ] Boutons Valider / Refuser avec commentaire

### 7. Composants et finitions

- [ ] StatutBadge, BesoinTable, ValidationTimeline, modale de confirmation
- [ ] Toasts de succès/erreur, états de chargement, affichage des erreurs de validation renvoyées par l'API
- [ ] Test du parcours complet avec les 4 comptes
