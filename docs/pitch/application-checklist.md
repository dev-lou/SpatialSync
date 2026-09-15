# Startup challenge — application checklist

What competitions actually score, what to prepare, and the honest language to use
about where the product is.

---

## 1. What judges score (and where you stand)

Every competition words it differently; the criteria repeat:

| Criterion | What it means | Where SpatialSync stands today |
|---|---|---|
| Working product | Can you drive it? | **Strong** — a real 3D editor, realtime collaboration, blueprint export, client review links |
| Customer validation | Have real people used it and said something? | **Weak** — no external users yet |
| Traction | Revenue, users, growth | **Absent** — none |
| Market | Is the market large and reachable? | **Good** — home improvement and small-scale construction are enormous and reachable without an enterprise sales team |
| Team | Can these people build it? | **Thin** — one developer; be upfront, not apologetic |
| Defensibility | Why won't this be copied? | **Position, not patent** — see `competitors.md` |

New York University's Entrepreneurs Challenge states the bar plainly: teams with a
**working product or prototype, demonstrated customer validation, and early signs
of traction** are most competitive. Pitch-competition rubrics usually add market
opportunity and team strength.

**Read this as: the application is not the bottleneck. The missing half is
validation and traction.**

## 2. Eligibility — check the specific competition

Rules differ and are disqualifying if missed. Before applying, confirm:

- [ ] **Founder status.** Some require an enrolled student founder (Columbia's AI
      Startup Challenge, for example, requires a founder enrolled that academic
      year). If you are not a student, filter these out early.
- [ ] **Legal entity.** Some require a registered company or an intent to
      incorporate; others require the opposite (pre-incorporation only).
- [ ] **Funding stage.** Most exclude companies that have raised above a threshold.
- [ ] **Geography.** Many are regional (city, state, university network).
- [ ] **Team size / exclusivity.** Some forbid entering the same idea elsewhere
      concurrently.
- [ ] **Deliverable list.** Deck, video, financials — note the formats and deadlines.

## 3. Assets to prepare

| Asset | Where it comes from | Status |
|---|---|---|
| Pitch deck (10 slides) | `deck-outline.md` | Outline ready; needs building |
| Live demo, 90 seconds | `demo-script.md` + `DemoHouseSeeder` | Script and seed ready; needs rehearsal |
| Recorded demo video | Screen recording of the same path | To do (do this even if the live demo works) |
| One-pager | `one-pager.md` | Ready |
| Competitor slide | `competitors.md` | Ready |
| Team slide | Honest: one developer, plus anyone who joins | To do |
| Financial one-pager | Three planned tiers ($0 / $19 / $49), the Supabase cost driver, and a bottom-up "100 Pro users = $1,900/mo" line | To do |
| Traction statement | Use the template below | Ready |

## 4. Honest traction language

Use this verbatim until the facts change:

> "SpatialSync is a working product with no paying customers yet. The 3D editor,
> realtime collaboration, role permissions, blueprint PDF export and client review
> links are built and running. Checkout is a sandbox — no payment provider is
> connected and no card is charged. My next milestone is not a feature: it is one
> client reviewing a real project through a link, and one person paying $19."

Why say it: a judge who discovers an overstatement stops believing everything
else. A judge who hears the limit from you starts treating the rest as credible.

## 5. Pre-submission verification

Run these and fix anything red before submitting.

- [ ] `vendor/bin/phpstan analyse` — clean at level max.
- [ ] `vendor/bin/phpunit --no-coverage` — unit suite green (this is what CI runs;
      `php artisan test` does not exist in this Laravel version).
- [ ] `npm run build` — production assets build.
- [ ] Walk the whole demo path twice, once in a private window for the client side.
- [ ] `php artisan db:seed --class=DemoHouseSeeder` on the environment you will
      demo from.
- [ ] The public URL loads and the sign-in page works.
- [ ] `database/migrations/2026_09_14_remove_biometric_data.sql` has been run
      against the live database.
- [ ] Old demo accounts removed or re-passworded.
- [ ] No secrets, cookies or local dumps in the repository: `git grep -nE
      'eyJhbGciOi|sb_secret|service_role'` on tracked files.
- [ ] `DEMO.md` read by whoever is presenting.

## 6. What would make the application competitive

Ranked by impact per hour of work:

1. **One paying customer.** Even $19 manually invoiced. It converts "prototype"
   into "business" and is the single strongest line in any application.
2. **One real client review through a link** — screenshots of a stranger's pinned
   comment on a wall, with their permission.
3. **Lock down row-level security.** Selling "your plans stay private" while every
   table is world-readable is the sharpest thing a technical judge can find.
   (`DEMO.md` §6.)
4. **A recorded 90-second demo.** Removes all execution risk from the room.
5. **A second pair of hands.** One developer is a team slide problem, not a
   capability problem — say what you'd hand off first.

## 7. Do not claim

- **AI.** There is none. `DEMO.md` §7 explains what was removed and why.
- **Payments or billing.** Checkout is a sandbox. Say "pricing is designed; billing
  is not connected".
- **IFC/DWG/Revit interop.** JSON export only.
- **"First" or "only".** Snaptrude, Arcol and Autodesk Forma are all live in this
  category and a judge may have seen them this month.
- **Enterprise readiness.** No SOC 2, no SSO, and row-level security is open.
