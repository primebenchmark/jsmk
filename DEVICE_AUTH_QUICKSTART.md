# Device Authentication — Quick Start Guide

## 1. Initialize Device-Auth

```bash
cd device-auth
php setup.php
```

This will:
- Create the SQLite database
- Prompt you for admin username and password
- Prompt you for a password reset PIN

**Keep these credentials safe!**

---

## 2. Configure Your Web Server

### Option A: Single VirtualHost (Recommended for Development)

Set your DocumentRoot to the main project root (`/home/geekom/project/jsmk`):

**Apache:**
```apache
<VirtualHost *:80>
    ServerName localhost
    DocumentRoot /home/geekom/project/jsmk
    <Directory /home/geekom/project/jsmk>
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

**Nginx:**
```nginx
server {
    listen 80;
    server_name localhost;
    root /home/geekom/project/jsmk;
    index index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/run/php/php-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

### Option B: Dual VirtualHost (Production)

If you want device-auth as a separate domain/subdomain:

**Main site VirtualHost:**
```apache
DocumentRoot /home/geekom/project/jsmk
```

**Device-auth VirtualHost:**
```apache
ServerName device-auth.yourdomain.com
DocumentRoot /home/geekom/project/jsmk/device-auth/public
```

Then update `api/device/` endpoints in your main site to point to the device-auth domain.

---

## 3. First-Time Login

### Step 1: Access Admin Panel

Visit the admin panel:
```
http://localhost/device-auth/public/admin/login.php
```

Log in with the admin credentials you set during `php setup.php`.

### Step 2: Approve Your Device

1. Visit `http://localhost/login.php`
2. Click **"Identify My Device"**
3. Your device will be created and appear in the admin panel as "Inactive"
4. In the admin panel, toggle the device to **"Active"** (set `is_active = 1`)

### Step 3: Access Mock Tests

1. Return to `http://localhost/login.php`
2. Click **"Identify My Device"** again
3. You'll be redirected to `http://localhost/index.php` ✅

---

## 4. Device Management

### Change Admin Password
In the admin panel: Device Settings → Reset Admin Password (requires PIN)

### Set Device Expiry
In the admin panel: Toggle `valid_days` for any device to auto-expire their access

### Reject a Device
Simply toggle `is_active = 0` to immediately revoke access

### View Device Activity
- **IP Address** - Device's IP
- **Last Seen** - Last login timestamp
- **Created At** - When device was first registered

---

## 5. Troubleshooting

### "Awaiting Admin Approval" Message
- Go to admin panel (`/device-auth/public/admin/login.php`)
- Find your device and toggle `is_active` to 1

### Admin Panel Not Accessible
- Make sure your DocumentRoot includes `/device-auth/public/`
- OR access it at its full path: `http://localhost/device-auth/public/admin/login.php`
- Check web server config is set correctly

### Token Expired
- Students with expired tokens will see: "Device access has expired"
- Admin must click the device and update `valid_days` or reset it

### Database Locked (SQLite)
- The database uses WAL (Write-Ahead Logging) mode for concurrent access
- Delete any `.sqlite-wal` or `.sqlite-shm` files if locked:
  ```bash
  rm device-auth/storage/database.sqlite-wal
  rm device-auth/storage/database.sqlite-shm
  ```

---

## 6. Security Checklist

- ✅ Device tokens are hashed with SHA-256 (raw token never stored)
- ✅ Admin passwords are bcrypt-hashed
- ✅ Rate limiting: 5 token generation/min, 10 login attempts/min per IP
- ✅ Sessions use HttpOnly, Secure (HTTPS), SameSite=Strict cookies
- ✅ All admin operations are CSRF-protected
- ✅ SQL injection prevention via prepared statements
- ✅ XSS prevention via output escaping

---

## 7. File Permissions

Set proper permissions for the storage directory:

```bash
chmod 775 device-auth/storage/
chmod 664 device-auth/storage/database.sqlite
chown www-data:www-data device-auth/storage/database.sqlite  # On Linux
```

---

## Need Help?

- Device-Auth runs completely independent of the main mock test site
- All device data is stored in `device-auth/storage/database.sqlite`
- Admin credentials are stored in the same DB
- Main site's mock test data is in `data/analytics.sqlite` (separate)

For device-auth documentation, see `device-auth/README.md`.
