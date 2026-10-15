<?php
$type = 'ar';
$fileid = '2011';
$status = 'd';
$creadate = '201009292040-';
$moddate = '201601080956';
$author = stripslashes('IT001Alex');
$email = stripslashes('it001alex@airinter-va.org');
$fielda1 = stripslashes('Instruction');
$fielda2 = stripslashes('');
$fieldb1 = stripslashes('Cost Index');
$fieldb2 = stripslashes('');
$fieldc1 = stripslashes('<div style=\"text-align: center;\"><span style=\"text-decoration: underline\"><strong><span style=\"font-size: x-large;\">COST INDEX</span><br />
</strong></span></div>
<span style=\"text-decoration:underline;\"><span style=\"text-decoration: underline\"><strong><br />
1) Qu\'est-ce que le Cost Index</strong></span></span><span style=\"text-decoration: underline\"><strong> ?<br />
2) Quel Cost Index utiliser en simulation ?<br />
3) Liste de Cost Index utilisés par quelques compagnies<br />
4) Glossaire<br />
</strong></span><span style=\"text-decoration: underline\"><strong><br />
<br />
1) Qu\'est-ce que le Cost Index ?<br />
</strong></span><br />
Le Cost Index ou CI permet la minimisation du coût global d\'une mission type. Ce coût provient essentiellement de deux sources&#160;: le coût horaire (personnel navigant, entretien) et le coût du carburant.&#160;Le Cost Index (CI), synthétise ces aspects en calculant le rapport des deux sources de coût.<br />
Le Cost Index permet ainsi aux compagnies de privilégier la durée du vol, ou bien de minimiser la quantité de carburant consommée, suivant les conditions d&rsquo;opération qu&rsquo;elles souhaitent utiliser.<br />
En fonction du Cost Index, le modèle d&rsquo;optimisation du FMS peut calculer les vitesses et altitudes optimales de vol.&#160; Ces vitesses et altitudes ont été précalculées au sol et enregistrées sous forme de modèles. Elles dépendent de paramètres courant du vol, la masse notamment, et correspondent à un avion nominal.<br />
En utilisant ces données, le FMS compose le profil vertical de la trajectoire par l&rsquo;application d&rsquo;une méthode de construction simple.<br />
Il est a noter que les Cost Index utilisés par les compagnies aériennes ne sont pas fixe, mais souvent modifiés en fonction du prix du baril de pétrole, etc...<br />
<br />
On peut définir le Cost Index par une formule mathématique, mais qui a des unités différentes suivant les constructeurs, ce qui ne permet pas de faire une comparaison directe.<br />
La formule est : <strong>CI = (Ctime) / (Cfuel) </strong><br />
Voici les formules chez Airbus et Boeing :<br />
<br />
Airbus&#160; : CI = ($/minute de vol) / ($/kg de fuel)<br />
Boeing : CI = ($/heure de vol) / ($/100 lbs de fuel)<br />
<br />
Vous trouverez <strong><span style=\"text-decoration: underline\"><a target=\"_blank\" href=\"file/Boeing_CI_1.pdf\"><span style=\"text-decoration:underline;\">ici</span></a></span></strong> et <a href=\"file/Boeing_CI_2.pdf\" target=\"_blank\"><span style=\"text-decoration:underline;\"><strong>ici</strong></span></a> deux documents en anglais concernant le Cost Index chez Boeing.<br />
<br />
&#160;&#160;&#160;&#160;&#160;&#160;&#160;&#160;&#160;&#160;&#160;&#160;&#160;&#160;&#160;&#160;&#160;&#160;&#160;&#160;&#160;&#160;&#160; <strong>Tableau récapitulatif CI courant Boeing</strong><br />
<table width=\"405\" height=\"189\" cellspacing=\"1\" cellpadding=\"1\" border=\"1\">
    <tbody>
        <tr>
            <td style=\"text-align: center;\"><strong>Appareil</strong></td>
            <td style=\"text-align: center;\"><strong>MRC</strong></td>
            <td style=\"text-align: center;\"><strong>CI typique</strong></td>
            <td style=\"text-align: center;\"><strong>LRC</strong></td>
        </tr>
        <tr>
            <td style=\"text-align: center;\">717-200</td>
            <td style=\"text-align: center;\">0</td>
            <td style=\"text-align: center;\">5 à 25</td>
            <td style=\"text-align: center;\">25</td>
        </tr>
        <tr>
            <td style=\"text-align: center;\">737-300/400/500</td>
            <td style=\"text-align: center;\">0</td>
            <td style=\"text-align: center;\">10 à 30</td>
            <td style=\"text-align: center;\">30</td>
        </tr>
        <tr>
            <td style=\"text-align: center;\">737-600/700/800</td>
            <td style=\"text-align: center;\">0</td>
            <td style=\"text-align: center;\">10 à 30</td>
            <td style=\"text-align: center;\">35</td>
        </tr>
        <tr>
            <td style=\"text-align: center;\">757-200/300</td>
            <td style=\"text-align: center;\">0</td>
            <td style=\"text-align: center;\">15 à 50</td>
            <td style=\"text-align: center;\">85</td>
        </tr>
        <tr>
            <td style=\"text-align: center;\">767-300/300ER</td>
            <td style=\"text-align: center;\">0</td>
            <td style=\"text-align: center;\">15 à 50</td>
            <td style=\"text-align: center;\">70</td>
        </tr>
        <tr>
            <td style=\"text-align: center;\">777-200/300</td>
            <td style=\"text-align: center;\">0</td>
            <td style=\"text-align: center;\">90 à 150</td>
            <td style=\"text-align: center;\">180</td>
        </tr>
        <tr>
            <td style=\"text-align: center;\">MD-11</td>
            <td style=\"text-align: center;\">0</td>
            <td style=\"text-align: center;\">80 à 120</td>
            <td style=\"text-align: center;\">200</td>
        </tr>
        <tr>
            <td style=\"text-align: center;\">747-400</td>
            <td style=\"text-align: center;\">0</td>
            <td style=\"text-align: center;\">25 à 80</td>
            <td style=\"text-align: center;\">230</td>
        </tr>
    </tbody>
</table>
<br />
<strong>&#160;&#160;&#160;&#160;&#160;&#160;&#160;&#160;&#160;&#160;&#160;&#160;&#160;&#160; Tableau récapitulatif Mach croisière ECON Boeing</strong><br />
<table width=\"407\" height=\"106\" cellspacing=\"1\" cellpadding=\"1\" border=\"1\">
    <tbody>
        <tr>
            <td style=\"text-align: center;\"><strong>CI</strong></td>
            <td style=\"text-align: center;\"><strong>100kt vent arrière</strong></td>
            <td style=\"text-align: center;\"><strong>Sans vent</strong></td>
            <td style=\"text-align: center;\"><strong>100kt vent de face</strong></td>
        </tr>
        <tr>
            <td style=\"text-align: center;\">0</td>
            <td style=\"text-align: center;\">0.773</td>
            <td style=\"text-align: center;\">0.773</td>
            <td style=\"text-align: center;\">0.785</td>
        </tr>
        <tr>
            <td style=\"text-align: center;\">80</td>
            <td style=\"text-align: center;\">0.787</td>
            <td style=\"text-align: center;\">0.796</td>
            <td style=\"text-align: center;\">0.803</td>
        </tr>
        <tr>
            <td style=\"text-align: center;\">Max</td>
            <td style=\"text-align: center;\">0.811</td>
            <td style=\"text-align: center;\">0.811</td>
            <td style=\"text-align: center;\">0.811</td>
        </tr>
    </tbody>
</table>
<br />
<br />
<span style=\"text-decoration: underline\"><strong>2) Quel Cost Index&#160;utiliser en simulation ?</strong></span><strong><br />
</strong><br />
Une utilisation moyenne d\'un&#160;CI varie de 0 à 200 mais peut être supérieur (jusqu\'à 9999 sur Boeing).<br />
<br />
A319/320/321 Moyen courrier&#160;&#160;&#160;&#160;&#160; = CI&#160; 50<br />
A319/320/321&#160;Court courrier&#160;&#160;&#160;&#160;&#160;&#160;&#160; = CI&#160; 80<br />
A319/320/321 Navette&#160;&#160;&#160;&#160;&#160;&#160;&#160;&#160;&#160;&#160;&#160;&#160;&#160;&#160;&#160;&#160; = CI 150<br />
A330 Moyen courrier&#160;&#160;&#160;&#160;&#160;&#160;&#160;&#160;&#160;&#160;&#160;&#160;&#160;&#160;&#160;&#160;&#160; = CI&#160; 60<br />
A330 Long courrier&#160;&#160;&#160;&#160;&#160;&#160;&#160;&#160;&#160;&#160;&#160;&#160;&#160;&#160;&#160;&#160;&#160;&#160;&#160;&#160; = CI&#160; 30<br />
Fokker 100 - Réacteurs&#160;TAY650&#160; = CI&#160; 40<br />
Fokker 100 - Réacteurs&#160;TAY620&#160; = CI&#160; 24 jusqu\'à la croisière puis croisière à CI 40<br />
<br />
En cas de retard sur l\'horaire on peut utiliser un CI 200.<br />
En cas de doute ou si l\'appareil n\'est pas listé, un CI 80 est un bon CI moyen à utiliser en simulation.<br />
<br />
<br />
<strong><span style=\"text-decoration: underline\">3) Liste de Cost Index utilisés par quelques compagnies :</span></strong><span style=\"text-decoration: underline\"><br />
<br />
</span><span style=\"text-decoration:underline;\"><b>Aegean Airlines</b></span>
<p>Airbus A320<br />
Cost Index : 20</p>
<p>Airbus A321<br />
Cost Index : 25<span style=\"text-decoration: underline\"><br />
<br />
</span><span style=\"text-decoration:underline;\"><strong>Air Berlin</strong></span><br />
<br />
Airbus A319<br />
Cost Index : 6<br />
<br />
Airbus A320/A321<br />
Cost Index : 8<br />
<br />
Airbus A330-200<br />
Cost Index : 80<br />
<br />
Boeing 737-700/800<br />
Cost Index : 8<br />
<strong><br />
</strong><span style=\"text-decoration:underline;\"><strong>Air France</strong></span><br />
<br />
Airbus A319/320/321 Moyen courrier<br />
Cost Index : 50<br />
<br />
Airbus A319/320/321 Court courrier<br />
Cost Index : 80<br />
<br />
Airbus A319/320/321 Navette<br />
Cost Index : 150 (250 si retard)<br />
<br />
Airbus A330-200 Moyen courrier<br />
Cost Index : 60<br />
<br />
Airbus A330-200 Long courrier<br />
Cost Index : 30<br />
<br />
<span style=\"text-decoration:underline;\"><strong>Britair</strong></span><strong><br />
<br />
</strong>Fokker 100 - Réacteurs&#160;TAY650<br />
Cost Index : 40<br />
<br />
Fokker 100 - Réacteurs TAY620<br />
Cost Index : 24 en montée puis 40 en croisière<br />
<br />
<span style=\"text-decoration:underline;\"><span style=\"font-weight: bold\">British Airways</span></span><br />
<br />
Airbus A318<br />
Cost Index : 30<br />
<br />
Airbus A319/A320/A321<br />
Cost Index : Sous le FL100 CI 0 - Au dessus du FL100 CI 15<br />
<br />
Boeing 737-300/400/500<br />
Cost Index : 28<br />
<br />
Boeing 747-400<br />
Cost Index : 50<br />
<br />
Boeing 757-200<br />
Cost Index : 40<br />
<br />
Boeing 767-300<br />
Cost Index : 40<br />
<br />
Boeing 777-200/300<br />
Cost Index : 100<br />
<br />
Boeing 787<br />
Cost Index : 25<br />
<br />
Airbus A380<br />
Cost Index : 120<br />
<br />
<span style=\"text-decoration:underline;\"><strong>Condor</strong></span><strong><br />
</strong><br />
Airbus A320<br />
Cost Index : 26<br />
<br />
Airbus A321<br />
Cost Index : 33<br />
<br />
Boeing 757-300<br />
Cost Index : 30<br />
<br />
Boeing 767-300ER<br />
Cost Index : 30<br />
<br />
<span style=\"text-decoration:underline;\"><strong>Delta Airline</strong></span><br />
<br />
Boeing 767-300ER<br />
ECON - Cost Index: 113<br />
Vent arrière ou en avance sur l\'horaire - Cost Index : 75<br />
Vent debout ou en retard sur l\'horaire - Cost Index : 377<br />
<br />
Boeing 747-400<br />
Cost Index : 100<br />
<br />
<span style=\"text-decoration:underline;\"><b>EasyJet</b></span><br />
<br />
Airbus A319<br />
Cost Index : 12<br />
<br />
Airbus A320/A320SL<br />
Cost Index : 12 <br />
<br />
Airbus A321<br />
Cost Index : 23<br />
<br />
<span style=\"text-decoration:underline;\"><strong>Egyptair</strong></span><br />
<br />
Boeing 737-500<br />
Cost Index : 18<br />
<br />
Boeing 737-800<br />
Cost Index : 22<br />
<br />
Airbus A320<br />
Cost Index : 23<br />
<br />
Airbus A321<br />
Cost Index : 25<br />
<br />
Airbus A330-200/300<br />
Cost Index : 35<br />
<br />
Boeing 777-200<br />
Cost Index : 37<br />
<br />
Boeing 777-300ER<br />
Cost Index : 44<br />
<br />
<span style=\"text-decoration:underline;\"><strong>Emirates</strong></span><strong><br />
</strong><br />
Airbus A330-200<br />
Cost Index : 25<br />
<br />
Airbus A340-300<br />
Cost Index : 24<br />
<br />
Airbus A340-500<br />
Cost Index : 28<br />
<br />
Airbus A380-800<br />
Cost Index : 50<br />
<br />
Boeing 777-200LR/200LRF<br />
Cost Index : 35<br />
<br />
Boeing 777-200ER<br />
Cost Index : 36<br />
<br />
Boeing 777-300ER<br />
Cost Index : 36<br />
<br />
<span style=\"text-decoration:underline;\"><strong>Etihad</strong></span><strong><br />
<br />
</strong>Airbus A319<br />
Cost Index : 18<br />
<br />
Airbus A320<br />
Cost Index : 16<br />
<br />
Airbus A320SL<br />
Cost Index : 18<br />
<br />
Airbus A321SL<br />
Cost Index : 15<br />
<br />
Airbus A330-200<br />
Cost Index : 23<br />
<br />
Airbus A330-200F<br />
Cost Index : 25<br />
<br />
Airbus A330-300<br />
Cost Index : 26<br />
<br />
Airbus A340-500<br />
Cost Index : 32<br />
<br />
Airbus A340-600<br />
Cost Index : 32<br />
<br />
Boeing 777-200LR<br />
Cost Index : 41<br />
<br />
Boeing 777-200LRF<br />
Cost Index : 44<br />
<br />
Boeing 777-300ER<br />
Cost Index : 44<br />
<br />
<span style=\"text-decoration:underline;\"><strong>Fly Dubaï</strong></span><br />
<br />
Boeing 737-800 : 15<br />
<br />
<span style=\"text-decoration:underline;\"><b>FlyGlobespan</b></span><br />
<br />
Boeing 737-700<br />
Cost Index : 14<br />
<br />
Boeing 737-800<br />
Cost Index : 13<br />
<br />
<span style=\"text-decoration:underline;\"><strong>Hamburg international</strong></span><strong><br />
<br />
</strong>Airbus A319<br />
Cost Index : 40<br />
<br />
Boeing 737-700<br />
Cost Index : 30<br />
<br />
<span style=\"text-decoration:underline;\"><strong>Icelandair</strong></span><br />
<br />
Boeing 757-200<br />
Cost Index : 16<br />
<br />
Boeing 757-300<br />
Cost Index : 18<br />
<br />
<span style=\"text-decoration:underline;\"><b>Jet2</b></span><br />
<br />
Boeing 737-300<br />
Cost Index : 0<br />
<br />
<span style=\"text-decoration:underline;\"><strong>KLM</strong></span><strong><br />
<br />
</strong>Airbus A330<br />
Cost Index : 60 en montée, 150 en croisière (300 si retard)&#160;et 30 en déscente<br />
<br />
Fokker 100 -&#160;Réacteurs TAY620<br />
Cost Index :&#160;24 en montée puis&#160;40 en croisière<br />
<br />
Fokker 100&#160;- Réacteurs&#160;TAY650<br />
Cost Index :&#160;40<br />
<br />
Fokker 70<br />
Cost Index :&#160;40<br />
<br />
McDonnell-Douglas MD-11<br />
Cost Index : 50 en montée, 100 en croisière et 20 en déscente<br />
<br />
<span style=\"text-decoration:underline;\"><strong>Nordavia</strong></span><strong><br />
<br />
</strong>Boeing 737-500<br />
Cost Index : 27<br />
<br />
<span style=\"text-decoration:underline;\"><strong>Norwegian</strong></span><strong><br />
<br />
</strong>Boeing 737-800<br />
Cost Index : 15<br />
<strong><br />
<span style=\"text-decoration:underline;\">Nouvelair</span><br />
<br />
</strong>Airbus A320<br />
Cost Index : 38<strong><br />
<br />
<span style=\"text-decoration:underline;\">PIA</span><br />
</strong><br />
Boeing 777-200ER/200LR<br />
Cost Index : 180<br />
<br />
Boeing 777-300ER<br />
Cost Index : 180<strong><br />
<br />
</strong><span style=\"text-decoration:underline;\"><strong>Qatar Airways</strong></span><br />
<br />
Airbus A319CJ<br />
Départ de Doha - Cost Index 9<br />
Retour Doha - Cost Index 8<br />
<br />
Airbus A320<br />
Cost Index : 10<br />
<br />
Airbus A321<br />
Cost Index : 11<br />
<br />
Airbus A330-200/200F<br />
Cost Index : 15<br />
<br />
Airbus A330-300<br />
Cost Index : 15<br />
<br />
Airbus A 340-600<br />
Cost Index : 21<br />
<br />
Airbus A350-900<br />
Cost Index : 30<br />
<br />
Airbus A380-800<br />
Cost Index : 160<br />
<br />
Boeing 777-200LR/200LRF<br />
Départ de Doha - Cost Index 120<br />
Retour Doha - Cost Index 110<br />
<br />
Boeing 777-300ER<br />
Cost Index : 120<br />
<br />
Boeing 787-800<br />
Départ de Doha - Cost Index 110<br />
Retour Doha - Cost Index 25<br />
<br />
<span style=\"text-decoration:underline;\"><strong>Ryanair</strong></span><br />
<br />
Boeing 737-800<br />
Cost Index : 30<br />
<br />
<span style=\"text-decoration:underline;\"><strong>Royal Air Maroc</strong></span><br />
<br />
Boeing 737-700<br />
Cost Index : 34<br />
<br />
Boeing 737-800<br />
Cost Index : 24 (50 au départ de LHR, ORY et AMS)<br />
<br />
Boeing 767-300<br />
Cost Index : 39<br />
<br />
Boeing 787-800<br />
Cost Index : 42<br />
<br />
<span style=\"text-decoration:underline;\"><strong>Southwest Airlines</strong></span><strong><br />
</strong><br />
Boeing 737-300/500<br />
Cost Index : 28<br />
<br />
Boeing 737-700<br />
Cost Index : 36<br />
<br />
<span style=\"text-decoration:underline;\"><strong>Thomas Cook UK</strong></span><br />
<br />
Airbus A321<br />
Cost Index : 13<br />
<br />
Airbus A330-200<br />
Cost Index : 27<br />
<br />
Boeing 757-200<br />
Cost Index : 28<br />
<br />
Boeing 757-300<br />
Cost Index : 29<br />
<br />
Boeing 767-300<br />
Cost Index : 27<br />
<br />
<span style=\"text-decoration:underline;\"><strong>Thomson Airways</strong></span><br />
<br />
Airbus A320<span style=\"text-decoration: underline\"><br />
</span>Cost Index : 13<br />
<br />
Airbus A321<br />
Cost Index : 17<br />
<br />
Boeing 737-300<br />
Cost Index : 10<br />
<br />
Boeing 737-800<br />
Cost Index : 13<br />
<br />
Boeing 757-200<br />
Cos Index : 23<br />
<br />
Boeing 767-300<br />
Cost Index : 25<br />
<br />
Boeing 787<br />
Cost Index : 21<br />
<br />
<span style=\"text-decoration:underline;\"><strong>Transavia</strong></span><strong><br />
<br />
</strong>Boeing 737-800 (CFM56-7B26-26k)<br />
Cost Index : 36<br />
<br />
<span style=\"text-decoration:underline;\"><strong>Turkish Airlines</strong></span><br />
<br />
Boeing 777-300ER<br />
Cost Index : 26<br />
<br />
<span style=\"text-decoration:underline;\"><strong>Tunisair</strong></span><br />
<br />
Airbus A320<br />
Cost Index : 17<br />
<br />
Boeing 737-600<br />
Cost Index : 13<br />
<br />
<span style=\"text-decoration:underline;\"><strong>United Airlines</strong></span><strong><br />
<br />
</strong>Durée de vol inférieur à 4h :<br />
<br />
Airbus A319/320<br />
Cost Index : 27<br />
<br />
Boeing 737-300/500<br />
Cost Index : 35<br />
<br />
Boeing 747-400<br />
Cost Index : 90<br />
<br />
Boeing 757-200<br />
Cost Index : 80<br />
<br />
Boeing 767-300<br />
Cost Index : 65<br />
<br />
Boeing 777-200<br />
Cost Index : 85<br />
<br />
Durée de vol supérieur à 4h :<br />
<br />
Airbus A319/320<br />
Cost Index : 22<br />
<br />
Boeing 737-300/500<br />
Cost Index : 30<br />
<br />
Boeing 747-400<br />
Cost Index : 85<br />
<br />
Boeing 757-200<br />
Cost Index: 75<br />
<br />
Boeing 767-300<br />
Cost Index : 60<br />
<br />
Boeing 777-200<br />
Cost Index : 80<br />
<br />
<span style=\"text-decoration:underline;\"><strong>Virgin Atlantic</strong></span><strong><br />
<br />
</strong>Airbus A330-300<br />
Cost Index : 32<br />
<br />
Airbus A340-300<br />
Cost Index : 30<br />
<br />
Airbus A340-600<br />
Cost Index : 40<br />
<br />
Boeing 747-400<br />
Cost Index : de 73 à 93 - En général CI:75 vers l\'Est et CI:90 vers l\'Ouest<br />
<br />
<span style=\"text-decoration:underline;\"><strong>Wizzair</strong></span><br />
<br />
Airbus A320/A320SL<br />
Cost Index : 15<br />
<br />
<br />
<strong><span style=\"text-decoration:underline;\">4) Glossaire</span></strong><br />
<br />
CI&#160;&#160;&#160;&#160;&#160; = Cost Index<br />
FMS&#160; = Flight Management System, l\'ordinateur de bord de l\'avion<br />
MRC&#160; = Maximum Range Cruise<br />
LRC&#160;&#160; = Long Range Cruise<br />
ECON = Economique<br />
MACH = Vitesse de l\'avion par rapport à la vitesse du son exprimée en pourcentage. Ex : M.082 ou M0.82 = 82% de la vitesse du son. La vitesse du son ne dépendant que de la température, il est impossible de donner une vitesse donnée à un pourcentage donné sans connaitre précisément la température ambiante à l\'instant T.</p>');
$fieldc2 = stripslashes('');
$fieldd1 = stripslashes('MNU 5836');
$fieldd2 = stripslashes('logo001.gif');
$fieldweb = stripslashes('');
$fieldmail = stripslashes('');
$fieldmod = stripslashes('');
?>
