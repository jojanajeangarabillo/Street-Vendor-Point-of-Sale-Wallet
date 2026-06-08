<?php

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class StellarService {
    private $client;
    private $horizonUrl = 'https://horizon-testnet.stellar.org';
    private $friendbotUrl = 'https://friendbot.stellar.org';

    public function __construct() {
        // Initialize Guzzle Client
        $this->client = new Client([
            'timeout'  => 15.0,
            'verify'   => false, // Useful for some XAMPP environments with SSL issues
        ]);
    }

    /**
     * Generate a new Stellar Keypair
     * Uses Ed25519 (libsodium) and encodes to StrKey format
     */
    public function generateKeypair(): array {
        if (!function_exists('sodium_crypto_sign_keypair')) {
            throw new Exception('libsodium extension is required for key generation.');
        }

        $seed = random_bytes(32);
        $keypair = sodium_crypto_sign_seed_keypair($seed);
        $secret = sodium_crypto_sign_secretkey($keypair);
        $public = sodium_crypto_sign_publickey($keypair);

        // Stellar secret seed is just the first 32 bytes of the Ed25519 secret key
        $stellarSeed = substr($secret, 0, 32);

        return [
            'publicKey' => $this->encodeCheck(48, $public), // 6 << 3 = 48 (G)
            'secretKey' => $this->encodeCheck(144, $stellarSeed) // 18 << 3 = 144 (S)
        ];
    }

    /**
     * Fund account using Friendbot API (Testnet only)
     */
    public function fundAccount(string $publicKey): bool {
        try {
            $response = $this->client->get($this->friendbotUrl, [
                'query' => ['addr' => $publicKey]
            ]);
            return $response->getStatusCode() === 200;
        } catch (GuzzleException $e) {
            return false;
        }
    }

    /**
     * Fetch native XLM balance from Horizon
     */
    public function getBalance(string $publicKey): ?string {
        try {
            $response = $this->client->get($this->horizonUrl . '/accounts/' . $publicKey);
            $data = json_decode($response->getBody()->getContents(), true);
            
            if (isset($data['balances'])) {
                foreach ($data['balances'] as $balance) {
                    if ($balance['asset_type'] === 'native') {
                        return $balance['balance'];
                    }
                }
            }
            return '0.0000000';
        } catch (GuzzleException $e) {
            // Account might not exist yet
            return '0.0000000';
        }
    }

    /**
     * Check if a payment with a specific memo has been received
     */
    public function verifyPayment(string $publicKey, string $memo): ?array {
        try {
            // Fetch recent transactions for the account
            $response = $this->client->get($this->horizonUrl . '/accounts/' . $publicKey . '/transactions', [
                'query' => [
                    'order' => 'desc',
                    'limit' => 10
                ]
            ]);
            $data = json_decode($response->getBody()->getContents(), true);
            
            if (isset($data['_embedded']['records'])) {
                foreach ($data['_embedded']['records'] as $tx) {
                    // Check if transaction is successful and memo matches
                    if ($tx['successful'] && isset($tx['memo']) && $tx['memo'] === $memo) {
                        return [
                            'hash' => $tx['hash'],
                            'created_at' => $tx['created_at']
                        ];
                    }
                }
            }
            return null;
        } catch (GuzzleException $e) {
            return null;
        }
    }

    /**
     * StrKey Encoding (Version + Payload + CRC16 + Base32)
     */
    private function encodeCheck(int $versionByte, string $data): string {
        $payload = chr($versionByte) . $data;
        $checksum = $this->crc16($payload);
        return $this->base32Encode($payload . $checksum);
    }

    private function crc16(string $data): string {
        $crc = 0x0000;
        for ($i = 0; $i < strlen($data); $i++) {
            $x = (($crc >> 8) ^ ord($data[$i])) & 0xff;
            $x ^= $x >> 4;
            $crc = (($crc << 8) ^ ($x << 12) ^ ($x << 5) ^ $x) & 0xffff;
        }
        return pack('v', $crc); // Little-endian
    }

    private function base32Encode(string $data): string {
        $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $binary = '';
        foreach (str_split($data) as $char) {
            $binary .= str_pad(decbin(ord($char)), 8, '0', STR_PAD_LEFT);
        }
        $res = '';
        foreach (str_split($binary, 5) as $chunk) {
            $res .= $alphabet[bindec(str_pad($chunk, 5, '0', STR_PAD_RIGHT))];
        }
        return $res;
    }
}
