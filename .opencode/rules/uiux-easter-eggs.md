---
description: Konami codes, hidden features, delightful surprises, and rewarding exploration. Making interfaces that reward curiosity.
---

# Easter Eggs & Delightful Surprises

> The best interfaces reward curiosity. This file covers hidden features,
> secret interactions, achievement unlocks, and delightful surprises that
> make users smile — without being annoying or inaccessible.

---

## CRITICAL RULES

1. **Never hide essential features** — Easter eggs are bonuses, not requirements.
2. **Accessible alternatives** — If a secret is useful, provide another path to it.
3. **Don't break flow** — Surprises should delight, not disrupt.
4. **Respect context** — No easter eggs in serious/medical/legal contexts.
5. **Performance** — Hidden features shouldn't load until triggered.

---

## KONAMI CODE DETECTOR

The classic: ↑ ↑ ↓ ↓ ← → ← → B A

```tsx
// useKonamiCode.ts
const KONAMI_CODE = [
  'ArrowUp', 'ArrowUp',
  'ArrowDown', 'ArrowDown',
  'ArrowLeft', 'ArrowRight',
  'ArrowLeft', 'ArrowRight',
  'KeyB', 'KeyA',
];

export function useKonamiCode(callback: () => void) {
  const [index, setIndex] = useState(0);

  useEffect(() => {
    const handleKeyDown = (e: KeyboardEvent) => {
      const expectedKey = KONAMI_CODE[index];
      
      if (e.code === expectedKey) {
        const nextIndex = index + 1;
        
        if (nextIndex === KONAMI_CODE.length) {
          callback();
          setIndex(0);
        } else {
          setIndex(nextIndex);
        }
      } else {
        setIndex(0);
      }
    };

    window.addEventListener('keydown', handleKeyDown);
    return () => window.removeEventListener('keydown', handleKeyDown);
  }, [index, callback]);
}

// Usage
function App() {
  const [partyMode, setPartyMode] = useState(false);

  useKonamiCode(() => {
    setPartyMode(true);
    swal.toast('🎉 Party mode activated!');
  });

  return (
    <div className={partyMode ? 'party-mode' : ''}>
      {/* ... */}
    </div>
  );
}
```

---

## CUSTOM SECRET CODES

Create your own activation sequences:

```tsx
// useSecretCode.ts
interface SecretCodeOptions {
  sequence: string[];      // Keys to detect
  timeout?: number;        // Reset after X ms of inactivity
  onActivate: () => void;
  onProgress?: (progress: number) => void;
}

export function useSecretCode({
  sequence,
  timeout = 2000,
  onActivate,
  onProgress,
}: SecretCodeOptions) {
  const [index, setIndex] = useState(0);
  const timeoutRef = useRef<number>();

  useEffect(() => {
    const handleKeyDown = (e: KeyboardEvent) => {
      // Clear previous timeout
      if (timeoutRef.current) {
        clearTimeout(timeoutRef.current);
      }

      const expectedKey = sequence[index];
      
      if (e.code === expectedKey || e.key.toLowerCase() === expectedKey.toLowerCase()) {
        const nextIndex = index + 1;
        onProgress?.(nextIndex / sequence.length);
        
        if (nextIndex === sequence.length) {
          onActivate();
          setIndex(0);
        } else {
          setIndex(nextIndex);
          
          // Reset after timeout
          timeoutRef.current = window.setTimeout(() => {
            setIndex(0);
            onProgress?.(0);
          }, timeout);
        }
      } else if (e.key.length === 1) {
        // Wrong key - reset (but ignore modifier keys)
        setIndex(0);
        onProgress?.(0);
      }
    };

    window.addEventListener('keydown', handleKeyDown);
    return () => {
      window.removeEventListener('keydown', handleKeyDown);
      if (timeoutRef.current) clearTimeout(timeoutRef.current);
    };
  }, [sequence, index, timeout, onActivate, onProgress]);

  return { progress: index / sequence.length };
}

// Usage: Type "debug" to open debug panel
function App() {
  const [showDebug, setShowDebug] = useState(false);

  useSecretCode({
    sequence: ['d', 'e', 'b', 'u', 'g'],
    onActivate: () => setShowDebug(true),
  });

  return (
    <>
      {/* ... */}
      {showDebug && <DebugPanel onClose={() => setShowDebug(false)} />}
    </>
  );
}
```

---

## CLICK COUNTER EASTER EGG

Multiple clicks on logo reveals something:

```tsx
// ClickCounter.tsx
interface ClickCounterProps {
  target: number;        // Clicks needed
  timeout?: number;      // Reset after X ms
  onReach: () => void;
  children: React.ReactNode;
}

export function ClickCounter({
  target,
  timeout = 500,
  onReach,
  children,
}: ClickCounterProps) {
  const [count, setCount] = useState(0);
  const timeoutRef = useRef<number>();

  const handleClick = () => {
    if (timeoutRef.current) {
      clearTimeout(timeoutRef.current);
    }

    const newCount = count + 1;
    
    if (newCount >= target) {
      onReach();
      setCount(0);
    } else {
      setCount(newCount);
      
      timeoutRef.current = window.setTimeout(() => {
        setCount(0);
      }, timeout);
    }
  };

  return (
    <div onClick={handleClick} style={{ cursor: 'pointer' }}>
      {children}
    </div>
  );
}

// Usage: Click logo 7 times to see credits
function Header() {
  const [showCredits, setShowCredits] = useState(false);

  return (
    <header>
      <ClickCounter target={7} onReach={() => setShowCredits(true)}>
        <Logo />
      </ClickCounter>
      
      {showCredits && <CreditsModal onClose={() => setShowCredits(false)} />}
    </header>
  );
}
```

---

## GESTURE EASTER EGGS

For mobile — swipe patterns, shake, etc:

```tsx
// useShakeToUnlock.ts
export function useShakeToUnlock(callback: () => void, threshold = 25) {
  useEffect(() => {
    let lastX = 0, lastY = 0, lastZ = 0;
    let shakeCount = 0;
    let lastShake = 0;

    const handleMotion = (e: DeviceMotionEvent) => {
      const { x, y, z } = e.accelerationIncludingGravity || {};
      if (x === null || y === null || z === null) return;

      const deltaX = Math.abs(x - lastX);
      const deltaY = Math.abs(y - lastY);
      const deltaZ = Math.abs(z - lastZ);

      if (deltaX + deltaY + deltaZ > threshold) {
        const now = Date.now();
        
        if (now - lastShake > 100) {
          shakeCount++;
          lastShake = now;
          
          if (shakeCount >= 3) {
            callback();
            shakeCount = 0;
          }
        }
      }

      lastX = x;
      lastY = y;
      lastZ = z;
    };

    // Request permission on iOS
    if (typeof DeviceMotionEvent !== 'undefined' && 
        typeof (DeviceMotionEvent as any).requestPermission === 'function') {
      // Need user gesture to request permission
      // This hook just sets up the listener if permission granted
    }

    window.addEventListener('devicemotion', handleMotion);
    return () => window.removeEventListener('devicemotion', handleMotion);
  }, [callback, threshold]);
}

// Usage
function App() {
  useShakeToUnlock(() => {
    swal.toast('🤫 You found a secret!');
    unlockFeature('shake-secret');
  });
}
```

---

## CONSOLE EASTER EGGS

Messages for developers who open DevTools:

```ts
// consoleEasterEgg.ts
export function initConsoleEasterEggs() {
  // ASCII art welcome
  console.log(`
%c
    ╔═══════════════════════════════════════╗
    ║                                       ║
    ║   👋 Hey there, curious developer!    ║
    ║                                       ║
    ║   We're hiring! Check out:            ║
    ║   yoursite.com/careers                ║
    ║                                       ║
    ╚═══════════════════════════════════════╝
  `, 'color: #00ff00; font-family: monospace;');

  // Warning for non-developers
  console.log(
    '%c⚠️ Warning!',
    'color: red; font-size: 24px; font-weight: bold;'
  );
  console.log(
    '%cIf someone told you to paste something here, it\'s likely a scam.',
    'color: red; font-size: 14px;'
  );

  // Secret function
  (window as any).secretFeature = () => {
    console.log('%c🎉 You found it! Use code DEVFRIEND for 20% off.', 'color: gold; font-size: 16px;');
  };
}

// Call on app init
if (typeof window !== 'undefined') {
  initConsoleEasterEggs();
}
```

---

## ACHIEVEMENT SYSTEM

Track and reward exploration:

```tsx
// achievements.ts
interface Achievement {
  id: string;
  title: string;
  description: string;
  icon: string;
  secret?: boolean;  // Don't show in list until unlocked
}

const ACHIEVEMENTS: Achievement[] = [
  {
    id: 'first-visit',
    title: 'Welcome!',
    description: 'Thanks for visiting',
    icon: '👋',
  },
  {
    id: 'night-owl',
    title: 'Night Owl',
    description: 'Browsing after midnight',
    icon: '🦉',
    secret: true,
  },
  {
    id: 'konami',
    title: 'Old School',
    description: 'Entered the Konami code',
    icon: '🎮',
    secret: true,
  },
  {
    id: 'explorer',
    title: 'Explorer',
    description: 'Visited every page',
    icon: '🗺️',
  },
  {
    id: 'speed-demon',
    title: 'Speed Demon',
    description: 'Completed checkout in under 30 seconds',
    icon: '⚡',
    secret: true,
  },
];

class AchievementManager {
  private unlocked: Set<string>;
  private listeners: Set<(achievement: Achievement) => void> = new Set();

  constructor() {
    const saved = localStorage.getItem('achievements');
    this.unlocked = new Set(saved ? JSON.parse(saved) : []);
  }

  unlock(id: string): boolean {
    if (this.unlocked.has(id)) return false;

    const achievement = ACHIEVEMENTS.find(a => a.id === id);
    if (!achievement) return false;

    this.unlocked.add(id);
    localStorage.setItem('achievements', JSON.stringify([...this.unlocked]));

    // Notify listeners
    this.listeners.forEach(fn => fn(achievement));
    
    return true;
  }

  isUnlocked(id: string): boolean {
    return this.unlocked.has(id);
  }

  getAll(): (Achievement & { unlocked: boolean })[] {
    return ACHIEVEMENTS
      .filter(a => !a.secret || this.unlocked.has(a.id))
      .map(a => ({ ...a, unlocked: this.unlocked.has(a.id) }));
  }

  onUnlock(fn: (achievement: Achievement) => void): () => void {
    this.listeners.add(fn);
    return () => this.listeners.delete(fn);
  }
}

export const achievements = new AchievementManager();

// React hook
export function useAchievements() {
  const [all, setAll] = useState(achievements.getAll());

  useEffect(() => {
    return achievements.onUnlock(() => {
      setAll(achievements.getAll());
    });
  }, []);

  return {
    achievements: all,
    unlock: achievements.unlock.bind(achievements),
    isUnlocked: achievements.isUnlocked.bind(achievements),
  };
}
```

### Achievement Toast

```tsx
// AchievementToast.tsx
function AchievementToast({ achievement }: { achievement: Achievement }) {
  return (
    <div className="achievement-toast" role="status" aria-live="polite">
      <span className="achievement-toast__icon">{achievement.icon}</span>
      <div className="achievement-toast__content">
        <strong>Achievement Unlocked!</strong>
        <span>{achievement.title}</span>
      </div>
    </div>
  );
}

// Provider that shows toasts
function AchievementProvider({ children }: { children: React.ReactNode }) {
  const [toast, setToast] = useState<Achievement | null>(null);

  useEffect(() => {
    return achievements.onUnlock((achievement) => {
      setToast(achievement);
      setTimeout(() => setToast(null), 4000);
    });
  }, []);

  return (
    <>
      {children}
      {toast && <AchievementToast achievement={toast} />}
    </>
  );
}
```

---

## CONTEXTUAL SURPRISES

Based on user behavior:

```tsx
// surprises.ts
export function initContextualSurprises() {
  // Night owl - browsing after midnight
  const hour = new Date().getHours();
  if (hour >= 0 && hour < 5) {
    achievements.unlock('night-owl');
  }

  // Long-time user
  const firstVisit = localStorage.getItem('first-visit');
  if (firstVisit) {
    const daysSince = (Date.now() - parseInt(firstVisit)) / (1000 * 60 * 60 * 24);
    if (daysSince >= 365) {
      achievements.unlock('veteran');
    }
  } else {
    localStorage.setItem('first-visit', String(Date.now()));
    achievements.unlock('first-visit');
  }

  // Birthday easter egg (if user shared birthday)
  const birthday = localStorage.getItem('user-birthday');
  if (birthday) {
    const today = new Date();
    const bday = new Date(birthday);
    if (today.getMonth() === bday.getMonth() && today.getDate() === bday.getDate()) {
      document.body.classList.add('birthday-mode');
      swal.toast('🎂 Happy Birthday!');
    }
  }
}
```

---

## HIDDEN PAGE / DEV TOOLS

```tsx
// DevTools.tsx
// Access via typing "devtools" anywhere on the page

function DevToolsPanel() {
  const [isOpen, setIsOpen] = useState(false);

  useSecretCode({
    sequence: ['d', 'e', 'v', 't', 'o', 'o', 'l', 's'],
    onActivate: () => setIsOpen(true),
  });

  if (!isOpen) return null;

  return (
    <div className="dev-tools-panel">
      <header>
        <h2>🛠️ Dev Tools</h2>
        <button onClick={() => setIsOpen(false)}>×</button>
      </header>
      
      <section>
        <h3>Feature Flags</h3>
        <FeatureFlagToggles />
      </section>
      
      <section>
        <h3>Theme Preview</h3>
        <ThemeSwitcher />
      </section>
      
      <section>
        <h3>Performance</h3>
        <PerformanceStats />
      </section>
      
      <section>
        <h3>Clear Data</h3>
        <button onClick={() => localStorage.clear()}>Clear localStorage</button>
        <button onClick={() => sessionStorage.clear()}>Clear sessionStorage</button>
      </section>
    </div>
  );
}
```

---

## MINI-GAMES

Hidden games for 404 pages, loading states, etc:

```tsx
// DinoGame.tsx (simplified concept)
function DinoGame() {
  const [isPlaying, setIsPlaying] = useState(false);
  const [score, setScore] = useState(0);

  // Simple jump game logic...
  
  return (
    <div className="dino-game" tabIndex={0}>
      <p className="dino-game__hint">Press Space to start</p>
      
      <canvas 
        width={600} 
        height={150}
        aria-label="Dinosaur jumping game. Press space to jump over obstacles."
      />
      
      <div className="dino-game__score">
        Score: {score}
      </div>
    </div>
  );
}

// Usage on 404 page
function NotFoundPage() {
  return (
    <div className="not-found">
      <h1>404 — Page Not Found</h1>
      <p>While you're here, want to play a game?</p>
      <DinoGame />
      <a href="/">Go Home</a>
    </div>
  );
}
```

---

## CSS EASTER EGGS

```css
/* Rainbow mode (activated by class) */
.party-mode {
  animation: rainbow-bg 3s linear infinite;
}

@keyframes rainbow-bg {
  0% { filter: hue-rotate(0deg); }
  100% { filter: hue-rotate(360deg); }
}

/* Birthday confetti */
.birthday-mode::before {
  content: '';
  position: fixed;
  inset: 0;
  pointer-events: none;
  background: url('/confetti.gif') center/cover;
  opacity: 0.3;
  z-index: 9999;
}

/* Developer mode indicator */
.dev-mode::after {
  content: 'DEV';
  position: fixed;
  bottom: 8px;
  right: 8px;
  padding: 4px 8px;
  background: #ff0;
  color: #000;
  font-size: 10px;
  font-weight: bold;
  border-radius: 4px;
  z-index: 99999;
}

@media (prefers-reduced-motion: reduce) {
  .party-mode,
  .birthday-mode::before {
    animation: none;
  }
}
```

---

## CHECKLIST

```
□ Easter eggs are non-essential (never hide features users need)
□ Accessible alternative exists for anything useful
□ Easter eggs lazy-load (not in main bundle)
□ Console messages include hiring/security warnings
□ Achievements persist in localStorage
□ No easter eggs in serious contexts (medical, legal, finance)
□ Touch/mobile alternatives for keyboard secrets
□ prefers-reduced-motion respected
□ Can be disabled if annoying
□ Internal analytics don't track secret usage (privacy)
□ Fun, not frustrating
```

---

## INSPIRATION

| App | Easter Egg |
|-----|------------|
| Google | "Do a barrel roll" search |
| Slack | Custom loading messages |
| GitHub | 404 parallax game |
| Discord | Discordo sound |
| VS Code | Type "happy" in settings |
| Chrome | Dinosaur game offline |
| Spotify | Star Wars lightsaber progress bar |
