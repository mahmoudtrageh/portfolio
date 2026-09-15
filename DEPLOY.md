# Deployment

Pushes to `main` deploy to **mahmoudtaha.dev** (66.29.141.240) via
`.github/workflows/deploy.yml`, but only after the `tests` workflow passes. You
can also deploy by hand from the Actions tab (**deploy → Run workflow**).

The host is **Namecheap shared hosting (cPanel)** with no SSH enabled, so
GitHub Actions builds everything and uploads the finished app over FTPS. The
server runs no build step and no commands.

---

## 1. Add the repository secrets

Only two. The domain and the remote directory are hardcoded in the workflow.

**Settings → Secrets and variables → Actions → New repository secret**

| Secret | Value |
|---|---|
| `FTP_USERNAME` | Your cPanel / FTP username |
| `FTP_PASSWORD` | That account's password |

## 2. The flat layout and the root `.htaccess`

Files land directly in `/public_html/`, so the Laravel root sits **inside** the
web root. Apache would happily serve `.env`, `vendor/`, and `app/` from there.

The repo's root **`.htaccess`** is what prevents that. It:

- blocks all dotfiles, including `.env`
- blocks `app/ bootstrap/ config/ database/ storage/ vendor/ …`
- blocks `artisan`, `composer.json`, and other root files
- 301s `/public/...` → `/...` so the site is not served at two URLs
- rewrites everything else into `public/`, where Laravel takes over

It is inert locally — `php artisan serve` and Vite never read it.

> Every deploy ends by fetching `/.env`, `/artisan`, `/vendor/autoload.php` and
> others, and **fails the run** if any returns 200. If that step ever goes red,
> treat it as urgent: the app root is publicly readable.

## 3. Create `.env` on the server

`.env` is excluded from the upload, so CI can never overwrite your production
secrets. Create it once via cPanel File Manager (Settings → *Show Hidden Files*),
based on `.env.example`:

```env
APP_ENV=production
APP_DEBUG=false
APP_KEY=                      # see below
APP_URL=https://mahmoudtaha.dev

MAIL_MAILER=smtp
MAIL_HOST=
MAIL_USERNAME=
MAIL_PASSWORD=
MAIL_FROM_ADDRESS="noreply@mahmoudtaha.dev"
```

Generate `APP_KEY` locally and paste the result:

```bash
php artisan key:generate --show
```

`APP_URL` drives `hreflang`, `canonical`, and `sitemap.xml` — it must be the
final domain.

## 4. Database

`.env.example` uses SQLite. Create an empty `database/database.sqlite` on the
server, then run migrations once from cPanel → **Terminal**, if your plan has
it. Otherwise upload a locally-migrated `database.sqlite` via File Manager.

---

## No cached config — deliberately

Laravel can compile config, routes and views into `bootstrap/cache/*.php`. That
is faster, but **nothing on this server can rebuild those files** — there is no
SSH and no post-deploy hook. A stale cache would silently override every future
deploy: you would change `config/portfolio.php`, deploy, and see no change.

So the workflow excludes `bootstrap/cache/*.php` from the upload and Laravel
reads its config on each request. Marginally slower, always correct.

> **Never run `php artisan config:cache` against production**, and never upload
> a `bootstrap/cache/*.php` file by hand. Doing so pins the site to whatever
> `.env` was in effect when the cache was built — including, if built locally,
> your dev database path and `APP_DEBUG=true`.
>
> If it happens: delete `bootstrap/cache/config.php` (and `routes-v7.php`) in
> File Manager. The site recovers on the next request.

---

## What each deploy does

1. Builds Composer deps (`--no-dev --optimize-autoloader`) and Vite assets on the runner
2. Fails early if `vendor/`, `public/build`, or the root `.htaccess` is missing
3. Uploads over **FTPS** to `/public_html/`, syncing only changed files
4. Checks the site returns 2xx/3xx
5. Checks `.env` and friends are **not** publicly readable

### Not uploaded

`.env`, `.git`, `.github`, `node_modules`, `tests`, `storage/app`,
`storage/logs`, `storage/framework/{cache,sessions,views}`, `public/storage`,
`bootstrap/cache/*.php`, lockfiles, and development-only config.

`vendor/` **is** uploaded, deliberately — the server has no Composer, so
excluding it would mean dependency updates never reach production.

### Migrations are not automatic

Run them from cPanel → Terminal when a release needs one:

```bash
cd ~/public_html && php artisan migrate --force
```

---

## Troubleshooting

**Site 404s everywhere after the first deploy** — the root `.htaccess` is
missing, or `public/index.php` did not upload. Requests must rewrite into
`public/`.

**"Sensitive files are publicly readable"** — the root `.htaccess` did not
upload, or `mod_rewrite` is off. Confirm the file is at the top of
`public_html` in File Manager. Until fixed, `.env` is downloadable — **rotate
`APP_KEY` and the mail password** afterwards.

**Config changes do not take effect** — a `bootstrap/cache/config.php` exists
on the server. Delete it; see the section above.

**500 after deploy** — check `storage/logs/laravel.log` in File Manager. Usually
a missing `.env` key or an unwritable `storage/`. Fix permissions with:
`chmod -R 775 storage bootstrap/cache`.

**Old assets still served** — Vite filenames are hashed, so confirm
`public/build/manifest.json` actually changed on the server.

**`Class not found` after a package update** — `vendor/` did not upload. Confirm
it is not in the workflow's `exclude` list.

**Deploy did not fire** — it is gated on `tests` passing on `main`. Use **Run
workflow** to deploy anyway.

**FTP times out** — Namecheap throttles concurrent FTP connections; re-run the
job. The sync state file makes the retry incremental.

---

## If SSH is enabled later

SSH on this host is port **21098** (not 22), and Namecheap requires key auth.
A deploy keypair already exists at `~/.ssh/portfolio_deploy`; its public half
goes in cPanel → **SSH Access → Manage SSH Keys → Import**, then **Authorize**.

Server host key fingerprints, scanned 2026-09-15:

```
RSA      SHA256:oBlrb4UdXHd2G+oljpQ6Qz60UlBPp67BJKIs3bgpCKk
ED25519  SHA256:pOwsXwwITs5KwqdnDW+0qvpkat526xfWFH8NtDtVv/w
ECDSA    SHA256:zu43wLTdKR4sfyV8YeFaASJH9kT4DXGBRcZ38mmdl0c
```

With SSH, rsync would replace the FTP upload, and `config:cache` would become
safe to use again — a real shell can always clear it.
