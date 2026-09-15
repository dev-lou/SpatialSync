# Deck outline — 10 slides

One idea per slide, one sentence spoken under it. Numbers are the ones you should
say out loud, verified against the app and against vendor pages (see
`competitors.md` for sources).

---

### 1. Title

**SpatialSync — build a house by dragging things, get a price and a plan.**
Sub-line: *The simplest 3D building tool that still ends in something you can
build from.*
Say: nothing. Let the seeded house screenshot sit there.

### 2. The problem

Three tools to do one job:

- Draw it → AutoCAD (~$2,095/yr) or Revit, both needing training.
- Price it → a spreadsheet, re-typed by hand.
- Discuss it → email, where the comment about the window is attached to nothing.

Say: "The drawing, the number and the conversation live in three places, and two
of them need a professional."

### 3. Who this is for

Homeowners renovating. Small builders and remodelers. Students. The designers who
serve them.
**Not** architects at firms — they already have tools and they are not switching.

Say: "I'm not trying to win the architect. I'm trying to win everyone the
architect quotes."

### 4. The product — live, not screenshots

One sentence per capability:

- Drag-and-drop 3D building in a browser, snapped to a grid, ten floors, nothing
  to install.
- A planning estimate that moves as you build.
- A real floor-plan PDF out — A4/A3, 1:50–1:200.
- Realtime collaboration: the same model, live, with chat and roles.
- A client review link: no account, comments pinned onto the actual wall.

Say: "Four of those five are the easy tool's features. The second and third are
the professional tool's. Nobody has both."

### 5. Demo

90 seconds, live. Script in `demo-script.md`. If the demo cannot run, say so and
show the recorded take — do not improvise screenshots.

### 6. Why now

- Browser 3D is finally good enough to be the product, not a preview.
- Realtime infrastructure is now a service line item (Supabase Realtime includes
  500 concurrent connections, then $10 per 1,000; 5M messages/month included, then
  $2.50 per 1M) — collaboration no longer needs a platform team.
- Every browser-CAD entrant has moved **up** into firms, leaving the on-ramp empty.

### 7. Competition — the honest table

Two columns of rivals, one seat in the middle.

| | Browser | Easy enough for a homeowner | Real output | Sells to |
|---|---|---|---|---|
| AutoCAD / Revit | No | No | Yes | Professionals |
| Snaptrude | Yes | No | BIM + Revit export | Architects |
| Arcol | Yes | No | Client design review | Design teams |
| Planner 5D / Floorplanner | Yes | Yes | Picture only | Consumers |
| **SpatialSync** | **Yes** | **Yes** | **Plan + estimate + review loop** | **Everyone else** |

Say: "The top row needs training. The bottom row stops at a picture. That middle
row is where I live."

### 8. Business model — planned, not yet charged

| Tier | Price | Why it exists |
|---|---|---|
| Free | $0 | Learn it, one project |
| Pro | $19/mo, $15/mo annual | Projects, estimates, blueprint PDF, client links |
| Enterprise | $49/mo, $39/mo annual | Team seats, roles, self-hosted |

Say: "Under AutoCAD LT's ~$540 a year, deliberately. Price isn't the wedge. Ease
is. And on the Enterprise tier the customer runs their own instance, so their
concurrency is not my bill."

### 9. Traction — say the real number

- Working product: yes, and you can drive it.
- Paying customers: **zero**.
- Client review links used by a real client: **not yet** — shipped 14 Sep 2026.
- Team: one developer.

Say it before they find it: "I'm early. The honest line is one project, one
developer, no revenue — and the next result that matters isn't a feature, it's one
client reviewing a real project through a link and one person paying $19."

### 10. Roadmap and ask

| Now | Next 90 days | After that |
|---|---|---|
| Demo-grade product, sandbox checkout | Real payments; lock down row-level security; IFC or glTF interop | Template gallery, asset library, Enterprise self-host tier |

Ask: "Introductions to small builders and remodelers who already pay someone to
draw plans. That's the only thing that changes this pitch."

---

## Delivery rules

1. **Show, don't slide.** Slide 4 is the only place a screenshot appears; slide 5
   is hands on keys.
2. **Never claim AI.** See `DEMO.md` §7 — the product has none, and the only thing
   that looked like it was removed.
3. **Volunteer your weakness first.** Zero customers, one developer, no interop.
   A judge who hears it from you stops hunting for it.
4. **End on the number.** "One client reviewing a real project, one person paying
   $19" is more memorable than any feature list.
