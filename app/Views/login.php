<!DOCTYPE html>
<html>

<head>
	<style>
	</style>
	<link rel="stylesheet" href="<?= base_url('css/login.css') ?>">
</head>

<body>


	<div class="kotak_login">
		<?php if (session()->getFlashdata('error')) : ?>
			<div class='alert'>
				<?= session()->getFlashdata('error'); ?>
			</div>
		<?php endif; ?>


		<p class="tulisan_login">Silahkan login</p>

		<form action="/cekLogin" method="post">
			<label>Username</label>
			<input type="text" name="username" class="form_login" placeholder="Username .." required="required">

			<label>Password</label>
			<input type="password" name="password" class="form_login" placeholder="Password .." required="required">

			<input type="submit" class="tombol_login" value="LOGIN">

			<br />
			<br />
		</form>

	</div>


</body>

</html>