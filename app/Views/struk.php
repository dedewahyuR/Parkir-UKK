<!DOCTYPE html>
<html>
<head>
    <title>Struk Parkir</title>
</head>
<body onload="window.print()">

<h2>STRUK PARKIR</h2>
<hr>
<hr>

<p>ID Transaksi : <?= $transaksi['id'] ?></p>
<p>ID Card : <?= $transaksi['idcard'] ?></p>
<p>Check In : <?= $transaksi['checkin_time'] ?></p>
<p>Check Out : <?= $transaksi['checkout_time'] ?></p>
<p>Durasi : <?= $transaksi['durasi'] ?> Jam</p>
<p>Bayar : Rp <?= number_format($transaksi['bayar']) ?></p>
<p>Status : <?= $transaksi['status'] ?></p>

<hr>
<hr>
<p>Terima Kasih 🙏</p>

</body>
</html>
