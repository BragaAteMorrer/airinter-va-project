<form action="<?php echo url('/login/forgotpassword');?>" method="post">
<p><strong>Entrez votre adresse de courriel: </strong>
	<input type="text" name="email" />
</p>
<p><input type="hidden" name="action" value="resetpass" />
   <input type="submit" name="submit" value="Envoi du mot de passe" />
</p>
</form>