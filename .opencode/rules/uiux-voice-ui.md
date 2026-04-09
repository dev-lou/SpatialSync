---
description: Web Speech API patterns, voice commands, audio feedback, and voice-first accessibility. Making interfaces speakable.
---

# Voice UI & Audio Feedback

> Voice is the most natural interface. This file covers Web Speech API,
> voice commands, spoken feedback, and accessibility for voice-first users.
> Not just "add a microphone button" — proper voice UX.

---

## CRITICAL RULES

1. **Always visual fallback** — Voice is enhancement, not requirement.
2. **Permission before listening** — Never auto-activate microphone.
3. **Clear feedback** — User must know when mic is hot.
4. **Privacy first** — Process locally when possible (no cloud for simple commands).
5. **Respect `prefers-reduced-motion`** — Voice waveform animations optional.

---

## WEB SPEECH API SUPPORT

```js
// Feature detection
const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
const SpeechSynthesis = window.speechSynthesis;

export const voiceSupport = {
  recognition: !!SpeechRecognition,
  synthesis: !!SpeechSynthesis,
  
  // Check if browser is actively blocking
  async checkPermission() {
    try {
      const result = await navigator.permissions.query({ name: 'microphone' });
      return result.state; // 'granted', 'denied', 'prompt'
    } catch {
      return 'unknown';
    }
  }
};
```

---

## VOICE COMMAND SYSTEM

```tsx
// useVoiceCommands.ts
import { useState, useEffect, useCallback, useRef } from 'react';

interface VoiceCommand {
  phrase: string | RegExp;
  action: (transcript: string, match?: RegExpMatchArray) => void;
  description: string; // For help/accessibility
}

interface UseVoiceCommandsOptions {
  commands: VoiceCommand[];
  continuous?: boolean;
  language?: string;
  onError?: (error: string) => void;
  onListeningChange?: (isListening: boolean) => void;
}

export function useVoiceCommands({
  commands,
  continuous = false,
  language = 'en-US',
  onError,
  onListeningChange,
}: UseVoiceCommandsOptions) {
  const [isListening, setIsListening] = useState(false);
  const [transcript, setTranscript] = useState('');
  const [confidence, setConfidence] = useState(0);
  const recognitionRef = useRef<SpeechRecognition | null>(null);

  useEffect(() => {
    const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
    if (!SpeechRecognition) return;

    const recognition = new SpeechRecognition();
    recognition.continuous = continuous;
    recognition.interimResults = true;
    recognition.lang = language;

    recognition.onresult = (event) => {
      const last = event.results[event.results.length - 1];
      const text = last[0].transcript.toLowerCase().trim();
      setTranscript(text);
      setConfidence(last[0].confidence);

      if (last.isFinal) {
        // Match against commands
        for (const cmd of commands) {
          if (typeof cmd.phrase === 'string') {
            if (text.includes(cmd.phrase.toLowerCase())) {
              cmd.action(text);
              break;
            }
          } else {
            const match = text.match(cmd.phrase);
            if (match) {
              cmd.action(text, match);
              break;
            }
          }
        }
      }
    };

    recognition.onerror = (event) => {
      onError?.(event.error);
      setIsListening(false);
    };

    recognition.onend = () => {
      setIsListening(false);
      onListeningChange?.(false);
    };

    recognitionRef.current = recognition;

    return () => {
      recognition.abort();
    };
  }, [commands, continuous, language, onError, onListeningChange]);

  const startListening = useCallback(async () => {
    if (!recognitionRef.current) {
      onError?.('Speech recognition not supported');
      return;
    }

    try {
      // Request microphone permission
      await navigator.mediaDevices.getUserMedia({ audio: true });
      recognitionRef.current.start();
      setIsListening(true);
      onListeningChange?.(true);
    } catch (err) {
      onError?.('Microphone permission denied');
    }
  }, [onError, onListeningChange]);

  const stopListening = useCallback(() => {
    recognitionRef.current?.stop();
    setIsListening(false);
    onListeningChange?.(false);
  }, [onListeningChange]);

  return {
    isListening,
    transcript,
    confidence,
    startListening,
    stopListening,
    isSupported: !!recognitionRef.current,
  };
}
```

### Usage Example

```tsx
// SearchWithVoice.tsx
function SearchWithVoice() {
  const [query, setQuery] = useState('');
  const inputRef = useRef<HTMLInputElement>(null);

  const commands: VoiceCommand[] = [
    {
      phrase: /search for (.+)/,
      action: (_, match) => {
        if (match?.[1]) {
          setQuery(match[1]);
          // Trigger search
        }
      },
      description: 'Say "search for [query]" to search',
    },
    {
      phrase: 'clear',
      action: () => setQuery(''),
      description: 'Say "clear" to clear the search',
    },
    {
      phrase: 'go back',
      action: () => window.history.back(),
      description: 'Say "go back" to navigate back',
    },
  ];

  const { isListening, transcript, startListening, stopListening, isSupported } =
    useVoiceCommands({
      commands,
      onError: (err) => swal.toast('Voice Error', err),
    });

  return (
    <div className="search-voice">
      <input
        ref={inputRef}
        type="search"
        value={query}
        onChange={(e) => setQuery(e.target.value)}
        placeholder="Search or use voice..."
        aria-label="Search"
      />

      {isSupported && (
        <button
          className={`voice-btn ${isListening ? 'voice-btn--active' : ''}`}
          onClick={isListening ? stopListening : startListening}
          aria-label={isListening ? 'Stop listening' : 'Start voice search'}
          aria-pressed={isListening}
        >
          <MicrophoneIcon />
          {isListening && <span className="voice-btn__pulse" aria-hidden="true" />}
        </button>
      )}

      {isListening && (
        <div className="voice-transcript" aria-live="polite">
          {transcript || 'Listening...'}
        </div>
      )}
    </div>
  );
}
```

---

## VOICE FEEDBACK BUTTON STYLES

```css
.voice-btn {
  position: relative;
  width: 48px;
  height: 48px;
  border-radius: 50%;
  background: var(--surface-2);
  border: 2px solid var(--border);
  color: var(--text-1);
  cursor: pointer;
  transition: background-color 0.2s, border-color 0.2s;
}

.voice-btn:hover {
  background: var(--surface-3);
}

.voice-btn:focus-visible {
  outline: 2px solid var(--accent);
  outline-offset: 2px;
}

.voice-btn--active {
  background: var(--accent);
  border-color: var(--accent);
  color: var(--accent-contrast);
}

/* Pulsing ring when listening */
.voice-btn__pulse {
  position: absolute;
  inset: -4px;
  border-radius: 50%;
  border: 2px solid var(--accent);
  animation: voice-pulse 1.5s ease-out infinite;
}

@keyframes voice-pulse {
  0% {
    transform: scale(1);
    opacity: 1;
  }
  100% {
    transform: scale(1.5);
    opacity: 0;
  }
}

@media (prefers-reduced-motion: reduce) {
  .voice-btn__pulse {
    animation: none;
    opacity: 0.5;
  }
}
```

---

## TEXT-TO-SPEECH FEEDBACK

```ts
// speak.ts — Utility for spoken feedback
interface SpeakOptions {
  text: string;
  rate?: number;      // 0.1 to 10, default 1
  pitch?: number;     // 0 to 2, default 1
  volume?: number;    // 0 to 1, default 1
  voice?: string;     // Voice name or lang code
  onEnd?: () => void;
}

export function speak({
  text,
  rate = 1,
  pitch = 1,
  volume = 1,
  voice,
  onEnd,
}: SpeakOptions): SpeechSynthesisUtterance | null {
  if (!window.speechSynthesis) return null;

  // Cancel any ongoing speech
  window.speechSynthesis.cancel();

  const utterance = new SpeechSynthesisUtterance(text);
  utterance.rate = rate;
  utterance.pitch = pitch;
  utterance.volume = volume;

  if (voice) {
    const voices = window.speechSynthesis.getVoices();
    const selectedVoice = voices.find(
      (v) => v.name === voice || v.lang.startsWith(voice)
    );
    if (selectedVoice) utterance.voice = selectedVoice;
  }

  if (onEnd) utterance.onend = onEnd;

  window.speechSynthesis.speak(utterance);
  return utterance;
}

// Convenience methods
export const announce = {
  success: (message: string) => speak({ text: message, pitch: 1.1 }),
  error: (message: string) => speak({ text: message, pitch: 0.9, rate: 0.9 }),
  info: (message: string) => speak({ text: message }),
  
  // For screen reader users — uses aria-live instead of TTS
  live: (message: string, priority: 'polite' | 'assertive' = 'polite') => {
    const el = document.getElementById('aria-live-region') || createLiveRegion();
    el.setAttribute('aria-live', priority);
    el.textContent = message;
    
    // Clear after announcement
    setTimeout(() => { el.textContent = ''; }, 1000);
  },
};

function createLiveRegion(): HTMLElement {
  const el = document.createElement('div');
  el.id = 'aria-live-region';
  el.className = 'sr-only';
  el.setAttribute('aria-live', 'polite');
  el.setAttribute('aria-atomic', 'true');
  document.body.appendChild(el);
  return el;
}
```

---

## VOICE-ENABLED FORM

```tsx
// VoiceForm.tsx — Fill form fields by voice
function VoiceForm() {
  const [formData, setFormData] = useState({
    name: '',
    email: '',
    message: '',
  });
  const [activeField, setActiveField] = useState<string | null>(null);

  const commands: VoiceCommand[] = [
    {
      phrase: /my name is (.+)/,
      action: (_, match) => {
        if (match?.[1]) {
          setFormData((prev) => ({ ...prev, name: match[1] }));
          announce.success('Name saved');
        }
      },
      description: 'Say "my name is [name]"',
    },
    {
      phrase: /email is (.+)/,
      action: (_, match) => {
        if (match?.[1]) {
          // Clean up spoken email
          const email = match[1]
            .replace(' at ', '@')
            .replace(' dot ', '.')
            .replace(/\s/g, '');
          setFormData((prev) => ({ ...prev, email }));
          announce.success('Email saved');
        }
      },
      description: 'Say "email is [email]" (say "at" for @, "dot" for .)',
    },
    {
      phrase: 'submit',
      action: () => {
        // Submit form
        announce.success('Submitting form');
      },
      description: 'Say "submit" to send the form',
    },
    {
      phrase: 'read back',
      action: () => {
        speak({
          text: `Your name is ${formData.name}. Your email is ${formData.email}.`,
        });
      },
      description: 'Say "read back" to hear your entries',
    },
  ];

  const { isListening, startListening, stopListening } = useVoiceCommands({
    commands,
  });

  return (
    <form className="voice-form">
      <div className="voice-form__header">
        <h2>Contact Us</h2>
        <button
          type="button"
          onClick={isListening ? stopListening : startListening}
          className="voice-toggle"
        >
          {isListening ? '🎤 Listening...' : '🎙️ Enable Voice'}
        </button>
      </div>

      {isListening && (
        <div className="voice-form__help" aria-live="polite">
          <p>Voice commands available:</p>
          <ul>
            {commands.map((cmd, i) => (
              <li key={i}>{cmd.description}</li>
            ))}
          </ul>
        </div>
      )}

      <div className="field">
        <input
          type="text"
          id="name"
          value={formData.name}
          onChange={(e) => setFormData((p) => ({ ...p, name: e.target.value }))}
          placeholder=" "
        />
        <label htmlFor="name">Name</label>
      </div>

      <div className="field">
        <input
          type="email"
          id="email"
          value={formData.email}
          onChange={(e) => setFormData((p) => ({ ...p, email: e.target.value }))}
          placeholder=" "
        />
        <label htmlFor="email">Email</label>
      </div>

      <button type="submit" className="btn btn--primary">
        Send Message
      </button>
    </form>
  );
}
```

---

## WAVEFORM VISUALIZER

```tsx
// VoiceWaveform.tsx — Visual feedback while listening
function VoiceWaveform({ isActive }: { isActive: boolean }) {
  const canvasRef = useRef<HTMLCanvasElement>(null);
  const analyserRef = useRef<AnalyserNode | null>(null);
  const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  useEffect(() => {
    if (!isActive || prefersReducedMotion) return;

    const canvas = canvasRef.current;
    if (!canvas) return;

    const ctx = canvas.getContext('2d');
    if (!ctx) return;

    // Get microphone stream
    navigator.mediaDevices.getUserMedia({ audio: true }).then((stream) => {
      const audioContext = new AudioContext();
      const analyser = audioContext.createAnalyser();
      const source = audioContext.createMediaStreamSource(stream);
      source.connect(analyser);
      analyser.fftSize = 256;
      analyserRef.current = analyser;

      const bufferLength = analyser.frequencyBinCount;
      const dataArray = new Uint8Array(bufferLength);

      function draw() {
        if (!isActive) return;
        requestAnimationFrame(draw);

        analyser.getByteFrequencyData(dataArray);

        ctx.fillStyle = 'var(--surface-1)';
        ctx.fillRect(0, 0, canvas.width, canvas.height);

        const barWidth = (canvas.width / bufferLength) * 2.5;
        let x = 0;

        for (let i = 0; i < bufferLength; i++) {
          const barHeight = (dataArray[i] / 255) * canvas.height;

          ctx.fillStyle = `hsl(var(--accent-hue), 70%, ${50 + dataArray[i] / 5}%)`;
          ctx.fillRect(x, canvas.height - barHeight, barWidth, barHeight);

          x += barWidth + 1;
        }
      }

      draw();
    });

    return () => {
      analyserRef.current = null;
    };
  }, [isActive, prefersReducedMotion]);

  if (prefersReducedMotion) {
    return isActive ? <div className="voice-indicator">Listening...</div> : null;
  }

  return (
    <canvas
      ref={canvasRef}
      className="voice-waveform"
      width={200}
      height={60}
      aria-hidden="true"
    />
  );
}
```

---

## ACCESSIBILITY CONSIDERATIONS

```html
<!-- Voice controls should never be the ONLY option -->
<div class="search-container">
  <!-- Primary: keyboard/mouse input -->
  <input type="search" id="search" placeholder="Search..." />
  
  <!-- Secondary: voice enhancement -->
  <button 
    class="voice-btn"
    aria-label="Search by voice"
    aria-describedby="voice-help"
  >
    <svg aria-hidden="true"><!-- mic icon --></svg>
  </button>
  
  <!-- Help text for screen readers -->
  <p id="voice-help" class="sr-only">
    Activates voice search. Speak your search query after the beep.
  </p>
</div>

<!-- Announce state changes -->
<div aria-live="polite" class="sr-only" id="voice-status">
  <!-- JS updates: "Listening", "Processing", "Search complete" -->
</div>
```

---

## CHECKLIST

```
□ Voice is enhancement, not requirement
□ Clear visual indicator when mic is active
□ Permission requested only on user action
□ Fallback for unsupported browsers
□ Transcript shown in real-time
□ Confidence threshold for commands (>0.7)
□ Spoken feedback uses appropriate rate/pitch
□ aria-live regions for screen reader users
□ Waveform respects prefers-reduced-motion
□ Commands documented and discoverable
□ Cancel/stop button always visible when listening
```
