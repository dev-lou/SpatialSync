---
description: Advanced admin dashboard patterns including widget grids, RBAC UI, KPI cards, activity timelines, notification centers, user menus, impersonation modes, and data exports.
---

# UI/UX Admin Dashboard 2026

> Dashboard widget grids, RBAC UI, KPI cards, activity timelines, 
> notification centers, user menus, impersonation modes, and data exports.

---

## Modern Dashboard Visual Principles (2026)

For any admin dashboard, analytics page, or data‑heavy interface, the AI MUST follow these visual rules by default (unless the user explicitly asks for “simple” or “compact”):

- **Asymmetric grid** – never centred columns. Use bento‑style uneven spans (e.g., one wide chart, two narrow KPI cards).
- **Generous whitespace** – use `--space-8` to `--space-16` between sections, not tight packing.
- **Floating elements** – cards should have `box-shadow: var(--shadow-lg)` and rounded corners (`--radius-lg`), with a subtle background that contrasts with the page background.
- **Breakout containers** – charts or tables should occasionally break out of the main container width (use negative margins or full‑bleed backgrounds).
- **Large, bold typography** – KPI values should be `--text-4xl` or larger, with thin font weight.
- **Glassmorphism / frosted panels** – use `backdrop-filter: blur(12px)` for sidebars or top bars, with a semi‑transparent background.
- **Minimal borders** – prefer shadows over borders. Only use borders when necessary for separation.
- **No centred text** except for specific callouts. Left‑align or use justified where appropriate.

The AI must generate layouts that look like modern SaaS dashboards (e.g., Linear, Vercel, Stripe), not like Bootstrap 4 admin templates.

---

## 1. DASHBOARD WIDGET GRID (Draggable & Resizable)

```html
<div class="dashboard-grid" id="dashboard-grid">
  <div class="widget" data-size="small">
    <header class="widget__header">
      <h3>Revenue</h3>
      <button class="widget__drag-handle"><i data-lucide="grip-vertical"></i></button>
    </header>
    <div class="widget__content">...</div>
  </div>
</div>
```

```css
.dashboard-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
  grid-auto-rows: 240px;
  gap: var(--space-6);
}

.widget {
  background: var(--surface);
  border: 1px solid var(--border);
  border-radius: var(--radius-lg);
  display: flex;
  flex-direction: column;
  transition: var(--transition-base);
  box-shadow: var(--shadow-sm);
}

.widget:hover { box-shadow: var(--shadow-md); }

.widget__header {
  padding: var(--space-4);
  border-bottom: 1px solid var(--border);
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.widget__content { padding: var(--space-4); flex: 1; }
```

---

## 2. RBAC UI (Permission Toggles & Role Selectors)

```html
<div class="rbac-panel">
  <h3 class="rbac-panel__title">Role Permissions</h3>
  <div class="rbac-group">
    <h4 class="rbac-group__title">Admin</h4>
    <div class="rbac-item">
      <span>Manage Users</span>
      <label class="toggle"><input type="checkbox" checked><span class="toggle__slider"></span></label>
    </div>
    <div class="rbac-item">
      <span>Manage Billing</span>
      <label class="toggle"><input type="checkbox" checked><span class="toggle__slider"></span></label>
    </div>
  </div>
</div>
```

```css
.rbac-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: var(--space-3) var(--space-4);
  border-bottom: 1px solid var(--border);
}

.toggle {
  position: relative;
  display: inline-block;
  width: 40px; height: 20px;
}

.toggle__slider {
  position: absolute; inset: 0;
  background: var(--surface-up);
  border-radius: var(--radius-full);
  transition: var(--transition-base);
}

.toggle input:checked + .toggle__slider { background: var(--accent); }
```

---

## 3. KPI CARDS (Trends & Sparklines)

```html
<div class="kpi-card">
  <div class="kpi-card__header">
    <span class="kpi-card__title">Total Users</span>
    <span class="kpi-card__trend trend--up">+12% <i data-lucide="trending-up"></i></span>
  </div>
  <div class="kpi-card__value">12,450</div>
  <div class="kpi-card__chart">
    <canvas id="kpi-sparkline"></canvas>
  </div>
</div>
```

```css
.kpi-card {
  background: var(--surface);
  border: 1px solid var(--border);
  border-radius: var(--radius-lg);
  padding: var(--space-6);
  display: flex;
  flex-direction: column;
  gap: var(--space-2);
}

.kpi-card__value { font-size: var(--text-4xl); font-weight: 300; }

.trend--up { color: var(--color-success); font-size: var(--text-xs); font-weight: 600; }
.trend--down { color: var(--color-error); font-size: var(--text-xs); font-weight: 600; }
```

---

## 4. NOTIFICATION CENTER (Bell Icon & Dropdown)

```html
<div class="notification-center">
  <button class="btn btn--icon notification-center__trigger">
    <i data-lucide="bell"></i>
    <span class="notification-center__badge">3</span>
  </button>
  <div class="notification-center__dropdown" hidden>
    <header class="notification-center__header">
      <h3>Notifications</h3>
      <button class="btn btn--link">Mark all as read</button>
    </header>
    <ul class="notification-center__list">
      <li class="notification-center__item unread">...</li>
    </ul>
  </div>
</div>
```

```css
.notification-center__badge {
  position: absolute; top: 0; right: 0;
  width: 16px; height: 16px;
  background: var(--color-error);
  color: white;
  border-radius: 50%;
  font-size: 10px;
  display: grid; place-items: center;
  border: 2px solid var(--bg);
}
```

---

## 5. IMPERSONATION MODE (Admin Acting as User)

```html
<div class="impersonation-banner">
  <div class="impersonation-banner__content">
    <i data-lucide="user-cog"></i>
    <span>Impersonating <strong>John Doe</strong> (john@example.com)</span>
  </div>
  <button class="btn btn--sm btn--inverse">Stop Impersonation</button>
</div>
```

```css
.impersonation-banner {
  position: fixed; top: 0; left: 0; right: 0;
  background: var(--accent);
  color: white;
  padding: var(--space-2) var(--space-6);
  display: flex;
  justify-content: space-between;
  align-items: center;
  z-index: 2000;
  font-size: var(--text-sm);
}
```

---

## 6. EXPORT DATA (CSV/PDF/Excel)

```html
<div class="export-menu">
  <button class="btn btn--outline btn--sm"><i data-lucide="download"></i> Export</button>
  <div class="export-menu__dropdown" hidden>
    <button class="export-menu__item"><i data-lucide="file-text"></i> CSV</button>
    <button class="export-menu__item"><i data-lucide="file-spreadsheet"></i> Excel</button>
    <button class="export-menu__item"><i data-lucide="file"></i> PDF</button>
  </div>
</div>
```

```css
.export-menu__dropdown {
  position: absolute; top: 100%; right: 0;
  background: var(--surface);
  border: 1px solid var(--border);
  border-radius: var(--radius-md);
  box-shadow: var(--shadow-lg);
  padding: var(--space-2);
  display: flex;
  flex-direction: column;
  gap: var(--space-1);
  z-index: 100;
}

.export-menu__item {
  display: flex;
  align-items: center;
  gap: var(--space-2);
  padding: var(--space-2) var(--space-4);
  border-radius: var(--radius-sm);
  transition: background var(--transition-micro);
}

.export-menu__item:hover { background: var(--surface-up); }
```

---

## Charts & Analytics Library (2026)

Priority for visual wow dashboards (React / Next.js):

- **Recharts** – declarative, composable, modern look, great for React dashboards. Default choice.
- **Tremor** – Tailwind‑based, pre‑styled, fast to implement. Good for quick, consistent dashboards.
- **AG Charts** – enterprise, free tier, high performance for large datasets.

For vanilla JS (non‑React) or lightweight needs:

- **Chart.js** – simple, flexible, but requires more styling to look modern. Use only if React is not available or user explicitly asks for simplicity.

> **Note**: All libraries mentioned have free tiers.

### Example: KPI Card with Recharts (React)

```jsx
import { ResponsiveContainer, AreaChart, Area, XAxis, YAxis, Tooltip } from 'recharts';

const data = [
  { name: 'Mon', value: 4000 },
  { name: 'Tue', value: 3000 },
  { name: 'Wed', value: 5000 },
  { name: 'Thu', value: 2780 },
  { name: 'Fri', value: 1890 },
  { name: 'Sat', value: 2390 },
  { name: 'Sun', value: 3490 },
];

export function RevenueKPI() {
  return (
    <div className="kpi-card">
      <div className="kpi-card__header">
        <span className="kpi-card__title">Revenue</span>
        <span className="kpi-card__trend trend--up">+8.4%</span>
      </div>
      <div className="kpi-card__value">$42,390</div>
      <div className="kpi-card__chart" style={{ height: 80, width: '100%' }}>
        <ResponsiveContainer width="100%" height="100%">
          <AreaChart data={data}>
            <defs>
              <linearGradient id="colorValue" x1="0" y1="0" x2="0" y2="1">
                <stop offset="5%" stopColor="var(--accent)" stopOpacity={0.3}/>
                <stop offset="95%" stopColor="var(--accent)" stopOpacity={0}/>
              </linearGradient>
            </defs>
            <Area type="monotone" dataKey="value" stroke="var(--accent)" fillOpacity={1} fill="url(#colorValue)" strokeWidth={2} />
          </AreaChart>
        </ResponsiveContainer>
      </div>
    </div>
  );
}
```

### Fallback: Chart.js (Vanilla JS)

```javascript
// Note: Extra styling needed to match modern aesthetics
const ctx = document.getElementById('myChart').getContext('2d');
const myChart = new Chart(ctx, {
    type: 'line',
    data: {
        labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
        datasets: [{
            label: 'Revenue',
            data: [4000, 3000, 5000, 2780, 1890, 2390, 3490],
            borderColor: '#0066FF',
            backgroundColor: 'rgba(0, 102, 255, 0.1)',
            borderWidth: 2,
            tension: 0.4,
            fill: true
        }]
    },
    options: {
        plugins: { legend: { display: false } },
        scales: { x: { display: false }, y: { display: false } },
        elements: { point: { radius: 0 } }
    }
});
```

### Interactive Data Binding

To connect chart data to a data table filter:

```javascript
// Example: Clicking a table row updates the chart
document.querySelectorAll('.data-table tr').forEach(row => {
  row.addEventListener('click', () => {
    const userId = row.dataset.id;
    updateChartForUser(userId); // Function to fetch data and update chart instance
    row.classList.add('selected');
  });
});
```

---

## Standard Admin Features – Decision Guide

When building any admin panel (SaaS, e‑commerce, CMS, etc.), always consider including:

1. **User management** (list, create, edit, delete, role assignment)
2. **Audit log** (immutable event trail with search/filter)
3. **Role‑based access control** (RBAC)
4. **Notification center**
5. **Settings page** (profile, security, API keys, theme)
6. **Export data**
7. **Activity timeline**
8. **Billing / subscription** (ask user)

Include these by default unless the user says “simple” or “MVP”.

### How to present options to the user:
"I've prepared a production-ready admin dashboard plan. By default, I've included user management, audit logs, and a settings page. Would you like to also include billing integration or a real-time notification center?"

---

## Visual Wow Checklist (for AI self‑review before output)

- [ ] Is the layout asymmetrical (not centred columns)?
- [ ] Is there generous whitespace between widgets (`--space-8` or more)?
- [ ] Do cards have shadows and rounded corners (`--radius-lg`)?
- [ ] Are KPI values large and bold (`--text-4xl` or larger)?
- [ ] Is there at least one full‑bleed or breakout element (chart or table)?
- [ ] Are borders minimised (prefer shadows)?
- [ ] Does the design avoid centred text for data?
- [ ] If glassmorphism is used, does it have proper contrast and fallback?

---

## Dark Mode Support for Recharts

**Rule**: Charts must adapt to the active theme. Colors for strokes, grids, axes, and tooltips must change based on `[data-theme]`.

### useChartTheme Hook (React)

```jsx
import { useState, useEffect, useMemo } from 'react';

/**
 * Hook to get theme-aware chart colors
 * Watches data-theme attribute and returns appropriate colors
 */
export function useChartTheme() {
  const [theme, setTheme] = useState('light');

  useEffect(() => {
    // Get initial theme
    const getTheme = () => document.documentElement.getAttribute('data-theme') || 'light';
    setTheme(getTheme());

    // Watch for theme changes
    const observer = new MutationObserver((mutations) => {
      mutations.forEach((mutation) => {
        if (mutation.attributeName === 'data-theme') {
          setTheme(getTheme());
        }
      });
    });

    observer.observe(document.documentElement, {
      attributes: true,
      attributeFilter: ['data-theme'],
    });

    return () => observer.disconnect();
  }, []);

  // Memoized color values based on theme
  const colors = useMemo(() => ({
    // Text and labels
    text: theme === 'dark' ? '#EDF0F5' : '#0F1117',
    textSecondary: theme === 'dark' ? '#8892A0' : '#4A5060',
    textMuted: theme === 'dark' ? '#4A5060' : '#6C737A',

    // Grid and axes
    grid: theme === 'dark' ? 'rgba(237, 240, 245, 0.08)' : 'rgba(15, 17, 23, 0.06)',
    axis: theme === 'dark' ? '#4A5060' : '#6C737A',
    
    // Chart strokes
    stroke: theme === 'dark' ? '#EDF0F5' : '#4A5060',
    strokeAccent: theme === 'dark' ? '#4D8EFF' : '#0066FF',
    
    // Fill colors with opacity
    fill: theme === 'dark' ? 'rgba(77, 142, 255, 0.2)' : 'rgba(0, 102, 255, 0.1)',
    fillSecondary: theme === 'dark' ? 'rgba(62, 207, 142, 0.2)' : 'rgba(26, 127, 55, 0.1)',
    
    // Tooltip
    tooltipBg: theme === 'dark' ? '#1C1F28' : '#FFFFFF',
    tooltipBorder: theme === 'dark' ? 'rgba(237, 240, 245, 0.1)' : 'rgba(15, 17, 23, 0.08)',

    // Status colors
    success: theme === 'dark' ? '#3FB950' : '#1A7F37',
    error: theme === 'dark' ? '#F85149' : '#CF222E',
    warning: theme === 'dark' ? '#D29922' : '#9A6700',
    
    // Palette for multiple series
    palette: theme === 'dark' 
      ? ['#4D8EFF', '#3ECF8E', '#F97316', '#A78BFA', '#FB7185', '#22D3EE']
      : ['#0066FF', '#1A7F37', '#EA580C', '#7C3AED', '#E11D48', '#0891B2'],
  }), [theme]);

  return { theme, colors };
}
```

### Usage with Recharts

```jsx
import { ResponsiveContainer, AreaChart, Area, XAxis, YAxis, CartesianGrid, Tooltip } from 'recharts';
import { useChartTheme } from '@/hooks/useChartTheme';

const data = [
  { name: 'Mon', value: 4000 },
  { name: 'Tue', value: 3000 },
  { name: 'Wed', value: 5000 },
  { name: 'Thu', value: 2780 },
  { name: 'Fri', value: 1890 },
  { name: 'Sat', value: 2390 },
  { name: 'Sun', value: 3490 },
];

export function RevenueChart() {
  const { colors } = useChartTheme();

  return (
    <div className="kpi-card">
      <h3 className="kpi-card__title">Weekly Revenue</h3>
      <div style={{ height: 200, width: '100%' }}>
        <ResponsiveContainer width="100%" height="100%">
          <AreaChart data={data}>
            {/* Theme-aware grid */}
            <CartesianGrid
              stroke={colors.grid}
              strokeDasharray="3 3"
              vertical={false}
            />
            
            {/* Theme-aware axes */}
            <XAxis
              dataKey="name"
              axisLine={false}
              tickLine={false}
              tick={{ fill: colors.textSecondary, fontSize: 12 }}
            />
            <YAxis
              axisLine={false}
              tickLine={false}
              tick={{ fill: colors.textSecondary, fontSize: 12 }}
              width={40}
            />
            
            {/* Theme-aware tooltip */}
            <Tooltip
              contentStyle={{
                backgroundColor: colors.tooltipBg,
                border: `1px solid ${colors.tooltipBorder}`,
                borderRadius: 8,
                boxShadow: '0 4px 16px rgba(0,0,0,0.15)',
              }}
              labelStyle={{ color: colors.text, fontWeight: 600 }}
              itemStyle={{ color: colors.textSecondary }}
            />
            
            {/* Gradient fill definition */}
            <defs>
              <linearGradient id="chartGradient" x1="0" y1="0" x2="0" y2="1">
                <stop offset="5%" stopColor={colors.strokeAccent} stopOpacity={0.3}/>
                <stop offset="95%" stopColor={colors.strokeAccent} stopOpacity={0}/>
              </linearGradient>
            </defs>
            
            {/* Theme-aware area */}
            <Area
              type="monotone"
              dataKey="value"
              stroke={colors.strokeAccent}
              strokeWidth={2}
              fill="url(#chartGradient)"
            />
          </AreaChart>
        </ResponsiveContainer>
      </div>
    </div>
  );
}
```

### Multi-Series Chart with Theme Colors

```jsx
export function MultiSeriesChart({ data }) {
  const { colors } = useChartTheme();

  return (
    <ResponsiveContainer width="100%" height={300}>
      <LineChart data={data}>
        <CartesianGrid stroke={colors.grid} strokeDasharray="3 3" />
        <XAxis 
          dataKey="month" 
          tick={{ fill: colors.textSecondary }} 
          axisLine={{ stroke: colors.axis }}
        />
        <YAxis 
          tick={{ fill: colors.textSecondary }} 
          axisLine={{ stroke: colors.axis }}
        />
        <Tooltip
          contentStyle={{
            backgroundColor: colors.tooltipBg,
            borderColor: colors.tooltipBorder,
          }}
        />
        
        {/* Multiple series using palette colors */}
        <Line type="monotone" dataKey="revenue" stroke={colors.palette[0]} strokeWidth={2} dot={false} />
        <Line type="monotone" dataKey="expenses" stroke={colors.palette[1]} strokeWidth={2} dot={false} />
        <Line type="monotone" dataKey="profit" stroke={colors.palette[2]} strokeWidth={2} dot={false} />
      </LineChart>
    </ResponsiveContainer>
  );
}
```

### Chart Theme Provider (Optional)

```jsx
import { createContext, useContext } from 'react';
import { useChartTheme } from '@/hooks/useChartTheme';

const ChartThemeContext = createContext(null);

export function ChartThemeProvider({ children }) {
  const chartTheme = useChartTheme();
  return (
    <ChartThemeContext.Provider value={chartTheme}>
      {children}
    </ChartThemeContext.Provider>
  );
}

export function useChartContext() {
  const context = useContext(ChartThemeContext);
  if (!context) {
    throw new Error('useChartContext must be used within ChartThemeProvider');
  }
  return context;
}
```
