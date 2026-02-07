# CRM Logging Operations

## 1) Kurulum

```bash
php artisan migrate
```

Bu migration'lar oluşturulur:
- `system_logs`
- `change_journals`

## 2) Log Kaynakları

- `server`:
  Laravel exception report akışında DB'ye yazılır (404 hariç).
- `client`:
  Tarayıcı `window.onerror` ve `unhandledrejection` kayıtları `/api/client-errors` ile DB'ye yazılır.
- `journal`:
  Değişiklik/deneme kayıtları için `change_journals`.

## 3) Log Ekranı

- URL: `/sistem-loglari`
- Filtre: kanal, seviye, serbest arama

## 4) Retention (Temizlik)

```bash
php artisan logs:prune --days=45
```

Hostinger cron önerisi (günde 1 kez):

```bash
/usr/bin/php /home/USERNAME/domains/muratpektas.com/public_html/artisan logs:prune --days=45 >/dev/null 2>&1
```

## 5) Change Journal API

Yeni deneme kaydı:

`POST /api/change-journals`

Örnek payload:

```json
{
  "task_key": "ziyaret-inline-edit",
  "attempt_no": 2,
  "actor": "codex",
  "status": "fail",
  "summary": "Tab geçişinde blur save çakışması",
  "commit_hash": "abc123",
  "meta": {
    "page": "ziyaretler",
    "note": "Kullanıcı testinde tekrarlandı"
  }
}
```

