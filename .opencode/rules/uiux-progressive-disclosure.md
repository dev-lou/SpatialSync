---
description: Information architecture, complexity hiding, expert mode toggles, and progressive disclosure patterns. Showing the right information at the right time.
---

# Progressive Disclosure

> Users don't need everything at once. This file covers hiding complexity,
> expert mode toggles, expanding details, and information architecture that
> reveals itself progressively — without frustrating power users.

---

## CRITICAL RULES

1. **Essential first** — Show must-have info immediately, nice-to-have on demand.
2. **Predictable revelation** — Users should anticipate what's behind the click.
3. **Preserve context** — Expanding content shouldn't lose the user's place.
4. **Accessible** — All disclosed content reachable via keyboard.
5. **Remember preferences** — Expert mode should persist.

---

## THE PROGRESSIVE DISCLOSURE SPECTRUM

```
[Immediate]     [1 Click]        [2 Clicks]       [Deep]
   |               |                |               |
 Title          Summary           Details        Expert
 Price          Description       Specs          Debug
 CTA            Reviews           Comparisons    Source
```

**Rule**: 80% of users only need the first two levels.

---

## BASIC DISCLOSURE PATTERNS

### Accordion

```tsx
// Accordion.tsx
interface AccordionItem {
  id: string;
  title: string;
  content: React.ReactNode;
  defaultOpen?: boolean;
}

export function Accordion({ 
  items, 
  allowMultiple = false 
}: { 
  items: AccordionItem[]; 
  allowMultiple?: boolean;
}) {
  const [openIds, setOpenIds] = useState<Set<string>>(() => {
    const defaults = items.filter(i => i.defaultOpen).map(i => i.id);
    return new Set(defaults);
  });

  const toggle = (id: string) => {
    setOpenIds(prev => {
      const next = new Set(allowMultiple ? prev : []);
      if (prev.has(id)) {
        next.delete(id);
      } else {
        next.add(id);
      }
      return next;
    });
  };

  return (
    <div className="accordion">
      {items.map(item => (
        <div 
          key={item.id} 
          className={`accordion__item ${openIds.has(item.id) ? 'accordion__item--open' : ''}`}
        >
          <button
            className="accordion__trigger"
            onClick={() => toggle(item.id)}
            aria-expanded={openIds.has(item.id)}
            aria-controls={`accordion-panel-${item.id}`}
          >
            <span>{item.title}</span>
            <ChevronIcon className="accordion__icon" aria-hidden="true" />
          </button>
          
          <div
            id={`accordion-panel-${item.id}`}
            className="accordion__panel"
            role="region"
            aria-labelledby={`accordion-trigger-${item.id}`}
            hidden={!openIds.has(item.id)}
          >
            <div className="accordion__content">
              {item.content}
            </div>
          </div>
        </div>
      ))}
    </div>
  );
}
```

### Show More / Less

```tsx
// ShowMore.tsx
interface ShowMoreProps {
  children: React.ReactNode;
  maxHeight?: number;  // px
  fadeHeight?: number; // px for gradient
}

export function ShowMore({ 
  children, 
  maxHeight = 200, 
  fadeHeight = 60 
}: ShowMoreProps) {
  const contentRef = useRef<HTMLDivElement>(null);
  const [isExpanded, setIsExpanded] = useState(false);
  const [needsExpansion, setNeedsExpansion] = useState(false);

  useEffect(() => {
    if (contentRef.current) {
      setNeedsExpansion(contentRef.current.scrollHeight > maxHeight);
    }
  }, [children, maxHeight]);

  return (
    <div className="show-more">
      <div
        ref={contentRef}
        className="show-more__content"
        style={{
          maxHeight: isExpanded ? 'none' : maxHeight,
          overflow: isExpanded ? 'visible' : 'hidden',
        }}
      >
        {children}
        
        {!isExpanded && needsExpansion && (
          <div 
            className="show-more__fade" 
            style={{ height: fadeHeight }}
            aria-hidden="true"
          />
        )}
      </div>
      
      {needsExpansion && (
        <button
          className="show-more__toggle"
          onClick={() => setIsExpanded(!isExpanded)}
          aria-expanded={isExpanded}
        >
          {isExpanded ? 'Show less' : 'Show more'}
        </button>
      )}
    </div>
  );
}
```

---

## EXPERT MODE TOGGLE

Let power users see more:

```tsx
// ExpertMode.tsx
const ExpertModeContext = createContext<{
  isExpert: boolean;
  setExpert: (value: boolean) => void;
}>({ isExpert: false, setExpert: () => {} });

export function ExpertModeProvider({ children }: { children: React.ReactNode }) {
  const [isExpert, setIsExpert] = useState(() => {
    return localStorage.getItem('expert-mode') === 'true';
  });

  const setExpert = (value: boolean) => {
    setIsExpert(value);
    localStorage.setItem('expert-mode', String(value));
  };

  return (
    <ExpertModeContext.Provider value={{ isExpert, setExpert }}>
      {children}
    </ExpertModeContext.Provider>
  );
}

export function useExpertMode() {
  return useContext(ExpertModeContext);
}

// Toggle component
export function ExpertModeToggle() {
  const { isExpert, setExpert } = useExpertMode();

  return (
    <label className="expert-toggle">
      <input
        type="checkbox"
        checked={isExpert}
        onChange={(e) => setExpert(e.target.checked)}
      />
      <span className="expert-toggle__label">
        Expert Mode
        <span className="expert-toggle__hint">
          Show advanced options and technical details
        </span>
      </span>
    </label>
  );
}

// Conditional rendering
export function ExpertOnly({ children }: { children: React.ReactNode }) {
  const { isExpert } = useExpertMode();
  return isExpert ? <>{children}</> : null;
}

// Usage
function ProductSettings() {
  return (
    <div className="settings">
      <BasicSettings />
      
      <ExpertOnly>
        <AdvancedSettings />
        <DebugPanel />
        <APIAccess />
      </ExpertOnly>
      
      <ExpertModeToggle />
    </div>
  );
}
```

---

## STEPPED FORMS

Reveal form sections progressively:

```tsx
// SteppedForm.tsx
interface Step {
  id: string;
  title: string;
  description?: string;
  fields: React.ReactNode;
  isOptional?: boolean;
}

export function SteppedForm({ 
  steps, 
  onSubmit 
}: { 
  steps: Step[]; 
  onSubmit: (data: any) => void;
}) {
  const [currentStep, setCurrentStep] = useState(0);
  const [completedSteps, setCompletedSteps] = useState<Set<number>>(new Set());

  const canProceed = (stepIndex: number) => {
    // Validate current step before proceeding
    return true; // Replace with actual validation
  };

  const goToStep = (index: number) => {
    if (index < currentStep || completedSteps.has(index - 1)) {
      setCurrentStep(index);
    }
  };

  const nextStep = () => {
    if (canProceed(currentStep)) {
      setCompletedSteps(prev => new Set([...prev, currentStep]));
      setCurrentStep(prev => Math.min(prev + 1, steps.length - 1));
    }
  };

  return (
    <form className="stepped-form" onSubmit={(e) => { e.preventDefault(); onSubmit({}); }}>
      {/* Progress indicator */}
      <nav className="stepped-form__progress" aria-label="Form progress">
        <ol>
          {steps.map((step, index) => (
            <li 
              key={step.id}
              className={`
                stepped-form__step
                ${index === currentStep ? 'stepped-form__step--active' : ''}
                ${completedSteps.has(index) ? 'stepped-form__step--completed' : ''}
              `}
            >
              <button
                type="button"
                onClick={() => goToStep(index)}
                disabled={index > currentStep && !completedSteps.has(index - 1)}
                aria-current={index === currentStep ? 'step' : undefined}
              >
                <span className="stepped-form__step-number">{index + 1}</span>
                <span className="stepped-form__step-title">{step.title}</span>
              </button>
            </li>
          ))}
        </ol>
      </nav>

      {/* Current step content */}
      <div className="stepped-form__content">
        <h2>{steps[currentStep].title}</h2>
        {steps[currentStep].description && (
          <p className="stepped-form__description">
            {steps[currentStep].description}
          </p>
        )}
        
        {steps[currentStep].fields}
      </div>

      {/* Navigation */}
      <div className="stepped-form__actions">
        {currentStep > 0 && (
          <button 
            type="button" 
            className="btn btn--secondary"
            onClick={() => setCurrentStep(prev => prev - 1)}
          >
            Back
          </button>
        )}
        
        {currentStep < steps.length - 1 ? (
          <button 
            type="button" 
            className="btn btn--primary"
            onClick={nextStep}
          >
            Continue
          </button>
        ) : (
          <button type="submit" className="btn btn--primary">
            Submit
          </button>
        )}
      </div>
    </form>
  );
}
```

---

## DETAILS ON HOVER/FOCUS

Quick disclosure without commitment:

```tsx
// HoverDetails.tsx
export function HoverDetails({ 
  summary, 
  details 
}: { 
  summary: React.ReactNode; 
  details: React.ReactNode;
}) {
  const [isOpen, setIsOpen] = useState(false);

  return (
    <div 
      className="hover-details"
      onMouseEnter={() => setIsOpen(true)}
      onMouseLeave={() => setIsOpen(false)}
      onFocus={() => setIsOpen(true)}
      onBlur={() => setIsOpen(false)}
    >
      <div className="hover-details__summary" tabIndex={0}>
        {summary}
        <InfoIcon className="hover-details__icon" aria-hidden="true" />
      </div>
      
      {isOpen && (
        <div 
          className="hover-details__panel" 
          role="tooltip"
        >
          {details}
        </div>
      )}
    </div>
  );
}

// For touch devices, use click instead
export function TouchDetails({ summary, details }: Props) {
  const [isOpen, setIsOpen] = useState(false);

  return (
    <div className="touch-details">
      <button
        className="touch-details__trigger"
        onClick={() => setIsOpen(!isOpen)}
        aria-expanded={isOpen}
      >
        {summary}
        <ChevronIcon className={`touch-details__icon ${isOpen ? 'rotated' : ''}`} />
      </button>
      
      {isOpen && (
        <div className="touch-details__content">
          {details}
        </div>
      )}
    </div>
  );
}
```

---

## PROGRESSIVE TABLE COLUMNS

Show essential columns, reveal more on demand:

```tsx
// ProgressiveTable.tsx
interface Column<T> {
  key: keyof T;
  header: string;
  priority: 'essential' | 'important' | 'optional';
  render?: (value: T[keyof T], row: T) => React.ReactNode;
}

export function ProgressiveTable<T extends { id: string }>({ 
  data, 
  columns,
  defaultLevel = 'important',
}: { 
  data: T[]; 
  columns: Column<T>[];
  defaultLevel?: 'essential' | 'important' | 'optional';
}) {
  const [detailLevel, setDetailLevel] = useState(defaultLevel);

  const visibleColumns = columns.filter(col => {
    if (detailLevel === 'optional') return true;
    if (detailLevel === 'important') return col.priority !== 'optional';
    return col.priority === 'essential';
  });

  return (
    <div className="progressive-table">
      <div className="progressive-table__controls">
        <label>
          Detail level:
          <select 
            value={detailLevel} 
            onChange={(e) => setDetailLevel(e.target.value as any)}
          >
            <option value="essential">Essential only</option>
            <option value="important">Standard</option>
            <option value="optional">All details</option>
          </select>
        </label>
      </div>
      
      <table>
        <thead>
          <tr>
            {visibleColumns.map(col => (
              <th key={String(col.key)}>{col.header}</th>
            ))}
          </tr>
        </thead>
        <tbody>
          {data.map(row => (
            <tr key={row.id}>
              {visibleColumns.map(col => (
                <td key={String(col.key)}>
                  {col.render 
                    ? col.render(row[col.key], row)
                    : String(row[col.key])
                  }
                </td>
              ))}
            </tr>
          ))}
        </tbody>
      </table>
    </div>
  );
}
```

---

## REVEAL ON SCROLL

Disclose content as user scrolls:

```tsx
// ScrollReveal.tsx
export function ScrollReveal({ 
  children,
  threshold = 0.2,
  delay = 0,
}: {
  children: React.ReactNode;
  threshold?: number;
  delay?: number;
}) {
  const ref = useRef<HTMLDivElement>(null);
  const [isVisible, setIsVisible] = useState(false);

  useEffect(() => {
    // Respect reduced motion
    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
      setIsVisible(true);
      return;
    }

    const observer = new IntersectionObserver(
      ([entry]) => {
        if (entry.isIntersecting) {
          setTimeout(() => setIsVisible(true), delay);
          observer.disconnect();
        }
      },
      { threshold }
    );

    if (ref.current) {
      observer.observe(ref.current);
    }

    return () => observer.disconnect();
  }, [threshold, delay]);

  return (
    <div 
      ref={ref}
      className={`scroll-reveal ${isVisible ? 'scroll-reveal--visible' : ''}`}
      style={{ transitionDelay: `${delay}ms` }}
    >
      {children}
    </div>
  );
}
```

```css
.scroll-reveal {
  opacity: 0;
  transform: translateY(20px);
  transition: opacity 0.5s ease, transform 0.5s ease;
}

.scroll-reveal--visible {
  opacity: 1;
  transform: translateY(0);
}

@media (prefers-reduced-motion: reduce) {
  .scroll-reveal {
    opacity: 1;
    transform: none;
    transition: none;
  }
}
```

---

## COMPLEXITY LEVELS

Different UI for different users:

```tsx
// ComplexityLevel.tsx
type ComplexityLevel = 'beginner' | 'intermediate' | 'advanced';

const ComplexityContext = createContext<{
  level: ComplexityLevel;
  setLevel: (level: ComplexityLevel) => void;
}>({ level: 'intermediate', setLevel: () => {} });

export function useComplexity() {
  return useContext(ComplexityContext);
}

// Conditional content based on complexity
export function ForLevel({ 
  min, 
  max,
  children 
}: { 
  min?: ComplexityLevel;
  max?: ComplexityLevel;
  children: React.ReactNode;
}) {
  const { level } = useComplexity();
  const levels: ComplexityLevel[] = ['beginner', 'intermediate', 'advanced'];
  const currentIndex = levels.indexOf(level);
  const minIndex = min ? levels.indexOf(min) : 0;
  const maxIndex = max ? levels.indexOf(max) : levels.length - 1;

  if (currentIndex >= minIndex && currentIndex <= maxIndex) {
    return <>{children}</>;
  }
  return null;
}

// Usage
function Dashboard() {
  return (
    <div>
      {/* Always shown */}
      <MetricsOverview />
      
      {/* Intermediate and above */}
      <ForLevel min="intermediate">
        <DetailedCharts />
      </ForLevel>
      
      {/* Advanced only */}
      <ForLevel min="advanced">
        <RawDataTable />
        <DebugConsole />
        <APIPlayground />
      </ForLevel>
      
      {/* Beginner only - extra guidance */}
      <ForLevel max="beginner">
        <OnboardingTips />
        <GuidedTour />
      </ForLevel>
    </div>
  );
}
```

---

## CSS PATTERNS

```css
/* Accordion */
.accordion__panel {
  display: grid;
  grid-template-rows: 0fr;
  transition: grid-template-rows 0.3s ease;
}

.accordion__item--open .accordion__panel {
  grid-template-rows: 1fr;
}

.accordion__content {
  overflow: hidden;
}

.accordion__icon {
  transition: transform 0.2s ease;
}

.accordion__item--open .accordion__icon {
  transform: rotate(180deg);
}

/* Show more fade */
.show-more__fade {
  position: absolute;
  bottom: 0;
  left: 0;
  right: 0;
  background: linear-gradient(transparent, var(--surface-1));
  pointer-events: none;
}

/* Expert mode indicator */
.expert-only {
  position: relative;
}

.expert-only::before {
  content: 'Advanced';
  position: absolute;
  top: 0;
  right: 0;
  padding: var(--space-1) var(--space-2);
  background: var(--accent);
  color: var(--accent-contrast);
  font-size: var(--text-xs);
  border-radius: var(--radius-sm);
}

@media (prefers-reduced-motion: reduce) {
  .accordion__panel,
  .accordion__icon {
    transition: none;
  }
}
```

---

## CHECKLIST

```
□ Essential content visible without interaction
□ Disclosure controls have clear affordances (icons, underlines)
□ Expanded state persists where appropriate
□ Expert mode saves to localStorage
□ Accordion uses aria-expanded and aria-controls
□ Show more measures content height dynamically
□ Forms show progress indicator for steps
□ Touch devices use tap, not hover
□ Scroll reveal respects prefers-reduced-motion
□ Complexity levels are user-configurable
□ No critical info hidden behind optional disclosure
```

---

## ANTI-PATTERNS

```
❌ Hiding essential info (price, availability) behind clicks
❌ Infinite nested accordions
❌ Expert mode that can't be turned off
❌ Form steps that can't go back
❌ Hover-only disclosure (fails on touch)
❌ Auto-collapsing content while user is reading
❌ Disclosure that requires scrolling to see content
❌ No visual indicator that more content exists
```
