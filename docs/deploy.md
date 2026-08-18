# Deploy em produção

## 1. Variáveis de ambiente

Configure o `.env` do servidor sem versioná-lo:

```env
APP_NAME="Cantinho das Receitas"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://seu-dominio.com.br

DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=cantinho_receitas
DB_USERNAME=usuario_producao
DB_PASSWORD=senha_forte

CACHE_STORE=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis
```

Gere uma chave nova com `php artisan key:generate` apenas na primeira configuração. Nunca copie a `APP_KEY` do ambiente local.

## 2. Publicação

Na raiz do projeto:

```bash
composer install --no-dev --optimize-autoloader
npm ci
npm run build
php artisan migrate --force
php artisan storage:link
php artisan optimize
```

O servidor web deve apontar o document root para `public/`, nunca para a raiz do repositório.

## 3. Worker de fila

Mantenha um worker sob Supervisor, systemd ou o supervisor do provedor:

```bash
php artisan queue:work --sleep=3 --tries=3 --timeout=90
```

Após cada deploy, reinicie o worker:

```bash
php artisan queue:restart
```

## 4. Verificação pós-deploy

```bash
php artisan about
php artisan migrate:status
php artisan route:list
curl -I https://seu-dominio.com.br/up
curl -I https://seu-dominio.com.br/sitemap.xml
```

O endpoint `/up` deve retornar HTTP 200. Confirme também HTTPS, permissões de `storage/` e que `APP_DEBUG=false` está ativo.
