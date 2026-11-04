## Bloc 1
- Departement (**id_departement**,libelle)
- Role (**id_role**,libelle) (manager,rh etc ...)
- Poste (**id_poste**,#id_departement,libelle)
- TypeBesoin (**id_type_besoin**,libelle) (creation na remplacement)
- DemandeRecrutement (**id_demande_recrutement**,date_soumission_demande,#id_manager,#id_statut_actuel)
- DetailDemandeRecrutement(**id_detail_demande_recrutement**,#id_demande_recrutement,#id_poste,effectif,#id_motif_recrutement,date_delai_recrutement,commentaire,budget)
- MotifRecrutement(**id_motif_recrutement**,libelle,#id_type_besoin) (nouveau projet , manque d'effectif na nouveau departement etc ...)
- StatutDemandeRecrutement (**id_statut_demande_recrutement**,libelle,delai_modif(`anaovana notification`))
- WorkflowDecision (**id_WorkflowDecision**,#id_statut_demande_recrutement_actuel,#id_statut_demande_recrutement_suivant,#id_role_decideur)
- HistoriqueStatutDemandeRecrutement(**id_historique_statue_demande**,#id_statut,#id_demande_recrutement,date_insertion,#id_decideur(`le nikitika bouton`), commentaire)
- Utilisateur(**id_utilisateur**,#id_role,nom,prenom,email,mot_de_passe,#id_departement)

Nokotiana:(Toky):
- DetailDemandeRecrutement :nanisy champ budget
- WorkflowDecision :nanisy clé primaire id_WorkflowDecision
- HistoriqueStatutDemandeRecrutement :nanisy champs commentaire(rehefa refus ilay statut an 'ilay Demande d ao no ahafantarana ny antony.D raha Validé d vide ilay commentaire')