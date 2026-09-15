# Competitors, and what not to claim

Written 14 September 2026. Prices and quotes were taken from vendor pages where
possible; anything second-hand is flagged. Check them again before you pitch —
these move.

---

## The three groups

### 1. Professional desktop tools — not the fight

| Tool | Price | Why it isn't the comparison |
|---|---|---|
| AutoCAD | ~$2,095/year (Autodesk's comparison page; **high-confidence, not first-hand** — their site blocked a direct fetch) | Files live on your machine, needs training, sold to professionals |
| Revit / ArchiCAD | Firm-level pricing | BIM authoring, years of training |

Never open with "AutoCAD in the browser". It is the most commonly made claim in
this category and it invites the one question you cannot answer: *"can it open my
files?"*

### 2. Browser-CAD companies — the real rivals

| Product | What they advertise, in their own words | Sells to |
|---|---|---|
| **Snaptrude** | "Concept to BIM-ready models in your browser" — realtime collaboration with teammates, consultants and clients, ten AI agents, Revit export, SOC 2 | Architecture firms |
| **Arcol** | Realtime client design reviews, live area and cost metrics in the browser | Design teams and their clients |
| **Autodesk Forma** | Autodesk's own browser-based AEC platform; its construction cloud has been folded into Forma | Autodesk's existing customers |

So "browser + realtime + AI" is a funded, occupied category. Nothing above is a
reason to stop — it is the reason the pitch has to be honest about the seat being
taken.

### 3. Easy consumer tools — where the customers actually are

| Product | Price | What it gives |
|---|---|---|
| Planner 5D | Free, with paid upgrades | Drag-and-drop home design, mostly visual |
| Floorplanner | Free + subscription | Browser floor plans |
| RoomSketcher | ~$24/month | Home design with floor plans |
| Cedreo | from ~$59/month | Sells to home builders and remodelers who must show clients a plan |
| Roblox / Minecraft | Free | The fun building people already do — culturally relevant, not a competitor |

**Cedreo is the closest commercial analogue** and the most useful one to
acknowledge: same buyer, similar promise, and it shows people already pay monthly
for "show my client a plan".

---

## The feature-by-feature truth

| Claim you might make | True? | Notes |
|---|---|---|
| "3D design in the browser, no install" | Not unique | Snaptrude, Arcol, Forma, Planner 5D all do this |
| "Realtime multi-user editing of one model" | Not unique | Snaptrude and Arcol ship it |
| "AI-assisted design" | **False here** | There is no model in this product. Do not say it |
| "Client can review without an account" | **Distinctly bundled** | Everyone else requires a seat, an account or a paid licence before a client can comment |
| "A real floor plan comes out of it" | **Distinct for this ease** | Consumer tools generally stop at a rendering; professional tools cost hours |
| "It exchanges IFC/DWG/Revit" | **False here** | JSON export only. IFC 4.3.2.0 is the open standard (ISO 16739, buildingSMART); DWG/DXF are Autodesk-owned with licence constraints; glTF (Khronos) is the realistic first export |

---

## Why you win anyway — and where you don't

**You win on:**

1. **Zero-training ease with real output.** The middle row of the table above is
   empty: simplest tool in the category that still produces a plan and a number.
2. **The review conversation inside the same tool.** Bluebeam and Procore sell
   that loop on its own, to firms with budgets.
3. **Own-your-own-instance.** The app already deploys one Supabase project per
   customer. For government, health or defence buyers that is a buying reason; for
   everyone else it is a footnote.

**You lose on, today:**

1. **Interop.** No IFC, no DWG, no Revit. Any professional evaluates you in one
   question and you fail it.
2. **Enterprise trust.** No SOC 2, no SSO, and row-level security in the database
   is currently wide open (`DEMO.md` §6). Do not sell to a firm until that changes.
3. **Depth.** No structural analysis, no sections, no schedules, no dimensions
   beyond the blueprint export.
4. **Scale.** One developer, no revenue, no customers.

## How to answer the comparison out loud

> "Snaptrude and Arcol are real companies solving the architect's problem, and
> they're better than me at it. My user can't afford an architect's tool and
> doesn't want one — they want a plan, a price and a place to argue about the
> window with their builder. Same category, opposite end of the market."

That answer survives follow-ups. "It's like AutoCAD but in the browser" does not.
