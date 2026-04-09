<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Parkir</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link rel="stylesheet" href="<?= base_url('css/dashboard.css') ?>">
</head>

<body>

<!-- ===== SIDEBAR ===== -->
<div class="sidebar">
    <h2>Dashboard</h2>
    <?php if (session()->get('level') === 'admin'): ?>
        <a href="<?= base_url('user') ?>" class="menu-link">Buat User</a>
        <!-- <a href="<?= base_url('tarif') ?>" class="menu-link">Tarif Parkir</a> -->
    <?php endif; ?>
    <a href="/logout" class="menu-link">Logout</a>


    <!-- <button onclick="showHistori()" class="btn btn-primary" style="margin-top:10px;">
        Histori Parkir
    </button> -->
</div>

<!-- ===== MAIN CONTENT ===== -->
<div class="main">

    <div class="header">
        <h3>
            Halo <?= session()->get('namaLengkap'); ?> 
            (<?= session()->get('level'); ?>)
        </h3>
    </div>

    <?php if (session()->getFlashdata('Selamat Datang')): ?>
        <div class="alert-success">
            <?= session()->getFlashdata('Selamat Datang'); ?>
        </div>
    <?php endif; ?>

    <?php if (session()->get('level') === 'petugas'): ?>

    <!-- FORM MASUK -->
    <!-- <div class="card">
        <h3>Tambah Parkir Masuk</h3>

        <form action="/transaksi/masuk" method="post">
            <label>ID Card</label>
            <input type="text" name="idcard" required class="input">

            <label>Jenis Kendaraan</label>
            <select name="jenisKendaraan" required class="input">
                <option value="">Pilih Kendaraan</option>
                <option value="motor">Motor</option>
                <option value="mobil">Mobil</option>
            </select>

            <button type="submit" class="btn btn-success">
                Masuk Parkir
            </button>
        </form>
    </div> -->

    <!-- CHECKIN -->
    <div class="card">
        <h3>Checkin</h3>

        <table class="table">
            <thead>
                <tr>
                    <th>ID Card</th>
                    <th>Checkin</th>
                    <!-- <th>Aksi</th> -->
                </tr>
            </thead>
            <tbody>
                    <?php foreach ($masuk as $m): ?>
                    <tr>
                        <td><?= esc($m['idcard']); ?></td>
                        <td><?= esc($m['checkin_time']); ?></td>
                        <!-- <td>
                            <?php if ($m['status'] === 'masuk'): ?>
                                <a href="/transaksi/prosesKeluar/<?= $m['id']; ?>" 
                                   class="btn btn-primary">
                                    Keluar
                                </a>
                            <?php endif; ?>
                        </td> -->
                    </tr>
                    <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- CHECKOUT -->
    <div class="card">
        <h3>Checkout</h3>

        <table class="table">
            <thead>
                <tr>
                    <th>ID Card</th>
                    <th>Checkin</th>
                    <th>Checkout</th>
                    <th>Durasi</th>
                    <th>Bayar</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                    <?php foreach ($keluar as $k): ?>
                    <tr>
                        <td><?= esc($k['idcard']); ?></td>
                        <td><?= esc($k['checkin_time']); ?></td>
                        <td><?= esc($k['checkout_time']); ?></td>
                        <td><?= esc($k['durasi']); ?></td>
                        <td><?= esc($k['bayar']); ?></td>
                        <td>
                            <a href="<?= base_url('transaksi/keluar/' . $k['id']); ?>" 
                               class="btn btn-success">
                                Buka Palang
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <?php endif; ?>

    <!-- HISTORI -->
    <!-- <section id="historiSection" class="card hidden"> -->
    <div class="card">
        <h3>Histori Parkir</h3>

        <table class="table">
            <thead>
                <tr>
                    <th>ID Card</th>
                    <th>Checkin</th>
                    <th>Checkout</th>
                    <th>Durasi</th>
                    <th>Bayar</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                    <?php foreach ($selesai as $s): ?>
                    <tr>
                        <td><?= $s['idcard']; ?></td>
                        <td><?= esc($s['checkin_time']); ?></td>
                        <td><?= esc($s['checkout_time']); ?></td>
                        <td><?= esc($s['durasi']); ?></td>
                        <td><?= esc($s['bayar']); ?></td>
                        <td><?= esc($s['status']); ?></td>
                    </tr>
                    <?php endforeach; ?>
            </tbody>
        </table>
        </div>
    <!-- </section> -->


</div>

<script>
function showHistori() {
    const section = document.getElementById('historiSection');
    section.classList.toggle('hidden');
}
</script>

</body>
</html>