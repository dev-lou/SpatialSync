<div class="empty-state">
    <div class="empty-state__icon">
        <i data-lucide="{{ $icon ?? 'inbox' }}" class="w-8 h-8"></i>
    </div>
    <h3 class="empty-state__title">{{ $title ?? 'Nothing here yet' }}</h3>
    <p class="empty-state__description">
        {{ $description ?? 'Get started by creating something new.' }}
    </p>
    @if(isset($action))
        {{ $action }}
    @endif
</div>
