<?php
declare(strict_types=1);

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    exit('Méthode non autorisée.');
}

// Keep the existing HTML form and its anti-spam question unchanged.
// Its fixed answer is not sufficient protection against automated submissions.
$antiSpam = $_POST['anti_spam'] ?? null;
if (!is_string($antiSpam) || trim($antiSpam) !== '10') {
    http_response_code(400);
    exit('Erreur : CAPTCHA incorrect !');
}

function inputText(string $key, int $maxLength = 200): string
{
    $value = $_POST[$key] ?? '';
    if (!is_string($value)) {
        http_response_code(400);
        exit('Erreur : champ invalide.');
    }

    $value = trim($value);
    if (strlen($value) > $maxLength) {
        http_response_code(400);
        exit('Erreur : champ trop long.');
    }

    // Keep newlines in free-text comments but prevent control bytes.
    return preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $value) ?? '';
}

$nom = inputText('nom');
$prenom = inputText('prenom');
$email = inputText('email');
$ville = inputText('ville');
$pays = inputText('pays');
$id_ivao = inputText('id_ivao');
$id_vatsim = inputText('id_vatsim');
$heures_vol = inputText('heures_vol');
$exp_vol = inputText('exp_vol');
$simulateur = inputText('simulateur');
$manops = inputText('manops');
$autre_va = inputText('autre_va');
$quelle_va = inputText('quelle_va');
$motivations = inputText('motivations', 5000);

if ($nom === '' || $prenom === '' || $email === '') {
    http_response_code(400);
    exit('Erreur : nom, prénom et adresse e-mail obligatoires.');
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL) || preg_match('/[\r\n]/', $email)) {
    http_response_code(400);
    exit('Erreur : adresse e-mail invalide.');
}

$destinataire = 'it001alex@airinter-va.org';
$sujet = 'Nouvelle demande d\'inscription - Air Inter VA';

$fields = [
    'Nom' => $nom,
    'Prénom' => $prenom,
    'Email' => $email,
    'Ville' => $ville,
    'Pays' => $pays,
    'ID IVAO' => $id_ivao,
    'ID VATSIM' => $id_vatsim,
    'Heures de vol' => $heures_vol,
    'Expérience vol réel' => $exp_vol,
    'Simulateur' => $simulateur,
    'A lu MANOPS/MANEX' => $manops,
    'Membre autre VA' => $autre_va,
    'Quelle VA' => $quelle_va,
    'Motivations' => $motivations,
];
$message = "Nouvelle inscription reçue :\n\n";
foreach ($fields as $label => $value) {
    $message .= $label . ' : ' . $value . "\n";
}

$headers = [
    'From' => 'Air Inter VA <noreply@airinter-va.org>',
    'Reply-To' => $email,
    'Content-Type' => 'text/plain; charset=UTF-8',
];

if (!mail($destinataire, $sujet, $message, $headers)) {
    error_log('Air Inter VA: failed to send registration email.');
    http_response_code(503);
    exit("Erreur : votre demande n'a pas pu être transmise. Veuillez réessayer ultérieurement.");
}

$_SESSION['nom'] = htmlspecialchars($nom, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
$_SESSION['prenom'] = htmlspecialchars($prenom, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
header('Location: https://www.airinter-va.org/pages/merci.php', true, 303);
exit;
