<?php
namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use PhpMqtt\Client\MqttClient;
use PhpMqtt\Client\ConnectionSettings;

class MqttServoTest extends BaseCommand
{
    protected $group       = 'custom';
    protected $name        = 'mqtt:servo-test';
    protected $description = 'Simulasi penerima sinyal Servo (Entry & Exit)';

    private $mqttConfig = [
        'host'     => '9516210ad30d490284b953b68fa72ef8.s1.eu.hivemq.cloud',
        'port'     => 8883,
        'username' => 'dedew',
        'password' => 'Adewahyu1',
        'clientId' => 'ci4-servo-sim-'
    ];

    public function run(array $params)
    {
        CLI::write(' MQTT Servo Simulator - Entry & Exit', 'cyan');
        CLI::newLine();

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
            $mqtt->connect($settings, true);
            CLI::write("🔗 Connected to Broker", 'green');
        } catch (\Exception $e) {
            CLI::error('❌ Connect Failed: ' . $e->getMessage());
            return;
        }

        // 🚗 Subscribe: ENTRY Servo
        $mqtt->subscribe('parking/adew/entry/servo', function ($topic, $message) {
            $this->handleServoCommand('ENTRY', $topic, $message);
        }, 0);

        // 🚪 Subscribe: EXIT Servo  
        $mqtt->subscribe('parking/adew/exit/servo', function ($topic, $message) {
            $this->handleServoCommand('EXIT', $topic, $message);
        }, 0);

        CLI::write('👂 Listening: parking/adew/{entry,exit}/servo', 'green');
        CLI::write('   Ctrl+C to stop', 'light_gray');
        CLI::newLine();
        
        $lastPing = time();
        while (true) {
            $mqtt->loop(true);
            if (time() - $lastPing >= 30) {
                CLI::write('💓 Still listening...', 'light_gray');
                $lastPing = time();
            }
        }
    }

    private function handleServoCommand(string $direction, string $topic, string $message)
    {
        CLI::newLine();
        CLI::write("📥 [$direction] Received: $message", 'yellow');
        
        $cmd = strtolower(trim($message));
        
        if ($cmd === 'open') {
            CLI::write("⚡️ [SERVO $direction] ➡️  PUTAR 90° 🚪 GATE OPEN", 'green');
            
            // 💡 Opsional: Auto-close setelah 3 detik (simulasi)
            CLI::write("   ⏱️  Auto-close in 3 seconds...", 'light_gray');
            // Di hardware nyata: gunakan timer/millis(), bukan sleep()
            
        } elseif ($cmd === 'close') {
            CLI::write("⚡️ [SERVO $direction] ➡️  PUTAR 0° 🚪 GATE CLOSE", 'red');
        } else {
            CLI::write("⚠️ Unknown command: $message", 'yellow');
        }
        CLI::newLine();
    }
}