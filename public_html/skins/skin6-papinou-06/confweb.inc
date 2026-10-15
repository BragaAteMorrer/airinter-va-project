<?php
if (stristr($_SERVER["SCRIPT_NAME"], "confweb.inc")) {
    header("location:../index.php");
    die();
}

$site[4]  = stripslashes("2");  // Nouvelles sur la page d accueil
$site[20] = stripslashes("5"); // Messages Forum sur la page d accueil
// site[21] = stripslashes(""); // Inutilisé
$site[29] = stripslashes("0"); // Hauteur en pixels des nouvelles en page d accueil
$site[31] = stripslashes("1"); // Blog sur la page d accueil
$site[33] = stripslashes("70"); // Hauteur en pixels des billets en page d accueil
$site[34] = stripslashes("40"); // Nombre de caractères des posts forum en page d accueil

$serviz[28]  = stripslashes("on");  // Préférences visiteurs
$serviz[38]  = stripslashes("on");  // RSS News ON / OFF
$serviz[74]  = stripslashes(""); // Cacher les boîtes en page d accueil

// Configurations accueil et pages secondaires
// $xposbox["E"][LS]
$xposbox["E"]["LS"][0] = stripslashes("");
$xposbox["E"]["LS"][1] = stripslashes("");
$xposbox["E"]["LS"][2] = stripslashes("");
$xposbox["E"]["LS"][3] = stripslashes("");
$xposbox["E"]["LS"][4] = stripslashes("");
$xposbox["E"]["LS"][5] = stripslashes("");
$xposbox["E"]["LS"][6] = stripslashes("");

// $xposbox["E"][L]
$xposbox["E"]["L"][0] = stripslashes("");
$xposbox["E"]["L"][1] = stripslashes("");
$xposbox["E"]["L"][2] = stripslashes("||fbox_news||W12");
$xposbox["E"]["L"][3] = stripslashes("");
$xposbox["E"]["L"][4] = stripslashes("||fbox_forum||W12");
$xposbox["E"]["L"][5] = stripslashes("");
$xposbox["E"]["L"][6] = stripslashes("");

// $xposbox["E"][C]
$xposbox["E"]["C"][0] = stripslashes("");
$xposbox["E"]["C"][1] = stripslashes("");
$xposbox["E"]["C"][2] = stripslashes("");
$xposbox["E"]["C"][3] = stripslashes("");
$xposbox["E"]["C"][4] = stripslashes("");
$xposbox["E"]["C"][5] = stripslashes("");
$xposbox["E"]["C"][6] = stripslashes("");

// $xposbox["E"][R]
$xposbox["E"]["R"][0] = stripslashes("");
$xposbox["E"]["R"][1] = stripslashes("");
$xposbox["E"]["R"][2] = stripslashes("");
$xposbox["E"]["R"][3] = stripslashes("");
$xposbox["E"]["R"][4] = stripslashes("");
$xposbox["E"]["R"][5] = stripslashes("");
$xposbox["E"]["R"][6] = stripslashes("");

// $xposbox["E"][RS]
$xposbox["E"]["RS"][0] = stripslashes("");
$xposbox["E"]["RS"][1] = stripslashes("");
$xposbox["E"]["RS"][2] = stripslashes("");
$xposbox["E"]["RS"][3] = stripslashes("");
$xposbox["E"]["RS"][4] = stripslashes("");
$xposbox["E"]["RS"][5] = stripslashes("");
$xposbox["E"]["RS"][6] = stripslashes("");

// $xposbox["B"][L]
$xposbox["B"]["L"][0] = stripslashes("");
$xposbox["B"]["L"][1] = stripslashes("");
$xposbox["B"]["L"][2] = stripslashes("");
$xposbox["B"]["L"][3] = stripslashes("");
$xposbox["B"]["L"][4] = stripslashes("");
$xposbox["B"]["L"][5] = stripslashes("");
$xposbox["B"]["L"][6] = stripslashes("");
$xposbox["B"]["L"][7] = stripslashes("");

// $xposbox["B"][R]
$xposbox["B"]["R"][0] = stripslashes("||fbox_blogcat");
$xposbox["B"]["R"][1] = stripslashes("||fbox_bloglast");
$xposbox["B"]["R"][2] = stripslashes("||fbox_blogcom");
$xposbox["B"]["R"][3] = stripslashes("||fbox_blogcal");
$xposbox["B"]["R"][4] = stripslashes("||fbox_blogrss");
$xposbox["B"]["R"][5] = stripslashes("||fbox_blogarch");
$xposbox["B"]["R"][6] = stripslashes("");
$xposbox["B"]["R"][7] = stripslashes("");


// NE PAS SUPPRIMER $templates[0]

$templates[0] = stripslashes("Principale");
$templates[1] = stripslashes("demo_jQuery");
//////////////////// Principale ////////////////////
// $xposbox[Principale][L]
$xposbox[0]["L"][0] = stripslashes("||fbox_freebox||6");
$xposbox[0]["L"][1] = stripslashes("");
$xposbox[0]["L"][2] = stripslashes("||fbox_menubox||5412");
$xposbox[0]["L"][3] = stripslashes("||fbox_menubox||5984");
$xposbox[0]["L"][4] = stripslashes("||fbox_menubox||5976");
$xposbox[0]["L"][5] = stripslashes("");
$xposbox[0]["L"][6] = stripslashes("||fbox_menubox||5981");
$xposbox[0]["L"][7] = stripslashes("||fbox_cal");
$xposbox[0]["L"][8] = stripslashes("||fbox_contact");
$xposbox[0]["L"][9] = stripslashes("||fbox_search");
$xposbox[0]["L"][10] = stripslashes("||fbox_cita");
$xposbox[0]["L"][11] = stripslashes("");
$xposbox[0]["L"][12] = stripslashes("");

// $xposbox[Principale][R]
$xposbox[0]["R"][0] = stripslashes("");
$xposbox[0]["R"][1] = stripslashes("");
$xposbox[0]["R"][2] = stripslashes("");
$xposbox[0]["R"][3] = stripslashes("");
$xposbox[0]["R"][4] = stripslashes("");
$xposbox[0]["R"][5] = stripslashes("");
$xposbox[0]["R"][6] = stripslashes("");
$xposbox[0]["R"][7] = stripslashes("");
$xposbox[0]["R"][8] = stripslashes("");
$xposbox[0]["R"][9] = stripslashes("");
$xposbox[0]["R"][10] = stripslashes("");
$xposbox[0]["R"][11] = stripslashes("");
$xposbox[0]["R"][12] = stripslashes("");

// $xposbox[Principale][A]
$xposbox[0]["A"][0] = stripslashes("||fbox_freebox||2");
$xposbox[0]["A"][1] = stripslashes("");

// $xposbox[Principale][U]
$xposbox[0]["U"][0] = stripslashes("");
$xposbox[0]["U"][1] = stripslashes("");

// $xposbox[Principale][T][LS]
$xposbox[0]["T"]["LS"][0] = stripslashes("||fbox_menu||W12");
$xposbox[0]["T"]["LS"][1] = stripslashes("");

// $xposbox[Principale][T][L]
$xposbox[0]["T"]["L"][0] = stripslashes("");
$xposbox[0]["T"]["L"][1] = stripslashes("");

// $xposbox[Principale][T][C]
$xposbox[0]["T"]["C"][0] = stripslashes("");
$xposbox[0]["T"]["C"][1] = stripslashes("");

// $xposbox[Principale][T][R]
$xposbox[0]["T"]["R"][0] = stripslashes("");
$xposbox[0]["T"]["R"][1] = stripslashes("");

// $xposbox[Principale][T][RS]
$xposbox[0]["T"]["RS"][0] = stripslashes("");
$xposbox[0]["T"]["RS"][1] = stripslashes("");

// $xposbox[Principale][B][LS]
$xposbox[0]["B"]["LS"][0] = stripslashes("");
$xposbox[0]["B"]["LS"][1] = stripslashes("||fbox_user||W6");
$xposbox[0]["B"]["LS"][2] = stripslashes("||fbox_freebox||1W12");
$xposbox[0]["B"]["LS"][3] = stripslashes("");

// $xposbox[Principale][B][L]
$xposbox[0]["B"]["L"][0] = stripslashes("");
$xposbox[0]["B"]["L"][1] = stripslashes("||fbox_webm||W6");
$xposbox[0]["B"]["L"][2] = stripslashes("");
$xposbox[0]["B"]["L"][3] = stripslashes("");

// $xposbox[Principale][B][C]
$xposbox[0]["B"]["C"][0] = stripslashes("");
$xposbox[0]["B"]["C"][1] = stripslashes("");
$xposbox[0]["B"]["C"][2] = stripslashes("");
$xposbox[0]["B"]["C"][3] = stripslashes("");

// $xposbox[Principale][B][R]
$xposbox[0]["B"]["R"][0] = stripslashes("");
$xposbox[0]["B"]["R"][1] = stripslashes("");
$xposbox[0]["B"]["R"][2] = stripslashes("");
$xposbox[0]["B"]["R"][3] = stripslashes("");

// $xposbox[Principale][B][RS]
$xposbox[0]["B"]["RS"][0] = stripslashes("");
$xposbox[0]["B"]["RS"][1] = stripslashes("");
$xposbox[0]["B"]["RS"][2] = stripslashes("");
$xposbox[0]["B"]["RS"][3] = stripslashes("");

//////////////////// demo_jQuery ////////////////////
// $xposbox[demo_jQuery][L]
$xposbox[1]["L"][0] = stripslashes("||fbox_freebox||382");
$xposbox[1]["L"][1] = stripslashes("||fbox_menubox||383");
$xposbox[1]["L"][2] = stripslashes("||fbox_freebox||390");
$xposbox[1]["L"][3] = stripslashes("");
$xposbox[1]["L"][4] = stripslashes("");
$xposbox[1]["L"][5] = stripslashes("");
$xposbox[1]["L"][6] = stripslashes("");
$xposbox[1]["L"][7] = stripslashes("");
$xposbox[1]["L"][8] = stripslashes("");
$xposbox[1]["L"][9] = stripslashes("");

// $xposbox[demo_jQuery][R]
$xposbox[1]["R"][0] = stripslashes("");
$xposbox[1]["R"][1] = stripslashes("");
$xposbox[1]["R"][2] = stripslashes("");
$xposbox[1]["R"][3] = stripslashes("");
$xposbox[1]["R"][4] = stripslashes("");
$xposbox[1]["R"][5] = stripslashes("");
$xposbox[1]["R"][6] = stripslashes("");
$xposbox[1]["R"][7] = stripslashes("");
$xposbox[1]["R"][8] = stripslashes("");
$xposbox[1]["R"][9] = stripslashes("");

// $xposbox[demo_jQuery][A]
$xposbox[1]["A"][0] = stripslashes("");
$xposbox[1]["A"][1] = stripslashes("");

// $xposbox[demo_jQuery][U]
$xposbox[1]["U"][0] = stripslashes("");
$xposbox[1]["U"][1] = stripslashes("");

// $xposbox[demo_jQuery][T][LS]
$xposbox[1]["T"]["LS"][0] = stripslashes("");
$xposbox[1]["T"]["LS"][1] = stripslashes("");
$xposbox[1]["T"]["LS"][2] = stripslashes("");
$xposbox[1]["T"]["LS"][3] = stripslashes("");

// $xposbox[demo_jQuery][T][L]
$xposbox[1]["T"]["L"][0] = stripslashes("");
$xposbox[1]["T"]["L"][1] = stripslashes("||fbox_socnet||W8");
$xposbox[1]["T"]["L"][2] = stripslashes("||fbox_menu||W12");
$xposbox[1]["T"]["L"][3] = stripslashes("");

// $xposbox[demo_jQuery][T][C]
$xposbox[1]["T"]["C"][0] = stripslashes("");
$xposbox[1]["T"]["C"][1] = stripslashes("");
$xposbox[1]["T"]["C"][2] = stripslashes("");
$xposbox[1]["T"]["C"][3] = stripslashes("");

// $xposbox[demo_jQuery][T][R]
$xposbox[1]["T"]["R"][0] = stripslashes("");
$xposbox[1]["T"]["R"][1] = stripslashes("||fbox_logo||W4");
$xposbox[1]["T"]["R"][2] = stripslashes("");
$xposbox[1]["T"]["R"][3] = stripslashes("");

// $xposbox[demo_jQuery][T][RS]
$xposbox[1]["T"]["RS"][0] = stripslashes("");
$xposbox[1]["T"]["RS"][1] = stripslashes("");
$xposbox[1]["T"]["RS"][2] = stripslashes("");
$xposbox[1]["T"]["RS"][3] = stripslashes("");

// $xposbox[demo_jQuery][B][LS]
$xposbox[1]["B"]["LS"][0] = stripslashes("");
$xposbox[1]["B"]["LS"][1] = stripslashes("");

// $xposbox[demo_jQuery][B][L]
$xposbox[1]["B"]["L"][0] = stripslashes("||fbox_ariane||W12");
$xposbox[1]["B"]["L"][1] = stripslashes("");

// $xposbox[demo_jQuery][B][C]
$xposbox[1]["B"]["C"][0] = stripslashes("");
$xposbox[1]["B"]["C"][1] = stripslashes("");

// $xposbox[demo_jQuery][B][R]
$xposbox[1]["B"]["R"][0] = stripslashes("");
$xposbox[1]["B"]["R"][1] = stripslashes("");

// $xposbox[demo_jQuery][B][RS]
$xposbox[1]["B"]["RS"][0] = stripslashes("");
$xposbox[1]["B"]["RS"][1] = stripslashes("");

?>