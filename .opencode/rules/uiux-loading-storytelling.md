---
description: Making wait times feel shorter through progress narratives, skeleton theatre, and entertaining loading states. Turning frustration into delight.
---

# Loading State Storytelling

> Users will wait. Make it worth their while. This file covers progress
> narratives, skeleton animations, entertaining loaders, and techniques
> that make loading feel faster — even when it isn't.

---

## CRITICAL RULES

1. **Always show progress** — Unknown waits feel 3x longer than known waits.
2. **Be honest** — Fake progress bars destroy trust when they stall at 99%.
3. **Respect reduced motion** — Animated loaders need static alternatives.
4. **Performance** — Loaders shouldn't be heavier than the content.
5. **Accessible** — Screen readers should announce loading states.

---

## PSYCHOLOGY OF WAITING

| Technique | Effect |
|-----------|--------|
| Show progress | Feels faster |
| Explain what's happening | Builds trust |
| Entertain | Distracts from wait |
| Let users do something | Active waiting feels shorter |
| Show remaining time | Sets expectations |

**Rule**: An 8-second wait with progress feels shorter than a 4-second wait without.

---

## PROGRESS NARRATIVES

Tell users what's happening, step by step:

```tsx
// ProgressNarrative.tsx
interface NarrativeStep {
  message: string;
  duration: number;  // Estimated ms
  icon?: string;
}

const uploadSteps: NarrativeStep[] = [
  { message: 'Preparing your files...', duration: 1000, icon: '📁' },
  { message: 'Encrypting for security...', duration: 2000, icon: '🔐' },
  { message: 'Uploading to cloud...', duration: 5000, icon: '☁️' },
  { message: 'Verifying integrity...', duration: 1500, icon: '✅' },
  { message: 'Almost there...', duration: 500, icon: '🎉' },
];

export function ProgressNarrative({ 
  steps, 
  onComplete 
}: { 
  steps: NarrativeStep[];
  onComplete: () => void;
}) {
  const [currentStep, setCurrentStep] = useState(0);
  const [progress, setProgress] = useState(0);

  useEffect(() => {
    if (currentStep >= steps.length) {
      onComplete();
      return;
    }

    const step = steps[currentStep];
    const interval = 50; // Update every 50ms
    const increment = 100 / (step.duration / interval);

    let stepProgress = 0;
    const timer = setInterval(() => {
      stepProgress += increment;
      
      // Calculate total progress
      const completedProgress = (currentStep / steps.length) * 100;
      const currentProgress = (stepProgress / 100) * (100 / steps.length);
      setProgress(completedProgress + currentProgress);

      if (stepProgress >= 100) {
        clearInterval(timer);
        setCurrentStep(prev => prev + 1);
      }
    }, interval);

    return () => clearInterval(timer);
  }, [currentStep, steps, onComplete]);

  const step = steps[Math.min(currentStep, steps.length - 1)];

  return (
    <div className="progress-narrative" role="status" aria-live="polite">
      <div className="progress-narrative__icon" aria-hidden="true">
        {step.icon}
      </div>
      
      <p className="progress-narrative__message">
        {step.message}
      </p>
      
      <div className="progress-narrative__bar">
        <div 
          className="progress-narrative__fill"
          style={{ width: `${progress}%` }}
          role="progressbar"
          aria-valuenow={Math.round(progress)}
          aria-valuemin={0}
          aria-valuemax={100}
        />
      </div>
      
      <span className="progress-narrative__percent">
        {Math.round(progress)}%
      </span>
    </div>
  );
}
```

---

## FUN LOADING MESSAGES

Rotating messages that entertain:

```tsx
// FunLoader.tsx
const loadingMessages = [
  { text: 'Reticulating splines...', icon: '🔧' },
  { text: 'Convincing electrons to cooperate...', icon: '⚡' },
  { text: 'Bribing the server hamsters...', icon: '🐹' },
  { text: 'Generating witty loading message...', icon: '💭' },
  { text: 'Dividing by zero... wait, that\'s bad', icon: '🧮' },
  { text: 'Warming up the quantum processors...', icon: '❄️' },
  { text: 'Asking ChatGPT for patience tips...', icon: '🤖' },
  { text: 'Untangling the internet cables...', icon: '🔌' },
  { text: 'Teaching AI to make coffee...', icon: '☕' },
  { text: 'Downloading more RAM...', icon: '🐏' },
];

export function FunLoader() {
  const [messageIndex, setMessageIndex] = useState(0);
  const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  useEffect(() => {
    if (prefersReducedMotion) return;

    const interval = setInterval(() => {
      setMessageIndex(prev => (prev + 1) % loadingMessages.length);
    }, 2500);

    return () => clearInterval(interval);
  }, [prefersReducedMotion]);

  const message = loadingMessages[messageIndex];

  return (
    <div className="fun-loader" role="status" aria-live="polite">
      <div className="fun-loader__spinner" aria-hidden="true">
        {message.icon}
      </div>
      <p className="fun-loader__message">{message.text}</p>
    </div>
  );
}
```

### Context-Appropriate Messages

```tsx
// contextualMessages.ts
const messagesByContext = {
  upload: [
    'Beaming your files to the cloud...',
    'Teaching bytes to fly...',
    'Your files are packing their bags...',
  ],
  checkout: [
    'Securing your payment...',
    'Counting the pennies...',
    'Making sure everything adds up...',
  ],
  search: [
    'Searching every corner of the internet...',
    'Asking the database nicely...',
    'Looking under digital rocks...',
  ],
  ai: [
    'Consulting the neural networks...',
    'AI is thinking really hard...',
    'Generating something special...',
  ],
  default: [
    'Loading...',
    'Please wait...',
    'Almost there...',
  ],
};

export function getLoadingMessages(context: keyof typeof messagesByContext = 'default') {
  return messagesByContext[context] || messagesByContext.default;
}
```

---

## SKELETON THEATRE

Animated skeletons that tell a story:

```tsx
// SkeletonTheatre.tsx
export function ArticleSkeleton() {
  return (
    <article className="skeleton-article" aria-busy="true" aria-label="Loading article">
      {/* Hero image loads first */}
      <div className="skeleton skeleton--image skeleton--animate-1" />
      
      {/* Then title */}
      <div className="skeleton skeleton--title skeleton--animate-2" />
      
      {/* Then metadata */}
      <div className="skeleton-row skeleton--animate-3">
        <div className="skeleton skeleton--avatar" />
        <div className="skeleton skeleton--text-short" />
        <div className="skeleton skeleton--text-short" />
      </div>
      
      {/* Then content, line by line */}
      <div className="skeleton skeleton--text skeleton--animate-4" />
      <div className="skeleton skeleton--text skeleton--animate-5" />
      <div className="skeleton skeleton--text-medium skeleton--animate-6" />
    </article>
  );
}
```

```css
/* Staggered skeleton animation */
.skeleton {
  background: linear-gradient(
    90deg,
    var(--surface-2) 0%,
    var(--surface-3) 50%,
    var(--surface-2) 100%
  );
  background-size: 200% 100%;
  animation: skeleton-shimmer 1.5s ease-in-out infinite;
  border-radius: var(--radius-sm);
}

@keyframes skeleton-shimmer {
  0% { background-position: 200% 0; }
  100% { background-position: -200% 0; }
}

/* Staggered delays for "loading" effect */
.skeleton--animate-1 { animation-delay: 0s; }
.skeleton--animate-2 { animation-delay: 0.1s; }
.skeleton--animate-3 { animation-delay: 0.2s; }
.skeleton--animate-4 { animation-delay: 0.3s; }
.skeleton--animate-5 { animation-delay: 0.4s; }
.skeleton--animate-6 { animation-delay: 0.5s; }

/* Skeleton shapes */
.skeleton--image {
  aspect-ratio: 16 / 9;
  width: 100%;
}

.skeleton--title {
  height: 2rem;
  width: 80%;
}

.skeleton--avatar {
  width: 40px;
  height: 40px;
  border-radius: 50%;
}

.skeleton--text {
  height: 1rem;
  width: 100%;
}

.skeleton--text-medium {
  height: 1rem;
  width: 70%;
}

.skeleton--text-short {
  height: 1rem;
  width: 30%;
}

@media (prefers-reduced-motion: reduce) {
  .skeleton {
    animation: none;
    background: var(--surface-2);
  }
}
```

---

## OPTIMISTIC SKELETON REVEAL

Content appears progressively as it loads:

```tsx
// ProgressiveSkeleton.tsx
interface ProgressiveSkeletonProps {
  isLoading: boolean;
  loadedFields: string[];  // Which fields have loaded
  children: React.ReactNode;
}

export function ProgressiveArticle({ 
  data, 
  isLoading, 
  loadedFields 
}: { 
  data: Partial<Article>;
  isLoading: boolean;
  loadedFields: string[];
}) {
  return (
    <article className="progressive-article">
      {/* Image */}
      {loadedFields.includes('image') ? (
        <img src={data.image} alt={data.title || ''} />
      ) : (
        <div className="skeleton skeleton--image" />
      )}

      {/* Title */}
      {loadedFields.includes('title') ? (
        <h1>{data.title}</h1>
      ) : (
        <div className="skeleton skeleton--title" />
      )}

      {/* Author */}
      {loadedFields.includes('author') ? (
        <div className="author">
          <img src={data.author?.avatar} alt="" />
          <span>{data.author?.name}</span>
        </div>
      ) : (
        <div className="skeleton-row">
          <div className="skeleton skeleton--avatar" />
          <div className="skeleton skeleton--text-short" />
        </div>
      )}

      {/* Content */}
      {loadedFields.includes('content') ? (
        <div dangerouslySetInnerHTML={{ __html: data.content || '' }} />
      ) : (
        <div className="skeleton-content">
          {[...Array(5)].map((_, i) => (
            <div key={i} className="skeleton skeleton--text" />
          ))}
        </div>
      )}
    </article>
  );
}
```

---

## LOADING MINI-GAME

Keep users engaged during long waits:

```tsx
// LoadingGame.tsx
function LoadingPongGame({ onComplete }: { onComplete: () => void }) {
  const [score, setScore] = useState(0);
  const targetScore = 5;

  const handleScore = () => {
    const newScore = score + 1;
    setScore(newScore);
    
    if (newScore >= targetScore) {
      onComplete();
    }
  };

  return (
    <div className="loading-game">
      <p className="loading-game__prompt">
        Score {targetScore} points while you wait!
      </p>
      
      {/* Simple click/tap game */}
      <div className="loading-game__area" onClick={handleScore}>
        <div className="loading-game__target" style={{
          left: `${Math.random() * 80}%`,
          top: `${Math.random() * 80}%`,
        }}>
          🎯
        </div>
      </div>
      
      <div className="loading-game__score">
        {score} / {targetScore}
      </div>
    </div>
  );
}

// Wrapper that shows game during loading
function GameWhileLoading({ 
  isLoading, 
  children 
}: { 
  isLoading: boolean; 
  children: React.ReactNode;
}) {
  const [gameComplete, setGameComplete] = useState(false);

  if (isLoading && !gameComplete) {
    return <LoadingPongGame onComplete={() => setGameComplete(true)} />;
  }

  if (isLoading && gameComplete) {
    return (
      <div className="loading-complete">
        <p>🎉 Nice! Still loading, but you're a champion.</p>
        <div className="spinner" />
      </div>
    );
  }

  return <>{children}</>;
}
```

---

## ESTIMATED TIME REMAINING

Honest time estimates:

```tsx
// TimeEstimate.tsx
interface TimeEstimateProps {
  startTime: number;
  progress: number;  // 0 to 100
  minTime?: number;  // Don't show "1 second" for tiny tasks
}

export function TimeEstimate({ 
  startTime, 
  progress, 
  minTime = 3000 
}: TimeEstimateProps) {
  const [estimate, setEstimate] = useState<string | null>(null);

  useEffect(() => {
    if (progress <= 0 || progress >= 100) {
      setEstimate(null);
      return;
    }

    const elapsed = Date.now() - startTime;
    const totalEstimate = (elapsed / progress) * 100;
    const remaining = totalEstimate - elapsed;

    // Don't show for short tasks
    if (totalEstimate < minTime) {
      setEstimate(null);
      return;
    }

    // Format time
    if (remaining < 5000) {
      setEstimate('A few seconds');
    } else if (remaining < 60000) {
      const seconds = Math.ceil(remaining / 1000);
      setEstimate(`About ${seconds} seconds`);
    } else {
      const minutes = Math.ceil(remaining / 60000);
      setEstimate(`About ${minutes} minute${minutes > 1 ? 's' : ''}`);
    }
  }, [startTime, progress, minTime]);

  if (!estimate) return null;

  return (
    <p className="time-estimate" aria-live="polite">
      {estimate} remaining
    </p>
  );
}
```

---

## BLUR-UP IMAGE LOADING

Low-quality preview that sharpens:

```tsx
// BlurUpImage.tsx
interface BlurUpImageProps {
  src: string;
  placeholderSrc: string;  // Tiny version (10-20px wide)
  alt: string;
  width: number;
  height: number;
}

export function BlurUpImage({ 
  src, 
  placeholderSrc, 
  alt, 
  width, 
  height 
}: BlurUpImageProps) {
  const [isLoaded, setIsLoaded] = useState(false);

  return (
    <div 
      className="blur-up-image"
      style={{ aspectRatio: `${width} / ${height}` }}
    >
      {/* Tiny placeholder, heavily blurred */}
      <img
        src={placeholderSrc}
        alt=""
        className="blur-up-image__placeholder"
        aria-hidden="true"
      />
      
      {/* Full image, fades in when loaded */}
      <img
        src={src}
        alt={alt}
        width={width}
        height={height}
        loading="lazy"
        className={`blur-up-image__full ${isLoaded ? 'blur-up-image__full--loaded' : ''}`}
        onLoad={() => setIsLoaded(true)}
      />
    </div>
  );
}
```

```css
.blur-up-image {
  position: relative;
  overflow: hidden;
  background: var(--surface-2);
}

.blur-up-image__placeholder {
  position: absolute;
  inset: 0;
  width: 100%;
  height: 100%;
  object-fit: cover;
  filter: blur(20px);
  transform: scale(1.1); /* Hide blur edges */
}

.blur-up-image__full {
  position: relative;
  width: 100%;
  height: 100%;
  object-fit: cover;
  opacity: 0;
  transition: opacity 0.3s ease;
}

.blur-up-image__full--loaded {
  opacity: 1;
}

@media (prefers-reduced-motion: reduce) {
  .blur-up-image__full {
    transition: none;
  }
}
```

---

## CONTENT-AWARE LOADING

Show relevant previews while loading:

```tsx
// ContentAwareLoader.tsx
interface ContentAwareLoaderProps {
  type: 'article' | 'product' | 'profile' | 'video';
  hint?: {
    title?: string;
    image?: string;
    description?: string;
  };
}

export function ContentAwareLoader({ type, hint }: ContentAwareLoaderProps) {
  return (
    <div className={`content-loader content-loader--${type}`}>
      {/* Show any available hint data */}
      {hint?.image && (
        <BlurUpImage 
          src={hint.image} 
          placeholderSrc={hint.image + '?w=20'} 
          alt="" 
          width={800} 
          height={400} 
        />
      )}
      
      {hint?.title && (
        <h1 className="content-loader__title">{hint.title}</h1>
      )}
      
      {/* Type-specific skeleton */}
      {type === 'article' && <ArticleSkeleton />}
      {type === 'product' && <ProductSkeleton />}
      {type === 'profile' && <ProfileSkeleton />}
      {type === 'video' && <VideoSkeleton />}
      
      <div className="content-loader__status" aria-live="polite">
        Loading {type}...
      </div>
    </div>
  );
}
```

---

## CSS FOR LOADERS

```css
/* Progress bar */
.progress-narrative__bar {
  width: 100%;
  height: 8px;
  background: var(--surface-3);
  border-radius: var(--radius-full);
  overflow: hidden;
}

.progress-narrative__fill {
  height: 100%;
  background: var(--accent);
  transition: width 0.1s ease-out;
}

/* Fun loader */
.fun-loader {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: var(--space-4);
  padding: var(--space-8);
}

.fun-loader__spinner {
  font-size: 3rem;
  animation: bounce 0.6s ease-in-out infinite;
}

@keyframes bounce {
  0%, 100% { transform: translateY(0); }
  50% { transform: translateY(-10px); }
}

.fun-loader__message {
  color: var(--text-muted);
  text-align: center;
  min-height: 1.5em; /* Prevent layout shift */
}

/* Time estimate */
.time-estimate {
  font-size: var(--text-sm);
  color: var(--text-muted);
}

@media (prefers-reduced-motion: reduce) {
  .fun-loader__spinner,
  .progress-narrative__fill {
    animation: none;
    transition: none;
  }
}
```

---

## CHECKLIST

```
□ Progress is shown for any wait > 1 second
□ Estimated time is honest (not fake progress)
□ Skeletons match actual content layout
□ Loading states are announced to screen readers
□ Fun messages are context-appropriate
□ prefers-reduced-motion disables animation
□ Long waits (>10s) offer entertainment/mini-games
□ Partial content shown as it loads (progressive)
□ Error states gracefully replace loading states
□ Cancel/retry options for user control
□ Loaders don't add to page weight significantly
```

---

## ANTI-PATTERNS

```
❌ Fake progress bars that stall at 99%
❌ Spinners with no context ("Loading..." forever)
❌ Loading states that take longer than the actual load
❌ Skeletons that don't match content layout (jarring)
❌ Heavy GIF loaders that slow down the page
❌ No way to cancel long operations
❌ Jokes that aren't funny after the 100th time
❌ Loading games that are mandatory (not skippable)
```
