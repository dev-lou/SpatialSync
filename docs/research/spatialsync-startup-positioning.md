# SpatialSync — venture positioning, business model, and startup viability

**Date:** 2026-09-14 · **Subject:** SpatialSync (`dev-lou/SpatialSync`, cloned locally)

> **Update, same day.** Biometric face login has since been removed from the codebase, the
> "dynamic material pricing" claim is now a real planning estimate, the simulated storage
> meter now measures stored geometry, and a no-signup client review link (`/share/{token}`)
> has been added. `DEMO.md` is the current, honest status list.
**Question asked:** Is this SaaS? What is the AI? Is there web3? Can it be called a startup?
What is unique versus AutoCAD-class tools? What is the business model?

**Method.** Product facts come from this repository's own source, cited by file and line —
the code is the primary source for what the software does. Market facts come from vendors'
own sites and documentation. Where a vendor page could not be read directly (JS-rendered or
403), the confidence level is stated rather than hidden. Nothing below is a projected
number; a projection is labelled `[ANALYSIS]`. Everything I could not verify is listed in
§10 instead of being smoothed over.

**Reading guide.** Claims with a source are facts. Claims marked `[ANALYSIS]` are my
judgment and are the part you are allowed to argue with.

---

## 1. The short answer

- **Is it SaaS?** No — it has a SaaS *shell*. Teams, roles, plan tiers and a pricing page
  exist; the checkout is a simulation with no payment processor anywhere in the codebase,
  and the deployment model is one Supabase project per install. A SaaS bills a stranger,
  automatically, and runs many tenants in one system. This does neither yet.
- **What is the AI?** One feature: client-side face recognition for login. No LLM, no
  generative design, no AI-assisted anything. Your marketing pages correctly never claim AI.
- **Is there web3?** No. Not a single line.
- **Can it be called a startup?** No authority adjudicates that, so §7 gives sourced
  criteria and the honest status against them. `[ANALYSIS]` Today it is a strong solo-built
  product prototype. The gap between that and a startup is one paying customer, not more
  features.
- **What is unique vs AutoCAD?** Nothing that AutoCAD defines the market by — and AutoCAD is
  the wrong comparison. The right comparison set is browser-native concept-design startups
  (Snaptrude, Arcol, Autodesk Forma), and those already ship browser + realtime multi-user +
  AI + Revit interoperability + SOC 2. `[ANALYSIS]` Your real differentiator is not a
  capability, it's a **market**: no-install, zero-training, consumer/prosumer spatial
  building with pinned review, i.e. Bloxburg for real houses rather than Revit for
  architects.

---

## 2. Fact sheet — what this software actually is

Audited from the repository. This is the raw material a positioning claim has to survive.

| Capability | Evidence |
|---|---|
| Browser 3D editor (Three.js 0.183) | `package.json` deps `three`, `@types/three`; `resources/js/build-editor.js` (5,000+ lines) |
| Primitive part system: `wall, floor, roof, door, window, stairs` | `database/migrations/2026_04_08_085448_create_build_parts_table.php:17` |
| Item geometry | same file: `position_x/y/z`, `width`, `height`, `depth`, `rotation_y`, `floor_number`, `z_index` |
| Polygon/custom-shape walls | `2026_04_09_090222_add_shape_points_to_build_parts_table.php:15` (`shape_points` JSON) |
| Materials + per-face colours | `2026_04_08_100000_add_wall_colors_to_build_parts.php:12-14` (`color_front`, `color_back`, `material`) |
| Reusable part presets | `app/Models/PartPreset.php`, `/admin/presets` |
| Teams, roles, granular permissions | `app/Models/Team.php`; `BuildController` permission map (`edit_geometry`, `delete_parts`, `can_export_build`) |
| Issue tracker (pin feedback to model) | `app/Models/BuildIssue.php`, `/editor/builds/{id}/issues/*` |
| In-editor chat | `app/Models/BuildMessage.php` |
| Public share links | `app/Models/BuildShare.php`, `/builds/{build}/shared/{token}` |
| Export | `BuildController::export()` writes **JSON only**; any other format hits `abort(404, 'Export format not supported')` (`app/Http/Controllers/BuildController.php:485-511`) |
| Realtime multi-user sync | `database/migrations/enable_supabase_realtime.sql` adds `build_parts` + `build_messages` to the `supabase_realtime` publication; client subscribes on channel `build:{id}` (`resources/views/builds/show.blade.php:1204`) |
| Auth | Supabase-backed session auth (`SupabaseAuthenticate`), plus guest client-review sessions for share links (`app/Http/Middleware/ResolveCollaborator.php`). Face login was **removed on 2026-09-14** — see §8 |
| Admin panel | `AdminController` (users, presets, builds) |
| Client review links | `/share/{token}` — no-signup view + comment, 64-char token, 30-day expiry, revocable (`BuildController@guestShow`, `build_shares`) |
| Billing | `/pricing` tiers Free / Pro $19→$15 / Enterprise $49→$39; `/checkout/{plan}` is commented as a **demo checkout with no payment provider connected** (`routes/web.php`, `CheckoutController`), and `process()` applies the plan immediately after validating it against a fixed list; **no Stripe/Paddle/billing code exists** |
| Deployment | `render.yaml`, Render free-tier keep-alives (`/ping` route, 5-second realtime heartbeat in `show.blade.php:1170`) |
| Origin of the product | First two commits: "feat: setup **Construct 3D Build Editor** with placement physics…" and "feat: Implement **Bloxburg-style** Custom Polygon Draw tool…" (`git log --reverse`) |
| Automated tests | 4 test files / 37 tests: middleware unit tests, unit tests for the guest-review and cost-estimate helpers, and a feature suite driving the client share link end to end (`tests/Feature/GuestReviewLinkTest.php`). **CI runs PHPUnit** (`.github/workflows/ci.yml`, `php-quality`). At the start of this pass it was a single 196-line unit test that no workflow executed |

**Origin matters.** The product began as a *game-style building editor*, evidenced by the
Bloxburg reference and "placement physics". That is a consumer artifact, not a drafting
artifact, and it is the most honest clue to where the product's advantage actually lives.

---

## 3. "What is the AI here?"

**The only machine learning in the product is face recognition.** `face-api.js@0.22.2` is
loaded from a CDN and runs three networks in the visitor's browser — `tinyFaceDetector`,
`faceLandmark68Net`, `faceRecognitionNet` — to enrol and verify a face for login
(`resources/views/layouts/auth.blade.php:491-528`, `resources/views/profile/show.blade.php`,
weights vendored at `public/js/face-models/`).

There is **no** LLM, no generation, no model-provider SDK, no embedding store:

- `composer.json` requires only `laravel/framework`, `laravel/jetstream`, `laravel/sanctum`,
  `livewire/livewire`, `saeedvir/supabase`.
- `package.json` dependencies are `three`, `@types/three`, `alpinejs`, `axios`, `fabric`,
  `@supabase/supabase-js`.
- Grepping the app for `openai|anthropic|gemini|gpt-|llm|embedding` returns nothing.

So: **you do not have an AI product, and you also are not making a false AI claim** — I
checked `home`, `features`, `pricing` and `about` and none of them asserts AI. That is a good
position to be honest from. Note also that "we use AI to build our app" is not product AI and
carries no weight with a customer or judge.

`[ANALYSIS]` The AI opportunity is not "add a chatbot". If you take the collaborative-review
position in §6, the two AI features that would actually differentiate are (a) reading a
pasted brief/plot description and producing a first massing pass, and (b) auto-generating the
issue list from a walkthrough. Both are copies of moves Snaptrude already advertises, so they
buy parity, not advantage.

---

## 4. "Is there web3?"

No. Greps for `web3|ethers|wagmi|viem|solana|solidity|nft|blockchain` across `app`,
`resources`, `routes`, `config`, `database` match nothing; the earlier raw hits were binary
`.webp` image frames and vendored model files, i.e. false positives.

`[ANALYSIS]` web3 adds no defensibility here and imports regulatory and reputational
baggage. A building model is not a bearer asset, and no professional buyer currently selects
a design tool on token mechanics. If the idea is provenance/sign-off audit trails, a plain
append-only audit table does that better and is understood by every compliance reviewer.

---

## 5. "Do I have SaaS?" — the five tests

| Test | Status |
|---|---|
| Multi-tenant (many customers, one deployment, isolated) | **Fail.** One Supabase project per install via `.env` (`SUPABASE_URL`, `SUPABASE_SERVICE_KEY`, pooler creds); `docs/`-less app ships its own database config. This is a self-hosted app. |
| Self-serve signup → pay | **Fail.** Registration exists; payment does not. |
| Recurring billing, dunning, plan enforcement | **Fail.** Checkout is simulated; no processor, no webhooks, no subscription records. `can_export_build`-style feature flags exist but nothing gates on *paid* tier. |
| Usage metering / quota enforcement | **Fail.** No counters tied to a plan. |
| Operational maturity a paying customer needs (backups, monitoring, SLA, audit log) | **Fail.** Free-tier keep-alive hacks indicate infrastructure that is not production-grade yet. |Nothing in that table passes, and that is the whole answer to "do I have SaaS?". What you have are the *artifacts* of a SaaS — team and role concepts, a pricing page, and a checkout flow that ends in a success screen. Those artifacts are exactly what make a project look finished while billing, tenant isolation and quota enforcement — the parts a customer's money actually depends on — are absent.

`[ANALYSIS]` Priorities, in order: (1) real billing; (2) a single-tenant-per-customer hosted
option so a buyer never touches a dashboard; (3) tenant isolation in one database if you want
per-seat SaaS economics. Trying to be both BYO-cloud and true multi-tenant SaaS is what
makes this feel unfinished; pick one as the default and sell the other as enterprise.

---

## 6. What is unique — versus AutoCAD, and versus what actually competes

### 6.1 The prices you are being compared against

Sourced from the vendors' own sites (see §9 for the URLs and confidence notes):

| Product | Published price | Browser or install | Notes |
|---|---|---|---|
| Autodesk AutoCAD | $2,095/yr, or $260/mo | install (+ web/mobile) | Full 2D/3D CAD; the industry default |
| Autodesk AutoCAD LT | $540/yr, or $70/mo | install | 2D drafting only |
| Autodesk Forma | quote-based | browser | Autodesk's own AI-native AECO cloud; absorbed Autodesk Construction Cloud (announced Feb 2026) |
| Trimble SketchUp Go | ~$129/yr (official page shows $10.75/user/mo annual) | browser + app | Consumer/prosumer entry |
| Trimble SketchUp Pro | ~$399/yr | install | The professional sketch tool |
| Trimble SketchUp Studio | ~$819/yr | install | Pro + point cloud/BIM extras |
| Snaptrude | quote / free trial | **browser** | AI-native BIM for architects |
| Arcol | paid, pricing page | **browser** | browser-first building authoring |

Two conclusions fall straight out of that table. First, **AutoCAD LT is $540 a year and your
Pro tier is $228 a year** — you are not competing on price against CAD, you are priced like a
consumer app, so your buyer is not a CAD seat holder. Second, and more important:

### 6.2 Your differentiators, checked against what already exists

I read Snaptrude's and Arcol's own sites rather than summaries of them, because this is the
claim your whole pitch rests on. Snaptrude's homepage advertises, in its own words: *"Concept
to BIM-ready models in your browser"*, *"Real-time collaboration — Collaborate in one
centralized place with teammates, consultants or clients"*, AI agents for programming,
massing and zoning, one-click "Mass to BIM", **Revit and Rhino export**, and *"SOC Type 2
Compliant"* security. Arcol's homepage advertises agentic design workflows, real-time design
reviews with clients, live metrics and estimating.

Now lay your feature list against that:

| Claim you might make | Already true of a funded competitor? |
|---|---|
| "3D architectural design in the browser, no install" | **Yes** — Snaptrude, Arcol, Forma |
| "Real-time multi-user editing of one model" | **Yes** — Snaptrude, Arcol |
| "Collaborate with clients/consultants in one place" | **Yes** — both, as a headline |
| "AI-assisted design" | **Yes** — both, far beyond what you have |
| "Feedback pinned to the model / issue tracking" | **Yes** — established coordination category (Bimsync, Procore, Bluebeam); theirs is a product, yours is a feature |
| "Export to professional tools" | **You lose** — you export JSON only |
| "Enterprise-grade security / SOC 2" | **You lose** — they hold SOC 2; you have an admin panel |

`[ANALYSIS]` So the honest answer to "what is unique vs AutoCAD?" is: **nothing of what
AutoCAD is for, and nothing of what the browser-native challengers already do either.** "It's
AutoCAD in the browser" is not an insight in 2026; it is the established thesis of at least
three better-funded products. Positioning against AutoCAD is also strategically bad — you
inherit a feature checklist (DWG, layouts, annotations, plot styles, exactness) that you
cannot win, and you invite a comparison a judge will make instantly.

### 6.3 Where the real, defensible difference sits

`[ANALYSIS]` Four candidates, ordered by how much evidence in this repo supports them:

1. **The zero-training consumer/prosumer on-ramp.** Your primitives, presets, placement
   physics, polygon draw and skybox are a *building toy* in the best sense: a homeowner,
   renovator, realtor or small builder can place walls without being taught CAD. Snaptrude
   and Arcol sell to architecture firms and their pricing and vocabulary reflect that. The
   Bloxburg lineage is a genuine asset here and you currently treat it as an embarrassment.
2. **Review as the product, not scaffolding.** Issue pins + chat + roles + share links is a
   coherent "send the client a link, they mark it up, everyone sees it" loop. That loop is
   what firms pay Bluebeam/Procore/Bimsync for separately from their design tool. Owning the
   *conversation around the model* is a viable wedge that does not require beating anyone's
   modeling.
3. **Data residency / BYO-cloud.** Because each install brings its own Supabase project, you
   can honestly sell "your models never sit in our cloud" — which matters to government,
   defense, healthcare and privacy-constrained buyers, and is awkward for SaaS incumbents to
   copy without cannibalising revenue. Today this is an accident of architecture; it could be
   a deliberate product.
4. **Biometric login.** Not a differentiator. See §8 — removed on 2026-09-14; it was a liability.

And the thing you should *not* assert: that you are more innovative, more modern, or
"AI-powered" than incumbents. A judge or investor who has seen Snaptrude will dismantle that
in one question, and losing that credibility costs you the whole pitch.

---

## 7. Can this be called a startup?

No official body certifies the word, so here are the criteria the term is actually used to
mean, with your status against each. `[ANALYSIS]` follows each row.

| Criterion | Status today |
|---|---|
| Solves a problem someone will pay to have solved | Unproven — zero payment path exists |
| Charges money, repeatably | **No** — checkout is simulated |
| Can grow without linear cost in labour | Partly — software, but the BYO-database model makes each new customer an onboarding project |
| Defensible against well-funded competition | Weak (§6.2) |
| Team | One primary author — `git shortlog -sn --all` shows 91 commits across the `Lou` / `dev-lou` / `Lou Vincent Baroro` aliases — plus a handful of contributing humans (`Sean`, `Allysandra`, `Linterna`, `jayr`) and bots |
| Verifiable demand evidence (retention, usage, letters of intent) | None in the repo. No analytics dependency (deps are only `three`, `alpinejs`, `axios`, `fabric`, `supabase-js`), no usage instrumentation. The 37-test suite verifies behaviour, not demand |
| Market size | Large. Occupied. Large and occupied are different problems. |

`[ANALYSIS]` **Verdict: not yet a startup — it is a strong product prototype.** The
distinction is not effort or quality; you have both. It is that a startup is defined by a
business that takes money and can repeat the process, and every part of that loop on your
side is currently a mock. Anyone can call their project a startup and nobody can stop them,
but a judge asking "who pays you, and for what?" ends the conversation unless you have an
answer, and today the codebase's answer is a success page.

The three things that would flip it, in priority order:

1. **Take a single real payment.** Stripe Checkout, one plan, enforce the entitlement in
   code so a Free user is actually blocked from a Pro feature. One paying customer converts
   this from mock to business more than any feature will.
2. **Pick one buyer and stay on them.** Consumer renovator, realtor/visualisation, small
   builder, and architect are four different products with four prices. Choose one, talk to
   five of them, and let their language replace yours on the pricing page.
3. **Own an interop or workflow wedge** (§6.3, and §8 for interop) so you are not the
   strictly-worse version of Snaptrude.

---

## 8. The two hard technical facts you cannot pitch around

**Interoperability.** The built-environment industry exchanges data through IFC
(Industry Foundation Classes), maintained by buildingSMART and published by ISO as
ISO 16739 — the current official version is IFC 4.3.2.0 (buildingSMART's own standards page).
DWG/DXF are Autodesk-owned formats with licensing constraints on third-party
implementations, and Revit (.RVT) has no open public reader. glTF (Khronos) is the realistic
web-friendly 3D interchange. **Today SpatialSync exports JSON and nothing else.** So an
architect or engineer cannot put a real project into it or take one out. `[ANALYSIS]` This
single gap is why "CAD for architects and engineers" is not the market to claim yet: the
claim invites the one question ("can it open my files?") whose answer is no. Either target a
market that does not speak IFC (consumer, realtor, early-stage visualisation) or implement
glTF export first as a cheap, honest step toward interop.

**Biometric data (resolved).** Face login was removed on 2026-09-14, and
`database/migrations/2026_09_14_remove_biometric_data.sql` clears any stored template. It was
exactly the liability described below: it stored a biometric template for identification. Under
the **Illinois BIPA** statute (740 ILCS 14/15(b), 14/20 — the Illinois General Assembly's own
published text) that requires a written release before collection and carries a **private
right of action with liquidated damages of $1,000 per negligent violation and $5,000 per
intentional or reckless violation**, and BIPA class actions have been a well-documented
industry pattern (the 2024 amendment limited per-person accrual but did not remove the risk).
Facial templates used for identification are also special-category data under **GDPR
Article 9**, which requires an explicit lawful basis and drags in retention limits and a
DPIA. `[ANALYSIS]` A face-login feature is a liability line item that wins you no customers
and can, on its own, make a US enterprise buyer disqualify you. Make it opt-in and off by
default, delete templates on account deletion, document retention, or remove it from the
product. This is the one item on this page that I would change before any pitch.

---

## 9. Business model — options, and what each costs to have

| Model | What it requires | Evidence it works |
|---|---|---|
| Per-seat SaaS ($15–19/user/mo, already scaffolded) | Real billing + plan enforcement + tenant isolation in one database | SketchUp Pro ~$399/yr (~$33/mo), AutoCAD LT $540/yr (~$45/mo) — your price sits below both, so you must justify it as a *different* tool, not a cheaper one |
| Per-project / per-package | Metering by project, one-off checkout, no subscription machinery | Common in AEC services; fits "client link per project" |
| Enterprise self-host / BYO-cloud licence | Support, install docs, upgrade path, security questionnaire answers | Matches your current architecture exactly — the cheapest model to actually ship |
| Preset/content marketplace | Curation, licensing, payments, contributor payouts; `PartPreset` already exists as the substrate | Content marketplaces are established in 3D (asset libraries) |
| API/embed (put a spatial viewer in someone's site) | Public API, keys, docs, rate limits | Requires the interop story first |
| Education | Free tier, verification, classroom tooling | SketchUp gives schools a free tier (its own pricing page) |

**Unit economics, from Supabase's own Realtime pricing page** (fetched directly, not
paraphrased): messages — Free 2M included, Pro 5M included, then **$2.50 per 1M**; peak
connections — Free 200 included, Pro/Team 500 included, then **$10 per 1,000**.

`[ANALYSIS]` Read those two numbers against your product. A collaboration session is a
message stream; every geometry nudge is a realtime event, so 5M messages/month is a genuine
ceiling for a busy multi-tenant deployment (≈166k edits/day before overage). And 500
concurrent connections on Pro is your whole paid tier's ceiling on one project — which is
fine, because your BYO-database model pushes that cost onto the customer. That is the
sharpest business argument for the self-hosted model: **on hosted realtime, a room full of
concurrent editors is your cost; on BYO-cloud, it is theirs.** Conversely it means per-seat
SaaS pricing on a shared project sets your gross margin against a metered bill you do not
control. Render hosting pricing was not verified in this pass — treat hosting cost as an open
line item.

`[ANALYSIS]` Recommended shape: **freemium single-player (consumer, free, BYO or hosted
tiny) → per-seat team tier for the review loop → enterprise self-host licence.** The
marketplace is a later, better-margin layer once you have an audience to curate for, not a
launch strategy.

---

## 10. Could not verify / open questions

Stated rather than filled in:

- **SketchUp's exact current USD list prices.** The official plans page loaded without
  prices (client-rendered); the figures in §6.1 come from the official domain's own search
  snippet ($10.75/user/mo annual for Go) plus widely-reported annual figures. Re-verify
  on the live pricing page before putting any of it in a deck.
- **Autodesk prices.** `autodesk.com/products/autocad/compare` returned **403** to direct
  fetch; the $2,095/yr / $540/yr / $260/mo / $70/mo figures come from Autodesk's own pages as
  surfaced through the search index. High confidence, not first-hand.
- **Snaptrude and Arcol list prices** — both are quote/trial-gated in what I could read.
- **Render's hosting tiers** and what a Laravel app of this shape actually costs to run.
- **Licence terms of `face-api.js` 0.22.2 and its CDN-hosted model weights**, and whether
  shipping them plus server-side face templates creates an additional licensing or
  data-processing obligation.
- **The actual competitive set for a consumer/DIY buyer** (Planner 5D, Homestyler and
  similar) — I did not open their sites in this pass, and they are the honest comparison if
  you take the consumer wedge.
- **Whether any of your existing users exist at all**, and what they use it for. No analytics
  or usage data is present in the repo.

---

## 11. Sources

Product facts: this repository (`git log`, `composer.json`, `package.json`,
`app/**`, `database/migrations/**`, `resources/**`) — accessed 2026-09-14.

- Supabase Realtime pricing — <https://supabase.com/docs/guides/realtime/pricing> (fetched directly)
- Supabase Realtime limits — <https://supabase.com/docs/guides/realtime/limits>
- AutoCAD pricing/plans — <https://www.autodesk.com/products/autocad/compare> (403 on fetch; figures via Autodesk's own indexed pages), <https://www.autodesk.com/solutions/autocad-subscription-faq>
- Autodesk Forma / construction cloud consolidation — <https://adsknews.autodesk.com/en/news/autodesk-construction-cloud-to-join-forma/>
- SketchUp plans and pricing — <https://sketchup.trimble.com/en/plans-and-pricing>
- Snaptrude (browser AI BIM, realtime collaboration, Revit export, SOC 2) — <https://www.snaptrude.com/>
- Arcol (browser authoring, realtime design review) — <https://arcol.io/>
- IFC / ISO 16739 — <https://www.buildingsmart.org/standards/bsi-standards/industry-foundation-classes/>
- Illinois BIPA statutory text — <https://www.ilga.gov/Documents/legislation/ilcs/documents/074000140K20.htm>; <https://law.justia.com/codes/illinois/chapter-740/act-740-ilcs-14/>

All URLs accessed 2026-09-14.
