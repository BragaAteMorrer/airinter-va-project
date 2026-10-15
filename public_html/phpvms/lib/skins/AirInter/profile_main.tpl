<div id="mainbox">
<h3>Op&eacute;rations</h3>
<div class="indent">
<p><strong>Bienvenue <?php echo $userinfo->firstname . ' ' . $userinfo->lastname; ?>!</strong></p>
<table>
<tr>
	<td valign="top" align="center">
		<img src="<?php echo PilotData::getPilotAvatar($pilotcode); ?>" />
		<br /><br />
		<img src="<?php echo RanksData::GetRankImage($userinfo->rank) ?>" />
	</td>
	<td valign="top">
		<ul style="margin-top: 0px;">
			<li><strong>Votre Identifiant: </strong> <?php echo $pilotcode; ?></li>
			<li><strong>Grade: </strong><?php echo $userinfo->rank;?></li>
			<?php
			if($report)
			{ ?>
				<li><strong>Dernier vol: </strong><a 
						href="<?php echo url('pireps/view/'.$report->pirepid); ?>">
						<?php echo $report->code . $report->flightnum; ?></a>
				</li>
			<?php
			}
			?>
			
			<li><strong>Total des Vols: </strong><?php echo $userinfo->totalflights?></li>
			<li><strong>Total des Heures: </strong><?php echo $userinfo->totalhours; ?></li>
			<li><strong>Total des Heures Transfer&eacute;es: </strong><?php echo $userinfo->transferhours?></li>
			<li><strong>Total dans le compte en banque: </strong><?php echo FinanceData::FormatMoney($userinfo->totalpay) ?></li>
		
			<?php
			if($nextrank)
			{
			?>
				<p>Vous avez <?php echo ($nextrank->minhours - $pilot_hours)?> heures 
					jusqu&#39;&agrave; votre promotion &agrave; <?php echo $nextrank->rank?></p>
			<?php
			}
			?>
		</ul>

	</td>
</tr>
</table>
	<table>
	<tr>
	<td valign="top" nowrap>
		<p>
			<strong>Options du profil</strong>
			<ul>
				<li><a href="<?php echo url('/profile/editprofile'); ?>">Editer Mon Profil, Courriel et Avatar</a></li>
				<li><a href="<?php echo url('/profile/changepassword'); ?>">Changer mon mot de passe</a></li>
				<li><a href="<?php echo SITE_URL.SIGNATURE_PATH.'/'.$pilotcode.'.png' ?>">Consulter mon insigne</a></li>
				<li><a href="<?php echo url('/profile/stats'); ?>">Mes Statistiques</a></li>
				<li><a href="<?php echo url('/downloads'); ?>">Les T&eacute;l&eacute;chargements</a></li>
			</ul>
		</p>
		<p>
			<strong>Operations de vols</strong>
			<ul>
				<li><a href="<?php echo url('/pireps/mine');?>">Mes PIREPs</a></li>
				<li><a href="<?php echo url('/pireps/routesmap');?>">Carte des vols enregistr&eacute;s</a></li>
				<li><a href="<?php echo url('/pireps/filepirep');?>">Enregistrer un PIREP</a></li>
				<li><a href="<?php echo url('/schedules/view');?>">Consulter les horaires</a></li>
				<li><a href="<?php echo url('/schedules/bids');?>">Mes r&eacute;servations</a></li>		
				<li><a href="<?php echo url('/finances');?>">Finances de la Compagnie</a></li>
			</ul>	
		</p>
		<p>
			<strong>Mes r&eacute;compenses</strong><br />
			<?php
			if(!$allawards)
			{
				echo 'Aucune r&eacute;compense';
			}
			else
			{	
			
				/* To show the image:
					<img src="<?php echo $award->image?>" alt="<?php echo $award->descrip?>" />
				*/
							
			?>
			<ul>
				<?php foreach($allawards as $award){ ?>
				<li><?php echo $award->name ?></li>
				<?php } ?>
				
				
			</ul>	
			<?php
			}
			?>
		</p>
		<p>
			<strong>Configuration ACARS</strong>
			<ul>
			  <!--			  
				<li><a href="<?php echo actionurl('/acars/fsacarsconfig');?>">T&eacute;l&eacute;chargement Configuration FSACARS</a></li>
				-->
				<li><a href="<?php echo actionurl('/acars/xacarsconfig');?>">T&eacute;l&eacute;chargement Configuration XAcars</a></li>
				<!--	
				<li><a href="<?php echo actionurl('/acars/fspaxconfig');?>">T&eacute;l&eacute;chargement Configuration FSPax</a></li>		
				<li><strong>For FSFK, you need the following (Place in Documents/FS Flight Keeper/Templates): </strong></li>
				<li><a href="<?php echo actionurl('/fsfk/vaconfig_template');?>">VA-Template.txt</a></li>
				-->
				
			</ul>
		</p>
	</td>
	<td valign="top">
		
	</td>
	</tr></table>
</div>
</div>
<br />