<?php
session_start();
$a = rand(1,9);
$b = rand(1,9);
$_SESSION['captcha'] = $a + $b;
?>
<!DOCTYPE html>
<html lang="fr">
<head><meta charset="UTF-8"><title>Formulaire d'inscription</title></head>
<body>
<h2>Inscription Air Inter VA</h2>
<form method="POST" action="traitement.php">
    Nom: <input type="text" name="nom" required><br>
    Prénom: <input type="text" name="prenom" required><br>
    Email: <input type="email" name="email" required><br>
    Ville: <input type="text" name="ville"><br>
    Pays: <input type="text" name="pays"><br>
    ID IVAO: <input type="text" name="ivao"><br>
    ID VATSIM: <input type="text" name="vatsim"><br>
    Heures de vol globales: <input type="number" name="heures"><br>
    Expérience vol réel: 
    <select name="vol_reel">
        <option value="oui">Oui</option>
        <option value="non">Non</option>
    </select><br>
    Simulateur utilisé:
    <select name="simulateur">
        <option value="FS2004">FS2004</option>
        <option value="FSX">FSX</option>
        <option value="X-Plane">X-Plane</option>
        <option value="MSFS2020">MSFS2020</option>
        <option value="MSFS2024">MSFS2024</option>
    </select><br>
    Avez-vous lu le MANOPS/MANEX: 
    <select name="manops">
        <option value="oui">Oui</option>
        <option value="non">Non</option>
    </select><br>
    Membre d'une autre VA:
    <select name="autre_va">
        <option value="oui">Oui</option>
        <option value="non">Non</option>
    </select><br>
    Motivations:<br>
    <textarea name="motivations" rows="4" cols="40"></textarea><br><br>

    Anti-spam: combien font <?php echo $a ?> + <?php echo $b ?> ?
    <input type="number" name="captcha" required><br><br>

    <button type="submit">Envoyer</button>
</form>
</body></html>
