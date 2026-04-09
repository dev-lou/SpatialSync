<div class="input-group" x-data="{ open: false, selected: '{{ $value ?? '' }}' }">
    @if($label ?? false)
        <label class="input-label {{ ($required ?? false) ? 'input-label--required' : '' }}">
            {{ $label }}
        </label>
    @endif

    <div class="relative">
        <button
            type="button"
            class="select-trigger w-full"
            @click="open = !open"
            :aria-expanded="open"
            aria-haspopup="listbox"
        >
            <span x-text="selected || '{{ $placeholder ?? 'Select...' }}'"></span>
            <i data-lucide="chevron-down" class="w-4 h-4 text-tertiary transition-transform" :class="{ 'rotate-180': open }"></i>
        </button>

        <input type="hidden" name="{{ $name }}" :value="selected">

        <ul
            x-show="open"
            x-transition:enter="transition ease-out duration-100"
            x-transition:enter-start="opacity-0 transform -translate-y-2"
            x-transition:enter-end="opacity-100 transform translate-y-0"
            x-transition:leave="transition ease-in duration-75"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            @click.outside="open = false"
            class="select-dropdown w-full mt-1"
            role="listbox"
        >
            @foreach($options as $value => $label)
                <li
                    @click="selected = '{{ is_numeric($value) ? $label : $value }}'; open = false"
                    :class="{ 'select-option--highlighted': selected === '{{ is_numeric($value) ? $label : $value }}' }"
                    class="select-option"
                    role="option"
                    :aria-selected="selected === '{{ is_numeric($value) ? $label : $value }}'"
                >
                    {{ $label }}
                </li>
            @endforeach
        </ul>
    </div>
</div>
