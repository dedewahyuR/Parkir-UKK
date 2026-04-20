<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link rel="stylesheet" href="<?= base_url('css/user.css') ?>">
</head>

<body>
	<h2>CRUD User</h2>
	<h3>Tambah User</h3>
	<form action="<?= base_url('user/simpan') ?>" method="post">
		<input type="text" name="namaLengkap" placeholder="Nama Lengkap" required>
		<input type="text" name="username" placeholder="Username" required>
		<input type="password" name="password" placeholder="Password" required>
		<select name="level">
			<option value="admin">Admin</option>
			<option value="petugas">Petugas</option>
		</select>

		<button>Buat Akun</button>

	</form>

	<hr>

	<h3>Data User</h3>

	<table border="1" cellpadding="10">

		<tr>
			<th>ID</th>
			<th>Nama</th>
			<th>Username</th>
			<th>Level</th>
			<th>Aksi</th>
		</tr>

		<?php foreach ($user as $u): ?>

			<tr>
				<td><?= $u['idUser'] ?></td>
				<td><?= $u['namaLengkap'] ?></td>
				<td><?= $u['username'] ?></td>
				<td><?= $u['level'] ?></td>

				<td>
					<a href="<?= base_url('user/hapus/' . $u['idUser']) ?>">
						Hapus
					</a>
				</td>

			</tr>

		<?php endforeach; ?>
	</table>
	<a href="/logout" class="menu-link">Logout</a>

</body>

</html>