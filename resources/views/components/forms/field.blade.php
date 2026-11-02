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
])

<div class="form-field">
    <label for="{{ $id }}">{{ $label }}</label>
    <div class="input-wrap @error($name) input-wrap-error @enderror">
        {{ $icon ?? '' }}
        <input
            id="{{ $id }}"
            name="{{ $name }}"
            type="{{ $type }}"
            placeholder="{{ $placeholder }}"
            autocomplete="{{ $autocomplete }}"
            @if ($maxlength) maxlength="{{ $maxlength }}" @endif
            @if ($value !== null) value="{{ $value }}" @endif
            @if ($required) required @endif
            aria-invalid="{{ $errors->has($name) ? 'true' : 'false' }}"
            aria-describedby="{{ $errors->has($name) ? $id . '-error' : '' }}"
            {{ $attributes }}
        >
        {{ $append ?? '' }}
    </div>
    @error($name)
        <p class="field-error" id="{{ $id }}-error">{{ $message }}</p>
    @enderror
</div>
