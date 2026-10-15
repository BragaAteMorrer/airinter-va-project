<?php
$type = 'ar';
$fileid = '5974';
$status = 'a';
$creadate = '202512122220-';
$moddate = '202512122220';
$author = stripslashes('IT001Alex');
$email = stripslashes('it001alex@airinter-va.org');
$fielda1 = stripslashes('Instruction');
$fielda2 = stripslashes('');
$fieldb1 = stripslashes('Points de décisions');
$fieldb2 = stripslashes('Decision points');
$fieldc1 = stripslashes('<h2 style=\"text-align: center;\"><span style=\"text-decoration:underline;\"><span style=\"font-size:22px;\"><strong>POINTS DE DÉCISION</strong></span></span><br />
&nbsp;</h2>

<p><strong>1️⃣</strong><strong>Point de Non-Retour Distance (PNR-D)<br />
2️⃣</strong><strong>Point de Non-Retour Temps (PNR-T)</strong><br />
<strong>3️⃣</strong><strong>Point Équitemps Distance (PET-D)</strong><br />
<strong>4️⃣</strong><strong>Point Équitemps Temps (PET-T)</strong><br />
5️⃣<strong>&nbsp;Comment ploter l\'ensemble sur l\'E6-B</strong><br />
<br />
&nbsp;</p>

<p><span style=\"color:#333399;\">🛠&nbsp;</span><strong>Soit un trajet de A vers B</strong></p>

<ul>
	<li><strong>D</strong> étant la distance de A vers B : <strong>500nm</strong></li>
	<li><strong>T</strong> étant le temps de vol en prenant en compte la vitesse sol&nbsp;: <strong>3h 50min</strong></li>
	<li><strong>A</strong> étant l\'autonomie <span style=\"text-decoration:underline;\">SANS</span> réserve : <strong>4h</strong></li>
	<li><strong>R</strong> étant la distance franchissable <span style=\"text-decoration:underline;\">SANS</span> réserve en prenant en compte la vitesse sol&nbsp;(4 x 130) : <strong>520nm</strong></li>
	<li><strong>VP</strong> étant la vitesse propre : <strong>140kt</strong></li>
	<li><strong>VsA</strong> étant la vitesse sol aller calculée avec le vent : <strong>VsA=130kt</strong></li>
	<li><strong>VsR</strong> étant la vitesse sol retour : <strong>VsR=150kt</strong></li>
	<li>VsA+Vsr est égale à 2 fois la vitesse propre : <strong>VsA + VsR = 2&nbsp;x VP</strong><br />
	&nbsp;</li>
</ul>

<p></p>

<p><strong>1️⃣</strong><span style=\"text-decoration:underline;\"><strong>&nbsp;Point de Non-Retour Distance (PNR-D)</strong></span></p>

<p data-end=\"441\" data-start=\"181\" style=\"text-align: justify;\">Le Point de Non-Retour Distance (PNR-D) est l’endroit sur une route aérienne où un avion ne peut plus retourner à l’aéroport de départ avec le carburant restant. Il est calculé en fonction de la distance, de la vitesse et de la consommation de carburant.<br />
&nbsp;</p>

<p data-end=\"441\" data-start=\"181\" style=\"text-align: justify;\"><strong data-end=\"467\" data-start=\"443\">Formule magistrale :</strong></p>

<p><span class=\"katex-display\"><span class=\"katex\"><span aria-hidden=\"true\" class=\"katex-html\"><span class=\"base\"><span class=\"mord\"><span class=\"mfrac\"><span class=\"vlist-t vlist-t2\"><span class=\"vlist-r\"><span class=\"vlist-s\">​</span></span></span></span></span></span></span></span></span><span style=\"font-size:16px;\">A (VsA x VsR) / (VsA + VsR) <strong>⇒</strong>&nbsp;4 x (130 x 150) / (130 + 150) <strong>⇒</strong>&nbsp;278nm</span><br />
&nbsp;</p>

<p><strong>Formule simplifiée :</strong></p>

<p><span style=\"font-size:16px;\">(R x VsR) / 2VP ⇒ (520 x 150) / 280 ⇒ 278nm</span><br />
&nbsp;</p>

<p data-end=\"616\" data-start=\"597\">💡<strong data-end=\"614\" data-start=\"597\">Utilisation :</strong></p>

<ul>
	<li data-end=\"723\" data-start=\"619\">Permet de planifier le moment où il devient <span style=\"text-decoration:underline;\">nécessaire de continuer vers la destination ou de revenir</span>.</li>
	<li data-end=\"808\" data-start=\"726\">Important pour la sécurité et la gestion du carburant sur les vols long-courriers.<br />
	&nbsp;</li>
</ul>

<p></p>

<p><strong>2️⃣</strong><span style=\"text-decoration:underline;\"><strong>&nbsp;Point de Non-Retour Temps (PNR-T)</strong></span></p>

<p data-end=\"2760\" data-start=\"2524\">Le Point de Non-Retour Temps (PNR-T) est le Point de Non-Retour Distance (PET-T), mais exprimé en temps.<br />
&nbsp;</p>

<p data-end=\"2760\" data-start=\"2524\"><strong>Formule magistrale :</strong></p>

<p><span style=\"font-size:16px;\">A (VsR / (VsA + VsR)) ⇒ (4 x 150) / (130 + 150)) ⇒ 02h 08min</span><br />
<br />
<strong>Formule simplifiée :</strong></p>

<p><span style=\"font-size:16px;\">(A x Vsr) / 2VP ⇒ (4x150) / 280 ⇒&nbsp;02h 08min</span><br />
&nbsp;</p>

<p></p>

<p data-end=\"808\" data-start=\"726\"><strong>3️⃣</strong><span style=\"text-decoration:underline;\"><strong>&nbsp;Point Équitemps Distance (PET-D)</strong></span></p>

<p data-end=\"1681\" data-start=\"1440\" style=\"text-align: justify;\">Le Point Équitemps Distance (PET-D) est l’endroit où le temps pour continuer vers la destination est égal au temps pour revenir à l’aéroport de départ ou à un aéroport de dégagement. Il est utilisé pour planifier des déroutements sûrs.<br />
&nbsp;</p>

<p data-end=\"1681\" data-start=\"1440\" style=\"text-align: justify;\"></p>

<p data-end=\"1709\" data-start=\"1683\"><strong data-end=\"1707\" data-start=\"1683\">Formule magistrale :</strong></p>

<p><span style=\"font-size:16px;\">D (VsR / (VsA + VsR)) ⇒ (500 x 150) / (130 + 150) ⇒ 268nm</span><br />
<br />
<strong>Formule simplifiée :</strong></p>

<p><span style=\"font-size:16px;\">(D x Vsr) / 2VP ⇒ (500x150) / 280 ⇒ 268nm</span><br />
&nbsp;</p>

<p data-end=\"1841\" data-start=\"1822\">💡<strong data-end=\"1839\" data-start=\"1822\">Utilisation :</strong></p>

<ul>
	<li data-end=\"1925\" data-start=\"1844\">Permet de savoir à quel point il est <span style=\"text-decoration:underline;\">plus rapide de retourner que de continuer</span>.</li>
	<li data-end=\"2005\" data-start=\"1928\">Essentiel pour la planification des vols transocéaniques ou en zones isolées.<br />
	&nbsp;</li>
</ul>

<p></p>

<p data-end=\"808\" data-start=\"726\"><strong>4️⃣</strong><span style=\"text-decoration:underline;\"><strong>&nbsp;Point Équitemps TEMPS (PET-T)</strong></span></p>

<p data-end=\"2760\" data-start=\"2524\">Le Point Équitemps Temps (PET-T) est le Point Équitemps Distance (PET-D), mais exprimé en temps.</p>

<p data-end=\"1709\" data-start=\"1683\"><br />
<strong data-end=\"1707\" data-start=\"1683\">Formule magistrale :</strong></p>

<p><span style=\"font-size:16px;\">D (VsR / (VsA + VsR)) ⇒ (500 x 150) / (130 x 280)) ⇒ 02h 04min</span><br />
<br />
<strong>Formule simplifiée :</strong></p>

<p><span style=\"font-size:16px;\">(T x Vsr) / 2VP ⇒ (3.8 x150) / 280 ⇒ 02h 04min</span><br />
&nbsp;</p>

<p>5️⃣<span style=\"text-decoration:underline;\"><strong>&nbsp;Comment ploter l\'ensemble sur l\'E6-B</strong></span></p>

<p style=\"text-align: justify;\">Au-delà des formules compliquées, il existe une méthode simple et rapide pour obtenir les quatre informations simultanément, sans effectuer de calculs. Il suffit d’utiliser un E6-B : pratique, sans pile, il ne tombe jamais en panne. À tel point qu’une montre, une boussole et un E6-B suffisent pour faire le tour du monde. Cet instrument très pratique permet de réaliser toutes sortes de calculs de navigation.<br />
&nbsp;</p>

<ul>
	<li><span style=\"color:#ff0000;\"><strong>Aligner 2 x VP (280) sur VsR (150)</strong></span><br />
	&nbsp;</li>
	<li><strong><span style=\"color:#0000cc;\">En face de R (520)</span> <span style=\"color:#009900;\">on lit PNR-D (278)</span></strong></li>
	<li><strong><span style=\"color:#0000cc;\">En face de A (4)</span> <span style=\"color:#009900;\">on lit PNR-T (128 ie:02h08)</span></strong></li>
	<li><strong><span style=\"color:#0000cc;\">En face de D (500)</span> <span style=\"color:#009900;\">on lit PET-D (268)</span></strong></li>
	<li><strong><span style=\"color:#0000cc;\">En face de T (3:50)</span> <span style=\"color:#009900;\">on lit PET-T (124 ie:02h04)</span></strong></li>
</ul>

<p style=\"text-align: center;\"><img alt=\"\" height=\"800\" src=\"/CIPNT/E6Bfr1.jpg\" width=\"800\" /></p>
');
$fieldc2 = stripslashes('<h2 style=\"text-align: center;\"><span style=\"text-decoration:underline;\"><strong><span style=\"font-size:22px;\">DECISION POINTS</span></strong></span><br />
&nbsp;</h2>

<p><strong>1️⃣</strong><strong>&nbsp;Point of No-Return Distance (PNR-D)</strong><br />
<strong>2️⃣</strong><strong>&nbsp;Point of No-Return Time (PNR-T)</strong><br />
<strong>3️⃣</strong><strong>&nbsp;Equitime Point Distance (ETP-D)</strong><br />
<strong>4️⃣</strong><strong>&nbsp;</strong><strong>Equitime Point</strong><strong>&nbsp;Time (ETP-T)</strong><br />
5️⃣<strong>&nbsp;How to plot all points on the E6-B</strong><br />
<br />
&nbsp;</p>

<p><span style=\"color:#333399;\">🛠&nbsp;</span><strong>Considering a route from A to B</strong></p>

<ul>
	<li><strong>D</strong> distance from A to B: <strong>500nm</strong></li>
	<li><strong>T</strong>&nbsp;flight time, taking ground speed into account:&nbsp;<strong>3h 50min</strong></li>
	<li><strong>A</strong>&nbsp;endurance WITHOUT reserve:&nbsp;<strong>4h</strong></li>
	<li><strong>R</strong>&nbsp;range WITHOUT reserve, considering ground speed (4 × 130):&nbsp;<strong>520nm</strong></li>
	<li><strong>TAS</strong>&nbsp;true airspeed: <strong>140kt</strong></li>
	<li><strong>VsA</strong>&nbsp;ground speed outbound, accounting for wind:&nbsp;<strong>VsA=130kt</strong></li>
	<li><strong>VsR</strong>&nbsp;ground speed return:&nbsp;<strong>VsR=150kt</strong></li>
	<li>VsA+Vsr equal 2 times the true airspeed : <strong>VsA + VsR = 2&nbsp;x TAS</strong><br />
	&nbsp;</li>
</ul>

<p></p>

<p><strong>1️⃣</strong><span style=\"text-decoration:underline;\"><strong>&nbsp;Point of No-Return Distance (PNR-D</strong></span></p>

<p data-end=\"441\" data-start=\"181\" style=\"text-align: justify;\">The Point of No-Return Distance (PNR-D) is the location on a flight route beyond which an aircraft can no longer return to the departure airport with the remaining fuel. It is calculated based on distance, speed, and fuel consumption.</p>

<p data-end=\"441\" data-start=\"181\" style=\"text-align: justify;\"><br />
<strong data-end=\"467\" data-start=\"443\">Master formula:</strong></p>

<p><span class=\"katex-display\"><span class=\"katex\"><span aria-hidden=\"true\" class=\"katex-html\"><span class=\"base\"><span class=\"mord\"><span class=\"mfrac\"><span class=\"vlist-t vlist-t2\"><span class=\"vlist-r\"><span class=\"vlist-s\">​</span></span></span></span></span></span></span></span></span><span style=\"font-size:16px;\">A (VsA x VsR) / (VsA + VsR) <strong>⇒</strong>&nbsp;4 x (130 x 150) / (130 + 150) <strong>⇒</strong>&nbsp;278nm</span><br />
&nbsp;</p>

<p><strong>Simplified formula:</strong></p>

<p><span style=\"font-size:16px;\">(R x VsR) / 2TAS ⇒ (520 x 150) / 280 ⇒ 278nm</span><br />
&nbsp;</p>

<p data-end=\"616\" data-start=\"597\">💡<strong data-end=\"614\" data-start=\"597\">Usage:</strong></p>

<ul>
	<li data-end=\"723\" data-start=\"619\">Allows planning the point at which it becomes <span style=\"text-decoration:underline;\">necessary to continue to the destination or return</span>.</li>
	<li data-end=\"808\" data-start=\"726\">Important for safety and fuel management on long-haul flights.<br />
	&nbsp;</li>
</ul>

<p></p>

<p><strong>2️⃣</strong><span style=\"text-decoration:underline;\"><strong>&nbsp;Point of No-Return Time (PNR-T)</strong></span></p>

<p data-end=\"2760\" data-start=\"2524\">The Point of No-Return Time (PNR-T) is the Point of No-Return Distance, but expressed in terms of time.<br />
&nbsp;</p>

<p data-end=\"2760\" data-start=\"2524\"><strong data-end=\"467\" data-start=\"443\">Master formula:</strong></p>

<p><span style=\"font-size:16px;\">A (VsR / (VsA + VsR)) ⇒ (4 x 150) / (130 + 150)) ⇒ 02h 08min</span><br />
&nbsp;</p>

<p><strong>Simplified formula:</strong></p>

<p><span style=\"font-size:16px;\">(A x Vsr) / 2TAS ⇒ (4x150) / 280 ⇒&nbsp;02h 08min</span><br />
&nbsp;</p>

<p></p>

<p data-end=\"808\" data-start=\"726\"><strong>3️⃣</strong><span style=\"text-decoration:underline;\"><strong>&nbsp;Equitime Point Distance (ETP-D)</strong></span></p>

<p data-end=\"1681\" data-start=\"1440\" style=\"text-align: justify;\">The Equitime Point Distance (PET-D)&nbsp;is the location where the time to continue to the destination equals the time to return to the departure airport or to an alternate airport. It is used to plan safe diversions.<br />
&nbsp;</p>

<p data-end=\"1709\" data-start=\"1683\"><strong data-end=\"467\" data-start=\"443\">Master formula:</strong></p>

<p><span style=\"font-size:16px;\">D (VsR / (VsA + VsR)) ⇒ (500 x 150) / (130 + 150) ⇒ 268nm</span><br />
<br />
<strong>Simplified formula:</strong></p>

<p><span style=\"font-size:16px;\">(D x Vsr) / 2TAS ⇒ (500x150) / 280 ⇒ 268nm</span></p>

<p></p>

<p data-end=\"1841\" data-start=\"1822\">💡<strong data-end=\"1839\" data-start=\"1822\">Usage:</strong></p>

<p></p>

<p></p>

<p></p>

<ul>
	<li data-end=\"1925\" data-start=\"1844\">Allows determining the point at which it is <span style=\"text-decoration:underline;\">faster to return than to continue</span>.</li>
	<li data-end=\"2005\" data-start=\"1928\">Essential for planning transoceanic flights or operations in remote areas.<br />
	&nbsp;</li>
</ul>

<p></p>

<p data-end=\"808\" data-start=\"726\"><strong>4️⃣</strong><span style=\"text-decoration:underline;\"><strong>&nbsp;</strong><strong>Equitime Point</strong><strong>&nbsp;Time (ETP-T)</strong></span></p>

<p data-end=\"2760\" data-start=\"2524\">The Equitime Point Time&nbsp;(ETP-T) is the&nbsp;Equitime Point Distance, but expressed in terms of time.<br />
&nbsp;</p>

<p data-end=\"1709\" data-start=\"1683\"><strong data-end=\"467\" data-start=\"443\">Master formula:</strong></p>

<p><span style=\"font-size:16px;\">D (VsR / (VsA + VsR)) ⇒ (500 x 150) / (130 x 280)) ⇒ 02h 04min</span><br />
<br />
<strong>Simplified formula:</strong></p>

<p><span style=\"font-size:16px;\">(T x Vsr) / 2TAS ⇒ (3.8 x150) / 280 ⇒ 02h 04min</span></p>

<p><br />
&nbsp;</p>

<p>5️⃣<strong>&nbsp;How to plot all points on the E6-B</strong></p>

<p style=\"text-align: justify;\">Beyond the complicated formulas, there is a simple and quick method to obtain all four pieces of information simultaneously, without doing any calculations. You just need to use an E6-B: practical, battery-free, and it never fails. So much so that a watch, a compass, and an E6-B are enough to navigate around the world. This very handy instrument allows you to perform all kinds of navigation calculations.<br />
&nbsp;</p>

<ul>
	<li><span style=\"color:#ff0000;\"><strong>Align&nbsp;2 x TAS (280) on VsR (150)</strong></span><br />
	&nbsp;</li>
	<li><strong><span style=\"color:#0000cc;\">Opposite R (520)</span> <span style=\"color:#009900;\">you read PNR-D (278)</span></strong></li>
	<li><strong><span style=\"color:#0000cc;\">Opposite</span></strong><strong><span style=\"color:#0000cc;\">&nbsp;A (4)</span>&nbsp;<span style=\"color:#009900;\"></span></strong><strong><span style=\"color:#009900;\">you read</span></strong><strong><span style=\"color:#009900;\">&nbsp;PNR-T (128 ie:02h08)</span></strong></li>
	<li><strong><span style=\"color:#0000cc;\">Opposite</span></strong><strong><span style=\"color:#0000cc;\">&nbsp;D (500)</span>&nbsp;<span style=\"color:#009900;\"></span></strong><strong><span style=\"color:#009900;\">you read</span></strong><strong><span style=\"color:#009900;\">&nbsp;ETP-D (268)</span></strong></li>
	<li><strong><span style=\"color:#0000cc;\">Opposite</span></strong><strong><span style=\"color:#0000cc;\">&nbsp;T (3:50)</span>&nbsp;<span style=\"color:#009900;\"></span></strong><strong><span style=\"color:#009900;\">you read</span></strong><strong><span style=\"color:#009900;\">&nbsp;ETP-T (124 ie:02h04)</span></strong></li>
</ul>

<p style=\"text-align: center;\"><img alt=\"\" src=\"/CIPNT/E6Bgb1.jpg\" style=\"width: 800px; height: 800px;\" /></p>
');
$fieldd1 = stripslashes('MNU 5412');
$fieldd2 = stripslashes('logo001.gif');
$fieldweb = stripslashes('S');
$fieldmail = stripslashes('ALL|0|on');
$fieldmod = stripslashes('');
?>
