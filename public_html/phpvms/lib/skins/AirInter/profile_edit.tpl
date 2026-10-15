<h3>Editer le Profil</h3>
<form action="<?php echo url('/profile');?>" method="post" enctype="multipart/form-data">
<dl>
	<dt>Nom</dt>
	<dd><?php echo $userinfo->firstname . ' ' . $userinfo->lastname;?></dd>
	
	<dt>Compagnie</dt>
	<dd><?php echo $userinfo->code?>
		<p>Pour demander un changement, contactez votre administrateur</p>
	</dd>
	
	<dt>Adresse Courriel</dt>
	<dd><input type="text" name="email" value="<?php echo $userinfo->email;?>" />
		<?php
			if(isset($email_error) && $email_error == true)
				echo '<p class="error">Please enter your email address</p>';
		?>
	</dd>
	
	<dt>Pays</dt>
	<dd><select name="location">
		<?php
		foreach($countries as $countryCode=>$countryName)
		{
			if($userinfo->location == $countryCode)
				$sel = 'selected="selected"';
			else	
				$sel = '';
			
			echo '<option value="'.$countryCode.'" '.$sel.'>'.$countryName.'</option>';
		}
		?>
		</select>
		<?php
			if(isset($location_error) &&  $location_error == true)
				echo '<p class="error">Entrez votre pays</p>';
		?>
	</dd>
	
	<dt>Image de Fond de la Signature</dt>
	<dd><select name="bgimage">
		<?php
		foreach($bgimages as $image)
		{
			if($userinfo->bgimage == $image)
				$sel = 'selected="selected"';
			else	
				$sel = '';
			
			echo '<option value="'.$image.'" '.$sel.'>'.$image.'</option>';
		}
		?>
		</select>
	</dd>
	
	<?php
	if($customfields)
	{
		foreach($customfields as $field)
		{
			echo '<dt>'.$field->title.'</dt>
				  <dd>';
			
			if($field->type == 'dropdown')
			{
				$field_values = SettingsData::GetField($field->fieldid);				
				$values = explode(',', $field_values->value);
				
				
				echo "<select name=\"{$field->fieldname}\">";
			
				if(is_array($values))
				{						
					foreach($values as $val)
					{
						$val = trim($val);
						
						if($val == $field->value)
							$sel = " selected ";
						else
							$sel = '';
						
						echo "<option value=\"{$val}\" {$sel}>{$val}</option>";
					}
				}
				
				echo '</select>';
			}
			elseif($field->type == 'textarea')
			{
				echo '<textarea class="customfield_textarea"></textarea>';
			}
			else
			{
				echo '<input type="text" name="'.$field->fieldname.'" value="'.$field->value.'" />';
			}
			
			echo '</dd>';
		}
	}
	?>
	
	<dt>Avatar:</dt>
	<dd><input type="hidden" name="MAX_FILE_SIZE" value="<?php echo Config::Get('AVATAR_FILE_SIZE');?>" />
		<input type="file" name="avatar" size="40"> 
		<p>Votre image doit faire <?php echo Config::Get('AVATAR_MAX_HEIGHT').'x'.Config::Get('AVATAR_MAX_WIDTH');?>pixels maximum</p>
	</dd>
	<dt>Avatar Actuel:</dt>
	<dd><?php	
			if(!file_exists(SITE_ROOT.AVATAR_PATH.'/'.$pilotcode.'.png'))
			{
				echo 'Aucune selection';
			}
			else
			{
		?>
			<img src="<?php	echo SITE_URL.AVATAR_PATH.'/'.$pilotcode.'.png';?>" /></dd>
		<?php
		}
		?>
	<dt></dt>
	<dd><input type="hidden" name="action" value="saveprofile" />
		<input type="submit" name="submit" value="Sauvegarder" /></dd>
</dl>
</form>