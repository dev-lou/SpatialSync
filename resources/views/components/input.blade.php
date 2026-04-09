<div class="input-group">
    @if($label ?? false)
        <label for="{{ $id ?? $name }}" class="input-label {{ ($required ?? false) ? 'input-label--required' : '' }}">
            {{ $label }}
        </label>
    @endif

    <input
        type="{{ $type ?? 'text' }}"
        id="{{ $id ?? $name }}"
        name="{{ $name }}"
        value="{{ $value ?? old($name) }}"
        placeholder="{{ $placeholder ?? '' }}"
        {{ $required ?? false ? 'required' : '' }}
        {{ $disabled ?? false ? 'disabled' : '' }}
        autocomplete="{{ $autocomplete ?? 'off' }}"
        class="input @error($name) input--error @enderror {{ $class ?? '' }}"
        {{ $attributes->except(['class']) }}
    />

    @error($name)
        <span class="input-error">{{ $message }}</span>
    @enderror

    @if($hint ?? false)
        <span class="text-xs text-tertiary">{{ $hint }}</span>
    @endif
</div>
