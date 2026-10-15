<?php
$MapTodisplay = $_POST["MapTodisplay"];
if (!isset($_POST['submit'])) {
?>
<h3>Briefing du Vol</h3>
<script src="http://skyvector.com/linkchart.js"></script>
<table width="98%" align="center" cellpadding="4">
	<!-- Flight ID -->
	<tr style="background-color: #333; color: #FFF;">
		<td colspan="2">Vol</td>
	</tr>
	<tr>
		<td colspan="2">
		<?php echo $schedule->code.$schedule->flightnum; ?>
		</td>
	</tr>
	
	<tr  style="background-color: #333; color: #FFF;">
		<td>D&eacute;part</td>
		<td>Arriv&eacute;e</td>
	</tr>
	
	<tr>
		<td width="50%" ><?php echo "{$schedule->depname} ($schedule->depicao)"; ?><br />
			<a href="https://pilotweb.nas.faa.gov/geo/icaoRadius.html?icao_id=<?php echo $schedule->depicao?>&radius=2"
				target="_blank">Cliquer pour afficher les NOTAMS</a>
		</td>
		<td width="50%" ><?php echo "{$schedule->arrname} ($schedule->arricao)"; ?><br />
			<a href="https://pilotweb.nas.faa.gov/geo/icaoRadius.html?icao_id=<?php echo $schedule->arricao?>&radius=2"
				target="_blank">Cliquer pour afficher les NOTAMS</a></td>
	</tr>
	
	<!-- Times Row -->
	<tr style="background-color: #333; color: #FFF;">
		<td>Heure de d&eacute;part</td>
		<td>Heure d&#39;arriv&eacute;e</td>
	</tr>
	<tr>
		<td width="50%" ><?php echo "{$schedule->deptime}"; ?></td>
		<td width="50%" ><?php echo "{$schedule->arrtime}"; ?></td>
	</tr>
	
	<!-- Aircraft and Distance Row -->
	<tr style="background-color: #333; color: #FFF;">
		<td>&Eacute;quipement</td>
		<td>Distance</td>
	</tr>
	<tr>
		<td width="50%" ><?php echo "{$schedule->aircraft}"; ?></td>
		<td width="50%" ><?php echo "{$schedule->distance}"; ?></td>
	</tr>
	
	<tr style="background-color: #333; color: #FFF;">
		<td>METAR au d&eacute;part</td>
		<td>METAR &agrave; l&#39;arriv&eacute;e</td>
	</tr>
	<tr>
		<td width="50%" ><div id="<?php echo $schedule->depicao; ?>" class="metar">Getting Airport METAR</div> <br />
			<a href="http://flightaware.com/resources/airport/<?php echo $schedule->depicao?>/weather" target="_blank">
			D&eacute;tails des pr&eacute;visions m&eacute;t&eacute;orologiques</a>
		</td>
		<td width="50%" ><div id="<?php echo $schedule->arricao; ?>" class="metar">Getting Airport METAR</div><br />
			<a href="http://flightaware.com/resources/airport/<?php echo $schedule->arricao?>/weather" target="_blank">
			D&eacute;tails des pr&eacute;visions m&eacute;t&eacute;orologiques</a></td>
	</tr>
	
	<!-- Route -->
	<tr style="background-color: #333; color: #FFF;">
		<td colspan="2">Route</td>
	</tr>
	<tr>
		<td colspan="2">
		<?php 
		# If it's empty, insert some blank lines
		if($schedule->route == '')
		{
			echo '<br /> <br /> <br />';
		}
		else
		{
			echo strtoupper($schedule->route); 
		}
		?>
		</td>
	</tr>
	
	<!-- Notes -->
	<tr style="background-color: #333; color: #FFF;">
		<td colspan="2">Notes</td>
	</tr>
	<tr>
		<td colspan="2" style="padding: 6px;">
		<?php 
			# If it's empty, insert some blank lines
			if($schedule->notes == '')
			{
				echo '<br /> <br /> <br />';
			}
			else
			{
				echo "{$schedule->notes}"; 
			}
		?>
		</td>
	</tr>
	
	
</table>

<table width="98%" align="center" padding="2">
<tr style="background-color: #333; color: #FFF; padding: 5px;">
	<td>Donn&eacute;es suppl&eacute;mentaires</td>
</tr>
<tr>
	<td><a href="http://rfinder.asalink.net/" target="_blank">Consulter les routes IFR r&eacute;centes</a></td>
</tr>
</table>

<h3>Proc&eacute;dures et Information</h3>
<table width="98%" align="center">

	<tr style="background-color: #333; color: #FFF;">
		<td>Cartes pour <?php echo $schedule->depicao?></td>
		<td>Cartes pour <?php echo $schedule->arricao?></td>
	</tr>
	<tr align="center">
		<td width="50%" valign="top">
		  <!--
			<a href="http://www.airnav.com/airport/<?php echo $schedule->depicao?>#ifr" target="_blank">
			<img border="0" src="http://flightaware.com/resources/airport/<?php echo $schedule->depicao?>/APD/AIRPORT+DIAGRAM/png"
				width="387px" height="594px" alt="Aucune carte n&#39;est disponible" /></a>
				-->
			<?php
			if (substr($schedule->depicao, 0, 2) == "LF"){ ?>
			  <strong><?php echo "Cartes Arr/D&eacute;p";?></strong>
			  <a href="https://www.sia.aviation-civile.gouv.fr/aip/enligne/fr/ARR%20DEPProduitPartieframeset.htm" target="_blank">
		    <img border="0" src="https://www.sia.aviation-civile.gouv.fr/aip/enligne/Images/icoWriting.gif" width="20px" height="20px" /></a><br />				
  			<strong><?php echo "Cartes IAC";?></strong>
	  		<a href="https://www.sia.aviation-civile.gouv.fr/aip/enligne/fr/IACProduitPartieframeset.htm" target="_blank">
		    <img border="0" src="https://www.sia.aviation-civile.gouv.fr/aip/enligne/Images/icoWriting.gif" width="20px" height="20px" /></a><br />			
			<?php
			} else { ?>
			  <strong><?php echo "Cartes Arr/D&eacute;p et IAC non disponible";?></strong>
			  <?php
			}
			?>
		</td>
		<td width="50%" valign="top">
			<!--
			<a href="http://www.airnav.com/airport/<?php echo $schedule->arricao?>#ifr" target="_blank">
			<img border="0" src="http://flightaware.com/resources/airport/<?php echo $schedule->arricao?>/APD/AIRPORT+DIAGRAM/png" 
				width="387px" height="594px" alt="Aucune carte n&#39;est disponible" /></a>
			-->
			<?php
			if (substr($schedule->arricao, 0, 2) == "LF"){ ?>
			  <strong><?php echo "Cartes Arr/D&eacute;p";?></strong>
			  <a href="https://www.sia.aviation-civile.gouv.fr/aip/enligne/fr/ARR%20DEPProduitPartieframeset.htm" target="_blank">
		    <img border="0" src="https://www.sia.aviation-civile.gouv.fr/aip/enligne/Images/icoWriting.gif" width="20px" height="20px" /></a><br />				
  			<strong><?php echo "Cartes IAC";?></strong>
	  		<a href="https://www.sia.aviation-civile.gouv.fr/aip/enligne/fr/IACProduitPartieframeset.htm" target="_blank">
		    <img border="0" src="https://www.sia.aviation-civile.gouv.fr/aip/enligne/Images/icoWriting.gif" width="20px" height="20px" /></a><br />
		  <?php
		  } else { ?>
			  <strong><?php echo "Cartes Arr/D&eacute;p et IAC non disponible";?></strong>
			  <?php
			}
			?>
		</td>
	</tr>
</table>

<h3>M&eacute;t&eacute;o</h3>
<form method="post" action="<?php echo $PHP_SELF;?>">
	<table width="98%" align="center" padding="2">
	<tr>
	<td>
  <select name="MapTodisplay"  id="mapvalue">
    <option value=""></option>
    <optgroup label="IR Satellite">
          <option value="http://aviationweather.gov/data/obs/sat/intl/ir_ICAO-B1_bw.jpg">Atlantique</option>
          <option value="http://aviationweather.gov/data/obs/sat/intl/ir_ICAO-H_bw.jpg">Atlantique Nord</option>
          <option value="http://aviationweather.gov/data/obs/sat/intl/ir_ICAO-G_bw.jpg">Europe / Asie</option>
          <option value="http://aviationweather.gov/data/obs/sat/intl/ir_ICAO-C_bw.jpg">Europe / Afrique</option>
          <option value="http://aviationweather.gov/data/obs/sat/intl/ir_ICAO-E_bw.jpg">Oc&eacute;an Indien</option>
          <option value="http://aviationweather.gov/data/obs/sat/intl/ir_ICAO-A_bw.jpg">Am&eacute;riques Nord / Sud</option>
          <option value="http://aviationweather.gov/data/obs/sat/intl/ir_ICAO-I_bw.jpg">Pacifique Nord</option>
          <option value="http://aviationweather.gov/data/obs/sat/intl/ir_ICAO-F_bw.jpg">Pacifique Sud</option>
          <option value="http://aviationweather.gov/data/obs/sat/intl/ir_ICAO-M_bw.jpg">Trans-Atlantique</option>
    </optgroup>
    <optgroup label="TEMSI">
          <option value="http://aviationweather.gov/data/iffdp/2106.gif">Asie / Oc&eacute;anie</option>
          <option value="http://aviationweather.gov/data/iffdp/2132.gif">Atlantique / P&ocirc;le Nord</option>
          <option value="http://aviationweather.gov/data/iffdp/2130.gif">Atlantique (Europe/Afrique/Am&eacute;rique)</option>
          <option value="http://aviationweather.gov/data/iffdp/2104.gif">Europe / Afrique</option>
          <option value="http://aviationweather.gov/data/iffdp/2107.gif">Europe / Asie</option>
          <option value="http://aviationweather.gov/data/iffdp/2105.gif">Europe / Asie Centrale</option>
          <option value="http://aviationweather.gov/data/iffdp/2108.gif">Europe / Am&eacute;rique du Nord</option>
          <option value="http://aviationweather.gov/data/iffdp/2101.gif">Am&eacute;rique du Nord</option>
          <option value="http://aviationweather.gov/data/iffdp/2133.gif">Pacifique Nord</option>
          <option value="http://aviationweather.gov/data/iffdp/2131.gif">Pacifique</option>
          <option value="http://aviationweather.gov/data/iffdp/2109.gif">Afrique du Sud / Australie</option>
          <option value="http://aviationweather.gov/data/iffdp/2103.gif">Atlantique Sud</option>
          <option value="http://aviationweather.gov/data/iffdp/2134.gif">Pacifique Sud</option>
    </optgroup>
    <optgroup label="TEMSI Couleur">
          <option value="http://aviationweather.gov/data/products/swh/PGEE07_00_CL_new.gif">Am&eacute;riques 0h UTC</option>
          <option value="http://aviationweather.gov/data/products/swh/PGEE07_06_CL_new.gif">Am&eacute;riques 6h UTC</option>
          <option value="http://aviationweather.gov/data/products/swh/PGEE07_12_CL_new.gif">Am&eacute;riques 12h UTC</option>
          <option value="http://aviationweather.gov/data/products/swh/PGEE07_18_CL_new.gif">Am&eacute;riques 18h UTC</option>
          <option value="http://aviationweather.gov/data/products/swh/PGAE07_00_CL_new.gif">Atlantique Centre 0h UTC</option>
          <option value="http://aviationweather.gov/data/products/swh/PGAE07_06_CL_new.gif">Atlantique Centre  6h UTC</option>
          <option value="http://aviationweather.gov/data/products/swh/PGAE07_12_CL_new.gif">Atlantique Centre  12h UTC</option>
          <option value="http://aviationweather.gov/data/products/swh/PGAE07_18_CL_new.gif">Atlantique Centre  18h UTC</option>
          <option value="http://ows-public.sembach.af.mil/gifImages/21OWS_EUROPE_ANALYSIS_METSAT_00_SFC_METSAT-ANALYSIS-7.gif">Europe Actuel</option>
          <option value="http://ows-public.sembach.af.mil/gifImages/21OWS_EUROPE_FITL_PROG-BRIEFING_24.gif">Europe Pr&eacute;visions &agrave; 24h</option>
          <option value="http://ows-public.sembach.af.mil/gifImages/21OWS_EUROPE_FITL_PROG-BRIEFING_48.gif">Europe Pr&eacute;visions &agrave; 48h</option>
          <option value="http://ows-public.sembach.af.mil/gifImages/21OWS_EUROPE_FITL_PROG-BRIEFING_72.gif">Europe Pr&eacute;visions &agrave; 72h</option>
          <option value="http://ows-public.sembach.af.mil/gifImages/21OWS_EUROPE_FITL_PROG-BRIEFING_96.gif">Europe Pr&eacute;visions &agrave; 96h</option>
          <option value="http://ows-public.sembach.af.mil/gifImages/21OWS_EUROPE_FITL_PROG-BRIEFING_120.gif">Europe Pr&eacute;visions &agrave; 120h</option>
          <option value="http://aviationweather.gov/data/products/swm/PGNE15_00_CL.gif">Europe / Am&eacute;rique du Nord 0h UTC</option>
          <option value="http://aviationweather.gov/data/products/swm/PGNE15_06_CL.gif">Europe / Am&eacute;rique du Nord 6h UTC</option>
          <option value="http://aviationweather.gov/data/products/swm/PGNE15_12_CL.gif">Europe / Am&eacute;rique du Nord 12h UTC</option>
          <option value="http://aviationweather.gov/data/products/swm/PGNE15_18_CL.gif">Europe / Am&eacute;rique du Nord 18h UTC</option>
          <option value="http://aviationweather.gov/data/products/swh/PGIE07_00_CL_new.gif">Europe / Afrique / Am&eacute;riques 0h UTC</option>
          <option value="http://aviationweather.gov/data/products/swh/PGIE07_06_CL_new.gif">Europe / Afrique / Am&eacute;riques 6h UTC</option>
          <option value="http://aviationweather.gov/data/products/swh/PGIE07_12_CL_new.gif">Europe / Afrique / Am&eacute;riques 12h UTC</option>
          <option value="http://aviationweather.gov/data/products/swh/PGIE07_18_CL_new.gif">Europe / Afrique / Am&eacute;riques 18h UTC</option>
          <option value="http://aviationweather.gov/data/products/swh/PGDE30_00_CL_new.gif">Pacifique Nord 0h UTC</option>
          <option value="http://aviationweather.gov/data/products/swh/PGDE30_06_CL_new.gif">Pacifique Nord 6h UTC</option>
          <option value="http://aviationweather.gov/data/products/swh/PGDE30_12_CL_new.gif">Pacifique Nord 12h UTC</option>
          <option value="http://aviationweather.gov/data/products/swh/PGDE30_18_CL_new.gif">Pacifique Nord 18h UTC</option>
          <option value="http://aviationweather.gov/data/products/swh/PGGE30_00_CL_new.gif">Pacifique Sud 0h UTC</option>
          <option value="http://aviationweather.gov/data/products/swh/PGGE30_06_CL_new.gif">Pacifique Sud 6h UTC</option>
          <option value="http://aviationweather.gov/data/products/swh/PGGE30_12_CL_new.gif">Pacifique Sud 12h UTC</option>
          <option value="http://aviationweather.gov/data/products/swh/PGGE30_18_CL_new.gif">Pacifique Sud 18h UTC</option>
    </optgroup>
    <optgroup label="WINTEM">
          <option value="http://aviationweather.gov/data/iffdp/2315.gif">Am&eacute;riques - FL100</option>
          <option value="http://aviationweather.gov/data/iffdp/2311.gif">Am&eacute;riques - FL340</option>
          <option value="http://aviationweather.gov/data/iffdp/2415.gif">Afrique / Am&eacute;riques - FL100</option>
          <option value="http://aviationweather.gov/data/iffdp/2411.gif">Afrique / Am&eacute;riques - FL340</option>
          <option value="http://aviationweather.gov/data/iffdp/2737.gif">Asie - FL100</option>
          <option value="http://aviationweather.gov/data/iffdp/2733.gif">Asie - FL340</option>
          <option value="http://aviationweather.gov/data/iffdp/2797.gif">Asie / Oc&eacute;anie - FL100</option>
          <option value="http://aviationweather.gov/data/iffdp/2793.gif">Asie / Oc&eacute;anie - FL340</option>
          <option value="http://aviationweather.gov/data/iffdp/2917.gif">Europe / Afrique - FL100</option>
          <option value="http://aviationweather.gov/data/iffdp/2913.gif">Europe / Afrique - FL340</option>
          <option value="http://aviationweather.gov/data/iffdp/2857.gif">Europe / Afrique / Asie - FL100</option>
          <option value="http://aviationweather.gov/data/iffdp/2853.gif">Europe / Afrique / Asie - FL340</option>
          <option value="http://aviationweather.gov/data/iffdp/2517.gif">Am&eacute;rique du Nord / Europe - FL100</option>
          <option value="http://aviationweather.gov/data/iffdp/2513.gif">Am&eacute;rique du Nord / Europe - FL340</option>
          <option value="http://aviationweather.gov/data/iffdp/2255.gif">Pacifique - FL100</option>
          <option value="http://aviationweather.gov/data/iffdp/2251.gif">Pacifique - FL340</option>
          <option value="http://aviationweather.gov/data/iffdp/2200.gif">Pacifique Nord - FL100</option>
          <option value="http://aviationweather.gov/data/iffdp/2196.gif">Pacifique Nord - FL340</option>
    </optgroup>
    <optgroup label="Pr&eacute;visions CAT">
          <option value="http://www.orbit.nesdis.noaa.gov/smcd/opdb/aviation/turb/tidaily/TIAVN24a.GIF">Atlantique - 0h UTC</option>
          <option value="http://www.orbit.nesdis.noaa.gov/smcd/opdb/aviation/turb/tidaily/TIAVN6a.GIF">Atlantique - 6h UTC</option>
          <option value="http://www.orbit.nesdis.noaa.gov/smcd/opdb/aviation/turb/tidaily/TIAVN12a.GIF">Atlantique - 12h UTC</option>
          <option value="http://www.orbit.nesdis.noaa.gov/smcd/opdb/aviation/turb/tidaily/TIAVN18a.GIF">Atlantique - 18h UTC</option>
          <option value="http://www.orbit.nesdis.noaa.gov/smcd/opdb/aviation/turb/tidaily/TIAVN24.GIF">Pacifique - 0h UTC</option>
          <option value="http://www.orbit.nesdis.noaa.gov/smcd/opdb/aviation/turb/tidaily/TIAVN6.GIF">Pacifique - 6h UTC</option>
          <option value="http://www.orbit.nesdis.noaa.gov/smcd/opdb/aviation/turb/tidaily/TIAVN12.GIF">Pacifique - 12h UTC</option>
          <option value="http://www.orbit.nesdis.noaa.gov/smcd/opdb/aviation/turb/tidaily/TIAVN18.GIF">Pacifique - 18h UTC</option>
          <option value="http://www.orbit.nesdis.noaa.gov/smcd/opdb/aviation/turb/tidaily/TIAVN24p.GIF">H&eacute;misph&egrave;re Nord - 0h UTC</option>
          <option value="http://www.orbit.nesdis.noaa.gov/smcd/opdb/aviation/turb/tidaily/TIAVN6p.GIF">H&eacute;misph&egrave;re Nord - 6h UTC</option>
          <option value="http://www.orbit.nesdis.noaa.gov/smcd/opdb/aviation/turb/tidaily/TIAVN12p.GIF">H&eacute;misph&egrave;re Nord - 12h UTC</option>
          <option value="http://www.orbit.nesdis.noaa.gov/smcd/opdb/aviation/turb/tidaily/TIAVN18p.GIF">H&eacute;misph&egrave;re Nord - 18h UTC</option>
    </optgroup>
    </select>
    <input type="submit" value="Afficher" name="submit">
  </td>
  </tr>
  </table>
</form>
<?PHP
} else {
?>
<h3>Briefing du Vol</h3>
<script src="http://skyvector.com/linkchart.js"></script>
<table width="98%" align="center" cellpadding="4">
	<!-- Flight ID -->
	<tr style="background-color: #333; color: #FFF;">
		<td colspan="2">Vol</td>
	</tr>
	<tr>
		<td colspan="2">
		<?php echo $schedule->code.$schedule->flightnum; ?>
		</td>
	</tr>
	
	<tr  style="background-color: #333; color: #FFF;">
		<td>D&eacute;part</td>
		<td>Arriv&eacute;e</td>
	</tr>
	
	<tr>
		<td width="50%" ><?php echo "{$schedule->depname} ($schedule->depicao)"; ?><br />
			<a href="https://pilotweb.nas.faa.gov/geo/icaoRadius.html?icao_id=<?php echo $schedule->depicao?>&radius=2"
				target="_blank">Cliquer pour afficher les NOTAMS</a>
		</td>
		<td width="50%" ><?php echo "{$schedule->arrname} ($schedule->arricao)"; ?><br />
			<a href="https://pilotweb.nas.faa.gov/geo/icaoRadius.html?icao_id=<?php echo $schedule->arricao?>&radius=2"
				target="_blank">Cliquer pour afficher les NOTAMS</a></td>
	</tr>
	
	<!-- Times Row -->
	<tr style="background-color: #333; color: #FFF;">
		<td>Heure de d&eacute;part</td>
		<td>Heure d&#39;arriv&eacute;e</td>
	</tr>
	<tr>
		<td width="50%" ><?php echo "{$schedule->deptime}"; ?></td>
		<td width="50%" ><?php echo "{$schedule->arrtime}"; ?></td>
	</tr>
	
	<!-- Aircraft and Distance Row -->
	<tr style="background-color: #333; color: #FFF;">
		<td>&Eacute;quipement</td>
		<td>Distance</td>
	</tr>
	<tr>
		<td width="50%" ><?php echo "{$schedule->aircraft}"; ?></td>
		<td width="50%" ><?php echo "{$schedule->distance}"; ?></td>
	</tr>
	
	<tr style="background-color: #333; color: #FFF;">
		<td>METAR au d&eacute;part</td>
		<td>METAR &agrave; l&#39;arriv&eacute;e</td>
	</tr>
	<tr>
		<td width="50%" ><div id="<?php echo $schedule->depicao; ?>" class="metar">Getting Airport METAR</div> <br />
			<a href="http://flightaware.com/resources/airport/<?php echo $schedule->depicao?>/weather" target="_blank">
			D&eacute;tails des pr&eacute;visions m&eacute;t&eacute;orologiques</a>
		</td>
		<td width="50%" ><div id="<?php echo $schedule->arricao; ?>" class="metar">Getting Airport METAR</div><br />
			<a href="http://flightaware.com/resources/airport/<?php echo $schedule->arricao?>/weather" target="_blank">
			D&eacute;tails des pr&eacute;visions m&eacute;t&eacute;orologiques</a></td>
	</tr>
	
	<!-- Route -->
	<tr style="background-color: #333; color: #FFF;">
		<td colspan="2">Route</td>
	</tr>
	<tr>
		<td colspan="2">
		<?php 
		# If it's empty, insert some blank lines
		if($schedule->route == '')
		{
			echo '<br /> <br /> <br />';
		}
		else
		{
			echo strtoupper($schedule->route); 
		}
		?>
		</td>
	</tr>
	
	<!-- Notes -->
	<tr style="background-color: #333; color: #FFF;">
		<td colspan="2">Notes</td>
	</tr>
	<tr>
		<td colspan="2" style="padding: 6px;">
		<?php 
			# If it's empty, insert some blank lines
			if($schedule->notes == '')
			{
				echo '<br /> <br /> <br />';
			}
			else
			{
				echo "{$schedule->notes}"; 
			}
		?>
		</td>
	</tr>
	
	
</table>

<table width="98%" align="center" padding="2">
<tr style="background-color: #333; color: #FFF; padding: 5px;">
	<td>Donn&eacute;es suppl&eacute;mentaires</td>
</tr>
<tr>
	<td><a href="http://rfinder.asalink.net/" target="_blank">Consulter les routes IFR r&eacute;centes</a></td>
</tr>
</table>

<h3>Proc&eacute;dures et Information</h3>
<table width="98%" align="center">

	<tr style="background-color: #333; color: #FFF;">
		<td>Cartes pour <?php echo $schedule->depicao?></td>
		<td>Cartes pour <?php echo $schedule->arricao?></td>
	</tr>
	<tr align="center">
		<td width="50%" valign="top">
			<a href="http://www.airnav.com/airport/<?php echo $schedule->depicao?>#ifr" target="_blank">
			<img border="0" src="http://flightaware.com/resources/airport/<?php echo $schedule->depicao?>/APD/AIRPORT+DIAGRAM/png"
				width="387px" height="594px" alt="Aucune carte n&#39;est disponible" /></a>
		</td>
		<td width="50%" valign="top">
			<a href="http://www.airnav.com/airport/<?php echo $schedule->arricao?>#ifr" target="_blank">
			<img border="0" src="http://flightaware.com/resources/airport/<?php echo $schedule->arricao?>/APD/AIRPORT+DIAGRAM/png" 
				width="387px" height="594px" alt="Aucune carte n&#39;est disponible" /></a>
		</td>
	
	</tr>
</table>

<h3>M&eacute;t&eacute;o</h3>
<form method="post" action="<?php echo $PHP_SELF;?>">
	<table width="98%" align="center" padding="2">
	<tr>
	<td>
  <select name="MapTodisplay"  id="mapvalue">
    <option value=""></option>
    <optgroup label="IR Satellite">
          <option value="http://aviationweather.gov/data/obs/sat/intl/ir_ICAO-B1_bw.jpg">Atlantique</option>
          <option value="http://aviationweather.gov/data/obs/sat/intl/ir_ICAO-H_bw.jpg">Atlantique Nord</option>
          <option value="http://aviationweather.gov/data/obs/sat/intl/ir_ICAO-G_bw.jpg">Europe / Asie</option>
          <option value="http://aviationweather.gov/data/obs/sat/intl/ir_ICAO-C_bw.jpg">Europe / Afrique</option>
          <option value="http://aviationweather.gov/data/obs/sat/intl/ir_ICAO-E_bw.jpg">Oc&eacute;an Indien</option>
          <option value="http://aviationweather.gov/data/obs/sat/intl/ir_ICAO-A_bw.jpg">Am&eacute;riques Nord / Sud</option>
          <option value="http://aviationweather.gov/data/obs/sat/intl/ir_ICAO-I_bw.jpg">Pacifique Nord</option>
          <option value="http://aviationweather.gov/data/obs/sat/intl/ir_ICAO-F_bw.jpg">Pacifique Sud</option>
          <option value="http://aviationweather.gov/data/obs/sat/intl/ir_ICAO-M_bw.jpg">Trans-Atlantique</option>
    </optgroup>
    <optgroup label="TEMSI">
          <option value="http://aviationweather.gov/data/iffdp/2106.gif">Asie / Oc&eacute;anie</option>
          <option value="http://aviationweather.gov/data/iffdp/2132.gif">Atlantique / P&ocirc;le Nord</option>
          <option value="http://aviationweather.gov/data/iffdp/2130.gif">Atlantique (Europe/Afrique/Am&eacute;rique)</option>
          <option value="http://aviationweather.gov/data/iffdp/2104.gif">Europe / Afrique</option>
          <option value="http://aviationweather.gov/data/iffdp/2107.gif">Europe / Asie</option>
          <option value="http://aviationweather.gov/data/iffdp/2105.gif">Europe / Asie Centrale</option>
          <option value="http://aviationweather.gov/data/iffdp/2108.gif">Europe / Am&eacute;rique du Nord</option>
          <option value="http://aviationweather.gov/data/iffdp/2101.gif">Am&eacute;rique du Nord</option>
          <option value="http://aviationweather.gov/data/iffdp/2133.gif">Pacifique Nord</option>
          <option value="http://aviationweather.gov/data/iffdp/2131.gif">Pacifique</option>
          <option value="http://aviationweather.gov/data/iffdp/2109.gif">Afrique du Sud / Australie</option>
          <option value="http://aviationweather.gov/data/iffdp/2103.gif">Atlantique Sud</option>
          <option value="http://aviationweather.gov/data/iffdp/2134.gif">Pacifique Sud</option>
    </optgroup>
    <optgroup label="TEMSI Couleur">
          <option value="http://aviationweather.gov/data/products/swh/PGEE07_00_CL_new.gif">Am&eacute;riques 0h UTC</option>
          <option value="http://aviationweather.gov/data/products/swh/PGEE07_06_CL_new.gif">Am&eacute;riques 6h UTC</option>
          <option value="http://aviationweather.gov/data/products/swh/PGEE07_12_CL_new.gif">Am&eacute;riques 12h UTC</option>
          <option value="http://aviationweather.gov/data/products/swh/PGEE07_18_CL_new.gif">Am&eacute;riques 18h UTC</option>
          <option value="http://aviationweather.gov/data/products/swh/PGAE07_00_CL_new.gif">Atlantique Centre 0h UTC</option>
          <option value="http://aviationweather.gov/data/products/swh/PGAE07_06_CL_new.gif">Atlantique Centre  6h UTC</option>
          <option value="http://aviationweather.gov/data/products/swh/PGAE07_12_CL_new.gif">Atlantique Centre  12h UTC</option>
          <option value="http://aviationweather.gov/data/products/swh/PGAE07_18_CL_new.gif">Atlantique Centre  18h UTC</option>
          <option value="http://ows-public.sembach.af.mil/gifImages/21OWS_EUROPE_ANALYSIS_METSAT_00_SFC_METSAT-ANALYSIS-7.gif">Europe Actuel</option>
          <option value="http://ows-public.sembach.af.mil/gifImages/21OWS_EUROPE_FITL_PROG-BRIEFING_24.gif">Europe Pr&eacute;visions &agrave; 24h</option>
          <option value="http://ows-public.sembach.af.mil/gifImages/21OWS_EUROPE_FITL_PROG-BRIEFING_48.gif">Europe Pr&eacute;visions &agrave; 48h</option>
          <option value="http://ows-public.sembach.af.mil/gifImages/21OWS_EUROPE_FITL_PROG-BRIEFING_72.gif">Europe Pr&eacute;visions &agrave; 72h</option>
          <option value="http://ows-public.sembach.af.mil/gifImages/21OWS_EUROPE_FITL_PROG-BRIEFING_96.gif">Europe Pr&eacute;visions &agrave; 96h</option>
          <option value="http://ows-public.sembach.af.mil/gifImages/21OWS_EUROPE_FITL_PROG-BRIEFING_120.gif">Europe Pr&eacute;visions &agrave; 120h</option>
          <option value="http://aviationweather.gov/data/products/swm/PGNE15_00_CL.gif">Europe / Am&eacute;rique du Nord 0h UTC</option>
          <option value="http://aviationweather.gov/data/products/swm/PGNE15_06_CL.gif">Europe / Am&eacute;rique du Nord 6h UTC</option>
          <option value="http://aviationweather.gov/data/products/swm/PGNE15_12_CL.gif">Europe / Am&eacute;rique du Nord 12h UTC</option>
          <option value="http://aviationweather.gov/data/products/swm/PGNE15_18_CL.gif">Europe / Am&eacute;rique du Nord 18h UTC</option>
          <option value="http://aviationweather.gov/data/products/swh/PGIE07_00_CL_new.gif">Europe / Afrique / Am&eacute;riques 0h UTC</option>
          <option value="http://aviationweather.gov/data/products/swh/PGIE07_06_CL_new.gif">Europe / Afrique / Am&eacute;riques 6h UTC</option>
          <option value="http://aviationweather.gov/data/products/swh/PGIE07_12_CL_new.gif">Europe / Afrique / Am&eacute;riques 12h UTC</option>
          <option value="http://aviationweather.gov/data/products/swh/PGIE07_18_CL_new.gif">Europe / Afrique / Am&eacute;riques 18h UTC</option>
          <option value="http://aviationweather.gov/data/products/swh/PGDE30_00_CL_new.gif">Pacifique Nord 0h UTC</option>
          <option value="http://aviationweather.gov/data/products/swh/PGDE30_06_CL_new.gif">Pacifique Nord 6h UTC</option>
          <option value="http://aviationweather.gov/data/products/swh/PGDE30_12_CL_new.gif">Pacifique Nord 12h UTC</option>
          <option value="http://aviationweather.gov/data/products/swh/PGDE30_18_CL_new.gif">Pacifique Nord 18h UTC</option>
          <option value="http://aviationweather.gov/data/products/swh/PGGE30_00_CL_new.gif">Pacifique Sud 0h UTC</option>
          <option value="http://aviationweather.gov/data/products/swh/PGGE30_06_CL_new.gif">Pacifique Sud 6h UTC</option>
          <option value="http://aviationweather.gov/data/products/swh/PGGE30_12_CL_new.gif">Pacifique Sud 12h UTC</option>
          <option value="http://aviationweather.gov/data/products/swh/PGGE30_18_CL_new.gif">Pacifique Sud 18h UTC</option>
    </optgroup>
    <optgroup label="WINTEM">
          <option value="http://aviationweather.gov/data/iffdp/2315.gif">Am&eacute;riques - FL100</option>
          <option value="http://aviationweather.gov/data/iffdp/2311.gif">Am&eacute;riques - FL340</option>
          <option value="http://aviationweather.gov/data/iffdp/2415.gif">Afrique / Am&eacute;riques - FL100</option>
          <option value="http://aviationweather.gov/data/iffdp/2411.gif">Afrique / Am&eacute;riques - FL340</option>
          <option value="http://aviationweather.gov/data/iffdp/2737.gif">Asie - FL100</option>
          <option value="http://aviationweather.gov/data/iffdp/2733.gif">Asie - FL340</option>
          <option value="http://aviationweather.gov/data/iffdp/2797.gif">Asie / Oc&eacute;anie - FL100</option>
          <option value="http://aviationweather.gov/data/iffdp/2793.gif">Asie / Oc&eacute;anie - FL340</option>
          <option value="http://aviationweather.gov/data/iffdp/2917.gif">Europe / Afrique - FL100</option>
          <option value="http://aviationweather.gov/data/iffdp/2913.gif">Europe / Afrique - FL340</option>
          <option value="http://aviationweather.gov/data/iffdp/2857.gif">Europe / Afrique / Asie - FL100</option>
          <option value="http://aviationweather.gov/data/iffdp/2853.gif">Europe / Afrique / Asie - FL340</option>
          <option value="http://aviationweather.gov/data/iffdp/2517.gif">Am&eacute;rique du Nord / Europe - FL100</option>
          <option value="http://aviationweather.gov/data/iffdp/2513.gif">Am&eacute;rique du Nord / Europe - FL340</option>
          <option value="http://aviationweather.gov/data/iffdp/2255.gif">Pacifique - FL100</option>
          <option value="http://aviationweather.gov/data/iffdp/2251.gif">Pacifique - FL340</option>
          <option value="http://aviationweather.gov/data/iffdp/2200.gif">Pacifique Nord - FL100</option>
          <option value="http://aviationweather.gov/data/iffdp/2196.gif">Pacifique Nord - FL340</option>
    </optgroup>
    <optgroup label="Pr&eacute;visions CAT">
          <option value="http://www.orbit.nesdis.noaa.gov/smcd/opdb/aviation/turb/tidaily/TIAVN24a.GIF">Atlantique - 0h UTC</option>
          <option value="http://www.orbit.nesdis.noaa.gov/smcd/opdb/aviation/turb/tidaily/TIAVN6a.GIF">Atlantique - 6h UTC</option>
          <option value="http://www.orbit.nesdis.noaa.gov/smcd/opdb/aviation/turb/tidaily/TIAVN12a.GIF">Atlantique - 12h UTC</option>
          <option value="http://www.orbit.nesdis.noaa.gov/smcd/opdb/aviation/turb/tidaily/TIAVN18a.GIF">Atlantique - 18h UTC</option>
          <option value="http://www.orbit.nesdis.noaa.gov/smcd/opdb/aviation/turb/tidaily/TIAVN24.GIF">Pacifique - 0h UTC</option>
          <option value="http://www.orbit.nesdis.noaa.gov/smcd/opdb/aviation/turb/tidaily/TIAVN6.GIF">Pacifique - 6h UTC</option>
          <option value="http://www.orbit.nesdis.noaa.gov/smcd/opdb/aviation/turb/tidaily/TIAVN12.GIF">Pacifique - 12h UTC</option>
          <option value="http://www.orbit.nesdis.noaa.gov/smcd/opdb/aviation/turb/tidaily/TIAVN18.GIF">Pacifique - 18h UTC</option>
          <option value="http://www.orbit.nesdis.noaa.gov/smcd/opdb/aviation/turb/tidaily/TIAVN24p.GIF">H&eacute;misph&egrave;re Nord - 0h UTC</option>
          <option value="http://www.orbit.nesdis.noaa.gov/smcd/opdb/aviation/turb/tidaily/TIAVN6p.GIF">H&eacute;misph&egrave;re Nord - 6h UTC</option>
          <option value="http://www.orbit.nesdis.noaa.gov/smcd/opdb/aviation/turb/tidaily/TIAVN12p.GIF">H&eacute;misph&egrave;re Nord - 12h UTC</option>
          <option value="http://www.orbit.nesdis.noaa.gov/smcd/opdb/aviation/turb/tidaily/TIAVN18p.GIF">H&eacute;misph&egrave;re Nord - 18h UTC</option>
    </optgroup>
    </select>
    <input type="submit" value="Afficher" name="submit">
  </td>
  </tr>
  </table>
</form>
<br>
<img id="mapframe" align="center" width="98%" src="<?php echo "$MapTodisplay";?>" />

<?PHP
}
?>
<br />
<div align="right" style="font-size: small;">
  <!--
  Data Courtesy of <a href="http://flightaware.com" target="_new">FlightAware</a>
  -->
</div>
<br />