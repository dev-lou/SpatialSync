---
description: Simulate color blindness (protanopia, deuteranopia, tritanopia) via SVG filters. Provides fixes including icons, patterns, and line styles for charts.
---

# UI/UX Color Blindness Simulation & Fixes

> Simulate color vision deficiencies and implement accessible alternatives.
> Ensures UI works for all users regardless of color perception.

---

## OVERVIEW

Approximately 8% of men and 0.5% of women have some form of color vision deficiency. This skill covers:
1. SVG filter simulation
2. Testing tools and workflows
3. Accessible color palettes
4. Non-color indicators (icons, patterns, shapes)
5. Chart accessibility

---

## COLOR BLINDNESS TYPES

| Type | Affected | Population | What's Hard to See |
|------|----------|------------|-------------------|
| **Protanopia** | Red cones | ~1% men | Red-green distinction |
| **Deuteranopia** | Green cones | ~6% men | Red-green distinction |
| **Tritanopia** | Blue cones | <1% all | Blue-yellow distinction |
| **Achromatopsia** | All cones | ~0.003% | All color (grayscale) |

---

## SVG FILTER SIMULATION

### CSS Filters (Quick Test)

```css
/* Apply to body or container for testing */
.simulate-protanopia {
  filter: url('#protanopia-filter');
}

.simulate-deuteranopia {
  filter: url('#deuteranopia-filter');
}

.simulate-tritanopia {
  filter: url('#tritanopia-filter');
}

.simulate-achromatopsia {
  filter: grayscale(100%);
}
```

### SVG Filter Definitions

Add this SVG to your HTML (can be hidden):

```html
<svg class="sr-only" aria-hidden="true">
  <defs>
    <!-- Protanopia (Red-blind) -->
    <filter id="protanopia-filter">
      <feColorMatrix type="matrix" values="
        0.567, 0.433, 0,     0, 0
        0.558, 0.442, 0,     0, 0
        0,     0.242, 0.758, 0, 0
        0,     0,     0,     1, 0
      "/>
    </filter>
    
    <!-- Deuteranopia (Green-blind) -->
    <filter id="deuteranopia-filter">
      <feColorMatrix type="matrix" values="
        0.625, 0.375, 0,   0, 0
        0.7,   0.3,   0,   0, 0
        0,     0.3,   0.7, 0, 0
        0,     0,     0,   1, 0
      "/>
    </filter>
    
    <!-- Tritanopia (Blue-blind) -->
    <filter id="tritanopia-filter">
      <feColorMatrix type="matrix" values="
        0.95, 0.05,  0,     0, 0
        0,    0.433, 0.567, 0, 0
        0,    0.475, 0.525, 0, 0
        0,    0,     0,     1, 0
      "/>
    </filter>
    
    <!-- Achromatopsia (Total color blindness) -->
    <filter id="achromatopsia-filter">
      <feColorMatrix type="matrix" values="
        0.299, 0.587, 0.114, 0, 0
        0.299, 0.587, 0.114, 0, 0
        0.299, 0.587, 0.114, 0, 0
        0,     0,     0,     1, 0
      "/>
    </filter>
  </defs>
</svg>
```

### Toggle Simulation Component

```tsx
import { useState } from 'react';
import { Eye, EyeOff } from 'lucide-react';

type SimulationType = 'none' | 'protanopia' | 'deuteranopia' | 'tritanopia' | 'achromatopsia';

export function ColorBlindnessSimulator() {
  const [simulation, setSimulation] = useState<SimulationType>('none');
  
  const handleChange = (type: SimulationType) => {
    setSimulation(type);
    
    // Apply filter to document
    const body = document.body;
    body.classList.remove(
      'simulate-protanopia',
      'simulate-deuteranopia',
      'simulate-tritanopia',
      'simulate-achromatopsia'
    );
    
    if (type !== 'none') {
      body.classList.add(`simulate-${type}`);
    }
  };
  
  return (
    <div className="cvd-simulator" role="group" aria-label="Color blindness simulation">
      <label className="cvd-simulator__label">
        <Eye size={16} />
        Simulate color blindness:
      </label>
      
      <select 
        className="cvd-simulator__select"
        value={simulation}
        onChange={(e) => handleChange(e.target.value as SimulationType)}
      >
        <option value="none">Normal vision</option>
        <option value="protanopia">Protanopia (red-blind)</option>
        <option value="deuteranopia">Deuteranopia (green-blind)</option>
        <option value="tritanopia">Tritanopia (blue-blind)</option>
        <option value="achromatopsia">Achromatopsia (no color)</option>
      </select>
    </div>
  );
}
```

---

## ACCESSIBLE COLOR PALETTES

### ✅ Safe Color Combinations

Colors that are distinguishable across most CVD types:

```css
:root {
  /* Primary distinguishable palette */
  --cvd-blue: #0066CC;      /* Safe for all */
  --cvd-orange: #E65100;    /* Distinct from blue */
  --cvd-purple: #7B1FA2;    /* Distinct from both */
  --cvd-teal: #00695C;      /* Good contrast */
  --cvd-brown: #5D4037;     /* Distinct */
  --cvd-pink: #C2185B;      /* Visible to most */
  
  /* Status colors (with backup indicators) */
  --status-success: #1B5E20;
  --status-warning: #E65100;
  --status-error: #B71C1C;
  --status-info: #0066CC;
}
```

### ❌ Avoid These Combinations

| Combination | Problem | Fix |
|-------------|---------|-----|
| Red + Green | Indistinguishable for protanopia/deuteranopia | Use blue + orange |
| Green + Brown | Similar for deuteranopia | Add pattern or icon |
| Blue + Purple | Can be confused | Add shape indicator |
| Red + Orange | Similar for protanopia | Use different values |

---

## NON-COLOR INDICATORS

### Rule: Never rely on color alone

Every color-coded element must have a secondary indicator:

### 1. Icons

```html
<!-- ❌ Bad: Color only -->
<span style="color: red">Error</span>
<span style="color: green">Success</span>

<!-- ✅ Good: Color + Icon -->
<span class="status status--error">
  <i data-lucide="x-circle" aria-hidden="true"></i>
  Error
</span>

<span class="status status--success">
  <i data-lucide="check-circle" aria-hidden="true"></i>
  Success
</span>
```

### 2. Patterns (CSS)

```css
/* Pattern fills for charts/data viz */
.pattern-diagonal {
  background: repeating-linear-gradient(
    45deg,
    currentColor,
    currentColor 2px,
    transparent 2px,
    transparent 8px
  );
}

.pattern-dots {
  background: radial-gradient(circle, currentColor 2px, transparent 2px);
  background-size: 8px 8px;
}

.pattern-horizontal {
  background: repeating-linear-gradient(
    0deg,
    currentColor,
    currentColor 2px,
    transparent 2px,
    transparent 6px
  );
}

.pattern-crosshatch {
  background: 
    repeating-linear-gradient(
      45deg,
      currentColor,
      currentColor 1px,
      transparent 1px,
      transparent 5px
    ),
    repeating-linear-gradient(
      -45deg,
      currentColor,
      currentColor 1px,
      transparent 1px,
      transparent 5px
    );
}
```

### 3. Shapes

```tsx
// Different shapes for data points
const SHAPES = {
  circle: (props) => <circle cx="0" cy="0" r="4" {...props} />,
  square: (props) => <rect x="-4" y="-4" width="8" height="8" {...props} />,
  triangle: (props) => <polygon points="0,-5 4.33,2.5 -4.33,2.5" {...props} />,
  diamond: (props) => <polygon points="0,-5 5,0 0,5 -5,0" {...props} />,
  cross: (props) => (
    <path d="M-4,0 L4,0 M0,-4 L0,4" strokeWidth="2" {...props} />
  ),
};
```

### 4. Text Labels

```html
<!-- Always include text alternative -->
<div class="legend">
  <span class="legend-item" style="--color: var(--cvd-blue)">
    <span class="legend-swatch"></span>
    <span class="legend-label">Category A (42%)</span>
  </span>
  <span class="legend-item" style="--color: var(--cvd-orange)">
    <span class="legend-swatch"></span>
    <span class="legend-label">Category B (35%)</span>
  </span>
</div>
```

---

## CHART ACCESSIBILITY

### Line Charts

```tsx
// Different line styles for color blindness
const LINE_STYLES = [
  { stroke: 'var(--cvd-blue)', strokeDasharray: 'none' },           // Solid
  { stroke: 'var(--cvd-orange)', strokeDasharray: '8,4' },          // Dashed
  { stroke: 'var(--cvd-purple)', strokeDasharray: '2,4' },          // Dotted
  { stroke: 'var(--cvd-teal)', strokeDasharray: '8,4,2,4' },        // Dash-dot
  { stroke: 'var(--cvd-brown)', strokeDasharray: '16,4' },          // Long dash
];

// Recharts example
<LineChart data={data}>
  {datasets.map((dataset, i) => (
    <Line
      key={dataset.name}
      dataKey={dataset.key}
      stroke={LINE_STYLES[i].stroke}
      strokeDasharray={LINE_STYLES[i].strokeDasharray}
      strokeWidth={2}
      dot={{ r: 4, shape: Object.keys(SHAPES)[i] }}
    />
  ))}
</LineChart>
```

### Bar Charts with Patterns

```css
/* SVG patterns for bar charts */
.bar--pattern-1 {
  fill: url(#pattern-diagonal);
}

.bar--pattern-2 {
  fill: url(#pattern-dots);
}

.bar--pattern-3 {
  fill: url(#pattern-horizontal);
}
```

```html
<svg>
  <defs>
    <pattern id="pattern-diagonal" patternUnits="userSpaceOnUse" width="10" height="10">
      <path d="M-1,1 l2,-2 M0,10 l10,-10 M9,11 l2,-2" 
            stroke="currentColor" stroke-width="1"/>
    </pattern>
    
    <pattern id="pattern-dots" patternUnits="userSpaceOnUse" width="8" height="8">
      <circle cx="4" cy="4" r="2" fill="currentColor"/>
    </pattern>
    
    <pattern id="pattern-horizontal" patternUnits="userSpaceOnUse" width="10" height="6">
      <line x1="0" y1="3" x2="10" y2="3" stroke="currentColor" stroke-width="2"/>
    </pattern>
  </defs>
</svg>
```

### Pie/Donut Charts

Always add direct labels or lead lines:

```tsx
<PieChart>
  <Pie 
    data={data} 
    label={({ name, value }) => `${name}: ${value}%`}
    labelLine={true}
  >
    {data.map((entry, index) => (
      <Cell 
        key={entry.name}
        fill={PALETTE[index]}
        stroke="#fff"
        strokeWidth={2}
      />
    ))}
  </Pie>
</PieChart>
```

---

## FORM VALIDATION

### Error States

```html
<!-- ❌ Bad: Red border only -->
<input class="input--error" style="border-color: red;">

<!-- ✅ Good: Icon + text + border -->
<div class="field field--error">
  <label for="email">Email</label>
  <div class="field__input-wrap">
    <input id="email" type="email" aria-invalid="true" aria-describedby="email-error">
    <i data-lucide="alert-circle" class="field__error-icon" aria-hidden="true"></i>
  </div>
  <p id="email-error" class="field__error" role="alert">
    Please enter a valid email address
  </p>
</div>
```

### Success States

```html
<div class="field field--success">
  <label for="username">Username</label>
  <div class="field__input-wrap">
    <input id="username" type="text" aria-describedby="username-success">
    <i data-lucide="check-circle" class="field__success-icon" aria-hidden="true"></i>
  </div>
  <p id="username-success" class="field__success">
    Username is available
  </p>
</div>
```

---

## TESTING WORKFLOW

### Browser DevTools

1. **Chrome**: DevTools → Rendering → Emulate vision deficiencies
2. **Firefox**: DevTools → Accessibility → Simulate

### Automated Testing

```javascript
// Check color contrast in different CVD modes
async function testColorBlindness(page) {
  const simulations = [
    'protanopia',
    'deuteranopia',
    'tritanopia'
  ];
  
  for (const type of simulations) {
    await page.evaluate((filterType) => {
      document.body.classList.add(`simulate-${filterType}`);
    }, type);
    
    // Run visual regression test
    await expect(page).toHaveScreenshot(`cvd-${type}.png`);
    
    // Check that all interactive elements are still distinguishable
    const buttons = await page.locator('button').all();
    for (const button of buttons) {
      const isVisible = await button.isVisible();
      expect(isVisible).toBe(true);
    }
    
    await page.evaluate(() => {
      document.body.className = '';
    });
  }
}
```

---

## CHECKLIST

Before shipping, verify:

- [ ] No information conveyed by color alone
- [ ] All status indicators have icons
- [ ] Charts use patterns/shapes in addition to color
- [ ] Form errors use text + icons
- [ ] Links are underlined OR have other indicator
- [ ] Tested with protanopia simulation
- [ ] Tested with deuteranopia simulation
- [ ] Tested with grayscale
- [ ] Focus states don't rely on color only
- [ ] Data tables don't use color coding alone

---

## QUICK FIXES

| Problem | Fix |
|---------|-----|
| Red/green status | Add ✓/✗ icons |
| Color-coded links | Add underline |
| Chart lines same in CVD | Add dash patterns |
| Pie chart segments | Add direct labels |
| Required field indicator | Add asterisk + text |
| Alert banner | Add icon prefix |
| Progress indicator | Add percentage text |
