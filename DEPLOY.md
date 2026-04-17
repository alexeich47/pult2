# Pult — Production Deploy

Target: `pult.swiftpunk.com`, running on `188.137.224.173` alongside `club.swiftpunk.com` (the same server already has Docker + a host-level nginx + certbot).

## Architecture

```
Internet → host nginx (443) → localhost:8818 → pult_app (nginx + php-fpm)
                                             → pult_queue
                                             → pult_scheduler
```

- Each app is isolated in its own Docker project (named volumes, no shared network with other projects).
- Pult uses **SQLite** on a named volume (`pult_db`). First boot creates an empty DB, runs migrations, and runs `db:seed` — producing a working system with admin user + reference data. Subsequent boots keep whatever is on the volume.
- Host nginx reverse-proxies `pult.swiftpunk.com` → `127.0.0.1:8818` with Let's Encrypt TLS. Same pattern as `club.swiftpunk.com`.

## One-time server setup

### 1. DNS (do this in Cloudflare first)

Set `pult.swiftpunk.com` → **A `188.137.224.173`**, **DNS only** (grey cloud, not proxied). Let's Encrypt's HTTP-01 challenge needs a direct path to the origin. Same as `club.swiftpunk.com`.

### 2. Clone the repo

```bash
mkdir -p /var/projects
cd /var/projects
git clone https://github.com/alexeich47/pult2.git pult
cd pult
```

### 3. Configure environment

```bash
cp .env.production.example .env
docker run --rm -v "$PWD:/app" -w /app composer:2 composer install --no-dev --no-scripts --no-autoloader
docker run --rm -v "$PWD:/app" -w /app php:8.3-cli php artisan key:generate --show >> .env
# Then edit .env so APP_KEY is the generated value (replace the empty line).
nano .env
```

Or the simpler one-shot:
```bash
APP_KEY="base64:$(openssl rand -base64 32)"
sed -i "s|^APP_KEY=.*|APP_KEY=${APP_KEY}|" .env
```

### 4. Build + start containers

```bash
docker compose -f docker-compose.production.yml up -d --build
```

First boot runs migrations and warms config/route/view caches via `docker/entrypoint.sh`.

Verify:
```bash
docker ps --filter name=pult_
curl -I http://127.0.0.1:8818   # should return 200 or Inertia's redirect to /login
```

### 5. Host nginx site

Copy `club.swiftpunk.com`'s config and adjust:
```nginx
# /etc/nginx/sites-available/pult.swiftpunk.com
server {
    server_name pult.swiftpunk.com;
    client_max_body_size 50M;

    location / {
        proxy_pass http://127.0.0.1:8818;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
    }

    listen 80;
}
```

Enable:
```bash
ln -sf /etc/nginx/sites-available/pult.swiftpunk.com /etc/nginx/sites-enabled/pult.swiftpunk.com
nginx -t && systemctl reload nginx
```

### 6. TLS via certbot

```bash
certbot --nginx -d pult.swiftpunk.com --non-interactive --agree-tos -m <your-email>
```

certbot auto-adds the `listen 443 ssl` block and the HTTP → HTTPS redirect. Renewal cron is already configured on this server.

### 7. Final check

```bash
curl -I https://pult.swiftpunk.com   # 200 from Inertia
docker logs pult_app --tail 40
```

## Routine operations

### Deploy a new version

```bash
cd /var/projects/pult
git pull origin main
docker compose -f docker-compose.production.yml up -d --build
# Migrations run automatically via entrypoint; no manual step.
```

### View logs

```bash
docker logs -f pult_app      # web
docker logs -f pult_queue    # queue worker
docker logs -f pult_scheduler
```

### Shell access

```bash
docker exec -it pult_app sh
# Inside: php artisan tinker
```

### Backup DB

```bash
docker exec pult_app sh -c 'sqlite3 /app/storage/db/database.sqlite ".backup /tmp/backup.sqlite"'
docker cp pult_app:/tmp/backup.sqlite /var/backups/pult-$(date +%F).sqlite
```

### Rollback

```bash
cd /var/projects/pult
git log --oneline -10
git checkout <previous-commit>
docker compose -f docker-compose.production.yml up -d --build
```

## What is NOT touched by this deploy

- `/root/.ssh/id_rsa*` — SSH keys used by other projects for git pull/push. Pult clones over HTTPS from a public repo and doesn't need a deploy key.
- `/var/projects/club/` — club.swiftpunk.com remains unchanged on port 8814/8816 with its own postgres+redis.
- Existing certbot certs for `club.swiftpunk.com` and `swiftpunk.com`.
- Host nginx `/etc/nginx/sites-available/{club,swiftpunk,mail,roundcube-port}.*` — we only add a new file, don't modify existing.

## Smoke test checklist

After deploy, verify in this order:

1. `https://pult.swiftpunk.com/login` — login form renders, no 502
2. Login as `admin@pult.local` / `password` — lands on dashboard
3. Navigate Финансы → Отклонение — sees Feb/Mar/Apr deviation bars
4. `https://club.swiftpunk.com` — still works (not affected)
5. `https://swiftpunk.com` — still works (not affected)
