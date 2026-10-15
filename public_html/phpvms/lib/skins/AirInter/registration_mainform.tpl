<h3>Inscription</h3>
<p>Bienvenue sur la page d&#39;inscription de <?php echo SITE_NAME; ?>. Apres l&#39;inscription, vous serez notifi&eacute; par un membre du personnel.</p>
<form method="post" action="<?php echo url('/registration');?>">
<dl>
	<dt>Pr&eacute;nom: *</dt>
	<dd><input type="text" name="firstname" value="<?php echo Vars::POST('firstname');?>" />
		<?php
			if($firstname_error == true)
				echo '<p class="error">Veuillez entrer votre pr&eacute;nom</p>';
		?>
	</dd>
	
	<dt>Nom de Famille: *</dt>
	<dd><input type="text" name="lastname" value="<?php echo Vars::POST('lastname');?>" />
		<?php
			if($lastname_error == true)
				echo '<p class="error">Veuillez entrer votre nom de famille</p>';
		?>
	</dd>
	
	<dt>Adresse courriel: *</dt>
	<dd><input type="text" name="email" value="<?php echo Vars::POST('email');?>" />
		<?php
			if($email_error == true)
				echo '<p class="error">Veuillez entrer votre adresse courriel</p>';
		?>
	</dd>
	
	<dt>S&eacute;l&eacute;ction de la compagnie: *</dt>
	<dd>
		<select name="code" id="code">
		<?php
		foreach($allairlines as $airline)
		{
			echo '<option value="'.$airline->code.'">'.$airline->code.' - '.$airline->name.'</option>';
		}
		?>
		</select>
	</dd>
	
	<dt>Hub: *</dt>
	<dd>
		<select name="hub" id="hub">
		<?php
		foreach($allhubs as $hub)
		{
			echo '<option value="'.$hub->icao.'">'.$hub->icao.' - ' . $hub->name .'</option>';
		}
		?>
		</select>
	</dd>

	<dt>Pays: *</dt>
	<dd><select name="location">
		<?php
			foreach($countries as $countryCode=>$countryName)
			{
				if(Vars::POST('location') == $countryCode)
					$sel = 'selected="selected"';
				else	
					$sel = '';
					
				echo '<option value="'.$countryCode.'" '.$sel.'>'.$countryName.'</option>';
			}
		?>
		</select>
		<?php
			if($location_error == true)
				echo '<p class="error">Entrez le pays/p>';
		?>
	</dd>
	
	<dt>Mot de passe: *</dt>
	<dd><input id="password" type="password" name="password1" value="" /></dd>
	
	<dt>V&eacute;rification du mot de passe: *</dt>
	<dd><input type="password" name="password2" value="" />
		<?php
			if($password_error != '')
				echo '<p class="error">'.$password_error.'</p>';
		?>
	</dd>
		
	<?php
	
	//Put this in a seperate template. Shows the Custom Fields for registration
	Template::Show('registration_customfields.tpl');
	
	?>
	
	<dt>Veuillez calculer <?php echo $rand1 .' + '.$rand2?></dt>
	<dd><input id="password" type="captcha" name="captcha" value="" />
		<?php
			if($captcha_error != '')
				echo '<p class="error">'.$captcha_error.'</p>';
		?>
	</dd>
		
	<dt></dt>
	<dd><p>En vous enregistrant, vous acceptez les termes et conditions</p></dd>
	<dt></dt>
	<dd><input type="submit" name="submit" value="Inscription!" /></dd>
</dl>
</form>
