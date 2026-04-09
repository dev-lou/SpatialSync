---
description: Meta-skill for heuristic evaluation using Nielsen's 10 usability heuristics plus design language consistency checks. Outputs severity levels and suggested fixes.
---

# UI/UX Design Critique Skill

> Perform structured heuristic evaluation of UI/UX designs.
> Based on Nielsen's 10 heuristics + design language consistency.

---

## OVERVIEW

This skill provides a systematic framework for evaluating user interfaces. Use it to:
- Conduct heuristic evaluations
- Identify usability issues
- Prioritize fixes by severity
- Ensure design language consistency

---

## NIELSEN'S 10 USABILITY HEURISTICS

### 1. Visibility of System Status

**Definition**: The system should always keep users informed about what is going on through appropriate feedback within reasonable time.

**Check for**:
- Loading indicators on async operations
- Progress bars for multi-step processes
- Success/error feedback after actions
- Current state indicators (active nav, selected items)

**Violations**:
- Button clicked but no visual feedback
- Form submitted with no confirmation
- Background process with no indicator
- No "you are here" in navigation

**Severity Guide**:
- 🔴 Critical: User cannot tell if action succeeded
- 🟠 Major: Feedback exists but is unclear
- 🟡 Minor: Feedback is delayed or subtle

---

### 2. Match Between System and Real World

**Definition**: The system should speak the users' language with words, phrases, and concepts familiar to the user.

**Check for**:
- User-friendly language (not developer jargon)
- Familiar icons and metaphors
- Logical information order
- Cultural appropriateness

**Violations**:
- Error codes instead of explanations
- Technical terms without context
- Unfamiliar icons without labels
- Date formats wrong for locale

---

### 3. User Control and Freedom

**Definition**: Users often make mistakes and need clearly marked "emergency exits" to leave unwanted states.

**Check for**:
- Undo/redo functionality
- Cancel buttons on forms
- Back navigation that works
- Easy logout/exit options
- Confirmation before destructive actions

**Violations**:
- No way to cancel mid-process
- Cannot undo accidental deletion
- Trapped in modal with no exit
- Forced to complete unwanted flow

---

### 4. Consistency and Standards

**Definition**: Users should not have to wonder whether different words, situations, or actions mean the same thing.

**Check for**:
- Consistent button styling/placement
- Same icons for same actions
- Uniform terminology
- Platform conventions followed

**Violations**:
- "Submit" vs "Send" vs "Save" inconsistency
- Different button styles for same action
- Non-standard placement of elements
- Breaking platform conventions

---

### 5. Error Prevention

**Definition**: Even better than good error messages is a careful design that prevents problems from occurring in the first place.

**Check for**:
- Form validation before submission
- Confirmation for destructive actions
- Disabled buttons when invalid
- Clear constraints and requirements

**Violations**:
- Allowing invalid input submission
- One-click permanent deletion
- No character count on limited fields
- Ambiguous required field indicators

---

### 6. Recognition Rather Than Recall

**Definition**: Minimize the user's memory load by making objects, actions, and options visible.

**Check for**:
- Visible navigation and options
- Autocomplete and suggestions
- Recent/frequent items shown
- Contextual help available

**Violations**:
- Relying on keyboard shortcuts only
- No search suggestions
- Hidden features in menus
- Instructions on previous page

---

### 7. Flexibility and Efficiency of Use

**Definition**: Accelerators for experts that don't burden novice users.

**Check for**:
- Keyboard shortcuts available
- Customizable interface
- Batch actions for power users
- Quick actions/shortcuts

**Violations**:
- No keyboard navigation
- Cannot customize workflow
- Single-item actions only
- No power user features

---

### 8. Aesthetic and Minimalist Design

**Definition**: Dialogues should not contain irrelevant or rarely needed information.

**Check for**:
- Clean, uncluttered interface
- Information hierarchy
- Progressive disclosure
- Focused content per screen

**Violations**:
- Too much information at once
- Decorative elements distracting
- No visual hierarchy
- Buried important content

---

### 9. Help Users Recognize, Diagnose, and Recover from Errors

**Definition**: Error messages should be expressed in plain language, precisely indicate the problem, and constructively suggest a solution.

**Check for**:
- Clear, specific error messages
- Explanation of what went wrong
- Suggestion for how to fix
- Error shown near the problem

**Violations**:
- "An error occurred"
- Technical error codes
- No recovery suggestion
- Errors far from source

---

### 10. Help and Documentation

**Definition**: It may be necessary to provide help and documentation that is easy to search and focused on tasks.

**Check for**:
- Searchable help content
- Contextual tooltips
- Onboarding for new users
- FAQ and troubleshooting

**Violations**:
- No help available
- Outdated documentation
- Help not searchable
- No onboarding guidance

---

## DESIGN LANGUAGE CONSISTENCY CHECKS

### Token Usage Audit

| Check | Pass Criteria |
|-------|---------------|
| Colors | All colors use semantic tokens (`--accent`, `--text`, etc.) |
| Spacing | All spacing on 8px grid using `--space-*` tokens |
| Typography | Font sizes use `--text-*` tokens |
| Radii | Border radius uses `--radius-*` tokens |
| Shadows | Box shadows use `--shadow-*` tokens |
| Transitions | Timing uses `--dur-*` tokens |

### Design Language Fit

| Language | Key Characteristics | Red Flags |
|----------|---------------------|-----------|
| Luxury | Slow, spacious, serif fonts | Rushed animations, crowded layout |
| Premium Modern | Polished, confident, subtle | Playful elements, loud colors |
| Minimalist | Silent, precise, maximum whitespace | Decorative elements, busy UI |
| Expressive | Loud, energetic, bold | Boring, static, corporate feel |
| Editorial | Typographic focus, readable | Cramped text, poor hierarchy |
| Warm & Human | Approachable, soft, friendly | Cold, sterile, impersonal |
| Technical | Dense, functional, zero decoration | Decorative, inefficient |

---

## SEVERITY RATING SCALE

### 🔴 Critical (4)
- Prevents task completion
- Causes data loss
- Major accessibility barrier
- Security vulnerability

**Action**: Fix immediately

### 🟠 Major (3)
- Significantly impacts usability
- Frequent user confusion
- Workaround exists but painful

**Action**: Fix before next release

### 🟡 Minor (2)
- Causes minor inconvenience
- Cosmetic inconsistency
- Occasional confusion

**Action**: Fix when time permits

### ⚪ Cosmetic (1)
- Minor visual issue
- Nitpick-level concern
- Polish item

**Action**: Consider for future

---

## EVALUATION TEMPLATE

```markdown
# Heuristic Evaluation Report

**Product**: [Name]
**Evaluator**: [AI/Name]
**Date**: [Date]
**Design Language**: [Language]

---

## Executive Summary

| Severity | Count |
|----------|-------|
| 🔴 Critical | X |
| 🟠 Major | X |
| 🟡 Minor | X |
| ⚪ Cosmetic | X |

**Overall Assessment**: [Pass / Needs Work / Fail]

---

## Findings

### Finding 1: [Title]

**Heuristic**: #X - [Name]
**Severity**: 🔴 Critical
**Location**: [Page / Component]

**Issue**:
[Description of the problem]

**Screenshot/Reference**:
[Link or description]

**Recommendation**:
[Specific fix with code example if applicable]

**Design Token Fix** (if applicable):
```css
/* Before */
color: #FF0000;

/* After */
color: var(--color-error);
```

---

[Repeat for each finding]

---

## Design Language Consistency

| Aspect | Status | Notes |
|--------|--------|-------|
| Color tokens | ✅ / ❌ | |
| Spacing tokens | ✅ / ❌ | |
| Typography | ✅ / ❌ | |
| Motion | ✅ / ❌ | |
| Native UI | ✅ / ❌ | No native browser UI elements |

---

## Accessibility Quick Check

- [ ] Color contrast ≥ 4.5:1
- [ ] Focus indicators visible
- [ ] ARIA labels present
- [ ] Keyboard navigable
- [ ] Screen reader tested

---

## Recommendations Priority

1. [Critical fix 1]
2. [Critical fix 2]
3. [Major fix 1]
...
```

---

## AI EVALUATION PROMPT

When asked to evaluate a design, use this process:

```
I'll evaluate this UI against Nielsen's 10 heuristics and design language consistency.

**Evaluation Scope**: [What I'm reviewing]
**Design Language**: [Detected/Stated language]

Let me check each heuristic...

[For each heuristic, note Pass ✅ or Issue ⚠️]

**Summary of Issues Found**:

| # | Heuristic | Severity | Issue |
|---|-----------|----------|-------|
| 1 | Visibility | 🔴 | No loading state |
| 4 | Consistency | 🟡 | Button style varies |
| ... | ... | ... | ... |

**Token Violations**:
- Line 45: `color: #666` → Use `var(--muted)`
- Line 78: `padding: 15px` → Use `var(--space-4)`

**Native UI Violations**:
- Line 102: `<select>` → Replace with custom select

**Top 3 Fixes**:
1. [Most important]
2. [Second]
3. [Third]

Would you like detailed recommendations for any specific issue?
```

---

## CHECKLIST FOR SELF-EVALUATION

Before shipping, verify:

- [ ] All 10 heuristics reviewed
- [ ] Design language consistent
- [ ] No hardcoded tokens
- [ ] No native browser UI elements
- [ ] Accessibility basics met
- [ ] Critical issues resolved
- [ ] Major issues have timeline
