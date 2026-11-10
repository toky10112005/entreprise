## candidature a une offre d'emploi
- dossiers a fournir : cv , lettre de motivation
 ### Workflow
 - le candidat clique sur le bouton postuler et
    tombe sur un formulaire a remplir:
        - nom , prenom
        - email
        - cv
        - lettre de motivation
 - le bouton envoyer appelle une api qui cree le candidat et la
 candidature en meme temps avec statut en attente

 Ensuite , une phase de preselection

 curl -i -X POST "http://localhost:8081/candidature/postuler"\
    -F "offre_id=1"\
    -F "email=monmail@gmail.com"\
    -F "nom=monnom"\
    -F "prenom=monprenom"\
    -F "cv=@/home/andrianina/s5/web/conception/niveau-1-enonce.pdf"\
    -F "lettre_motivation=@/home/andrianina/s5/web/conception/niveau-1-enonce.pdf"