<div class="emr-empty-state">
    <span class="emr-empty-icon"><i class="fa {{ $icon ?? 'fa-folder-open-o' }}"></i></span>
    <strong>{{ $title }}</strong>
    @if (!empty($message))
        <span>{{ $message }}</span>
    @endif
</div>
