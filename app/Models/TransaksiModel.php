<?php
namespace App\Models;

use CodeIgniter\Model;

class TransaksiModel extends Model
{
    protected $table = 'transaksi_parkir';
    protected $returnType = 'array';
    protected $primaryKey = 'id';
    
    // ✅ Gunakan lowercase/underscore konsisten
    protected $allowedFields = [
        'idcard',
        'checkin_time',
        'checkout_time',
        'durasi',
        'bayar',
        'status',
        'jenisKendaraan',  // ✅ Ubah ke snake_case (konsisten dengan DB)
        'tarif'
    ];

    // ✅ Opsional: Tambahkan validasi
    protected $validationRules = [
        'idcard' => 'required|min_length[5]',
        'status' => 'required|in_list[masuk,keluar,selesai]',
        'tarif' => 'required|numeric'
    ];

    // ✅ Opsional: Auto timestamps
    protected $useTimestamps = false; // true jika kolom created_at/updated_at ada
}