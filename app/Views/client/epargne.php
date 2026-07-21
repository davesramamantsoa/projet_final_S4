<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>epargne de transfert</title>
</head>
<body>
    <form action="<?=base_url("client/enregistrer")?>" method="post">
        <label for="epargne">choisissez le pourcentage epargne</label>
        <input type="number" name="epargne" id="epargne">
        <input type="submit" value="valider">
    </form>
</body>
</html>