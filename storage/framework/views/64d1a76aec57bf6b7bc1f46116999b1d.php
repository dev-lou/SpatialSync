
<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'poster' => 'https://images.unsplash.com/photo-1503387762-592deb58ef4e?w=1280&h=720&fit=crop',
    'videoUrl' => null,
    'youtubeId' => null,
    'title' => 'Watch video'
]));

foreach ($attributes->all() as $__key => $__value) {
    if (in_array($__key, $__propNames)) {
        $$__key = $$__key ?? $__value;
    } else {
        $__newAttributes[$__key] = $__value;
    }
}

$attributes = new \Illuminate\View\ComponentAttributeBag($__newAttributes);

unset($__propNames);
unset($__newAttributes);

foreach (array_filter(([
    'poster' => 'https://images.unsplash.com/photo-1503387762-592deb58ef4e?w=1280&h=720&fit=crop',
    'videoUrl' => null,
    'youtubeId' => null,
    'title' => 'Watch video'
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars); ?>

<div <?php echo e($attributes->merge(['class' => 'video-player reveal'])); ?> x-data="{ playing: false }">
    <div class="video-player__wrapper">
        
        <img 
            src="<?php echo e($poster); ?>" 
            alt="<?php echo e($title); ?>"
            class="video-player__poster"
            width="1280"
            height="720"
            loading="lazy"
            x-show="!playing"
        >
        
        
        <button 
            class="video-player__play"
            @click="playing = true"
            x-show="!playing"
            aria-label="Play video"
        >
            <span class="video-player__play-icon">
                <svg viewBox="0 0 24 24" fill="currentColor">
                    <path d="M8 5v14l11-7z"/>
                </svg>
            </span>
            <span class="video-player__play-ring"></span>
        </button>
        
        
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($youtubeId): ?>
            <iframe
                x-show="playing"
                x-transition
                class="video-player__iframe"
                :src="playing ? 'https://www.youtube.com/embed/<?php echo e($youtubeId); ?>?autoplay=1&rel=0' : ''"
                frameborder="0"
                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                allowfullscreen
                title="<?php echo e($title); ?>"
            ></iframe>
        <?php elseif($videoUrl): ?>
            <video
                x-show="playing"
                x-ref="video"
                @click="$refs.video.paused ? $refs.video.play() : $refs.video.pause()"
                class="video-player__video"
                controls
                autoplay
            >
                <source src="<?php echo e($videoUrl); ?>" type="video/mp4">
            </video>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>
</div>
<?php /**PATH C:\xampp\htdocs\flow\resources\views/components/video-player.blade.php ENDPATH**/ ?>