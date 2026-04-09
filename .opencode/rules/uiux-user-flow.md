---
description: Generate Mermaid user flow diagrams from natural language descriptions. Visualize user journeys, conversion funnels, and navigation paths.
---

# UI/UX User Flow Diagrams

> Generate Mermaid user flow diagrams from natural language descriptions.
> Visualize user journeys, conversion funnels, and navigation paths.

---

## OVERVIEW

This skill converts natural language descriptions into Mermaid flowchart diagrams. Use it to:
- Document user journeys before implementation
- Visualize conversion funnels
- Plan navigation architecture
- Communicate flows to stakeholders

---

## INPUT FORMAT

Describe the flow in plain language:
- Use `→` or "then" to indicate sequence
- Use `|` or "or" to indicate branches
- Use `(condition)` to indicate decision points

### Examples

```
"checkout: cart → address → payment → confirmation"
"login: email → (valid?) → dashboard | error"
"onboarding: welcome → profile → preferences → complete"
```

---

## OUTPUT FORMAT

### Standard Flow (Mermaid)

```mermaid
flowchart LR
    A[Cart] --> B[Address]
    B --> C[Payment]
    C --> D[Confirmation]
```

### Flow with Decisions

```mermaid
flowchart TD
    A[Start] --> B{Valid User?}
    B -->|Yes| C[Dashboard]
    B -->|No| D[Login Page]
    D --> E[Enter Credentials]
    E --> B
```

---

## FLOW PATTERNS

### 1. Linear Flow (Checkout)

**Input**: "checkout: cart → shipping → payment → review → confirmation"

**Output**:
```mermaid
flowchart LR
    subgraph Checkout Flow
        A[🛒 Cart] --> B[📦 Shipping]
        B --> C[💳 Payment]
        C --> D[📋 Review]
        D --> E[✅ Confirmation]
    end
```

### 2. Branching Flow (Authentication)

**Input**: "auth: login page → (has account?) → sign in form | sign up form → (valid?) → dashboard | show error"

**Output**:
```mermaid
flowchart TD
    A[Login Page] --> B{Has Account?}
    B -->|Yes| C[Sign In Form]
    B -->|No| D[Sign Up Form]
    C --> E{Valid?}
    D --> E
    E -->|Yes| F[Dashboard]
    E -->|No| G[Show Error]
    G --> C
```

### 3. Multi-Step Form (Onboarding)

**Input**: "onboarding: welcome → personal info → company → team invite → complete"

**Output**:
```mermaid
flowchart LR
    subgraph Onboarding
        direction LR
        A[1. Welcome] --> B[2. Personal Info]
        B --> C[3. Company]
        C --> D[4. Team Invite]
        D --> E[5. Complete]
    end
    
    style A fill:#4D8EFF,color:#fff
    style E fill:#1A7F37,color:#fff
```

### 4. E-commerce User Journey

**Input**: "shopping: browse → product page → add to cart → (logged in?) → checkout | login → checkout → payment → order complete"

**Output**:
```mermaid
flowchart TD
    A[Browse Products] --> B[Product Page]
    B --> C[Add to Cart]
    C --> D{Logged In?}
    D -->|Yes| E[Checkout]
    D -->|No| F[Login/Register]
    F --> E
    E --> G[Payment]
    G --> H{Payment Success?}
    H -->|Yes| I[Order Complete]
    H -->|No| J[Payment Error]
    J --> G
    
    style I fill:#1A7F37,color:#fff
    style J fill:#CF222E,color:#fff
```

### 5. Error Handling Flow

**Input**: "form submission: fill form → validate → (valid?) → submit → (success?) → success page | retry | show errors → fill form"

**Output**:
```mermaid
flowchart TD
    A[Fill Form] --> B[Validate]
    B --> C{Valid?}
    C -->|Yes| D[Submit]
    C -->|No| E[Show Errors]
    E --> A
    D --> F{Success?}
    F -->|Yes| G[Success Page]
    F -->|No| H[Retry?]
    H -->|Yes| D
    H -->|No| I[Error Page]
    
    style G fill:#1A7F37,color:#fff
    style E fill:#CF222E,color:#fff
    style I fill:#CF222E,color:#fff
```

### 6. SaaS Subscription Flow

**Input**: "subscription: pricing page → select plan → (has account?) → create account | sign in → payment → (trial?) → start trial | activate → dashboard"

**Output**:
```mermaid
flowchart TD
    subgraph Subscription Flow
        A[Pricing Page] --> B[Select Plan]
        B --> C{Has Account?}
        C -->|No| D[Create Account]
        C -->|Yes| E[Sign In]
        D --> F[Payment]
        E --> F
        F --> G{Free Trial?}
        G -->|Yes| H[Start Trial]
        G -->|No| I[Activate Subscription]
        H --> J[Dashboard]
        I --> J
    end
    
    style J fill:#4D8EFF,color:#fff
```

---

## GENERATION RULES

1. **Start nodes**: Use rectangle `[text]`
2. **Decision points**: Use diamond `{question?}`
3. **End states**: Style with success/error colors
4. **Grouping**: Use `subgraph` for related steps
5. **Direction**: 
   - `LR` (left-right) for linear flows
   - `TD` (top-down) for branching flows

### Node Shapes

```mermaid
flowchart LR
    A[Rectangle] --> B(Rounded)
    B --> C{Diamond}
    C --> D([Stadium])
    D --> E[[Subroutine]]
    E --> F[(Database)]
    F --> G((Circle))
```

### Styling

```mermaid
flowchart LR
    A[Start]:::start --> B[Process]
    B --> C[End]:::success
    
    classDef start fill:#4D8EFF,color:#fff
    classDef success fill:#1A7F37,color:#fff
    classDef error fill:#CF222E,color:#fff
```

---

## CONVERSION FUNNEL EXAMPLE

**Input**: "funnel: landing page (1000) → sign up (300) → verify email (250) → complete profile (150) → first purchase (50)"

**Output**:
```mermaid
flowchart TD
    subgraph Conversion Funnel
        A[Landing Page<br/>1000 visitors] --> B[Sign Up<br/>300 users<br/>30% conversion]
        B --> C[Verify Email<br/>250 users<br/>83% of signups]
        C --> D[Complete Profile<br/>150 users<br/>60% of verified]
        D --> E[First Purchase<br/>50 users<br/>33% of profiles]
    end
    
    style A fill:#E8F4FD,stroke:#0066FF
    style B fill:#D4EDDA,stroke:#1A7F37
    style C fill:#D4EDDA,stroke:#1A7F37
    style D fill:#FFF3CD,stroke:#9A6700
    style E fill:#D4EDDA,stroke:#1A7F37
```

---

## MOBILE APP NAVIGATION

**Input**: "app navigation: splash → (logged in?) → home | onboarding → home → [products, cart, profile, settings]"

**Output**:
```mermaid
flowchart TD
    A[Splash Screen] --> B{Logged In?}
    B -->|Yes| C[Home]
    B -->|No| D[Onboarding]
    D --> C
    
    C --> E[Products]
    C --> F[Cart]
    C --> G[Profile]
    C --> H[Settings]
    
    E --> C
    F --> C
    G --> C
    H --> C
    
    style A fill:#4D8EFF,color:#fff
    style C fill:#1A7F37,color:#fff
```

---

## AI PROMPT TEMPLATE

When the user asks to create a user flow, use this template:

```
Based on your description: "{user_input}"

I'll generate a Mermaid flowchart diagram. Here's the visualization:

[Mermaid code block]

**Flow Summary:**
- Entry point: {first_step}
- Decision points: {list_decisions}
- End states: {list_outcomes}
- Total steps: {count}

Would you like me to:
1. Add more detail to any step?
2. Include error handling?
3. Show mobile vs desktop variations?
4. Add metrics/conversion rates?
```
