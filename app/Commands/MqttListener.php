<?php
namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use PhpMqtt\Client\MqttClient;
use PhpMqtt\Client\ConnectionSettings;
use App\Models\TransaksiModel;

class MqttListener extends BaseCommand
{
    protected $group = 'custom';
    protected $name = 'mqtt:listen';
    protected $description = 'Listen MQTT RFID untuk Smart Parking';

    private $mqttConfig = [
        'host' => '9516210ad30d490284b953b68fa72ef8.s1.eu.hivemq.cloud',
        'port' => 8883,
        'username' => 'dedew',
        'password' => 'Adewahyu1',
        'clientId' => 'ci4-cli-listener-'
    ];
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
    public function run(array $params)
    {
        CLI::write('🚀 Starting MQTT Listener...', 'yellow');

        $clientId = $this->mqttConfig['clientId'] . bin2hex(random_bytes(4));

        $settings = (new ConnectionSettings)
            ->setUsername($this->mqttConfig['username'])
            ->setPassword($this->mqttConfig['password'])
            ->setUseTls(true)
            ->setKeepAliveInterval(60)
            ->setConnectTimeout(10)
            ->setTlsSelfSignedAllowed(false);

        $mqtt = new MqttClient($this->mqttConfig['host'], $this->mqttConfig['port'], $clientId);
        
        try {
            CLI::write("🔗 Connecting to {$this->mqttConfig['host']}:{$this->mqttConfig['port']}...", 'yellow');
            $mqtt->connect($settings, true);
            CLI::write('✅ Connected to MQTT Broker', 'green');
        } catch (\Exception $e) {
            $msg = $e->getMessage();
            CLI::error('❌ MQTT Connect Failed: ' . $msg);
            
            if (strpos($msg, 'certificate') !== false || strpos($msg, 'SSL') !== false) {
                CLI::error('💡 Fix: Set openssl.cafile di php.ini');
            } elseif (strpos($msg, '10060') !== false || strpos($msg, 'timeout') !== false) {
                CLI::error('💡 Fix: Cek firewall atau Test-NetConnection');
            }
            return;
        }

        $model = new TransaksiModel();
        $lastPing = time();

        // 🟢 SUBSCRIBE: ENTRY RFID
        $mqtt->subscribe('parking/adew/entry/rfid', function ($topic, $message) use ($model) {
            try {
                // ✅ PARSE: JSON → Plain Text
                $parsed = $this->parseMqttMessageInline($message);
                $rfid = $parsed['rfid'];
                $jenis = $parsed['jenis'];
                $published = $this->publishMqtt("parking/adew/entry/servo", "OPEN");
                $msg = $published 
            ? 'Kendaraan berhasil masuk.' 
            : 'Kendaraan masuk, tapi servo gagal (cek log).';
                // ✅ FIX: 'gray' → 'light_gray'
                CLI::write("🔍 Raw: $message → RFID=$rfid, Jenis=$jenis", 'light_gray');
                
                if (empty($rfid) || strlen($rfid) < 4) {
                    CLI::write("⚠️ Invalid RFID: $rfid", 'yellow');
                    return;
                }
                
                // Cek duplikat
                $exists = $model->where('idcard', $rfid)
                               ->where('status', 'masuk')
                               ->where('checkout_time', null)
                               ->first();
                
                if (!$exists) {
                    $tarif = ($jenis === 'mobil') ? 5000 : 2000;
                    
                    $model->insert([
                        'idcard' => $rfid,
                        'jenis_kendaraan' => $jenis,
                        'tarif' => $tarif,
                        'checkin_time' => date('Y-m-d H:i:s'),
                        'status' => 'masuk'
                    ]);
                    CLI::write("🚗 ENTRY: $rfid | Jenis: $jenis | Tarif: Rp $tarif", 'cyan');
                }
            } catch (\Exception $e) {
                log_message('error', 'ENTRY Callback Error: ' . $e->getMessage());
                CLI::error('⚠️ Error processing ENTRY: ' . $e->getMessage());
            }
        }, 0);

        // 🔴 SUBSCRIBE: EXIT RFID
        $mqtt->subscribe('parking/adew/exit/rfid', function ($topic, $message) use ($model) {
            try {
                // ✅ PARSE: JSON → Plain Text
                $parsed = $this->parseMqttMessageInline($message);
                $rfid = $parsed['rfid'];
                
                if (empty($rfid) || strlen($rfid) < 4) return;
                
                $transaksi = $model->where('idcard', $rfid)
                                   ->where('status', 'masuk')
                                   ->where('checkout_time', null)
                                   ->first();

                if ($transaksi) {
                    $checkin = $transaksi['checkin_time'];
                    
                    if (empty($checkin)) {
                        CLI::error("⚠️ Invalid checkin_time for RFID: $rfid");
                        return;
                    }
                    
                    $checkinTimestamp = strtotime((string) $checkin);
                    if ($checkinTimestamp === false) {
                        CLI::error("⚠️ Cannot parse checkin_time: $checkin");
                        return;
                    }

                    $durasiDetik = time() - $checkinTimestamp;
                    $durasiJam = $durasiDetik / 3600;
                    $tarif = $transaksi['tarif'] ?? 2000;
                    $bayar = ceil($durasiJam) * $tarif;

                    $model->update($transaksi['id'], [
                        'checkout_time' => date('Y-m-d H:i:s'),
                        'durasi' => round($durasiJam, 2),
                        'bayar' => $bayar,
                        'status' => 'keluar'
                    ]);
                    CLI::write("🚙 EXIT: $rfid | Durasi: " . round($durasiJam, 2) . " jam | Bayar: Rp " . number_format($bayar, 0, ',', '.'), 'yellow');
                } else {
                    CLI::write("⚠️ No active session for RFID: $rfid", 'yellow');
                }
            } catch (\Exception $e) {
                log_message('error', 'EXIT Callback Error: ' . $e->getMessage());
                CLI::error('⚠️ Error processing EXIT: ' . $e->getMessage());
            }
        }, 0);

        CLI::write('🔄 Listening... (Ctrl+C to stop)', 'green');
        
        while (true) {
            $mqtt->loop(true);
            
            if (time() - $lastPing >= 30) {
                // ✅ FIX: 'gray' → 'light_gray'
                CLI::write('💓 Still listening...', 'light_gray');
                $lastPing = time();
            }
        }
    }

    // ✅ FUNGSI: Parse JSON → Plain Text
    private function parseMqttMessageInline(string $message): array
    {
        $result = ['rfid' => '', 'jenis' => 'motor'];
        
        $data = json_decode($message, true);
        
        if (json_last_error() === JSON_ERROR_NONE && is_array($data)) {
            if (isset($data['rfid'])) {
                $result['rfid'] = strtoupper(trim($data['rfid']));
            }
            if (isset($data['jenis'])) {
                $result['jenis'] = strtolower(trim($data['jenis']));
            }
        } else {
            // Bukan JSON → anggap message adalah plain RFID
            $result['rfid'] = strtoupper(trim($message));
        }
        
        return $result;
    }
}