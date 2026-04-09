<?php
namespace App\Controllers;

use App\Models\TransaksiModel;
use PhpMqtt\Client\MqttClient;
use PhpMqtt\Client\ConnectionSettings;

class Transaksi extends BaseController
{
    protected $model;

    // MQTT Config
    protected $server = '9516210ad30d490284b953b68fa72ef8.s1.eu.hivemq.cloud';
    protected $port = 8883;  // TLS port
    protected $username = 'dedew';
    protected $password = 'Adewahyu1';

    public function __construct()
    {
        $this->model = new TransaksiModel();
        // ✅ HAPUS: $this->modelT = new TarifModel(); (tidak dipakai)
    }

    private function getMqttClient()
    {
        // ✅ WAJIB: setUseTls(true) untuk port 8883
        $settings = (new ConnectionSettings)
            ->setUsername($this->username)
            ->setPassword($this->password)
            ->setUseTls(true)           // ✅ INI YANG KURANG!
            ->setConnectTimeout(5)
            ->setKeepAliveInterval(30);

        $client = new MqttClient($this->server, $this->port, 'ci4-web-' . bin2hex(random_bytes(4)));
        $client->connect($settings, true);
        return $client;
    }

    private function publishMqtt(string $topic, string $message): bool
    {
        try {
            $client = $this->getMqttClient();
            $client->publish($topic, $message, 0, false);
            $client->disconnect();
            
            log_message('info', "✅ MQTT published: $topic = $message");
            return true;
        } catch (\Exception $e) {
            log_message('error', '❌ MQTT Publish Failed: ' . $e->getMessage());
            return false;
        }
    }

    public function index()
    {
        $data['masuk'] = $this->model
            ->where('status', 'masuk')
            ->where('checkout_time', null)
            ->orderBy('id', 'ASC')
            ->findAll();

        $data['checkout'] = $this->model
            ->where('status', 'masuk')
            ->where('checkout_time !=', null)
            ->orderBy('id', 'ASC')
            ->findAll();

        $data['keluar'] = $this->model
            ->where('status', 'keluar')
            ->orderBy('id', 'DESC')
            ->findAll();
        
        $data['selesai'] = $this->model
            ->where('status', 'selesai')
            ->orderBy('id', 'DESC')
            ->findAll();

        return view('dashboard', $data);
    }

    public function masuk()
    {
        $idcard = $this->request->getPost('idcard');
        $jenis = $this->request->getPost('jenisKendaraan') ?? 'motor';

        $tarif = match($jenis) {
            'mobil' => 5000,
            default => 2000
        };

        $this->model->insert([
            'idcard' => $idcard,
            'jenis_kendaraan' => $jenis,
            'tarif' => $tarif,
            'checkin_time' => date('Y-m-d H:i:s'),
            'status' => 'masuk'
        ]);

        $published = $this->publishMqtt("parking/adew/entry/servo", "OPEN");
        
        $msg = $published 
            ? 'Kendaraan berhasil masuk.' 
            : 'Kendaraan masuk, tapi servo gagal (cek log).';
            
        return redirect()->to('/dashboard')->with('success', $msg);
    }

    public function prosesKeluar($id)
    {
        $data = $this->model->find($id);
        
        if (!$data || empty($data['checkin_time'])) {
            return redirect()->back()->with('error', 'Data tidak valid.');
        }

        $checkout = date('Y-m-d H:i:s');
        $checkinTimestamp = strtotime((string) $data['checkin_time']);
        
        if ($checkinTimestamp === false) {
            return redirect()->back()->with('error', 'Waktu check-in invalid.');
        }

        $durasiJam = (time() - $checkinTimestamp) / 3600;
        $bayar = ceil($durasiJam) * ($data['tarif'] ?? 2000);

        // ✅ Update SEMUA field + status
        $this->model->update($id, [
            'checkout_time' => $checkout,
            'durasi' => round($durasiJam, 2),
            'bayar' => $bayar,
            'status' => 'keluar'  // ✅ PENTING!
        ]);
        return redirect()->to('/dashboard');
        // ✅ Buka palang
        
    }

    public function keluar($id)
    {
        $this->model->update($id, [
            'status' => 'selesai'  
        ]);


        $published = $this->publishMqtt("parking/adew/entry/servo", "OPEN");
        
        $msg = $published 
            ? 'Kendaraan berhasil keluar.' 
            : 'Kendaraan masuk, tapi servo gagal (cek log).';

        return redirect()->to('/dashboard')
            ->with('success', $msg);
    }

    // ❌ HAPUS method keluar() yang lama - sudah digabung

    public function bukaPalangMasuk()
    {
        $this->publishMqtt("parking/adew/entry/servo", "OPEN");
        return redirect()->to('/dashboard');
    }

    public function bukaPalangKeluar()
    {
        $this->publishMqtt("parking/adew/exit/servo", "OPEN");
        return redirect()->to('/dashboard');
    }
}