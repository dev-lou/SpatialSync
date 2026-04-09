<div class="input-group">
    @if($label ?? false)
        <label for="{{ $id ?? $name }}" class="input-label {{ ($required ?? false) ? 'input-label--required' : '' }}">
            {{ $label }}
        </label>
    @endif

    <textarea
        id="{{ $id ?? $name }}"
        name="{{ $name }}"
        rows="{{ $rows ?? 3 }}"
        placeholder="{{ $placeholder ?? '' }}"
        {{ $required ?? false ? 'required' : '' }}
        {{ $disabled ?? false ? 'disabled' : '' }}
        autocomplete="{{ $autocomplete ?? 'off' }}"
        class="input resize-none @error($name) input--error @enderror {{ $class ?? '' }}"
        {{ $attributes->except(['class']) }}
    >{{ $value ?? old($name) }}</textarea>

    @error($name)
        <span class="input-error">{{ $message }}</span>
    @enderror
</div>
