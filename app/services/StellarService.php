<?php

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class StellarService {
    private $client;
    private $horizonUrl;
    private $friendbotUrl;
    private $cache = [];

    public function __construct() {
        $this->horizonUrl = defined('STELLAR_HORIZON_URL') ? STELLAR_HORIZON_URL : 'https://horizon-testnet.stellar.org';
        $this->friendbotUrl = defined('STELLAR_FRIENDBOT_URL') ? STELLAR_FRIENDBOT_URL : 'https://friendbot.stellar.org';

        // Initialize Guzzle Client with SSL verification enabled (default)
        $this->client = new Client([
            'timeout'  => 20.0,
        ]);
    }

    /**
     * Internal cache check (simple per-request lifetime)
     */
    private function getCachedResponse(string $url, array $query) {
        $key = md5($url . serialize($query));
        if (isset($this->cache[$key]) && (time() - $this->cache[$key]['time'] < 5)) {
            return $this->cache[$key]['data'];
        }
        return null;
    }

    private function setCacheResponse(string $url, array $query, $data) {
        $key = md5($url . serialize($query));
        $this->cache[$key] = [
            'time' => time(),
            'data' => $data
        ];
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
        $url = $this->horizonUrl . '/accounts/' . $publicKey;
        $cached = $this->getCachedResponse($url, []);
        if ($cached) return $cached;

        try {
            $response = $this->client->get($url);
            $data = json_decode($response->getBody()->getContents(), true);
            
            $balanceStr = '0.0000000';
            if (isset($data['balances'])) {
                foreach ($data['balances'] as $balance) {
                    if ($balance['asset_type'] === 'native') {
                        $balanceStr = $balance['balance'];
                        break;
                    }
                }
            }
            $this->setCacheResponse($url, [], $balanceStr);
            return $balanceStr;
        } catch (GuzzleException $e) {
            return '0.0000000';
        }
    }

    /**
     * Check if a payment with specific criteria has been received
     */
    public function verifyPayment(string $publicKey, string $memo, string $expectedAmount, string $expectedAsset = 'native'): ?array {
        try {
            $cursor = null;
            $maxPages = 3; // Check up to 60 transactions
            
            for ($i = 0; $i < $maxPages; $i++) {
                $url = $this->horizonUrl . '/accounts/' . $publicKey . '/transactions';
                $query = [
                    'order' => 'desc',
                    'limit' => 20
                ];
                if ($cursor) $query['cursor'] = $cursor;

                $data = $this->getCachedResponse($url, $query);
                if (!$data) {
                    $response = $this->client->get($url, ['query' => $query]);
                    $data = json_decode($response->getBody()->getContents(), true);
                    $this->setCacheResponse($url, $query, $data);
                }
                
                if (empty($data['_embedded']['records'])) break;

                foreach ($data['_embedded']['records'] as $tx) {
                    // Update cursor for next page if needed
                    $cursor = $tx['paging_token'];

                    // 1. Check if transaction was successful and memo matches
                    if (!$tx['successful']) continue;
                    if (!isset($tx['memo']) || $tx['memo'] !== $memo) continue;

                    // 2. Fetch operations for this transaction to verify payment details
                    $opsResponse = $this->client->get($tx['_links']['operations']['href']);
                    $opsData = json_decode($opsResponse->getBody()->getContents(), true);

                    foreach ($opsData['_embedded']['records'] as $op) {
                        if ($op['type'] === 'payment') {
                            $isNative = ($expectedAsset === 'native' && $op['asset_type'] === 'native');
                            $isAsset = ($op['asset_code'] === $expectedAsset);
                            
                            // 3. Verify destination, amount, and asset
                            if ($op['to'] === $publicKey && 
                                ($isNative || $isAsset) && 
                                floatval($op['amount']) >= floatval($expectedAmount)) {
                                
                                return [
                                    'hash' => $tx['hash'],
                                    'amount' => $op['amount'],
                                    'sender' => $op['from'],
                                    'created_at' => $tx['created_at']
                                ];
                            }
                        }
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
