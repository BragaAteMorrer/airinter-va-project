<?php
mysql_connect("sql5", "airinterva", "2526305");
mysql_select_db("airinterva");

// Sélectionner les 100 premières entrées de la table vols_horaire
$reponse = mysql_query("SELECT num_vol FROM horaire LIMIT 0, 100") or die(mysql_error());

echo "Voici les 100 premières entrées de la table horaire :<p>";

while ($donnees = mysql_fetch_array($reponse) )
{
?>

<?php echo $donnees['num_vol']; ?><br />

<?php
}

mysql_close(); // Déconnexion de MySQL
?>