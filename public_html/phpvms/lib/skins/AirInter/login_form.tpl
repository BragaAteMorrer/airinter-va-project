<h1>Connection</h1>
<form name="loginform" action="<?php echo url('/login');?>" method="post">
<?php echo "<?xml version='1.0'?>"; ?>
<?php
if(isset($message))
	echo '<p class="error">'.$message.'</p>';
?>
<dl>
	<dt>Identifiant:</dt>
	<dd><input type="text" name="email" value="" />
	
	<dt>Mot de passe:</dt>
	<dd><input type="password" name="password" value="" />
	   
	<dt></dt>
	<dd>Se souvenir de moi? <input type="checkbox" name="remember" /></dd>

	<dt></dt>
	<dd><input type="hidden" name="redir" value="index.php/profile" />
		<input type="hidden" name="action" value="login" />
		<input type="submit" name="submit" value="Connection" />

	
		
	<dt></dt>
	<dd><a href="<?php echo url('Login/forgotpassword'); ?>">J&#39;ai oubli&eacute; mon mot de passe</a></dd>
</dl>
</form>