<h3>Changer le mot de passe</h3>
<form action="<?php echo url('/profile');?>" method="post">
<dl>

	<dt>Nouveau mot de passe</dt>
	<dd><input type="password" id="password" name="password1" value="" /></dd>
	
	<dt>Confirmer le mot de passe</dt>
	<dd><input type="password" name="password2" value="" /></dd>
	
	<dt>Ancien mot de passe</dt>
	<dd><input type="password" name="oldpassword" /></dd>
	
	<dt></dt>
	<dd><input type="hidden" name="action" value="changepassword" />
		<input type="submit" name="submit" value="Sauvegarder" />
	</dd>
</dl>
</form>