Ajouter un système de filtre par rôle pour les utilisateurs:
    1.Ajouter le Middleware côté CI4    OK
    2.ajouter les router par filtre     OK
    3.Récupérer les Erreur(satatus 403) côté front et afficher le message d'erreur  

Note:à savoir le concept de token utilisé ici.
Ici chaque utilisateur qui s'authentifie reçoit un token JWT (JSON Web Token) qui contient des informations sur l'utilisateur, y compris son rôle. Ce token est ensuite utilisé pour vérifier l'accès aux routes protégées par le filtre de rôle. Si un utilisateur tente d'accéder à une route pour laquelle il n'a pas les autorisations appropriées, le serveur renverra une réponse avec le statut 403 (Forbidden), indiquant que l'accès est refusé. 


Raha misy test ana Vue atao de le view/testconnex.vue kitihana f aza mamorina fichier instony

I.Bloc 1:

