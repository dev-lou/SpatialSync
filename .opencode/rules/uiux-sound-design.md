---
description: UI sound design, scroll-linked audio, data sonification, and audio feedback patterns. Making interfaces audible.
---

# Sound Design for Web Interfaces

> Sound is the forgotten dimension of UX. This file covers UI audio feedback,
> scroll-linked sounds, data sonification, and how to make your interface
> *feel* alive through careful audio design — without being annoying.

---

## CRITICAL RULES

1. **Off by default** — Sound must be opt-in. Auto-playing audio is hostile.
2. **Respect system settings** — Check `prefers-reduced-motion` (often correlates with audio preference).
3. **Short and subtle** — UI sounds are 50-200ms. Never jarring.
4. **Volume control** — Always provide a way to adjust or mute.
5. **Accessibility** — Sound is enhancement, never the only feedback.
6. **Context-aware** — No sounds in serious/professional contexts unless expected.

---

## AUDIO CONTEXT SETUP

```ts
// audioManager.ts — Singleton for managing UI sounds

class AudioManager {
  private context: AudioContext | null = null;
  private gainNode: GainNode | null = null;
  private sounds: Map<string, AudioBuffer> = new Map();
  private enabled: boolean;
  private volume: number;

  constructor() {
    this.enabled = localStorage.getItem('ui-sounds') !== 'false';
    this.volume = parseFloat(localStorage.getItem('ui-sounds-volume') || '0.5');
  }

  private async getContext(): Promise<AudioContext> {
    if (!this.context) {
      this.context = new AudioContext();
      this.gainNode = this.context.createGain();
      this.gainNode.gain.value = this.volume;
      this.gainNode.connect(this.context.destination);
    }

    // Resume if suspended (browser autoplay policy)
    if (this.context.state === 'suspended') {
      await this.context.resume();
    }

    return this.context;
  }

  async loadSound(name: string, url: string): Promise<void> {
    try {
      const response = await fetch(url);
      const arrayBuffer = await response.arrayBuffer();
      const context = await this.getContext();
      const audioBuffer = await context.decodeAudioData(arrayBuffer);
      this.sounds.set(name, audioBuffer);
    } catch (error) {
      console.warn(`Failed to load sound: ${name}`, error);
    }
  }

  async play(name: string, options: { volume?: number; playbackRate?: number } = {}): Promise<void> {
    if (!this.enabled) return;

    const buffer = this.sounds.get(name);
    if (!buffer) {
      console.warn(`Sound not found: ${name}`);
      return;
    }

    const context = await this.getContext();
    const source = context.createBufferSource();
    source.buffer = buffer;
    source.playbackRate.value = options.playbackRate ?? 1;

    // Per-sound volume adjustment
    if (options.volume !== undefined) {
      const volumeNode = context.createGain();
      volumeNode.gain.value = options.volume;
      source.connect(volumeNode);
      volumeNode.connect(this.gainNode!);
    } else {
      source.connect(this.gainNode!);
    }

    source.start(0);
  }

  setVolume(value: number): void {
    this.volume = Math.max(0, Math.min(1, value));
    localStorage.setItem('ui-sounds-volume', String(this.volume));
    if (this.gainNode) {
      this.gainNode.gain.value = this.volume;
    }
  }

  setEnabled(enabled: boolean): void {
    this.enabled = enabled;
    localStorage.setItem('ui-sounds', String(enabled));
  }

  isEnabled(): boolean {
    return this.enabled;
  }
}

export const audio = new AudioManager();
```

---

## PRELOADING SOUNDS

```ts
// Load on user's first interaction (not page load)
async function initializeSounds() {
  await Promise.all([
    audio.loadSound('tap', '/sounds/tap.mp3'),
    audio.loadSound('success', '/sounds/success.mp3'),
    audio.loadSound('error', '/sounds/error.mp3'),
    audio.loadSound('notification', '/sounds/notification.mp3'),
    audio.loadSound('swoosh', '/sounds/swoosh.mp3'),
    audio.loadSound('click', '/sounds/click.mp3'),
    audio.loadSound('toggle-on', '/sounds/toggle-on.mp3'),
    audio.loadSound('toggle-off', '/sounds/toggle-off.mp3'),
    audio.loadSound('type', '/sounds/type.mp3'),
  ]);
}

// Trigger on first interaction
document.addEventListener('click', function initOnce() {
  initializeSounds();
  document.removeEventListener('click', initOnce);
}, { once: true });
```

---

## UI SOUND LIBRARY

```ts
// uiSounds.ts — Pre-defined sound effects

export const uiSounds = {
  // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
  // BUTTON INTERACTIONS
  // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
  
  /** Light click — buttons, links */
  click: () => audio.play('click', { volume: 0.3 }),
  
  /** Toggle on — switch enabled */
  toggleOn: () => audio.play('toggle-on', { volume: 0.4 }),
  
  /** Toggle off — switch disabled */
  toggleOff: () => audio.play('toggle-off', { volume: 0.3 }),
  
  // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
  // FEEDBACK
  // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
  
  /** Success — form submitted, action complete */
  success: () => audio.play('success', { volume: 0.5 }),
  
  /** Error — validation failed */
  error: () => audio.play('error', { volume: 0.4 }),
  
  /** Notification — new message */
  notification: () => audio.play('notification', { volume: 0.5 }),
  
  // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
  // NAVIGATION
  // ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
  
  /** Swoosh — page transition, modal open */
  swoosh: () => audio.play('swoosh', { volume: 0.3 }),
  
  /** Type — keyboard input (use sparingly!) */
  type: () => audio.play('type', { volume: 0.1, playbackRate: 0.9 + Math.random() * 0.2 }),
};
```

---

## REACT HOOKS

```tsx
// useUISound.ts
import { useCallback } from 'react';
import { uiSounds, audio } from './uiSounds';

export function useUISound() {
  const play = useCallback((sound: keyof typeof uiSounds) => {
    if (audio.isEnabled()) {
      uiSounds[sound]();
    }
  }, []);

  return { play, isEnabled: audio.isEnabled() };
}

// SoundButton.tsx
function SoundButton({ sound = 'click', onClick, children, ...props }) {
  const { play } = useUISound();

  const handleClick = (e) => {
    play(sound);
    onClick?.(e);
  };

  return (
    <button onClick={handleClick} {...props}>
      {children}
    </button>
  );
}
```

---

## SCROLL-LINKED AUDIO

Create ambient soundscapes that respond to scroll:

```tsx
// ScrollSoundscape.tsx
function ScrollSoundscape({ 
  audioSrc, 
  startScroll = 0, 
  endScroll = 1 
}: Props) {
  const audioRef = useRef<HTMLAudioElement | null>(null);
  const [isEnabled, setIsEnabled] = useState(false);

  useEffect(() => {
    if (!isEnabled) return;

    const audio = audioRef.current;
    if (!audio) return;

    const handleScroll = () => {
      const scrollY = window.scrollY;
      const maxScroll = document.documentElement.scrollHeight - window.innerHeight;
      const progress = scrollY / maxScroll;

      // Map scroll to volume
      if (progress >= startScroll && progress <= endScroll) {
        const normalizedProgress = (progress - startScroll) / (endScroll - startScroll);
        audio.volume = Math.sin(normalizedProgress * Math.PI) * 0.3; // Bell curve
        
        if (audio.paused) audio.play();
      } else {
        audio.volume = 0;
      }
    };

    window.addEventListener('scroll', handleScroll, { passive: true });
    return () => window.removeEventListener('scroll', handleScroll);
  }, [isEnabled, startScroll, endScroll]);

  return (
    <>
      <audio ref={audioRef} src={audioSrc} loop preload="auto" />
      
      {/* User must enable sounds */}
      <button 
        className="sound-toggle"
        onClick={() => setIsEnabled(!isEnabled)}
        aria-pressed={isEnabled}
      >
        {isEnabled ? '🔊' : '🔇'} Sound
      </button>
    </>
  );
}
```

---

## DATA SONIFICATION

Turn data into sound — useful for dashboards, real-time monitoring:

```tsx
// DataSonification.tsx
function DataSonification({ 
  value, 
  min = 0, 
  max = 100, 
  enabled = false 
}: Props) {
  const contextRef = useRef<AudioContext | null>(null);
  const oscillatorRef = useRef<OscillatorNode | null>(null);

  useEffect(() => {
    if (!enabled) return;

    const context = new AudioContext();
    const oscillator = context.createOscillator();
    const gainNode = context.createGain();

    oscillator.type = 'sine';
    gainNode.gain.value = 0.1;

    oscillator.connect(gainNode);
    gainNode.connect(context.destination);
    oscillator.start();

    contextRef.current = context;
    oscillatorRef.current = oscillator;

    return () => {
      oscillator.stop();
      context.close();
    };
  }, [enabled]);

  useEffect(() => {
    if (!oscillatorRef.current) return;

    // Map value to frequency (200Hz - 800Hz)
    const normalized = (value - min) / (max - min);
    const frequency = 200 + normalized * 600;
    oscillatorRef.current.frequency.setValueAtTime(frequency, 0);
  }, [value, min, max]);

  return null; // No visual output
}

// Usage: Real-time stock price
function StockTicker({ symbol }: { symbol: string }) {
  const { price, change } = useStockPrice(symbol);
  const [soundEnabled, setSoundEnabled] = useState(false);

  return (
    <div className="stock-ticker">
      <span className="stock-ticker__price">${price}</span>
      
      <DataSonification 
        value={price} 
        min={minPrice} 
        max={maxPrice} 
        enabled={soundEnabled} 
      />
      
      <button onClick={() => setSoundEnabled(!soundEnabled)}>
        {soundEnabled ? '🔊' : '🔇'}
      </button>
    </div>
  );
}
```

---

## PROGRESS SOUND

Audio feedback for progress (loading, uploads):

```ts
// progressSound.ts
class ProgressSound {
  private context: AudioContext;
  private oscillator: OscillatorNode;
  private gain: GainNode;

  constructor() {
    this.context = new AudioContext();
    this.oscillator = this.context.createOscillator();
    this.gain = this.context.createGain();

    this.oscillator.type = 'sine';
    this.oscillator.frequency.value = 220;
    this.gain.gain.value = 0;

    this.oscillator.connect(this.gain);
    this.gain.connect(this.context.destination);
    this.oscillator.start();
  }

  updateProgress(progress: number) {
    // Progress 0-100
    const frequency = 220 + (progress * 4.4); // 220Hz to 660Hz
    const volume = progress < 100 ? 0.1 : 0;

    this.oscillator.frequency.linearRampToValueAtTime(
      frequency,
      this.context.currentTime + 0.1
    );
    this.gain.gain.linearRampToValueAtTime(
      volume,
      this.context.currentTime + 0.1
    );
  }

  complete() {
    // Play completion sound
    const now = this.context.currentTime;
    
    // Ascending notes
    this.oscillator.frequency.setValueAtTime(440, now);
    this.gain.gain.setValueAtTime(0.15, now);
    
    this.oscillator.frequency.setValueAtTime(554.37, now + 0.1);
    this.oscillator.frequency.setValueAtTime(659.25, now + 0.2);
    
    // Fade out
    this.gain.gain.linearRampToValueAtTime(0, now + 0.4);
  }

  destroy() {
    this.oscillator.stop();
    this.context.close();
  }
}
```

---

## SOUND SETTINGS UI

```tsx
// SoundSettings.tsx
function SoundSettings() {
  const [enabled, setEnabled] = useState(() => audio.isEnabled());
  const [volume, setVolume] = useState(0.5);

  const handleVolumeChange = (value: number) => {
    setVolume(value);
    audio.setVolume(value);
    uiSounds.click(); // Preview
  };

  const handleToggle = (value: boolean) => {
    setEnabled(value);
    audio.setEnabled(value);
    if (value) uiSounds.toggleOn();
  };

  return (
    <div className="settings-section">
      <h3>Sound Settings</h3>
      
      <div className="settings-item">
        <label htmlFor="sound-toggle">UI Sounds</label>
        <Toggle
          id="sound-toggle"
          checked={enabled}
          onChange={handleToggle}
        />
      </div>

      {enabled && (
        <div className="settings-item">
          <label htmlFor="sound-volume">Volume</label>
          <input
            type="range"
            id="sound-volume"
            min="0"
            max="1"
            step="0.1"
            value={volume}
            onChange={(e) => handleVolumeChange(parseFloat(e.target.value))}
          />
          <span>{Math.round(volume * 100)}%</span>
        </div>
      )}

      <p className="text-muted text-sm">
        UI sounds provide audio feedback for interactions. They are always optional.
      </p>
    </div>
  );
}
```

---

## NOTIFICATION SOUNDS

```tsx
// NotificationSound.tsx
function useNotificationSound() {
  const playNotification = useCallback((type: 'message' | 'mention' | 'alert') => {
    if (!audio.isEnabled()) return;

    switch (type) {
      case 'message':
        audio.play('notification', { volume: 0.3 });
        break;
      case 'mention':
        audio.play('notification', { volume: 0.5, playbackRate: 1.2 });
        break;
      case 'alert':
        audio.play('notification', { volume: 0.7, playbackRate: 0.8 });
        break;
    }
  }, []);

  return playNotification;
}

// In your notification handler
function NotificationHandler() {
  const playSound = useNotificationSound();

  useEffect(() => {
    const unsubscribe = notificationService.subscribe((notification) => {
      // Show visual notification
      showToast(notification);
      
      // Play appropriate sound
      if (notification.type === 'direct_message') {
        playSound('message');
      } else if (notification.mentioned) {
        playSound('mention');
      }
    });

    return unsubscribe;
  }, [playSound]);

  return null;
}
```

---

## SOUND DESIGN PRINCIPLES

### Frequency Guidelines
| Interaction | Frequency Range | Emotion |
|-------------|----------------|---------|
| Success | 400-600 Hz | Bright, positive |
| Error | 200-300 Hz | Serious, attention |
| Click | 800-1200 Hz | Crisp, responsive |
| Notification | 500-700 Hz | Alert but friendly |
| Toggle | 300-500 Hz | Mechanical, satisfying |

### Duration Guidelines
| Type | Duration | Notes |
|------|----------|-------|
| Click | 20-50ms | Instant |
| Toggle | 50-100ms | Snappy |
| Success | 150-300ms | Satisfying |
| Error | 200-400ms | Attention-grabbing |
| Notification | 300-500ms | Noticeable |

---

## GENERATING UI SOUNDS PROGRAMMATICALLY

No audio files needed — synthesize on the fly:

```ts
// synthSounds.ts
function createClick(): void {
  const context = new AudioContext();
  const oscillator = context.createOscillator();
  const gain = context.createGain();

  oscillator.type = 'sine';
  oscillator.frequency.setValueAtTime(1000, context.currentTime);
  oscillator.frequency.exponentialRampToValueAtTime(100, context.currentTime + 0.05);

  gain.gain.setValueAtTime(0.3, context.currentTime);
  gain.gain.exponentialRampToValueAtTime(0.01, context.currentTime + 0.05);

  oscillator.connect(gain);
  gain.connect(context.destination);

  oscillator.start();
  oscillator.stop(context.currentTime + 0.05);
}

function createSuccess(): void {
  const context = new AudioContext();
  const now = context.currentTime;

  // Three ascending notes
  [440, 554.37, 659.25].forEach((freq, i) => {
    const osc = context.createOscillator();
    const gain = context.createGain();

    osc.type = 'sine';
    osc.frequency.value = freq;

    gain.gain.setValueAtTime(0, now + i * 0.1);
    gain.gain.linearRampToValueAtTime(0.2, now + i * 0.1 + 0.02);
    gain.gain.linearRampToValueAtTime(0, now + i * 0.1 + 0.15);

    osc.connect(gain);
    gain.connect(context.destination);

    osc.start(now + i * 0.1);
    osc.stop(now + i * 0.1 + 0.15);
  });
}
```

---

## CHECKLIST

```
□ Sound is OFF by default — user must enable
□ Volume control available
□ Mute button easily accessible
□ Sounds are short (50-300ms)
□ Sounds loaded on first interaction, not page load
□ Falls back gracefully if AudioContext blocked
□ No sounds for routine navigation (page loads, etc.)
□ Sounds complement visual feedback, not replace
□ Tested with screen readers (sounds don't interfere)
□ Consider context (no sounds in serious/medical/legal apps)
□ prefers-reduced-motion users may prefer no sound
```

---

## DON'T BE THIS DEVELOPER

```
❌ Auto-playing background music
❌ Sound on every single click
❌ Sounds that can't be muted
❌ Loud, jarring error sounds
❌ Sounds for page navigation
❌ Notification sounds that play even when muted system-wide
❌ Sounds longer than 500ms for UI feedback
❌ Different sounds for every button (pick a consistent set)
```
