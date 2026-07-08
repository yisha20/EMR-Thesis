@php
    $panelId = $panelId ?? 'prescription-panel-' . $prescription->id;
    $isOpen = (bool) ($isOpen ?? false);
    $autoPrint = (bool) ($autoPrint ?? false);
    $variant = $variant ?? null;
    $showClose = (bool) ($showClose ?? true);
    $canExport = $canExport ?? true;
    $variantClass = in_array($variant, ['medical-record', 'medical-record-modal'], true) ? 'is-medical-record-notice' : '';
    $panelClass = trim('inline-prescription-panel ' . $variantClass . ' ' . ($variant === 'medical-record-modal' ? 'is-modal-preview' : '') . ' ' . ($isOpen ? 'is-open' : ''));
@endphp

<section
    id="{{ $panelId }}"
    class="{{ $panelClass }}"
    data-prescription-panel
    data-prescription-id="{{ $prescription->id }}"
    data-auto-print="{{ $autoPrint ? 'true' : 'false' }}"
    {{ $isOpen ? '' : 'hidden' }}
>
    <div class="inline-prescription-toolbar">
        <div>
            <p class="eyebrow">Prescription preview</p>
            <h3>{{ $prescription->prescription_number }}</h3>
        </div>
        <div class="inline-prescription-actions">
            @if ($canExport)
                <button type="button" class="btn btn-primary btn-sm" data-prescription-print="{{ $panelId }}">
                    <i class="fa fa-print"></i> Print
                </button>
                <a href="{{ route('prescriptions.download', $prescription) }}" class="btn btn-light btn-sm">
                    <i class="fa fa-download"></i> PDF
                </a>
            @endif
            @if ($showClose)
                <button type="button" class="btn btn-light btn-sm" data-prescription-close="{{ $panelId }}" aria-label="Close prescription preview">
                    <i class="fa fa-times"></i> Close
                </button>
            @endif
        </div>
    </div>

    <div class="inline-prescription-document" data-prescription-print-area>
        @include('prescriptions.document', ['prescription' => $prescription])
    </div>
</section>
