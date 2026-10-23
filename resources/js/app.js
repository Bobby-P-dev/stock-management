import Swal from 'sweetalert2';
import { Html5Qrcode, Html5QrcodeSupportedFormats } from 'html5-qrcode';
import QRCode from 'qrcode';

window.Swal = Swal;
window.Html5Qrcode = Html5Qrcode;
window.Html5QrcodeSupportedFormats = Html5QrcodeSupportedFormats;
window.QRCode = QRCode;

/**
 * Play a crisp, pleasant barcode scan beep sound via Web Audio API.
 */
window.playScanBeep = function () {
    try {
        const audioCtx = new (window.AudioContext || window.webkitAudioContext)();
        const osc = audioCtx.createOscillator();
        const gainNode = audioCtx.createGain();

        osc.type = 'sine';
        osc.frequency.setValueAtTime(1760, audioCtx.currentTime); // Crisp A6 tone
        gainNode.gain.setValueAtTime(0.18, audioCtx.currentTime);
        gainNode.gain.exponentialRampToValueAtTime(0.001, audioCtx.currentTime + 0.12);

        osc.connect(gainNode);
        gainNode.connect(audioCtx.destination);

        osc.start();
        osc.stop(audioCtx.currentTime + 0.12);
    } catch (e) {
        // AudioContext may be blocked before user interaction
    }
};

/**
 * Trigger haptic vibration on mobile device.
 */
window.triggerHaptic = function (duration = 100) {
    if (typeof navigator !== 'undefined' && navigator.vibrate) {
        try {
            navigator.vibrate(duration);
        } catch (e) {}
    }
};

const variantButtonClasses = {
    blue: 'bg-blue-600 hover:bg-blue-700 text-white focus-visible:ring-blue-600',
    indigo: 'bg-indigo-600 hover:bg-indigo-700 text-white focus-visible:ring-indigo-600',
    emerald: 'bg-emerald-600 hover:bg-emerald-700 text-white focus-visible:ring-emerald-600',
    rose: 'bg-rose-600 hover:bg-rose-700 text-white focus-visible:ring-rose-600',
    amber: 'bg-amber-600 hover:bg-amber-700 text-white focus-visible:ring-amber-600',
    slate: 'bg-slate-800 hover:bg-slate-900 text-white focus-visible:ring-slate-700',
};

const baseConfirmBtnClass =
    'inline-flex items-center justify-center gap-1.5 px-4 py-2.5 rounded-xl text-xs font-semibold shadow-xs transition cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2';

const baseCancelBtnClass =
    'inline-flex items-center justify-center px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold transition cursor-pointer focus:outline-none focus-visible:ring-2 focus-visible:ring-slate-400 focus-visible:ring-offset-2';

/**
 * Show a modern toast notification in the top-right corner.
 */
window.SwalToast = function ({
    icon = 'success',
    title = '',
    text = '',
    timer = 3800,
} = {}) {
    return Swal.fire({
        toast: true,
        position: 'top-end',
        backdrop: false,
        icon,
        title,
        text,
        showConfirmButton: false,
        showCloseButton: true,
        timer,
        timerProgressBar: true,
        customClass: {
            container: 'bmi-swal-toast-container',
            popup: 'bmi-swal-toast',
            title: 'text-xs font-bold text-slate-900 leading-snug',
            htmlContainer: 'text-[11px] text-slate-600 mt-0.5 leading-normal',
            closeButton: 'text-slate-400 hover:text-slate-600 focus:outline-none',
        },
        didOpen: (toast) => {
            toast.onmouseenter = Swal.stopTimer;
            toast.onmouseleave = Swal.resumeTimer;
        },
    });
};

/**
 * Show a modern modal alert dialog.
 */
window.SwalAlert = function ({
    icon = 'info',
    title = 'Informasi',
    text = '',
    confirmText = 'Mengerti',
    variant = null,
} = {}) {
    const resolvedVariant =
        variant ||
        (icon === 'error'
            ? 'rose'
            : icon === 'success'
              ? 'emerald'
              : icon === 'warning'
                ? 'amber'
                : 'indigo');

    return Swal.fire({
        icon,
        title,
        text,
        buttonsStyling: false,
        confirmButtonText: confirmText,
        customClass: {
            popup: 'bmi-swal-popup max-w-md w-full',
            title: 'text-base font-bold text-slate-900 tracking-tight pt-1',
            htmlContainer: 'text-xs text-slate-600 leading-relaxed mt-1.5',
            actions: 'flex items-center justify-center gap-2.5 mt-5 w-full',
            confirmButton: `${baseConfirmBtnClass} ${variantButtonClasses[resolvedVariant] || variantButtonClasses.indigo}`,
        },
    });
};

/**
 * Show a modern confirmation dialog before executing an action (Delete, Toggle, Submit, Logout).
 */
window.SwalConfirm = function ({
    title = 'Konfirmasi Tindakan',
    text = 'Apakah Anda yakin ingin melanjutkan proses ini?',
    icon = 'warning',
    confirmText = 'Ya, Lanjutkan',
    cancelText = 'Batal',
    variant = 'indigo',
    onConfirm = null,
} = {}) {
    return Swal.fire({
        icon,
        title,
        text,
        showCancelButton: true,
        buttonsStyling: false,
        reverseButtons: true,
        focusCancel: variant === 'rose',
        confirmButtonText: confirmText,
        cancelButtonText: cancelText,
        customClass: {
            popup: 'bmi-swal-popup max-w-md w-full',
            title: 'text-base font-bold text-slate-900 tracking-tight pt-1',
            htmlContainer: 'text-xs text-slate-600 leading-relaxed mt-1.5',
            actions: 'flex items-center justify-center gap-2.5 mt-6 w-full',
            confirmButton: `${baseConfirmBtnClass} ${variantButtonClasses[variant] || variantButtonClasses.indigo}`,
            cancelButton: baseCancelBtnClass,
        },
    }).then((result) => {
        if (result.isConfirmed && typeof onConfirm === 'function') {
            onConfirm();
        }
        return result;
    });
};

/**
 * Helper to handle incoming payload from Livewire $this->dispatch('swal', ...)
 */
function handleSwalPayload(rawPayload) {
    const payload = Array.isArray(rawPayload) ? rawPayload[0] : rawPayload;
    if (!payload || typeof payload !== 'object') {
        return;
    }

    const {
        icon = 'success',
        title = '',
        text = '',
        toast = icon === 'success',
        timer = 3800,
        confirmText = 'Mengerti',
        variant = null,
    } = payload;

    if (toast) {
        window.SwalToast({ icon, title, text, timer });
    } else {
        window.SwalAlert({ icon, title, text, confirmText, variant });
    }
}

document.addEventListener('livewire:init', () => {
    Livewire.on('swal', (event) => {
        handleSwalPayload(event);
    });

    Livewire.on('remote-barcode-received', () => {
        if (window.playScanBeep) window.playScanBeep();
        if (window.triggerHaptic) window.triggerHaptic(100);
    });
});

window.addEventListener('swal:alert', (event) => {
    handleSwalPayload(event.detail);
});
