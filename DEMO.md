# Demo runbook

Everything needed to show SpatialSync, and an honest account of what is real
behind each screen.

**In one line:** drag objects into a 3D scene in the browser and a house appears —
then it ends in a **price**, a **plan**, and a **conversation**.

---

## 1. Run it locally

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
npm run build
php artisan serve            # http://127.0.0.1:8000
```

The app talks to Supabase. Set `SUPABASE_URL`, `SUPABASE_ANON_KEY` and
`SUPABASE_SERVICE_KEY` in `.env`, then apply the database scripts in
`database/migrations/` (see `supabase_schema.sql` first, then
`supabase_build_issues.sql`, then `enable_supabase_realtime.sql`).

**Register an account in the app** — that is how real accounts are created. The
`php artisan db:seed` users are local placeholders with factory-random passwords.

## 2. Seed a finished house

A demo should never open on an empty grid:

```bash
php artisan db:seed --class=DemoHouseSeeder
```

It creates *Demo House — Riverside Cottage* (floor, four walls, a partition, a
door, a window, a flat roof) for the first registered user, or for
`DEMO_OWNER_EMAIL` if that variable is set. Re-running replaces it.

## 3. The 90-second demo path

The full storyboard is in [`docs/pitch/demo-script.md`](docs/pitch/demo-script.md).
The spine:

1. Open the seeded house — drag a wall from the left panel and place it.
2. Point at the **Planning estimate** chip in the toolbar: it moves as you build.
3. Open **Export** → *Blueprint PDF* → generate a real floor-plan drawing.
4. **Generate Client Link** → open it in a private window → the client types a
   name and is in the model with **no account**.
5. As the client, click a wall and pin a comment; back in the owner's window the
   pin is already there.

---

## 4. What is real, and what is not

Say this out loud when asked. A judge who catches an unsupported claim stops
believing the rest.

| Screen | Status |
|---|---|
| 3D editor, presets, snapping, multi-floor, undo/redo | **Real** — Three.js, `resources/js/build-editor.js` |
| Realtime multi-user editing, chat, presence | **Real** — Supabase Realtime channels |
| Blueprint PDF export (A4/A3, 1:50–1:200) | **Real** — `public/js/blueprint-exporter.js` |
| Issue pins, roles, permissions, share links | **Real** — `build_issues`, `build_members`, `build_shares` |
| Client review link with no signup | **Real** — `/share/{token}`, view + comment only |
| Planning cost estimate | **Real maths, indicative rates.** Rates live in `config/spatialsync.php` and are illustrative defaults, not market data. The UI says "estimate", never "price" |
| Storage meter on the dashboard | **Measured** — it totals the geometry actually stored, no simulated constant |
| Checkout | **Sandbox.** No payment provider is connected and no card is ever charged. Do not call it billing |
| Files in and out | **Only JSON export.** No IFC/DWG/Revit import or export yet |
| AI | **None.** There is no model, no LLM and nothing generated. See §7 |

## 5. Client review links

- **Create:** in the editor sidebar → *Client Review Link* → *Generate Client Link*.
- **Open:** `/share/{token}` — the visitor gives a name (no account, no email,
  no password) and lands in the model read-only.
- **What a guest can do:** look around, pin comments on parts, and post in chat.
- **What a guest cannot do:** move, add or delete geometry, manage members, or
  export. Guests have no row in `users`.
- **Expiry and revocation:** links are created with a 30-day expiry and a
  *Revoke* control in the sidebar that stops a link immediately. Both are checked
  server-side on every request — the token is the only credential a guest holds.
- **Throttling:** the share routes are rate limited (`throttle:60,1`).

## 6. Before you share any link publicly

**The database is currently wide open.** Every table in `supabase_schema.sql`
has row-level security policies of `USING (true)`, and the Supabase **anon key
is embedded in the editor page** for realtime. Anyone who reads that key can read
and write every row in the database — including builds they were never invited to.
Guest review links do not make this worse (it is already true for any visitor),
but "your plans stay private" is **not** a claim you can make until RLS is
rewritten to scope rows per authenticated user and the editor's realtime path
authenticates properly. That is the top engineering item after the demo.

Also worth doing before a public link goes out:

- Clear any stored facial-biometric templates: run
  `database/migrations/2026_09_14_remove_biometric_data.sql` (face login has been
  removed from the code; the stored templates were the liability).
- Delete or re-password the old demo accounts. Formerly-committed bcrypt hashes
  meant those logins were effectively public.
- The old product name still appears in `composer.json`, `config/cache.php`, the
  Render deploy workflow and the deployment docs. Renaming the Render *service*
  is your call — the deploy workflow depends on that identifier.

## 7. Why there is no "AI" claim

Face-login was removed on 2026-09-14. It was the only thing in the product that
looked like AI: a browser face detector that matched a visitor against **every**
enrolled user's stored template at a 0.45 distance threshold, then signed them in
as the closest match. It was identification, not verification — the wrong person
could be signed in as the right one — and it stored a facial template per user,
which brings GDPR Article 9 and Illinois BIPA exposure for no commercial gain.

Nothing else in the repository calls a model. Do not describe this product as
AI-assisted, AI-powered or AI-native until a real model does real work.

## 8. Checks

```bash
vendor/bin/phpstan analyse       # level max, baseline in phpstan-baseline.neon
vendor/bin/phpunit --no-coverage # 37 tests: unit suite + the share-link feature suite
npm run build                    # Vite production build
```

`php artisan test` is **not** available in this Laravel version; use PHPUnit
directly, which is what `.github/workflows/ci.yml` does.

The client review link is covered end to end in
`tests/Feature/GuestReviewLinkTest.php` with only the Supabase REST calls faked,
so it runs with no credentials: the no-signup join screen, comment attribution,
expired and revoked tokens, a token that belongs to a different build, and every
write a guest is refused. If you are asked "does it actually work?", that file is
the answer you can run in front of someone.
