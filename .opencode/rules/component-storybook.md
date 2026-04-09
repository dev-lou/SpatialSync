---
description: >
  Storybook story generation skill. Load when the user asks to "generate stories",
  "add Storybook", "document this component", or "create stories for". Analyses
  React or Vue component files, extracts props, variants, and states, and outputs
  production-quality .stories.tsx / .stories.ts files using the project's design tokens.
  Load alongside uiux-testing.md and uiux-states.md.
---

# Component Storybook 2026

> Component file → prop extraction → story generation → interaction tests → a11y checks.
> Produces stories that document every design system variant and all 9 component states.

---

## PREREQUISITES

```bash
# Install Storybook (if not present)
npx storybook@latest init

# Required addons
npm install -D \
  @storybook/addon-a11y \
  @storybook/addon-interactions \
  @storybook/test \
  @storybook/addon-docs \
  chromatic  # optional — visual regression
```

---

## STEP 1 — ANALYSE THE COMPONENT

Read the component file and extract:

```markdown
## Component Analysis: {ComponentName}

### Props interface
{extracted TypeScript interface or prop-types}

### Variants (from union types / enums)
{e.g., variant: 'primary' | 'secondary' | 'ghost' | 'danger'}

### States (from the 9-state law)
{which of default/hover/focus/active/loading/disabled/error/success/empty apply}

### Design tokens used
{list of CSS custom property references found in the component}

### Sub-components / composition
{e.g., Button is used inside Form, Card, etc.}
```

---

## STEP 2 — STORYBOOK CONFIG

```typescript
// .storybook/main.ts
import type { StorybookConfig } from '@storybook/nextjs';

const config: StorybookConfig = {
  stories: ['../src/**/*.stories.@(ts|tsx)'],
  addons: [
    '@storybook/addon-essentials',
    '@storybook/addon-interactions',
    '@storybook/addon-a11y',
    '@storybook/addon-docs',
  ],
  framework: {
    name: '@storybook/nextjs',
    options: {},
  },
};
export default config;
```

```typescript
// .storybook/preview.ts
import type { Preview } from '@storybook/react';
import '../src/tokens.css';   // design tokens — must be first
import '../src/globals.css';

const preview: Preview = {
  parameters: {
    controls: { matchers: { color: /(background|color)$/i, date: /Date$/i } },
    a11y: { config: { rules: [{ id: 'color-contrast', enabled: true }] } },
    backgrounds: {
      default: 'light',
      values: [
        { name: 'light', value: 'var(--bg)' },
        { name: 'dark',  value: '#0A0B0E' },
      ],
    },
  },
  globalTypes: {
    theme: {
      name: 'Theme',
      description: 'Global theme for components',
      defaultValue: 'light',
      toolbar: {
        icon: 'circlehollow',
        items: ['light', 'dark'],
        showName: true,
      },
    },
  },
  decorators: [
    (Story, context) => {
      const theme = context.globals.theme;
      document.documentElement.setAttribute('data-theme', theme);
      return <Story />;
    },
  ],
};
export default preview;
```

---

## STEP 3 — STORY TEMPLATE (Button example — adapt for any component)

```typescript
// src/components/ui/Button/Button.stories.tsx
import type { Meta, StoryObj } from '@storybook/react';
import { expect, userEvent, within } from '@storybook/test';
import { Button } from './Button';

// ─── Meta ────────────────────────────────────────────────────────────────────
const meta = {
  title:     'UI / Button',
  component:  Button,
  tags:      ['autodocs'],

  parameters: {
    docs: {
      description: {
        component: `
Button is the primary interactive element in the design system.
It follows **uiux-states.md** — all 9 states are documented below.

**Design tokens used:** \`--accent\`, \`--radius-md\`, \`--dur-micro\`, \`--dur-base\`, \`--shadow-md\`
        `,
      },
    },
  },

  argTypes: {
    variant: {
      control: { type: 'select' },
      options: ['primary', 'secondary', 'ghost', 'danger', 'link'],
      description: 'Visual style of the button',
      table: { defaultValue: { summary: 'primary' } },
    },
    size: {
      control: { type: 'select' },
      options: ['xs', 'sm', 'base', 'lg', 'xl'],
      description: 'Size variant',
      table: { defaultValue: { summary: 'base' } },
    },
    loading: {
      control: 'boolean',
      description: 'Shows spinner and disables interaction',
    },
    disabled: {
      control: 'boolean',
      description: 'Disables the button',
    },
    fullWidth: {
      control: 'boolean',
      description: 'Stretches button to full container width',
    },
    onClick: { action: 'clicked' },
  },

  args: {
    children: 'Button',
    variant:  'primary',
    size:     'base',
    loading:  false,
    disabled: false,
    fullWidth: false,
  },
} satisfies Meta<typeof Button>;

export default meta;
type Story = StoryObj<typeof meta>;

// ─── Variants ────────────────────────────────────────────────────────────────
export const Primary: Story = {
  args: { variant: 'primary', children: 'Get Started' },
};

export const Secondary: Story = {
  args: { variant: 'secondary', children: 'Learn More' },
};

export const Ghost: Story = {
  args: { variant: 'ghost', children: 'Cancel' },
};

export const Danger: Story = {
  args: { variant: 'danger', children: 'Delete Account' },
};

export const LinkStyle: Story = {
  args: { variant: 'link', children: 'View Details →' },
};

// ─── Sizes ───────────────────────────────────────────────────────────────────
export const Sizes: Story = {
  render: (args) => (
    <div style={{ display: 'flex', alignItems: 'center', gap: 'var(--space-4)', flexWrap: 'wrap' }}>
      <Button {...args} size="xs">Extra Small</Button>
      <Button {...args} size="sm">Small</Button>
      <Button {...args} size="base">Base</Button>
      <Button {...args} size="lg">Large</Button>
      <Button {...args} size="xl">Extra Large</Button>
    </div>
  ),
};

// ─── States (all 9) ──────────────────────────────────────────────────────────
export const AllStates: Story = {
  render: (args) => (
    <div style={{ display: 'flex', alignItems: 'center', gap: 'var(--space-4)', flexWrap: 'wrap' }}>
      <Button {...args}>Default</Button>
      <Button {...args} data-hover>Hover</Button>        {/* CSS :hover simulation */}
      <Button {...args} autoFocus>Focus</Button>
      <Button {...args} data-active>Active / Pressed</Button>
      <Button {...args} loading>Loading</Button>
      <Button {...args} disabled>Disabled</Button>
      <Button {...args} variant="danger">Error / Destructive</Button>
      <Button {...args} data-success>Success</Button>
    </div>
  ),
  parameters: {
    docs: {
      description: { story: 'All 9 component states per uiux-states.md law #13.' },
    },
  },
};

// ─── With Icon ───────────────────────────────────────────────────────────────
export const WithIconLeft: Story = {
  args: { children: <><PlusIcon /> Add Item</> },
};

export const IconOnly: Story = {
  args: { children: <SettingsIcon />, 'aria-label': 'Settings' },
  parameters: {
    docs: {
      description: { story: 'Icon-only buttons require an `aria-label` for accessibility.' },
    },
  },
};

export const FullWidth: Story = {
  args: { fullWidth: true, children: 'Full Width Button' },
};

// ─── Interaction Tests ────────────────────────────────────────────────────────
export const ClickInteraction: Story = {
  args: { children: 'Click me' },
  play: async ({ canvasElement, args }) => {
    const canvas = within(canvasElement);
    const button = canvas.getByRole('button', { name: /click me/i });

    await userEvent.click(button);
    await expect(args.onClick).toHaveBeenCalledOnce();
  },
};

export const KeyboardInteraction: Story = {
  play: async ({ canvasElement, args }) => {
    const canvas = within(canvasElement);
    const button = canvas.getByRole('button');

    // Tab to focus
    await userEvent.tab();
    await expect(button).toHaveFocus();

    // Enter to activate
    await userEvent.keyboard('{Enter}');
    await expect(args.onClick).toHaveBeenCalledOnce();

    // Space to activate
    await userEvent.keyboard('{ }');
    await expect(args.onClick).toHaveBeenCalledTimes(2);
  },
};

export const LoadingPreventsDoubleSubmit: Story = {
  args: { children: 'Submit', loading: true },
  play: async ({ canvasElement, args }) => {
    const canvas = within(canvasElement);
    const button = canvas.getByRole('button');

    // Clicking a loading button should not trigger onClick
    await userEvent.click(button);
    await expect(args.onClick).not.toHaveBeenCalled();
    await expect(button).toBeDisabled();
    await expect(button).toHaveAttribute('aria-busy', 'true');
  },
};

// ─── Dark Mode ───────────────────────────────────────────────────────────────
export const DarkMode: Story = {
  parameters: { backgrounds: { default: 'dark' } },
  decorators: [
    (Story) => {
      document.documentElement.setAttribute('data-theme', 'dark');
      return <Story />;
    },
  ],
};

// ─── Design Language Variants ─────────────────────────────────────────────────
export const LuxuryStyle: Story = {
  decorators: [
    (Story) => (
      <div data-lang="luxury">
        <Story />
      </div>
    ),
  ],
  parameters: {
    docs: { description: { story: 'Button rendered in Luxury design language — sharp, uppercase, no radius.' } },
  },
};
```

---

## STEP 4 — STORY TEMPLATE (Form field — more complex)

```typescript
// src/components/ui/Field/Field.stories.tsx
import type { Meta, StoryObj } from '@storybook/react';
import { expect, userEvent, within } from '@storybook/test';
import { Field } from './Field';

const meta = {
  title: 'UI / Form / Field',
  component: Field,
  tags: ['autodocs'],
  argTypes: {
    state: {
      control: { type: 'select' },
      options: ['default', 'hover', 'focus', 'loading', 'error', 'success', 'disabled'],
    },
  },
  args: { label: 'Email address', type: 'email', placeholder: 'you@example.com' },
} satisfies Meta<typeof Field>;
export default meta;

type Story = StoryObj<typeof meta>;

export const Default:  Story = {};
export const WithHelperText: Story = { args: { helperText: "We'll never share your email." } };
export const ErrorState: Story = {
  args: { state: 'error', errorText: 'Email already in use.' },
};
export const SuccessState: Story = {
  args: { state: 'success', successText: '✓ Email available' },
};
export const DisabledState: Story = {
  args: { state: 'disabled', value: 'jane@example.com' },
};
export const FloatingLabel: Story = {
  args: { floatingLabel: true },
  play: async ({ canvasElement }) => {
    const input = within(canvasElement).getByRole('textbox');
    await expect(input).toHaveAttribute('placeholder', ' '); // floating label technique
    await userEvent.click(input);
    await userEvent.type(input, 'jane@example.com');
    // Label should be floating above
  },
};
```

---

## STEP 5 — CHROMATIC VISUAL REGRESSION

```bash
# One-time setup
npx chromatic --project-token=YOUR_TOKEN --only-changed
```

```yaml
# .github/workflows/chromatic.yml
name: Visual Regression
on: [push]
jobs:
  chromatic:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4
        with: { fetch-depth: 0 }
      - uses: actions/setup-node@v4
        with: { node-version: '20' }
      - run: npm ci
      - name: Run Chromatic
        uses: chromaui/action@latest
        with:
          projectToken: ${{ secrets.CHROMATIC_PROJECT_TOKEN }}
          onlyChanged: true          # only stories in changed files
          exitZeroOnChanges: true    # don't fail PR, just flag
```

---

## STEP 6 — STORY GENERATION CHECKLIST

For every component, ensure stories exist for:

```markdown
- [ ] Default state (minimal props, renders without error)
- [ ] All named variants (every value in `variant` union type)
- [ ] All sizes (if size prop exists)
- [ ] All 9 states that apply (per uiux-states.md)
- [ ] Icon-only variant (if applicable) — must have aria-label story
- [ ] Full-width / responsive variant
- [ ] Dark mode
- [ ] Design language variant (if component has lang-specific styles)
- [ ] At least one `play` interaction test (click, keyboard, form submit)
- [ ] A11y story that verifies no axe violations
- [ ] Documentation description in meta.parameters.docs
- [ ] All argTypes have control, description, and defaultValue
```

---

## AUTODOC PATTERN

With `tags: ['autodocs']`, Storybook auto-generates a Docs page from the meta.
Enhance it with:

```typescript
// Inline prop documentation
argTypes: {
  variant: {
    description: 'Visual style variant. Maps to CSS class `.btn--{variant}`.',
    table: {
      type:         { summary: "'primary' | 'secondary' | 'ghost' | 'danger' | 'link'" },
      defaultValue: { summary: 'primary' },
    },
  },
}
```
