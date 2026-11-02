@props([
    'id',
    'name',
    'label',
    'type' => 'text',
    'placeholder' => '',
    'autocomplete' => 'off',
    'maxlength' => null,
    'value' => null,
    'required' => true,
    'hint' => null,
    'idPrefix' => '',
])

<div class="form-field">
    <label for="{{ $idPrefix . $id }}">{{ $label }}</label>
    <div class="input-wrap @error($name) input-wrap-error @enderror">
        {{ $icon ?? '' }}
        <input
            id="{{ $idPrefix . $id }}"
            name="{{ $name }}"
            type="{{ $type }}"
            placeholder="{{ $placeholder }}"
            autocomplete="{{ $autocomplete }}"
            @if ($maxlength) maxlength="{{ $maxlength }}" @endif
            @if ($value !== null) value="{{ $value }}" @endif
            @if ($required) required @endif
            aria-invalid="{{ $errors->has($name) ? 'true' : 'false' }}"
            aria-describedby="{{ trim(($hint ? $idPrefix . $id . '-hint ' : '') . ($errors->has($name) ? $idPrefix . $id . '-error' : '')) }}"
            {{ $attributes }}
        >
        {{ $append ?? '' }}
    </div>
    @if ($hint)
        <p class="field-hint" id="{{ $idPrefix . $id }}-hint">{{ $hint }}</p>
    @endif
    @error($name)
        <p class="field-error" id="{{ $idPrefix . $id }}-error">{{ $message }}</p>
    @enderror
</div>
