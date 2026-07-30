@php
    $type = $type ?? 'text';
    $required = $required ?? false;
    $autocomplete = $autocomplete ?? null;
@endphp
<div class="form-group">
    <label for="{{ $name }}">{{ $label }} @if($required)<span aria-hidden="true">*</span>@endif</label>
    <input id="{{ $name }}" type="{{ $type }}" name="{{ $name }}"
        @if($type !== 'password') value="{{ old($name) }}" @endif
        class="form-control @error($name) is-invalid @enderror"
        @if($required) required @endif
        @if($autocomplete) autocomplete="{{ $autocomplete }}" @endif>
    @error($name)<span class="invalid-feedback">{{ $message }}</span>@enderror
</div>
