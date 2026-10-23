<?php

namespace App\Services;

use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class ScannerPairingService
{
    private const CACHE_PREFIX = 'scanner_session:';

    private const USER_TOKEN_PREFIX = 'scanner_user_token:';

    private const TTL_MINUTES = 120;

    /**
     * Create a new remote scanner pairing session.
     *
     * @return array{token: string, type: string, user_id: int, user_name: string, status: string, device_name: ?string, created_at: int}
     */
    public function createSession(string $type, User $user): array
    {
        $token = 'BSC-'.Str::upper(Str::random(8));

        $sessionData = [
            'token' => $token,
            'type' => strtoupper($type),
            'user_id' => $user->id,
            'user_name' => $user->name,
            'status' => 'waiting',
            'device_name' => null,
            'scanned_queue' => [],
            'recent_scans' => [],
            'last_transaction' => null,
            'created_at' => Carbon::now()->timestamp,
            'last_active' => Carbon::now()->timestamp,
        ];

        Cache::put(self::CACHE_PREFIX.$token, $sessionData, Carbon::now()->addMinutes(self::TTL_MINUTES));
        Cache::put(self::USER_TOKEN_PREFIX.$user->id, $token, Carbon::now()->addMinutes(self::TTL_MINUTES));

        return $sessionData;
    }

    /**
     * Retrieve or create an active session for the authenticated user.
     *
     * @return array{token: string, type: string, user_id: int, user_name: string, status: string, device_name: ?string, scanned_queue: list<array{code: string, time: string}>, recent_scans: list<array{code: string, name: string, time: string}>, created_at: int, last_active: int}
     */
    public function getOrCreateSessionForUser(string $type, User $user): array
    {
        $existing = $this->getActiveUserSession($user->id);
        if ($existing) {
            if ($existing['type'] !== strtoupper($type)) {
                $existing['type'] = strtoupper($type);
                $existing['last_active'] = Carbon::now()->timestamp;
                Cache::put(self::CACHE_PREFIX.$existing['token'], $existing, Carbon::now()->addMinutes(self::TTL_MINUTES));
            }

            return $existing;
        }

        return $this->createSession($type, $user);
    }

    /**
     * Retrieve active session for a specific user ID.
     *
     * @return array{token: string, type: string, user_id: int, user_name: string, status: string, device_name: ?string, scanned_queue: list<array{code: string, time: string}>, recent_scans: list<array{code: string, name: string, time: string}>, created_at: int, last_active: int}|null
     */
    public function getActiveUserSession(int $userId): ?array
    {
        $token = Cache::get(self::USER_TOKEN_PREFIX.$userId);
        if (! $token) {
            return null;
        }

        $session = $this->getSession((string) $token);
        if (! $session || ($session['status'] ?? '') === 'closed') {
            Cache::forget(self::USER_TOKEN_PREFIX.$userId);

            return null;
        }

        return $session;
    }

    /**
     * Retrieve an active session by token.
     *
     * @return array{token: string, type: string, user_id: int, user_name: string, status: string, device_name: ?string, scanned_queue: list<array{code: string, time: string}>, recent_scans: list<array{code: string, name: string, time: string}>, created_at: int, last_active: int}|null
     */
    public function getSession(string $token): ?array
    {
        return Cache::get(self::CACHE_PREFIX.trim($token));
    }

    /**
     * Mark device as connected to the pairing session.
     */
    public function connectDevice(string $token, string $deviceName = 'Smartphone'): bool
    {
        $session = $this->getSession($token);
        if (! $session) {
            return false;
        }

        $session['status'] = 'connected';
        $session['device_name'] = $deviceName;
        $session['last_active'] = Carbon::now()->timestamp;

        Cache::put(self::CACHE_PREFIX.$token, $session, Carbon::now()->addMinutes(self::TTL_MINUTES));

        return true;
    }

    /**
     * Notify mobile scanner that a transaction was completed on the laptop.
     */
    public function notifyTransactionCompleted(string $token, string $transactionNo): void
    {
        $session = $this->getSession($token);
        if (! $session) {
            return;
        }

        $session['last_transaction'] = [
            'transaction_no' => $transactionNo,
            'time' => Carbon::now()->format('H:i:s'),
            'timestamp' => Carbon::now()->timestamp,
        ];
        $session['last_active'] = Carbon::now()->timestamp;

        Cache::put(self::CACHE_PREFIX.$token, $session, Carbon::now()->addMinutes(self::TTL_MINUTES));
    }

    /**
     * Push a scanned barcode from mobile scanner to the session queue.
     *
     * @return array{success: bool, product_found: bool, product_name: ?string, code: string}
     */
    public function pushScannedBarcode(string $token, string $barcode): array
    {
        $session = $this->getSession($token);
        if (! $session) {
            return [
                'success' => false,
                'product_found' => false,
                'product_name' => null,
                'code' => $barcode,
            ];
        }

        $cleanBarcode = trim($barcode);
        $product = Product::where('barcode', $cleanBarcode)->orWhere('sku', $cleanBarcode)->first();
        $productName = $product?->name ?? 'Barang Belum Terdaftar';

        $scanItem = [
            'code' => $cleanBarcode,
            'name' => $productName,
            'time' => Carbon::now()->format('H:i:s'),
        ];

        $session['scanned_queue'][] = $scanItem;
        array_unshift($session['recent_scans'], $scanItem);
        $session['recent_scans'] = array_slice($session['recent_scans'], 0, 5);
        $session['last_active'] = Carbon::now()->timestamp;

        Cache::put(self::CACHE_PREFIX.$token, $session, Carbon::now()->addMinutes(self::TTL_MINUTES));

        return [
            'success' => true,
            'product_found' => $product !== null,
            'product_name' => $product?->name,
            'code' => $cleanBarcode,
        ];
    }

    /**
     * Poll and pop pending barcodes for the laptop Livewire form.
     *
     * @return list<array{code: string, name: string, time: string}>
     */
    public function pollScannedBarcodes(string $token): array
    {
        $session = $this->getSession($token);
        if (! $session || empty($session['scanned_queue'])) {
            return [];
        }

        $queue = $session['scanned_queue'];
        $session['scanned_queue'] = [];
        $session['last_active'] = Carbon::now()->timestamp;

        Cache::put(self::CACHE_PREFIX.$token, $session, Carbon::now()->addMinutes(self::TTL_MINUTES));

        return $queue;
    }

    /**
     * Close the pairing session.
     */
    public function closeSession(string $token): void
    {
        $session = $this->getSession($token);
        if ($session && isset($session['user_id'])) {
            Cache::forget(self::USER_TOKEN_PREFIX.$session['user_id']);
        }
        Cache::forget(self::CACHE_PREFIX.trim($token));
    }
}
