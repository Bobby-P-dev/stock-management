<?php

namespace App\Livewire\Scanner;

use App\Services\ScannerPairingService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.mobile-scanner')]
#[Title('Wireless Scanner HP - Berkah Mandiri Inventory')]
class RemoteScanner extends Component
{
    public string $token = '';

    public ?array $sessionData = null;

    public bool $isValid = false;

    /** @var list<array{code: string, name: string, time: string}> */
    public array $recentScans = [];

    public int $scanCount = 0;

    public function mount(string $token, ScannerPairingService $pairingService): void
    {
        $this->token = trim($token);
        $this->sessionData = $pairingService->getSession($this->token);

        if (! $this->sessionData) {
            $this->isValid = false;

            return;
        }

        $this->isValid = true;
        $deviceName = request()->header('User-Agent', 'Smartphone');
        $pairingService->connectDevice($this->token, $deviceName);
        $this->recentScans = $this->sessionData['recent_scans'] ?? [];
    }

    public function processBarcode(string $barcode, ScannerPairingService $pairingService): void
    {
        $code = trim($barcode);
        if ($code === '' || ! $this->isValid) {
            return;
        }

        $result = $pairingService->pushScannedBarcode($this->token, $code);

        if ($result['success']) {
            $this->scanCount++;
            $this->sessionData = $pairingService->getSession($this->token);
            $this->recentScans = $this->sessionData['recent_scans'] ?? [];

            $productLabel = $result['product_name'] ?? 'Barang Belum Terdaftar';

            $this->dispatch('swal', [
                'icon' => $result['product_found'] ? 'success' : 'info',
                'title' => $result['product_found'] ? 'Terkirim ke Laptop!' : 'Barcode Terkirim!',
                'text' => "{$productLabel} ({$code})",
                'toast' => true,
                'timer' => 2000,
            ]);
        }
    }

    public function render()
    {
        return view('livewire.scanner.remote-scanner');
    }
}
