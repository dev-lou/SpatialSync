---
description: A guide for contributing new patterns and rules to the OpenCode UI/UX 2026 Design System.
---

# Contributing to OpenCode UI/UX 2026 Design System

This guide outlines the process for adding new patterns, components, or rules to the OpenCode UI/UX 2026 Design System. Following these steps ensures consistency, maintainability, and adherence to our established standards.

## 1. Identify the Right Category

Before creating a new file, determine the most appropriate category for your pattern. The `.opencode/rules/` directory contains various categories, such as `uiux-components.md`, `uiux-forms.md`, `uiux-motion.md`, etc. If your pattern doesn't fit an existing category, consider if a new category is truly warranted or if it can be integrated into a broader existing rule.

## 2. File Naming Conventions

All new rule files should follow the `uiux-<category-name>.md` or `<meta-skill-name>.md` convention. For example, a new rule for advanced tables would be `uiux-advanced-tables.md`. Ensure names are descriptive and concise.

## 3. Using Design Tokens

Strict adherence to the design token system is paramount. Always use semantic design tokens (e.g., `var(--space-4)`, `var(--accent)`) instead of raw pixel values or hardcoded colors. Refer to `uiux-tokens.md` for the complete token architecture and available tokens. This ensures brand consistency and easy theming.

```css
/* GOOD: Using a semantic design token for spacing */
.my-component {
  padding: var(--space-4);
  background-color: var(--surface);
}

/* BAD: Hardcoded values */
.my-component {
  padding: 16px;
  background-color: #FFFFFF;
}
```

## 4. Updating AGENTS.md

After creating a new rule file, you **MUST** update `AGENTS.md` to include it in the `RULE FILES` table. This ensures that AI agents are aware of the new rule and can load it when relevant. Add an entry with the file name, a brief description of its contents, and when it should be loaded.

```markdown
| File               | Contents                                     | Load when          |
|--------------------|----------------------------------------------|--------------------|
| `uiux-new-rule.md` | Guidelines for implementing new UI patterns. | New UI patterns    |
```

## 5. Updating the Changelog

Every significant addition or change to the design system requires an update to `uiux-changelog.md`. Add a new entry under the latest version (or create a new version if it's a major release) in the appropriate `Added`, `Changed`, or `Fixed` section. Clearly describe what was introduced, modified, or resolved.

```markdown
## v7 - 2026-04-06

### Added
- **New Rule File:** `uiux-new-rule.md` outlining best practices for X.
```

## 6. Pull Request (PR) Process

All contributions must go through a Pull Request (PR) process. Follow these steps:

1.  **Branching**: Create a new branch from `main` with a descriptive name (e.g., `feature/add-new-rule-x` or `fix/bug-in-y`).
2.  **Development**: Implement your changes, ensuring all guidelines (design tokens, file naming, etc.) are followed.
3.  **Testing**: Verify your changes locally. If applicable, add or update tests.
4.  **Documentation**: Ensure `AGENTS.md` and `uiux-changelog.md` are updated.
5.  **Commit**: Write clear, concise commit messages.
6.  **Push**: Push your branch to the remote repository.
7.  **Open PR**: Open a Pull Request against the `main` branch. Provide a detailed description of your changes, referencing any related issues.
8.  **Review**: Address feedback from reviewers promptly.
9.  **Merge**: Once approved, your PR will be merged.

By following this process, we maintain a high-quality, consistent, and well-documented design system.
