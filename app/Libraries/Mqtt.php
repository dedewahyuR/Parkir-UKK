<?php
namespace App\Libraries;

use PhpMqtt\Client\MqttClient;
use PhpMqtt\Client\ConnectionSettings;
use CodeIgniter\CLI\CLI; // Opsional: untuk debug output

class Mqtt
{
    // ✅ Konfigurasi - Sebaiknya pindah ke .env
    protected $server = '9516210ad30d490284b953b68fa72ef8.s1.eu.hivemq.cloud';
    protected $port = 8883;  // ✅ Port TLS (bukan 1883)
    protected $username = 'dedew';
    protected $password = 'Adewahyu1';
    protected $clientIdPrefix = 'ci4-mqtt-lib';

    /**
     * Publish pesan ke topik MQTT
     * 
     * @param string $topic Topik MQTT
     * @param string $message Pesan yang dikirim
     * @param int $qos Quality of Service (0, 1, atau 2)
     * @param bool $retain Apakah pesan disimpan oleh broker
     * @return bool true jika sukses, false jika gagal
     */
    public function publish(string $topic, string $message, int $qos = 0, bool $retain = false): bool
    {
        try {
            // ✅ Unique clientId tiap publish (hindari conflict)
            $clientId = $this->clientIdPrefix . '-' . uniqid('', true);
            
            $mqtt = new MqttClient($this->server, $this->port, $clientId);

            $settings = (new ConnectionSettings)
                ->setUsername($this->username)      // ✅ Kirim username!
                ->setPassword($this->password)      // ✅ Kirim password!
                ->setUseTls(true)                    // ✅ Aktifkan TLS untuk port 8883
                ->setKeepAliveInterval(60)
                ->setConnectTimeout(5);              // ✅ Timeout 5 detik

            // Connect ke broker
            $mqtt->connect($settings, true);

            // Publish pesan
            $mqtt->publish($topic, $message, $qos, $retain);

            // Disconnect dengan bersih
            $mqtt->disconnect();

            // Opsional: log sukses
            // log_message('info', "MQTT published to $topic");
            
            return true;

        } catch (\Exception $e) {
            // ✅ Tangani error dengan graceful
            $errorMsg = 'MQTT Publish Failed: ' . $e->getMessage();
            log_message('error', $errorMsg);
            
            // Opsional: output ke CLI jika dijalankan via spark
            if (php_sapi_name() === 'cli') {
                CLI::error('❌ ' . $errorMsg);
            }
            
            return false;
        }
    }

    /**
     * Helper: Publish dengan payload JSON
     */
    public function publishJson(string $topic, array $data, int $qos = 0): bool
    {
        $message = json_encode($data);
        return $this->publish($topic, $message, $qos);
    }

    /**
     * Setter untuk konfigurasi dinamis (opsional)
     */
    public function setConfig(array $config): self
    {
        foreach ($config as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }
        return $this;
    }
}