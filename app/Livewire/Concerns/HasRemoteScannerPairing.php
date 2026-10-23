<?php

namespace App\Livewire\Concerns;

use App\Services\ScannerPairingService;

trait HasRemoteScannerPairing
{
    public string $pairingToken = '';

    public bool $showPairingModal = false;

    public bool $pairingConnected = false;

    public ?string $pairingDeviceName = null;

    public function initRemoteScannerSession(ScannerPairingService $service): void
    {
        if (! empty($this->pairingToken)) {
            return;
        }

        if (auth()->check()) {
            $activeSession = $service->getActiveUserSession(auth()->id());
            if ($activeSession && in_array($activeSession['status'], ['waiting', 'connected'])) {
                $this->pairingToken = $activeSession['token'];
                $this->pairingConnected = ($activeSession['status'] === 'connected');
                $this->pairingDeviceName = $activeSession['device_name'];
            }
        }
    }

    public function startRemotePairing(ScannerPairingService $service, string $type = 'IN'): void
    {
        $session = $service->getOrCreateSessionForUser($type, auth()->user());
        $this->pairingToken = $session['token'];
        $this->pairingConnected = ($session['status'] === 'connected');
        $this->pairingDeviceName = $session['device_name'] ?? null;
        $this->showPairingModal = true;
    }

    public function checkRemoteScanner(ScannerPairingService $service): void
    {
        if (empty($this->pairingToken)) {
            return;
        }

        $session = $service->getSession($this->pairingToken);
        if (! $session) {
            $this->pairingConnected = false;

            return;
        }

        $this->pairingConnected = ($session['status'] === 'connected');
        $this->pairingDeviceName = $session['device_name'];

        $scannedQueue = $service->pollScannedBarcodes($this->pairingToken);
        if (! empty($scannedQueue)) {
            $this->dispatch('remote-barcode-received');
        }
        foreach ($scannedQueue as $item) {
            $this->handleBarcodeScan($item['code']);
        }
    }

    public function closePairingModal(): void
    {
        $this->showPairingModal = false;
    }

    public function disconnectPairing(ScannerPairingService $service): void
    {
        if (! empty($this->pairingToken)) {
            $service->closeSession($this->pairingToken);
        }

        $this->pairingToken = '';
        $this->pairingConnected = false;
        $this->pairingDeviceName = null;
        $this->showPairingModal = false;

        $this->dispatch('swal', [
            'icon' => 'info',
            'title' => 'Pairing HP Diputus',
            'text' => 'Koneksi remote scanner nirkabel telah dihentikan.',
            'toast' => true,
        ]);
    }
}
