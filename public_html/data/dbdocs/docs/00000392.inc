<?php
$type = 'ar';
$fileid = '392';
$status = 'a';
$creadate = '201009292040-';
$moddate = '201601080956';
$author = stripslashes('IT001Alex');
$email = stripslashes('it001alex@airinter-va.org');
$fielda1 = stripslashes('Instruction');
$fielda2 = stripslashes('');
$fieldb1 = stripslashes('Cost Index');
$fieldb2 = stripslashes('Cost Index');
$fieldc1 = stripslashes('<h2 style=\"text-align: center;\"><span style=\"text-decoration: underline\"><strong><span style=\"font-size: x-large;\">COST INDEX</span></strong></span><br />
&nbsp;</h2>

<p style=\"text-align: justify;\"><span style=\"text-decoration:underline;\"><span style=\"text-decoration: underline\"></span></span></p>

<h2><strong>1️⃣</strong><span style=\"text-decoration:underline;\"><span style=\"text-decoration: underline\"><strong>Qu\'est-ce que le Cost Index</strong></span></span><span style=\"text-decoration: underline\"><strong> ?</strong></span><br />
<strong>2️⃣</strong><span style=\"text-decoration: underline\"><strong>Quel Cost Index utiliser en simulation ?</strong></span><br />
<strong>3️⃣</strong><span style=\"text-decoration: underline\"><strong>Liste de Cost Index utilisés par quelques compagnies</strong></span><br />
<strong>4️⃣</strong><span style=\"text-decoration: underline\"><strong>Glossaire</strong></span></h2>

<p style=\"text-align: justify;\"><span style=\"text-decoration: underline\"></span><br />
<strong>1️⃣</strong><span style=\"text-decoration:underline;\"><span style=\"text-decoration: underline\"><strong>Qu\'est-ce que le Cost Index</strong></span></span><span style=\"text-decoration: underline\"><strong> ?</strong></span><br />
<br />
Le Cost Index ou CI permet la minimisation du coût global d\'une mission type. Ce coût provient essentiellement de deux sources&nbsp;: le coût horaire (personnel navigant, entretien) et le coût du carburant.&nbsp;Le Cost Index (CI), synthétise ces aspects en calculant le rapport des deux sources de coût.<br />
<br />
Le Cost Index permet ainsi aux compagnies de privilégier la durée du vol, ou bien de minimiser la quantité de carburant consommée, suivant les conditions d’opération qu’elles souhaitent utiliser.<br />
En fonction du Cost Index, le modèle d’optimisation du FMS peut calculer les vitesses et altitudes optimales de vol.&nbsp; Ces vitesses et altitudes ont été précalculées au sol et enregistrées sous forme de modèles. Elles dépendent de paramètres courant du vol, la masse notamment, et correspondent à un avion nominal.<br />
En utilisant ces données, le FMS compose le profil vertical de la trajectoire par l’application d’une méthode de construction simple.<br />
<br />
Il est a noter que les Cost Index utilisés par les compagnies aériennes ne sont pas fixe, mais souvent modifiés en fonction du prix du baril de pétrole, etc...<br />
Dans la pratique, il est très courant que les pilotes adaptent le Cost Index en fonction des besoins du jour/vol (voir de leurs envies) et programment un autre CI que celui prévu dans le manifeste de vol.<br />
<br />
On peut définir le Cost Index par une formule mathématique, mais qui a des unités différentes suivant les constructeurs, ce qui ne permet pas de faire une comparaison directe.<br />
La formule est : <strong>CI = (Ctime) / (Cfuel) </strong><br />
Voici les formules chez Airbus et Boeing :<br />
<br />
<strong>Airbus&nbsp; : CI = ($/minute de vol) / ($/kg de fuel)<br />
Boeing : CI = ($/heure de vol) / ($/100 lbs de fuel)</strong><br />
<br />
Vous trouverez <span style=\"text-decoration:underline;\"><strong><span style=\"text-decoration: underline\"><a href=\"CIPNT/Boeing_CI_1.pdf\" target=\"_blank\"><span style=\"color:#0000cc;\"><span style=\"text-decoration:underline;\">ici</span></span></a></span></strong></span> et <span style=\"text-decoration:underline;\"><a href=\"CIPNT/Boeing_CI_2.pdf\" target=\"_blank\"><span style=\"color:#0000cc;\"><span style=\"text-decoration:underline;\"><strong>ici</strong></span></span></a></span> deux documents concernant le Cost Index chez <strong>Boeing</strong> (documents en anglais).<br />
Vous trouverez&nbsp;<a href=\"CIPNT/Airbus_CI.pdf\" target=\"_blank\"><span style=\"color:#0000cc;\"><span style=\"text-decoration:underline;\"><strong>ici</strong></span></span></a>&nbsp;un document concernant le Cost Index chez <strong>Airbus</strong> (en anglais également).<br />
<br />
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <strong>Tableau récapitulatif CI courant Boeing</strong></p>

<p></p>

<table border=\"1\" cellpadding=\"1\" cellspacing=\"1\" height=\"189\" width=\"405\">
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

<p><br />
<strong>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Tableau récapitulatif Mach croisière ECON Boeing</strong></p>

<p></p>

<p></p>

<table border=\"1\" cellpadding=\"1\" cellspacing=\"1\" height=\"106\" width=\"407\">
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

<p><br />
<br />
<strong>2️⃣</strong><span style=\"text-decoration: underline\"><strong>Quel Cost Index utiliser en simulation ?</strong></span><br />
<br />
Une utilisation moyenne d\'un&nbsp;CI varie de 0 à 200 mais peut être supérieur (jusqu\'à 9999 sur Boeing).</p>

<ul>
	<li>A319/320/321 Moyen courrier&nbsp;= CI&nbsp;50</li>
	<li>A319/320/321&nbsp;Court courrier&nbsp;= CI&nbsp;80</li>
	<li>A319/320/321 Navette&nbsp;= CI&nbsp;150</li>
	<li>A330 Moyen courrier&nbsp;= CI&nbsp;60</li>
	<li>A330 Long courrier&nbsp;= CI&nbsp;30</li>
	<li>Fokker 100 - Réacteurs&nbsp;TAY650&nbsp;= CI&nbsp;40</li>
	<li>Fokker 100 - Réacteurs&nbsp;TAY620&nbsp;= CI&nbsp;24 jusqu\'à la croisière puis croisière à CI 40</li>
</ul>

<p>En cas de retard sur l\'horaire on peut utiliser un CI 200.<br />
En cas de doute ou si l\'appareil n\'est pas listé, un CI 80 est un bon CI moyen à utiliser en simulation.<br />
<br />
<br />
<strong>3️⃣</strong><span style=\"text-decoration: underline\"><strong>Liste de Cost Index utilisés par quelques compagnies</strong></span><span style=\"text-decoration: underline\"></span><br />
<br />
<span style=\"text-decoration:underline;\"><b>Aegean Airlines</b></span></p>

<p></p>

<p></p>

<p>Airbus A320<br />
Cost Index : 20</p>

<p>Airbus A321<br />
Cost Index : 25<span style=\"text-decoration: underline\"></span><br />
<br />
<span style=\"text-decoration:underline;\"><strong>Air Berlin</strong></span><br />
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
<br />
<span style=\"text-decoration:underline;\"><strong>Air France</strong></span><br />
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
<span style=\"text-decoration:underline;\"><strong>Britair</strong></span><br />
<br />
Fokker 100 - Réacteurs&nbsp;TAY650<br />
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
<span style=\"text-decoration:underline;\"><strong>Condor</strong></span><br />
<br />
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
Cost Index : 12<br />
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
<span style=\"text-decoration:underline;\"><strong>Emirates</strong></span><br />
<br />
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
<span style=\"text-decoration:underline;\"><strong>Etihad</strong></span><br />
<br />
Airbus A319<br />
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
<span style=\"text-decoration:underline;\"><strong>Hamburg international</strong></span><br />
<br />
Airbus A319<br />
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
<span style=\"text-decoration:underline;\"><strong>KLM</strong></span><br />
<br />
Airbus A330<br />
Cost Index : 60 en montée, 150 en croisière (300 si retard)&nbsp;et 30 en déscente<br />
<br />
Fokker 100 -&nbsp;Réacteurs TAY620<br />
Cost Index :&nbsp;24 en montée puis&nbsp;40 en croisière<br />
<br />
Fokker 100&nbsp;- Réacteurs&nbsp;TAY650<br />
Cost Index :&nbsp;40<br />
<br />
Fokker 70<br />
Cost Index :&nbsp;40<br />
<br />
McDonnell-Douglas MD-11<br />
Cost Index : 50 en montée, 100 en croisière et 20 en déscente<br />
<br />
<span style=\"text-decoration:underline;\"><strong>Nordavia</strong></span><br />
<br />
Boeing 737-500<br />
Cost Index : 27<br />
<br />
<span style=\"text-decoration:underline;\"><strong>Norwegian</strong></span><br />
<br />
Boeing 737-800<br />
Cost Index : 15<br />
<br />
<strong><span style=\"text-decoration:underline;\">Nouvelair</span></strong><br />
<br />
Airbus A320<br />
Cost Index : 38<br />
<br />
<strong><span style=\"text-decoration:underline;\">PIA</span></strong><br />
<br />
Boeing 777-200ER/200LR<br />
Cost Index : 180<br />
<br />
Boeing 777-300ER<br />
Cost Index : 180<br />
<br />
<span style=\"text-decoration:underline;\"><strong>Qatar Airways</strong></span><br />
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
<span style=\"text-decoration:underline;\"><strong>Southwest Airlines</strong></span><br />
<br />
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
Airbus A320<span style=\"text-decoration: underline\"></span><br />
Cost Index : 13<br />
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
<span style=\"text-decoration:underline;\"><strong>Transavia</strong></span><br />
<br />
Boeing 737-800 (CFM56-7B26-26k)<br />
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
<span style=\"text-decoration:underline;\"><strong>United Airlines</strong></span><br />
<br />
Durée de vol inférieur à 4h :<br />
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
<span style=\"text-decoration:underline;\"><strong>Virgin Atlantic</strong></span><br />
<br />
Airbus A330-300<br />
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
<strong>4️⃣</strong><span style=\"text-decoration: underline\"><strong>Glossaire</strong></span></p>

<ul>
	<li>CI&nbsp;= Cost Index</li>
	<li>FMS&nbsp;= Flight Management System, l\'ordinateur de bord de l\'avion</li>
	<li>MRC&nbsp;= Maximum Range Cruise</li>
	<li>LRC&nbsp;= Long Range Cruise</li>
	<li>ECON = Economique</li>
	<li>MACH = Vitesse de l\'avion par rapport à la vitesse du son exprimée en pourcentage. Ex : M.082 ou M0.82 = 82% de la vitesse du son. La vitesse du son ne dépendant que de la température, il est impossible de donner une vitesse donnée à un pourcentage donné sans connaitre précisément la température ambiante à l\'instant T.</li>
</ul>
');
$fieldc2 = stripslashes('<h2 style=\"text-align: center;\"><span style=\"text-decoration: underline\"><strong><span style=\"font-size: x-large;\">COST INDEX</span></strong></span></h2>

<h2 style=\"text-align: justify;\"><span style=\"text-decoration:underline;\"><span style=\"text-decoration: underline\"></span></span><br />
<strong>1️⃣</strong><span style=\"text-decoration:underline;\"><span style=\"text-decoration: underline\"><strong>&nbsp;What is the Cost Index</strong></span></span><span style=\"text-decoration: underline\"><strong>?</strong></span><br />
<strong>2️⃣</strong><span style=\"text-decoration: underline\"><strong>&nbsp;Which Cost Index should be used in simulation?</strong></span><br />
<strong>3️⃣</strong><span style=\"text-decoration: underline\"><strong>&nbsp;List of Cost Index values used by various airlines</strong></span><br />
<strong>4️⃣</strong><span style=\"text-decoration: underline\"><strong>&nbsp;Glossary</strong></span><span style=\"text-decoration: underline\"></span><br />
<br />
&nbsp;</h2>

<p style=\"text-align: justify;\"><strong>1️⃣</strong><span style=\"text-decoration: underline\"><strong>&nbsp;What is the Cost Index?</strong></span><br />
&nbsp;</p>

<p data-end=\"544\" data-start=\"266\" style=\"text-align: justify;\">The Cost Index, or CI, is used to minimize the overall cost of a typical mission. This cost mainly comes from two sources: hourly operating costs (crew, maintenance) and fuel cost. The Cost Index synthesizes these aspects by calculating the ratio between these two cost sources.<br />
<br />
The Cost Index therefore allows airlines to prioritize either flight time or fuel savings, depending on the operational conditions they wish to use.<br />
Based on the Cost Index, the FMS optimization model can compute the optimal flight speeds and altitudes. These speeds and altitudes are pre-calculated on the ground and stored as performance models. They depend on current flight parameters—especially weight—and correspond to a nominal aircraft.<br />
Using this data, the FMS builds the vertical profile of the trajectory using a straightforward construction method.<br />
<br />
It should be noted that Cost Index values used by airlines are not fixed; they are often adjusted depending on fuel prices and other economic factors.<br />
In practice, it is very common for pilots to adapt the Cost Index according to the needs of the day or the specific flight (or even their preferences), and to enter a CI different from the one planned in the flight release.</p>

<p style=\"text-align: justify;\"><br />
<br />
The Cost Index can be defined using a mathematical formula, but units vary depending on the manufacturer, which makes direct comparison impossible.<br data-end=\"1637\" data-start=\"1634\" />
The formula is: <strong data-end=\"1679\" data-start=\"1653\">CI = (Ctime) / (Cfuel)</strong><br data-end=\"1682\" data-start=\"1679\" />
Below are the formulas for Airbus and Boeing:<br />
<br />
<strong>Airbus&nbsp; : CI = ($/minute of flight) / ($/kg of fuel)<br />
Boeing : CI = ($/hour of flight) / ($/100 lbs of fuel)</strong><br />
<br />
You will find two documents&nbsp;<span style=\"text-decoration:underline;\"><strong><span style=\"text-decoration: underline\"><a href=\"CIPNT/Boeing_CI_1.pdf\" target=\"_blank\"><span style=\"color:#0000cc;\"><span style=\"text-decoration:underline;\">HERE</span></span></a></span></strong></span>&nbsp;and&nbsp;<span style=\"text-decoration:underline;\"><a href=\"CIPNT/Boeing_CI_2.pdf\" target=\"_blank\"><span style=\"color:#0000cc;\"><span style=\"text-decoration:underline;\"><strong>HERE</strong></span></span></a></span>&nbsp;regarding the Cost Index for Boeing.<br />
You will find a document&nbsp;<a href=\"CIPNT/Airbus_CI.pdf\" target=\"_blank\"><span style=\"color:#0000cc;\"><span style=\"text-decoration:underline;\"><strong>HERE</strong></span></span></a>&nbsp;regarding the Cost Index for Airbus.<br />
<br />
<strong>Summary table of typical Boeing Cost Index values</strong></p>

<p></p>

<table border=\"1\" cellpadding=\"1\" cellspacing=\"1\" height=\"189\" width=\"405\">
	<tbody>
		<tr>
			<td style=\"text-align: center;\"><strong>Aircraft</strong></td>
			<td style=\"text-align: center;\"><strong>MRC</strong></td>
			<td style=\"text-align: center;\"><strong>Typical CI</strong></td>
			<td style=\"text-align: center;\"><strong>LRC</strong></td>
		</tr>
		<tr>
			<td style=\"text-align: center;\">717-200</td>
			<td style=\"text-align: center;\">0</td>
			<td style=\"text-align: center;\">5 to 25</td>
			<td style=\"text-align: center;\">25</td>
		</tr>
		<tr>
			<td style=\"text-align: center;\">737-300/400/500</td>
			<td style=\"text-align: center;\">0</td>
			<td style=\"text-align: center;\">10to 30</td>
			<td style=\"text-align: center;\">30</td>
		</tr>
		<tr>
			<td style=\"text-align: center;\">737-600/700/800</td>
			<td style=\"text-align: center;\">0</td>
			<td style=\"text-align: center;\">10 to 30</td>
			<td style=\"text-align: center;\">35</td>
		</tr>
		<tr>
			<td style=\"text-align: center;\">757-200/300</td>
			<td style=\"text-align: center;\">0</td>
			<td style=\"text-align: center;\">15 to 50</td>
			<td style=\"text-align: center;\">85</td>
		</tr>
		<tr>
			<td style=\"text-align: center;\">767-300/300ER</td>
			<td style=\"text-align: center;\">0</td>
			<td style=\"text-align: center;\">15 to 50</td>
			<td style=\"text-align: center;\">70</td>
		</tr>
		<tr>
			<td style=\"text-align: center;\">777-200/300</td>
			<td style=\"text-align: center;\">0</td>
			<td style=\"text-align: center;\">90 to 150</td>
			<td style=\"text-align: center;\">180</td>
		</tr>
		<tr>
			<td style=\"text-align: center;\">MD-11</td>
			<td style=\"text-align: center;\">0</td>
			<td style=\"text-align: center;\">80 to 120</td>
			<td style=\"text-align: center;\">200</td>
		</tr>
		<tr>
			<td style=\"text-align: center;\">747-400</td>
			<td style=\"text-align: center;\">0</td>
			<td style=\"text-align: center;\">25 to 80</td>
			<td style=\"text-align: center;\">230</td>
		</tr>
	</tbody>
</table>

<p><br />
<strong>Summary table ECON cruise for Boeing aircraft</strong></p>

<p></p>

<p></p>

<table border=\"1\" cellpadding=\"1\" cellspacing=\"1\" height=\"106\" width=\"407\">
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

<p></p>

<p><br />
<strong>2️⃣</strong><span style=\"text-decoration: underline\"><strong>&nbsp;Which Cost Index should be used in simulation?</strong></span><br />
&nbsp;</p>

<p data-end=\"196\" data-start=\"102\">A typical CI value ranges from 0 to 200, but it can be higher (up to 9999 on Boeing aircraft).</p>

<p></p>

<p></p>

<p></p>

<p></p>

<p></p>

<p></p>

<p></p>

<p></p>

<p></p>

<p></p>

<p></p>

<p></p>

<p></p>

<ul>
	<li data-end=\"239\" data-start=\"200\"><strong data-end=\"231\" data-start=\"200\">A319/320/321 – Medium-haul:</strong> CI 50</li>
	<li data-end=\"280\" data-start=\"242\"><strong data-end=\"272\" data-start=\"242\">A319/320/321 – Short-haul:</strong> CI 80</li>
	<li data-end=\"330\" data-start=\"283\"><strong data-end=\"321\" data-start=\"283\">A319/320/321 – Shuttle operations:</strong> CI 150</li>
	<li data-end=\"364\" data-start=\"333\"><strong data-end=\"356\" data-start=\"333\">A330 – Medium-haul:</strong> CI 60</li>
	<li data-end=\"396\" data-start=\"367\"><strong data-end=\"388\" data-start=\"367\">A330 – Long-haul:</strong> CI 30</li>
	<li data-end=\"439\" data-start=\"399\"><strong data-end=\"431\" data-start=\"399\">Fokker 100 – TAY650 engines:</strong> CI 40</li>
	<li data-end=\"519\" data-start=\"442\"><strong data-end=\"474\" data-start=\"442\">Fokker 100 – TAY620 engines:</strong> CI 24 until cruise, then CI 40 during cruise</li>
</ul>

<p></p>

<p data-end=\"693\" data-start=\"521\">In case of a delay in the schedule, a CI of 200 may be used.<br data-end=\"584\" data-start=\"581\" />
If in doubt, or if the aircraft is not listed, <strong data-end=\"640\" data-start=\"631\">CI 80</strong> is a good average value to use in flight simulation.<br />
<br />
<br />
<strong>3️⃣</strong><strong><span style=\"text-decoration: underline\">&nbsp;</span></strong><span style=\"text-decoration: underline\"><strong>List of Cost Index values used by various airlines</strong></span><strong><span style=\"text-decoration: underline\">:</span></strong><span style=\"text-decoration: underline\"></span><br />
<br />
<span style=\"text-decoration:underline;\"><b>Aegean Airlines</b></span></p>

<p></p>

<p></p>

<p>Airbus A320<br />
Cost Index : 20</p>

<p>Airbus A321<br />
Cost Index : 25<span style=\"text-decoration: underline\"></span><br />
<br />
<span style=\"text-decoration:underline;\"><strong>Air Berlin</strong></span><br />
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
<br />
<span style=\"text-decoration:underline;\"><strong>Air France</strong></span><br />
<br />
Airbus A319/320/321 Medium haul<br />
Cost Index : 50<br />
<br />
Airbus A319/320/321 Short haul<br />
Cost Index : 80<br />
<br />
Airbus A319/320/321 Shuttle<br />
Cost Index : 150 (250 if late)<br />
<br />
Airbus A330-200 Medium haul<br />
Cost Index : 60<br />
<br />
Airbus A330-200 Long haul<br />
Cost Index : 30<br />
<br />
<span style=\"text-decoration:underline;\"><strong>Britair</strong></span><br />
<br />
Fokker 100 - TAY650 engines<br />
Cost Index : 40<br />
<br />
Fokker 100 - TAY620 engines<br />
Cost Index : climb at 24 then cruise at 40<br />
<br />
<span style=\"text-decoration:underline;\"><span style=\"font-weight: bold\">British Airways</span></span><br />
<br />
Airbus A318<br />
Cost Index : 30<br />
<br />
Airbus A319/A320/A321<br />
Cost Index : Under FL100 CI 0 - Above FL100 CI 15<br />
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
<span style=\"text-decoration:underline;\"><strong>Condor</strong></span><br />
<br />
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
Tail wind or early - Cost Index : 75<br />
Headwind or late - Cost Index : 377<br />
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
Cost Index : 12<br />
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
<span style=\"text-decoration:underline;\"><strong>Emirates</strong></span><br />
<br />
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
<span style=\"text-decoration:underline;\"><strong>Etihad</strong></span><br />
<br />
Airbus A319<br />
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
<span style=\"text-decoration:underline;\"><strong>Hamburg international</strong></span><br />
<br />
Airbus A319<br />
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
<span style=\"text-decoration:underline;\"><strong>KLM</strong></span><br />
<br />
Airbus A330<br />
Cost Index : Climb at 60, cruise at 150 (300 if late) and descent at 30<br />
<br />
Fokker 100 - TAY620 engines<br />
Cost Index : Climb at 24 then cruise at 40<br />
<br />
Fokker 100&nbsp;- TAY650 engines<br />
Cost Index :&nbsp;40<br />
<br />
Fokker 70<br />
Cost Index :&nbsp;40<br />
<br />
McDonnell-Douglas MD-11<br />
Cost Index : Climb at 50, cruise at 100 and descent at 20<br />
<br />
<span style=\"text-decoration:underline;\"><strong>Nordavia</strong></span><br />
<br />
Boeing 737-500<br />
Cost Index : 27<br />
<br />
<span style=\"text-decoration:underline;\"><strong>Norwegian</strong></span><br />
<br />
Boeing 737-800<br />
Cost Index : 15<br />
<br />
<strong><span style=\"text-decoration:underline;\">Nouvelair</span></strong><br />
<br />
Airbus A320<br />
Cost Index : 38<br />
<br />
<strong><span style=\"text-decoration:underline;\">PIA</span></strong><br />
<br />
Boeing 777-200ER/200LR<br />
Cost Index : 180<br />
<br />
Boeing 777-300ER<br />
Cost Index : 180<br />
<br />
<span style=\"text-decoration:underline;\"><strong>Qatar Airways</strong></span><br />
<br />
Airbus A319CJ<br />
From Doha - Cost Index 9<br />
To Doha - Cost Index 8<br />
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
From Doha - Cost Index 120<br />
To Doha - Cost Index 110<br />
<br />
Boeing 777-300ER<br />
Cost Index : 120<br />
<br />
Boeing 787-800<br />
From Doha - Cost Index 110<br />
To Doha - Cost Index 25<br />
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
Cost Index : 24 (50 when departing LHR, ORY et AMS)<br />
<br />
Boeing 767-300<br />
Cost Index : 39<br />
<br />
Boeing 787-800<br />
Cost Index : 42<br />
<br />
<span style=\"text-decoration:underline;\"><strong>Southwest Airlines</strong></span><br />
<br />
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
Airbus A320<span style=\"text-decoration: underline\"></span><br />
Cost Index : 13<br />
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
<span style=\"text-decoration:underline;\"><strong>Transavia</strong></span><br />
<br />
Boeing 737-800 (CFM56-7B26-26k)<br />
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
<span style=\"text-decoration:underline;\"><strong>United Airlines</strong></span><br />
<br />
Flight duration under 4h :<br />
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
Flight duration over 4h :<br />
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
<span style=\"text-decoration:underline;\"><strong>Virgin Atlantic</strong></span><br />
<br />
Airbus A330-300<br />
Cost Index : 32<br />
<br />
Airbus A340-300<br />
Cost Index : 30<br />
<br />
Airbus A340-600<br />
Cost Index : 40<br />
<br />
Boeing 747-400<br />
Cost Index : de 73 à 93 - In general CI:75 Eastbound and CI:90 Westbound<br />
<br />
<span style=\"text-decoration:underline;\"><strong>Wizzair</strong></span><br />
<br />
Airbus A320/A320SL<br />
Cost Index : 15<br />
<br />
<br />
<strong>4️⃣</strong><strong><span style=\"text-decoration:underline;\">&nbsp;Glossary</span></strong></p>

<ul>
	<li>CI&nbsp;= Cost Index</li>
	<li>FMS&nbsp;= Flight Management System</li>
	<li>MRC&nbsp;= Maximum Range Cruise</li>
	<li>LRC&nbsp;= Long Range Cruise</li>
	<li>ECON = Economy</li>
	<li>MACH = The aircraft’s speed relative to the speed of sound, expressed as a percentage.</li>
	<li>Example: M.82 or M0.82 = 82% of the speed of sound.</li>
	<li>Since the speed of sound depends only on temperature, it is impossible to give an exact speed for a given Mach number without knowing the precise ambient temperature at that moment.</li>
</ul>
');
$fieldd1 = stripslashes('none');
$fieldd2 = stripslashes('logo001.gif');
$fieldweb = stripslashes('S');
$fieldmail = stripslashes('ALL|0|on');
$fieldmod = stripslashes('');
?>
