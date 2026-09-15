# SpatialSync — one-pager

*Status: 14 September 2026. Written for a startup challenge; every claim here is
checkable against the repository. Where something is planned rather than built,
it says so.*

---

## One line

**Drag objects into a 3D scene in your browser and a house appears — then it ends
in a price, a plan, and a conversation.**

## The problem

Everyone who wants to change a space hits the same wall. The tools that produce
real construction output — AutoCAD, Revit, ArchiCAD — need years of training and
cost over $2,000 a year. The tools that are easy — Planner 5D, Floorplanner,
Roblox — produce a picture, and a picture is not something you can price, build,
or argue with your contractor about. So the drawing happens in one tool, the
budget in a spreadsheet, and the conversation in a third place, in email, where
the comment about the window is attached to nothing at all.

## Who it is for

**People who will never learn CAD but still need a real answer:** homeowners
renovating, small builders and remodelers, students, and the designers who serve
them. This is the on-ramp the industry skipped — not the professional's desktop
replaced.

## What it does

1. **Build by dragging.** Walls, floors, roofs, doors, windows, stairs and
   furniture from a palette, snapped to a grid, in 3D, in the browser, on any
   machine. Ten floors supported. Nothing to install.
2. **See a price as you build.** A running planning estimate updates the moment
   a part is placed, moved or deleted.
3. **Get a plan out.** One click produces a real floor-plan PDF — A4 or A3, at
   1:50, 1:100 or 1:200, all floors or one.
4. **Bring people in without accounts.** Generate a review link; your client
   opens it in a browser, gives a name, and pins comments straight onto a wall.
   Your co-designer can edit the same model at the same time, live.

## Why this is a real gap

The browser-CAD seats are taken: **Snaptrude** sells "concept to BIM-ready models
in your browser" with real-time collaboration and Revit export to architects;
**Arcol** sells real-time client design reviews; **Autodesk Forma** is Autodesk's
own browser answer. They all sell *up* into firms. Meanwhile the easy tools sell a
rendering and stop.

Nobody is holding both ends: **the ease of a drag-and-drop tool and the output of
a real one**. That is the seat SpatialSync is built for, and unlike a feature it
cannot be copied by adding a menu item — it changes who the product is for.

## The wedge, in order

1. **Zero-training 3D that ends in a deliverable.** Simplest tool in the category
   that still produces a plan and a number.
2. **The review loop.** Pins + chat + roles + a no-signup client link, inside the
   same tool. Firms buy Bluebeam or Procore for that loop alone.
3. **Your data on your own Supabase project.** One instance per customer is how
   the app already deploys — an accident that turns into a genuine answer for
   privacy-sensitive buyers.

## Business model (planned — nothing is being charged yet)

| Tier | Price | What it is for |
|---|---|---|
| Free | $0 | Single player, one project, learn it in an afternoon |
| Pro | $19/mo or $15/mo billed annually | Unlimited projects, cost estimates, blueprint PDF, client review links |
| Enterprise | $49/mo or $39/mo billed annually | Team seats, roles, self-hosted deployment |

Annual Pro ($228/yr) sits deliberately **below** AutoCAD LT (~$540/yr) — price is
not the wedge; ease is. On hosted realtime, concurrent editors are the vendor's
cost (Supabase Realtime includes 500 concurrent connections, then $10 per 1,000),
which is the commercial argument for the self-hosted tier.

## Where we actually are

- **Working product:** 3D editor, multi-floor building, presets and snapping,
  realtime collaboration with chat and presence, role-based permissions, issue
  pins, blueprint PDF export, JSON export, admin panel, marketing site.
- **Users:** none paying. No revenue. One project, one developer.
- **Honest gaps:** no IFC/DWG/Revit import or export; row-level security in the
  database is currently open; checkout is a sandbox with no provider connected;
  the client review link shipped 14 September 2026 and has not been used by a real
  client yet.

The next result that changes this pitch is not a feature. It is **one client
reviewing a real project through a link, and one person paying $19.**

## Roadmap

| Now | Next | Later |
|---|---|---|
| Demo-grade product, sandbox checkout | Real payments, locked-down row-level security, IFC or glTF interop | Template gallery, asset marketplace, Enterprise self-host tier |

## The ask

A challenge win buys runway and credibility, not a business. What is needed is
the room's introductions to **small builders and remodelers** — the people who
already pay for sketched plans and would pay $19/month to stop paying for the
third revision.
