<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Details</title>
</head>
<body>
    <h1>Détails</h1>
    <p>Nom: <?= $etudiant['nom'] ?></p>
    <p>Prénom: <?= $etudiant['prenom'] ?></p>
    <p>Âge: <?= $etudiant['age'] ?></p>

    <a href="/">Retour à la liste</a>
</body>
</html>