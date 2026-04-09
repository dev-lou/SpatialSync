---
description: Time-of-day themes, deadline urgency, personalized greetings, and temporal context. Making interfaces aware of when.
---

# Temporal UI — Time-Aware Interfaces

> Time matters. A dashboard at 2am should feel different than at 2pm.
> This file covers time-of-day theming, deadline urgency, personalized
> greetings, and building interfaces that respect temporal context.

---

## CRITICAL RULES

1. **User timezone** — Always use client-side time, not server time.
2. **Privacy** — Don't log/track user's local time without consent.
3. **Override option** — Let users disable time-based features.
4. **Graceful transitions** — Theme changes should be smooth, not jarring.
5. **Accessibility** — Never rely solely on color for urgency.

---

## TIME-OF-DAY THEME

Automatic theme based on local time:

```ts
// temporalTheme.ts
type TimeOfDay = 'night' | 'morning' | 'afternoon' | 'evening';

interface TimeConfig {
  night: { start: number; end: number };      // 22:00 - 06:00
  morning: { start: number; end: number };    // 06:00 - 12:00
  afternoon: { start: number; end: number };  // 12:00 - 18:00
  evening: { start: number; end: number };    // 18:00 - 22:00
}

const defaultConfig: TimeConfig = {
  night: { start: 22, end: 6 },
  morning: { start: 6, end: 12 },
  afternoon: { start: 12, end: 18 },
  evening: { start: 18, end: 22 },
};

export function getTimeOfDay(config = defaultConfig): TimeOfDay {
  const hour = new Date().getHours();
  
  if (hour >= config.night.start || hour < config.night.end) return 'night';
  if (hour >= config.morning.start && hour < config.morning.end) return 'morning';
  if (hour >= config.afternoon.start && hour < config.afternoon.end) return 'afternoon';
  return 'evening';
}

export function applyTemporalTheme(): void {
  // Respect manual override
  if (localStorage.getItem('theme-manual') === 'true') return;
  
  const timeOfDay = getTimeOfDay();
  const systemPrefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
  
  // Combine time + system preference
  let theme: string;
  if (timeOfDay === 'night') {
    theme = 'dark';
  } else if (timeOfDay === 'evening' && systemPrefersDark) {
    theme = 'dark';
  } else if (timeOfDay === 'morning') {
    theme = 'light-warm'; // Slightly warmer morning light
  } else {
    theme = 'light';
  }
  
  document.documentElement.setAttribute('data-theme', theme);
  document.documentElement.setAttribute('data-time-of-day', timeOfDay);
}

// Update every 15 minutes
export function initTemporalTheme(): () => void {
  applyTemporalTheme();
  const interval = setInterval(applyTemporalTheme, 15 * 60 * 1000);
  return () => clearInterval(interval);
}
```

### CSS Variables per Time of Day

```css
/* Base tokens */
:root {
  --greeting-color: var(--text-1);
  --ambient-warmth: 0;
}

/* Morning — warm, energizing */
[data-time-of-day="morning"] {
  --ambient-warmth: 0.05;
  --greeting-color: var(--accent-warm);
}

/* Afternoon — neutral, focused */
[data-time-of-day="afternoon"] {
  --ambient-warmth: 0;
  --greeting-color: var(--text-1);
}

/* Evening — transitional, calming */
[data-time-of-day="evening"] {
  --ambient-warmth: 0.03;
  --greeting-color: var(--accent-soft);
}

/* Night — dark, low contrast */
[data-time-of-day="night"] {
  --ambient-warmth: 0.08;
  --greeting-color: var(--accent-muted);
}

/* Apply subtle warmth filter */
body {
  filter: sepia(var(--ambient-warmth));
}

/* Warmer background for night reading */
[data-theme="dark"][data-time-of-day="night"] {
  --surface-1: hsl(30, 10%, 8%);
  --surface-2: hsl(30, 8%, 12%);
}
```

---

## PERSONALIZED GREETINGS

```tsx
// Greeting.tsx
interface GreetingProps {
  userName?: string;
  showTime?: boolean;
}

function getGreeting(): string {
  const hour = new Date().getHours();
  
  if (hour >= 5 && hour < 12) return 'Good morning';
  if (hour >= 12 && hour < 17) return 'Good afternoon';
  if (hour >= 17 && hour < 21) return 'Good evening';
  return 'Good night';
}

function getContextualMessage(): string {
  const hour = new Date().getHours();
  const day = new Date().getDay();
  
  // Weekend
  if (day === 0 || day === 6) {
    return 'Enjoy your weekend!';
  }
  
  // Late night
  if (hour >= 23 || hour < 5) {
    return 'Working late? Don\'t forget to rest.';
  }
  
  // Early morning
  if (hour >= 5 && hour < 7) {
    return 'You\'re up early!';
  }
  
  // Friday afternoon
  if (day === 5 && hour >= 16) {
    return 'Almost weekend!';
  }
  
  // Monday morning
  if (day === 1 && hour < 12) {
    return 'Fresh start to the week.';
  }
  
  return '';
}

export function Greeting({ userName, showTime = true }: GreetingProps) {
  const [greeting, setGreeting] = useState(getGreeting());
  const [contextMessage, setContextMessage] = useState(getContextualMessage());
  
  useEffect(() => {
    // Update every minute
    const interval = setInterval(() => {
      setGreeting(getGreeting());
      setContextMessage(getContextualMessage());
    }, 60000);
    
    return () => clearInterval(interval);
  }, []);

  return (
    <div className="greeting">
      <h1 className="greeting__text">
        {greeting}{userName ? `, ${userName}` : ''}
      </h1>
      
      {contextMessage && (
        <p className="greeting__context">{contextMessage}</p>
      )}
      
      {showTime && (
        <time className="greeting__time">
          {new Date().toLocaleTimeString([], { 
            hour: '2-digit', 
            minute: '2-digit' 
          })}
        </time>
      )}
    </div>
  );
}
```

---

## DEADLINE URGENCY

Visual urgency that increases as deadline approaches:

```tsx
// DeadlineIndicator.tsx
interface DeadlineProps {
  deadline: Date;
  label: string;
}

type Urgency = 'none' | 'low' | 'medium' | 'high' | 'critical' | 'overdue';

function getUrgency(deadline: Date): Urgency {
  const now = new Date();
  const diff = deadline.getTime() - now.getTime();
  const hours = diff / (1000 * 60 * 60);
  
  if (diff < 0) return 'overdue';
  if (hours < 1) return 'critical';
  if (hours < 24) return 'high';
  if (hours < 72) return 'medium';
  if (hours < 168) return 'low'; // 1 week
  return 'none';
}

function getTimeRemaining(deadline: Date): string {
  const now = new Date();
  const diff = deadline.getTime() - now.getTime();
  
  if (diff < 0) {
    const overdue = Math.abs(diff);
    const hours = Math.floor(overdue / (1000 * 60 * 60));
    return `${hours}h overdue`;
  }
  
  const days = Math.floor(diff / (1000 * 60 * 60 * 24));
  const hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
  
  if (days > 0) return `${days}d ${hours}h left`;
  if (hours > 0) return `${hours}h left`;
  
  const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
  return `${minutes}m left`;
}

export function DeadlineIndicator({ deadline, label }: DeadlineProps) {
  const [urgency, setUrgency] = useState(getUrgency(deadline));
  const [timeRemaining, setTimeRemaining] = useState(getTimeRemaining(deadline));
  
  useEffect(() => {
    const interval = setInterval(() => {
      setUrgency(getUrgency(deadline));
      setTimeRemaining(getTimeRemaining(deadline));
    }, 60000);
    
    return () => clearInterval(interval);
  }, [deadline]);

  return (
    <div 
      className={`deadline deadline--${urgency}`}
      role="status"
      aria-label={`${label}: ${timeRemaining}`}
    >
      <span className="deadline__label">{label}</span>
      <span className="deadline__time">{timeRemaining}</span>
      
      {/* Visual indicator */}
      <span className="deadline__indicator" aria-hidden="true">
        {urgency === 'critical' && '🔴'}
        {urgency === 'high' && '🟠'}
        {urgency === 'medium' && '🟡'}
        {urgency === 'low' && '🟢'}
        {urgency === 'overdue' && '⚫'}
      </span>
    </div>
  );
}
```

### CSS for Urgency Levels

```css
.deadline {
  display: flex;
  align-items: center;
  gap: var(--space-2);
  padding: var(--space-2) var(--space-3);
  border-radius: var(--radius-md);
  font-size: var(--text-sm);
}

.deadline--none {
  background: var(--surface-2);
  color: var(--text-muted);
}

.deadline--low {
  background: var(--success-bg);
  color: var(--success-text);
}

.deadline--medium {
  background: var(--warning-bg);
  color: var(--warning-text);
}

.deadline--high {
  background: var(--error-bg);
  color: var(--error-text);
}

.deadline--critical {
  background: var(--error);
  color: var(--error-contrast);
  animation: pulse-urgent 1s ease-in-out infinite;
}

.deadline--overdue {
  background: var(--surface-invert);
  color: var(--text-invert);
  text-decoration: line-through;
}

@keyframes pulse-urgent {
  0%, 100% { opacity: 1; }
  50% { opacity: 0.7; }
}

@media (prefers-reduced-motion: reduce) {
  .deadline--critical {
    animation: none;
    border: 2px solid currentColor;
  }
}
```

---

## COUNTDOWN TIMER

```tsx
// Countdown.tsx
interface CountdownProps {
  targetDate: Date;
  onComplete?: () => void;
  showDays?: boolean;
}

export function Countdown({ targetDate, onComplete, showDays = true }: CountdownProps) {
  const [remaining, setRemaining] = useState(calculateRemaining(targetDate));
  
  useEffect(() => {
    const interval = setInterval(() => {
      const newRemaining = calculateRemaining(targetDate);
      setRemaining(newRemaining);
      
      if (newRemaining.total <= 0) {
        clearInterval(interval);
        onComplete?.();
      }
    }, 1000);
    
    return () => clearInterval(interval);
  }, [targetDate, onComplete]);

  if (remaining.total <= 0) {
    return <span className="countdown countdown--complete">Now!</span>;
  }

  return (
    <div className="countdown" aria-live="polite" aria-atomic="true">
      {showDays && remaining.days > 0 && (
        <div className="countdown__unit">
          <span className="countdown__value">{remaining.days}</span>
          <span className="countdown__label">days</span>
        </div>
      )}
      <div className="countdown__unit">
        <span className="countdown__value">{pad(remaining.hours)}</span>
        <span className="countdown__label">hrs</span>
      </div>
      <div className="countdown__unit">
        <span className="countdown__value">{pad(remaining.minutes)}</span>
        <span className="countdown__label">min</span>
      </div>
      <div className="countdown__unit">
        <span className="countdown__value">{pad(remaining.seconds)}</span>
        <span className="countdown__label">sec</span>
      </div>
    </div>
  );
}

function calculateRemaining(target: Date) {
  const total = target.getTime() - Date.now();
  return {
    total,
    days: Math.floor(total / (1000 * 60 * 60 * 24)),
    hours: Math.floor((total / (1000 * 60 * 60)) % 24),
    minutes: Math.floor((total / (1000 * 60)) % 60),
    seconds: Math.floor((total / 1000) % 60),
  };
}

function pad(n: number): string {
  return n.toString().padStart(2, '0');
}
```

---

## RELATIVE TIME DISPLAY

```ts
// relativeTime.ts
const rtf = new Intl.RelativeTimeFormat('en', { numeric: 'auto' });

type TimeUnit = 'second' | 'minute' | 'hour' | 'day' | 'week' | 'month' | 'year';

const units: { unit: TimeUnit; ms: number }[] = [
  { unit: 'year', ms: 31536000000 },
  { unit: 'month', ms: 2628000000 },
  { unit: 'week', ms: 604800000 },
  { unit: 'day', ms: 86400000 },
  { unit: 'hour', ms: 3600000 },
  { unit: 'minute', ms: 60000 },
  { unit: 'second', ms: 1000 },
];

export function relativeTime(date: Date | number): string {
  const timestamp = typeof date === 'number' ? date : date.getTime();
  const diff = timestamp - Date.now();
  
  for (const { unit, ms } of units) {
    if (Math.abs(diff) >= ms || unit === 'second') {
      return rtf.format(Math.round(diff / ms), unit);
    }
  }
  
  return 'just now';
}

// Smart display: recent = relative, old = absolute
export function smartTime(date: Date): string {
  const diff = Date.now() - date.getTime();
  const week = 7 * 24 * 60 * 60 * 1000;
  
  if (diff < week) {
    return relativeTime(date);
  }
  
  // Same year: "Mar 15"
  if (date.getFullYear() === new Date().getFullYear()) {
    return date.toLocaleDateString('en', { month: 'short', day: 'numeric' });
  }
  
  // Different year: "Mar 15, 2024"
  return date.toLocaleDateString('en', { 
    month: 'short', 
    day: 'numeric', 
    year: 'numeric' 
  });
}
```

---

## DATE-AWARE CONTENT

Show different content based on date:

```tsx
// DateAwareContent.tsx
interface DateRange {
  start: Date;
  end: Date;
}

interface DateAwareContentProps {
  ranges: {
    range: DateRange;
    content: React.ReactNode;
  }[];
  fallback: React.ReactNode;
}

function isInRange(date: Date, range: DateRange): boolean {
  return date >= range.start && date <= range.end;
}

export function DateAwareContent({ ranges, fallback }: DateAwareContentProps) {
  const now = new Date();
  
  for (const { range, content } of ranges) {
    if (isInRange(now, range)) {
      return <>{content}</>;
    }
  }
  
  return <>{fallback}</>;
}

// Usage: Holiday-specific content
<DateAwareContent
  ranges={[
    {
      range: { 
        start: new Date('2026-12-20'), 
        end: new Date('2026-12-31') 
      },
      content: <HolidayBanner message="Happy Holidays! 🎄" />
    },
    {
      range: { 
        start: new Date('2026-01-01'), 
        end: new Date('2026-01-02') 
      },
      content: <HolidayBanner message="Happy New Year! 🎉" />
    },
  ]}
  fallback={null}
/>
```

---

## WORKING HOURS INDICATOR

Show when support/team is available:

```tsx
// WorkingHours.tsx
interface WorkingHoursConfig {
  timezone: string;
  hours: { start: number; end: number };
  days: number[]; // 0 = Sunday, 6 = Saturday
}

const defaultConfig: WorkingHoursConfig = {
  timezone: 'America/New_York',
  hours: { start: 9, end: 17 },
  days: [1, 2, 3, 4, 5], // Monday-Friday
};

function isWithinWorkingHours(config: WorkingHoursConfig): boolean {
  const now = new Date();
  const formatter = new Intl.DateTimeFormat('en-US', {
    timeZone: config.timezone,
    hour: 'numeric',
    hour12: false,
  });
  
  const hour = parseInt(formatter.format(now), 10);
  const day = now.getDay();
  
  return config.days.includes(day) && 
         hour >= config.hours.start && 
         hour < config.hours.end;
}

export function WorkingHoursIndicator({ config = defaultConfig }: Props) {
  const [isOpen, setIsOpen] = useState(isWithinWorkingHours(config));
  
  useEffect(() => {
    const interval = setInterval(() => {
      setIsOpen(isWithinWorkingHours(config));
    }, 60000);
    
    return () => clearInterval(interval);
  }, [config]);

  return (
    <div className={`working-hours ${isOpen ? 'working-hours--open' : 'working-hours--closed'}`}>
      <span className="working-hours__indicator" aria-hidden="true" />
      <span className="working-hours__text">
        {isOpen ? 'We\'re online' : 'Currently offline'}
      </span>
      {!isOpen && (
        <span className="working-hours__schedule">
          Available Mon-Fri, 9am-5pm EST
        </span>
      )}
    </div>
  );
}
```

---

## CHECKLIST

```
□ Always use client timezone (new Date())
□ User can disable time-based features
□ Theme transitions are smooth (CSS transitions)
□ Urgency has non-color indicators (text, icons)
□ Countdowns are aria-live for screen readers
□ Relative time updates periodically
□ Date ranges account for timezones
□ Working hours show next availability
□ Night mode reduces blue light
□ Greetings feel personal, not creepy
□ No tracking of user's local time
```
