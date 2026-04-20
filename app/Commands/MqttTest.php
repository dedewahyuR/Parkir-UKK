<?php
namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use PhpMqtt\Client\MqttClient;
use PhpMqtt\Client\ConnectionSettings;

class MqttTest extends BaseCommand
{
    protected $group       = 'custom';
    protected $name        = 'mqtt:test';
    protected $description = 'Kirim data test RFID ke MQTT untuk Smart Parking';

    private $mqttConfig = [
        'host'     => '9516210ad30d490284b953b68fa72ef8.s1.eu.hivemq.cloud',
        'port'     => 8883,
        'username' => 'dedew',
        'password' => 'Adewahyu1',
        'clientId' => 'ci4-test-sender-'
    ];

    public function run(array $params)
    {
        CLI::write('🚀 MQTT Test Sender - Smart Parking', 'cyan');
        CLI::newLine();

        // === Pilihan Mode Test ===
        $mode = CLI::prompt('Pilih mode test', ['entry', 'exit', 'loop', 'custom'], 'required');
        
        if ($mode === 'custom') {
            $this->sendCustom();
            return;
        }

        if ($mode === 'loop') {
            $this->sendLoop();
            return;
        }

        // === Test Single Entry/Exit ===
        $rfid = CLI::prompt('Masukkan RFID (kosongkan untuk random)', '', 'required');
        if (empty($rfid)) {
            $rfid = strtoupper(bin2hex(random_bytes(4)));
            CLI::write(" RFID Generated: $rfid", 'yellow');
        }

        $jenis = CLI::prompt('Jenis kendaraan', ['motor', 'mobil'], 'required');
        $topic = ($mode === 'entry') ? 'parking/adew/entry/rfid' : 'parking/adew/exit/rfid';
        
        $payload = json_encode([
            'rfid'  => $rfid,
            'jenis' => $jenis,
            'timestamp' => time()
        ]);

        $this->publish($topic, $payload);
        
        CLI::newLine();
        CLI::write(" Test $mode sent!", 'green');
        CLI::write("   Topic: $topic");
        CLI::write("   Payload: $payload");
    }

    // ─────────────────────────────────────────
    private function sendCustom()
    {
        CLI::write(' Mode Custom - Input Manual', 'yellow');
        
        $topic = CLI::prompt('Topic MQTT', [
            'parking/adew/entry/rfid',
            'parking/adew/exit/rfid'
        ], 'required');
        
        $format = CLI::prompt('Format payload', ['json', 'plain'], 'required');
        
        if ($format === 'json') {
            $rfid  = CLI::prompt('RFID', '', 'required');
            $jenis = CLI::prompt('Jenis (opsional)', ['motor', 'mobil', '']);
            $payload = json_encode([
                'rfid'  => strtoupper($rfid),
                'jenis' => $jenis ?: 'motor'
            ]);
        } else {
            $payload = CLI::prompt('Payload (plain text)', '', 'required');
        }

        $this->publish($topic, $payload);
        CLI::write(" Sent!", 'green');
    }

    // ─────────────────────────────────────────
    private function sendLoop()
    {
        CLI::write(' Mode Loop - Simulasi 5x Entry + Exit', 'yellow');
        
        $rfidBase = strtoupper(bin2hex(random_bytes(3)));
        
        for ($i = 1; $i <= 5; $i++) {
            $rfid = $rfidBase . sprintf('%02X', $i);
            $jenis = ($i % 2 === 0) ? 'mobil' : 'motor';
            
            // ENTRY
            $entryPayload = json_encode(['rfid' => $rfid, 'jenis' => $jenis]);
            $this->publish('parking/adew/entry/rfid', $entryPayload);
            CLI::write("[$i/5] 🚗 ENTRY: $rfid | $jenis", 'cyan');
            sleep(1);
            
            // EXIT 
            sleep(2);
            $exitPayload = json_encode(['rfid' => $rfid]);
            $this->publish('parking/adew/exit/rfid', $exitPayload);
            CLI::write("[$i/5] 🚪 EXIT : $rfid", 'yellow');
            sleep(1);
        }
        
        CLI::newLine();
        CLI::write(' Loop test completed!', 'green');
    }

    private function publish(string $topic, string $payload): bool
    {
        try {
            $clientId = $this->mqttConfig['clientId'] . bin2hex(random_bytes(4));
            
            $settings = (new ConnectionSettings)
                ->setUsername($this->mqttConfig['username'])
                ->setPassword($this->mqttConfig['password'])
                ->setUseTls(true)
                ->setConnectTimeout(10)
                ->setKeepAliveInterval(30)
                ->setTlsSelfSignedAllowed(false);

            $client = new MqttClient(
                $this->mqttConfig['host'],
                $this->mqttConfig['port'],
                $clientId
            );

            $client->connect($settings, true);
            $client->publish($topic, $payload, 0, false);
            $client->disconnect();
            
            log_message('info', "[MQTT TEST] Published to $topic: $payload");
            return true;
            
        } catch (\Exception $e) {
            CLI::error('❌ Publish Failed: ' . $e->getMessage());
            
            if (stripos($e->getMessage(), 'certificate') !== false) {
                CLI::error('💡 Fix: Pastikan openssl.cafile diatur di php.ini');
                CLI::error('   Cek: ' . php_ini_loaded_file());
            }
            return false;
        }
    }
}