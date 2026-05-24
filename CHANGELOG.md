# SpatialSync Changelog

All notable changes to SpatialSync will be documented in this file.

---

## 1.0.0 (2026-05-24)

### Features

* Add AuthenticatedRequest, Form Requests, middleware updates, and test infrastructure ([4605b8c](https://github.com/dev-lou/SpatialSync/commit/4605b8cd9f1d80d37d8484e72b3c8ed06f15d97d))
* Implement Bloxburg-style Custom Polygon Draw tool and Texture Mapping engine ([a7e4eff](https://github.com/dev-lou/SpatialSync/commit/a7e4eff107050df79f95bc7048cb2c35898bf554))
* implement simulated pricing system and dashboard UI polish ([df5f621](https://github.com/dev-lou/SpatialSync/commit/df5f62176c14595bc38ed1933c8f2926df010bf2))
* setup Construct 3D Build Editor with placement physics and modern skybox UI ([b114412](https://github.com/dev-lou/SpatialSync/commit/b11441256a2e80579f34bc9723eefdc7ce7c0e22))
* update dashboard UI, footer, and add OG-site; cleanup storage tracking and update .gitignore ([79535f9](https://github.com/dev-lou/SpatialSync/commit/79535f98dffac282e1f734cc3180808b4a6c262d))

### Bug Fixes

* Add @semantic-release/npm plugin, fix dependency-review on push ([56cf570](https://github.com/dev-lou/SpatialSync/commit/56cf57016e51ca60a818fe9ed13d20c11816014d))
* Add event_name to security.yml concurrency group to prevent push vs workflow_call conflicts ([ea7dc4c](https://github.com/dev-lou/SpatialSync/commit/ea7dc4c3fe5a4256ba596550d05bfe10cb22fd8e))
* Add fail-safe logic for user plans to resolve 500 error on dashboard ([9d414ce](https://github.com/dev-lou/SpatialSync/commit/9d414ce37033016ac0e3370ea871c4e184b92918))
* Add missing conventional-changelog-conventionalcommits package to release workflow ([af0a5cc](https://github.com/dev-lou/SpatialSync/commit/af0a5cc905f545342c3592d8f1527cfb74c54b32))
* add required name field to package.json for semantic-release compatibility ([84e22cb](https://github.com/dev-lou/SpatialSync/commit/84e22cb8932c82e006247f2f1b6a037894b5e28e))
* Add top-level permissions to release.yml (security-events: write, actions: read) ([4668b31](https://github.com/dev-lou/SpatialSync/commit/4668b316f03781c70839db3bb2e5da6effd04d89))
* Add version field to package.json, fix semantic-release & dependency-review workflows ([6be95b5](https://github.com/dev-lou/SpatialSync/commit/6be95b5590a5529de8ad9b5ef0ccca865ab88d7e))
* Bump NODE_VERSION to 22 in release.yml (semantic-release v25 requires Node 22.14+) ([67c6717](https://github.com/dev-lou/SpatialSync/commit/67c671751462661cbba9d91f7d8b79c3611334e1))
* Corrected Dockerfile heredoc escaping for Nginx config ([81e7c5b](https://github.com/dev-lou/SpatialSync/commit/81e7c5be0dc28ae7c06290477993be5a0c37f8a0))
* Disable composer scripts in vendor stage to prevent artisan error ([edcb342](https://github.com/dev-lou/SpatialSync/commit/edcb342acda615675f59f690b2d229edd0c01ea7))
* Enable plan-aware storage calculation by fetching user plan data ([5a860b4](https://github.com/dev-lou/SpatialSync/commit/5a860b4bfff61ea8e340a4472f84a1ea0d2e919f))
* Final robustness polish for HTTPS enforcement ([4f20431](https://github.com/dev-lou/SpatialSync/commit/4f20431f0bc82b103594e016bf0ab161a8166a0d))
* Pricing card badge overflow clipping ([fdf422f](https://github.com/dev-lou/SpatialSync/commit/fdf422fd239a70067dd51543f0be89f93c7d52d3))
* Resolve 500 error by removing redundant AppServiceProvider and cleaning up bootstrap config ([0f2a8ec](https://github.com/dev-lou/SpatialSync/commit/0f2a8ec0956fc917d4cc95e36a7034ebb6b7a209))
* Shared links now properly add users to build and redirect to editor instead of crashing ([b5deb39](https://github.com/dev-lou/SpatialSync/commit/b5deb399fad7f2d9401f33d026f6c3176df84c9f))
* Trust all proxies to ensure correct HTTPS detection on Render ([66c563a](https://github.com/dev-lou/SpatialSync/commit/66c563a2968976d77d4c407d028eaf1f63ee63c6))
* Update NODE_VERSION to 22 in ci.yml and security.yml (Node 20 deprecated) ([e87ad55](https://github.com/dev-lou/SpatialSync/commit/e87ad5551b01f5713ff529d5a043c64a0607720a))
* Use DEBUG env var instead of --debug flag for semantic-release ([6907e5a](https://github.com/dev-lou/SpatialSync/commit/6907e5aa1630056a2942fb92473830e01bedbaf0))

### Documentation

* Complete industry-standard 2026 README with full feature docs, setup tutorial, architecture, API reference, and fixed broken logo ([8424a5e](https://github.com/dev-lou/SpatialSync/commit/8424a5e97473799f523da107468797391c147791))
* Remove suggested repo names section from README.md ([5a6c000](https://github.com/dev-lou/SpatialSync/commit/5a6c00006a6f699e8a42508c2efda4f773d17670))
* Update .env.example with correct production values ([fa115e8](https://github.com/dev-lou/SpatialSync/commit/fa115e8279223dbffdcea08a0dfdbcaa2919137e))
* Update README.md to 2026 industry standards ([f65a7ed](https://github.com/dev-lou/SpatialSync/commit/f65a7eddce515135464bb9aa2d84649277eb49ca))

### CI/CD

* Add .gitattributes to enforce LF line endings (fixes Pint line_ending violations on Linux CI) ([0bef353](https://github.com/dev-lou/SpatialSync/commit/0bef353ca2942d0c04fb5fee723ba4c77b36000a))
* Add ESLint and Prettier to pre-commit hooks via lint-staged ([8d672ad](https://github.com/dev-lou/SpatialSync/commit/8d672ad2499e434d017ac53c59ba0f70bc2b1260))
* Auto-fix Pint violations before test (handles cross-platform style diffs) ([6783e25](https://github.com/dev-lou/SpatialSync/commit/6783e255d58cd91901192ecf9ac3f1123c47eb50))
* Capture Pint output as artifact for debugging CI failures ([5dab439](https://github.com/dev-lou/SpatialSync/commit/5dab4392bff84a8b1c39dda0d4a4809a6bc2996a))
* Drop post-autoload-dump scripts (not needed for CI checks) ([c142fc7](https://github.com/dev-lou/SpatialSync/commit/c142fc772cbb1bfa856a2a43efa3661670f2754c))
* Fix all failing CI checks - ESLint, Prettier, Pint, PHPStan, composer audit ([8d1253a](https://github.com/dev-lou/SpatialSync/commit/8d1253aa3c56be00d240252d6c176aa3d424eac6))
* Fix circular dep — composer install --no-scripts before key:generate ([5fdfe37](https://github.com/dev-lou/SpatialSync/commit/5fdfe37650f7832d660cf4492c450a13d139ae15))
* Fix composer install failures by creating .env from .env.example before install ([b000d5e](https://github.com/dev-lou/SpatialSync/commit/b000d5ecec49a9abddb68fc88977238bc5aea439))
* Generate valid APP_KEY after creating .env for composer install ([fa44fd8](https://github.com/dev-lou/SpatialSync/commit/fa44fd809d2740f85252522dae6b3c982790cbc4))
* Remove debug artifact step (Pint violations resolved by .gitattributes) ([89d3e8e](https://github.com/dev-lou/SpatialSync/commit/89d3e8edc776eb6415dd8b37b4afbe5a96539c14))
* Replace artisan key:generate with PHP one-liner (avoids Laravel boot) [skip-act] ([88ec00a](https://github.com/dev-lou/SpatialSync/commit/88ec00a2cf1fbaa4bf46a533c2a3d2edeeb9bad7))

### Reverts

* Remove unsafe URL::forceScheme call causing 500 errors ([b036dd7](https://github.com/dev-lou/SpatialSync/commit/b036dd7ff4489ecc60b22390ee7cd03826c1c6a3))
