<h2>CRUD Tarif Parkir</h2>

<form action="<?= base_url('tarif/simpan') ?>" method="post">
<select name="jenis">
<option value="motor">Motor</option>
<option value="mobil">Mobil</option>
</select>

<input type="number" name="tarif">
<button>Simpan</button>
</form>

<a href="<?= base_url('/dashboard')?>"> <b> Kembali ke halaman Dashbaord</b></a>

<hr>

<table border="1">
<tr>
<th>Jenis</th>
<th>Tarif/Jam</th>
<th>Aksi</th>
</tr>

<?php foreach($tarif as $t): ?>
<tr>
<td><?= $t['jenis_kendaraan'] ?></td>
<td><?= $t['tarif_per_jam'] ?></td>
<td>
<a href="<?= base_url('tarif/edit/'.$t['id']) ?>">Edit</a>
<a href="<?= base_url('tarif/hapus/'.$t['id']) ?>">Hapus</a>
</td>
</tr>
<?php endforeach; ?>

</table>
