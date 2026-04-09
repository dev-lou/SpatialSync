{{-- Video Player Component with Play Overlay --}}
@props([
    'poster' => 'https://images.unsplash.com/photo-1503387762-592deb58ef4e?w=1280&h=720&fit=crop',
    'videoUrl' => null,
    'youtubeId' => null,
    'title' => 'Watch video'
])

<div {{ $attributes->merge(['class' => 'video-player reveal']) }} x-data="{ playing: false }">
    <div class="video-player__wrapper">
        {{-- Poster Image --}}
        <img 
            src="{{ $poster }}" 
            alt="{{ $title }}"
            class="video-player__poster"
            width="1280"
            height="720"
            loading="lazy"
            x-show="!playing"
        >
        
        {{-- Play Button Overlay --}}
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
        
        {{-- Video Embed (YouTube) --}}
        @if($youtubeId)
            <iframe
                x-show="playing"
                x-transition
                class="video-player__iframe"
                :src="playing ? 'https://www.youtube.com/embed/{{ $youtubeId }}?autoplay=1&rel=0' : ''"
                frameborder="0"
                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                allowfullscreen
                title="{{ $title }}"
            ></iframe>
        @elseif($videoUrl)
            <video
                x-show="playing"
                x-ref="video"
                @click="$refs.video.paused ? $refs.video.play() : $refs.video.pause()"
                class="video-player__video"
                controls
                autoplay
            >
                <source src="{{ $videoUrl }}" type="video/mp4">
            </video>
        @endif
    </div>
</div>
