# Fix Storage Link for cPanel

## Problem
Laravel kwa default inaexpect `public/storage` lakini kwenye cPanel public folder ni `public_html`.

## Solution

### Option 1: Manual Link Creation (Recommended)

Kwenye cPanel Terminal, run:

```bash
cd /home/stellahomes/repositories/stellahomes

# Remove old link if exists
rm -f public/storage
rm -f public_html/storage

# Create correct link for cPanel
ln -s ../storage/app/public public_html/storage

# Verify link
ls -la public_html/ | grep storage
```

Unapaswa kuona: `storage -> ../storage/app/public`

### Option 2: Update Laravel Config

Kama unatumia `public_html` kama public folder, update `bootstrap/app.php`:

```php
return Application::configure(basePath: dirname(__DIR__))
    ->withPublicPath(__DIR__.'/../public_html')  // Add this line
    ->withRouting(...)
```

Kisha run:
```bash
php artisan storage:link
```

### Option 3: Symlink Manually

```bash
cd /home/stellahomes/repositories/stellahomes
cd public_html
ln -s ../storage/app/public storage
```

## Verify

1. Check link exists:
   ```bash
   ls -la public_html/storage
   ```

2. Test image upload kwenye admin panel

3. Check kama image inaonekana:
   - Upload image
   - Check `storage/app/public/` directory
   - Access image via browser: `https://yourdomain.com/storage/path/to/image.jpg`

## Troubleshooting

### Link haipo:
```bash
# Remove and recreate
rm -f public_html/storage
ln -s ../storage/app/public public_html/storage
chmod -R 755 storage/app/public
```

### Permission Denied:
```bash
chmod -R 755 storage
chmod -R 755 public_html
```

### 404 Error on Images:
1. Check `.htaccess` ipo kwenye `public_html`
2. Check `APP_URL` kwenye `.env`
3. Verify link: `ls -la public_html/storage`

