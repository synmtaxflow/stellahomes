# cPanel Setup Instructions

## Project Structure on cPanel

```
home/stellahomes/repositories/stellahomes/  (Laravel Root)
├── app/
├── bootstrap/
├── config/
├── database/
├── public_html/  (Public Folder - This is your document root)
├── resources/
├── routes/
├── storage/
└── vendor/
```

## Step 1: Verify Project Structure

Hakikisha structure yako ni hivi:

```
home/stellahomes/repositories/stellahomes/  (Laravel Root)
├── app/
├── bootstrap/
├── config/
├── database/
├── public_html/  (Public Folder - Document Root)
│   ├── index.php
│   ├── .htaccess
│   └── storage/  (Symbolic link - created in Step 2)
├── resources/
├── routes/
├── storage/
│   └── app/
│       └── public/  (Actual storage location)
│           ├── blocks/
│           ├── rooms/
│           ├── profile_pictures/
│           └── ...
└── vendor/
```

## Step 2: Create Storage Link (IMPORTANT - Manual Method for cPanel)

**SHIDA**: Kwenye cPanel, `php artisan storage:link` inaunda link kwenye `public/storage`, lakini cPanel document root ni `public_html`. 
Hivyo lazima ucreate link manually kwenye `public_html/storage`.

### Solution: Create Link Manually

```bash
cd /home/stellahomes/repositories/stellahomes

# Remove old links (both locations)
rm -f public/storage
rm -f public_html/storage

# Create manual symbolic link kwenye public_html (cPanel document root)
ln -s ../storage/app/public public_html/storage

# Verify link
ls -la public_html/ | grep storage
```

Unapaswa kuona: `storage -> ../storage/app/public`

### Quick Fix Script

Unaweza pia kutumia script hii:

```bash
cd /home/stellahomes/repositories/stellahomes
chmod +x fix_storage_link.sh
./fix_storage_link.sh
```

### Verify Link Works

Baada ya kucreate link, test kama inafanya kazi:

```bash
# Check kama link ipo
ls -la public_html/storage

# Check kama inapoint kwenye sahihi location
readlink -f public_html/storage

# Unapaswa kuona: /home/stellahomes/repositories/stellahomes/storage/app/public
```

## Step 3: Set Permissions

```bash
chmod -R 755 storage
chmod -R 755 bootstrap/cache
chmod -R 755 public_html
```

## Step 4: Update .env File

Fungua `.env` file na hakikisha:

```env
APP_URL=https://yourdomain.com
APP_ENV=production
APP_DEBUG=false

# Storage Configuration
FILESYSTEM_DISK=public
```

## Step 5: Verify Storage Directory

Hakikisha directory zifuatazo zipo na zina permissions sahihi:

```bash
storage/app/public
storage/app/public/blocks
storage/app/public/rooms
storage/app/public/profile_pictures
storage/app/public/owner-profiles
storage/app/public/landing-page
storage/app/public/hostel
```

## Step 6: Run Composer Autoload

Baada ya kuadd helper function, run:

```bash
composer dump-autoload
```

## Step 7: Test Image Upload

1. Login kwenye admin panel
2. Try kuupload image (block, room, profile, etc.)
3. Check kama file imesave kwenye `storage/app/public`
4. Check kama image inaonekana kwenye browser

## Troubleshooting

### Images hazionyeshi:
1. Check kama storage link ipo: `ls -la public_html/storage`
2. Check permissions: `chmod -R 755 storage/app/public`
3. Check kama symbolic link ipo: `ls -la public_html/ | grep storage`

### Images hazikupload:
1. Check storage directory permissions: `chmod -R 755 storage`
2. Check kama directory exists: `ls -la storage/app/public`
3. Check Laravel logs: `tail -f storage/logs/laravel.log`

### 404 Error kwenye images:
1. Verify storage link: `php artisan storage:link`
2. Check .htaccess file kwenye public_html
3. Check APP_URL kwenye .env file

## Important Notes

- **Public Folder**: Kwenye cPanel, public folder ni `public_html` sio `public`
- **Storage Link**: Lazima ucreate storage link kwa ku-run `php artisan storage:link`
- **Permissions**: Storage directory lazima iwe na write permissions (755 au 775)
- **APP_URL**: Lazima iwe set correctly kwenye .env file

## Quick Fix Commands

```bash
# Navigate to project root
cd /home/stellahomes/repositories/stellahomes

# Create storage link
php artisan storage:link

# Set permissions
chmod -R 755 storage
chmod -R 755 bootstrap/cache
chmod -R 755 public_html

# Clear cache
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# Create storage directories if missing
mkdir -p storage/app/public/blocks
mkdir -p storage/app/public/rooms
mkdir -p storage/app/public/profile_pictures
mkdir -p storage/app/public/owner-profiles
mkdir -p storage/app/public/landing-page
mkdir -p storage/app/public/hostel

# Set permissions for storage directories
chmod -R 755 storage/app/public
```

