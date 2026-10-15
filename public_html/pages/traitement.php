<?php
session_start();

// ---------------------------------------------------------
// VÉRIFICATION DU CAPTCHA
// ---------------------------------------------------------
if (!isset($_POST['anti_spam']) || trim($_POST['anti_spam']) === '') {
    die("Erreur : veuillez répondre à la question anti-spam !");
}
if (trim($_POST['anti_spam']) !== '10') {
    die("Erreur : CAPTCHA incorrect !");
}

// ---------------------------------------------------------
// VALIDATION ET NETTOYAGE DES DONNÉES
// ---------------------------------------------------------
function clean($field) {
    return htmlspecialchars(trim($field));
}

$nom = clean($_POST['nom']);
$prenom = clean($_POST['prenom']);
$email = clean($_POST['email']);
$ville = clean($_POST['ville']);
$pays = clean($_POST['pays']);
$id_ivao = clean($_POST['id_ivao']);
$id_vatsim = clean($_POST['id_vatsim']);
$heures_vol = clean($_POST['heures_vol']);
$exp_vol = clean($_POST['exp_vol']);
$simulateur = clean($_POST['simulateur']);
$manops = clean($_POST['manops']);
$autre_va = clean($_POST['autre_va']);
$quelle_va = clean($_POST['quelle_va']);
$motivations = clean($_POST['motivations']);

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    die("Erreur : adresse email invalide !");
}

// ---------------------------------------------------------
// ENVOI DE L'EMAIL
// ---------------------------------------------------------
$destinataire = "it001alex@airinter-va.org"; // ⬅️ À MODIFIER
$sujet = "Nouvelle demande d'inscription - Air Inter VA";

$message = "
Nouvelle inscription reçue :\n
Nom : $nom
Prénom : $prenom
Email : $email
Ville : $ville
Pays : $pays
ID IVAO : $id_ivao
ID VATSIM : $id_vatsim
Heures de vol : $heures_vol
Expérience vol réel : $exp_vol
Simulateur : $simulateur
A lu MANOPS/MANEX : $manops
Membre autre VA : $autre_va
Quelle VA : $quelle_va
Motivations :
$motivations
";

$headers = "From: Air Inter VA <noreply@airinter-va.org>\r\n";
$headers .= "Reply-To: $email\r\n";

// Envoi
@mail($destinataire, $sujet, $message, $headers);

// ---------------------------------------------------------
// REDIRECTION VERS MERCI
// ---------------------------------------------------------
$_SESSION['nom'] = $nom;
$_SESSION['prenom'] = $prenom;
header("Location: https://www.airinter-va.org/pages/merci.php");
exit;
?>
