# The 90-second demo

One continuous run, no slides, no dead air. Rehearse it until the clicks are
muscle memory — judges forgive small bugs, not fumbling.

**Before you start**

- `php artisan db:seed --class=DemoHouseSeeder` and confirm *Demo House —
  Riverside Cottage* opens.
- Two windows ready: the owner's window (signed in) and a **private/incognito**
  window for the client (nothing signed in).
- The Build tab open at the editor, not the marketing site.
- Zoom the browser to ~110% so the toolbar chip is readable on a projector.

---

## The script

### 0:00 — Set the scene (one sentence, no preamble)

> "Every tool that draws a house properly needs years of training. Every tool
> that's easy draws a picture you can't build from. I'm going to build part of a
> house in ninety seconds, get a price for it, get a real plan out of it, and
> then hand it to a client to leave comments on — without them making an account."

### 0:10 — Build by dragging

Drag a wall from the left panel onto the grid. Place two more. Snap one onto the
end of another.

> "Drag, drop, snapped to the grid. No install, no tutorial, no CAD."

**Carry on if:** you get three walls down. Do not aim for a perfect model.

### 0:30 — The price

Point at the **Planning estimate** chip in the toolbar.

> "That number is moving as I build. Walls by the square metre, doors and windows
> per unit. It says *estimate*, not quote — the rates are ours to tune."

Place one more wall so the number visibly jumps. This is the beat people remember.

### 0:45 — The plan

Open the export menu → **Export Blueprint PDF** → *Generate PDF*.

> "That's a real floor-plan drawing — A4, one to a hundred, every floor. This is
> the thing the easy tools don't give you, and the thing the professional tools
> make you spend an hour on."

Show the opened PDF for two seconds. Do not read it out.

### 1:05 — The client, with no account

In the sidebar → **Client Review Link** → **Generate Client Link**. Copy it, and
switch to the incognito window. Paste and open.

> "I send this to my client. Watch what they have to do to get in."

The client page asks for a name. Type `Maria (client)`. Land in the model.

> "No account, no email, no password. She's in the actual 3D model."

### 1:20 — The comment

As Maria, click a wall, write *"This window sits too high"* and pin it. Switch
back to the owner's window.

> "And it's already on my model — on the wall she was talking about, with her
> name on it."

### 1:30 — Close

> "Cheapest on-ramp in the category, and the only one where the client's comment
> lands on the wall it's about. That's the whole product."

Stop talking. Let them ask.

---

## Answers you will need

Have these ready — one line each, no hedging.

| Question | Answer |
|---|---|
| "Is this AI?" | "No. There is no AI in this, and I won't claim there is. Every feature you just saw is deterministic — that's why it's fast." |
| "Is it a competitor to AutoCAD?" | "No. AutoCAD is for the people who already know how to use AutoCAD. This is for everyone who doesn't." |
| "What about Snaptrude and Arcol?" | "They sell into architecture firms and they're good at it. They start where this finishes — with a real building. We're the on-ramp." |
| "Is this SaaS?" | "The product is; the billing isn't yet. Checkout is a sandbox — no card is charged today." |
| "Do you have customers?" | "Not paying ones. One project, one developer. The client review link shipped this week." |
| "How is this defensible?" | "Not by features — by who it's for. Simplify-first is a position you hold by refusing to add the professional features that make a competitor's tool feel like work." |
| "Can it open my Revit file?" | "No. JSON export only today. IFC interop is on the roadmap, after payments." |

**Never say:** "the first", "the only", "AI-powered", "AutoCAD-level", "we process
payments". Each one invites a single question that ends the pitch.

---

## If something breaks

- **Editor won't load:** reload once. If it still fails, go to the seeded house and
  continue from 0:30 — the price, the plan and the client link still demo.
- **Client link page won't open:** the link has a 30-day expiry and can be
  revoked; generate a fresh one from the sidebar.
- **Realtime doesn't visibly sync:** don't wait for it. Say "the client's comment
  is stored against the wall — here it is on my side" and keep moving.
- **Never debug live.** Narrate past it: "this one's slow on demo wifi — here's
  the part that matters."
