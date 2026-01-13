# MAMA Fashion - Deployment Guide

## Files Created for Vercel Deployment

### 1. vercel.json
- Configures Vercel to handle PHP
- Routes all requests through index.php
- Sets up serverless environment

### 2. config/database_supabase.php
- Connects to Supabase PostgreSQL
- Uses environment variables for security
- Replaces your local MySQL connection

### 3. api/cart.php
- Serverless API for cart operations
- Handles JSON requests/responses
- Works with Vercel's serverless functions

## Next Steps

1. **Update your config.php** to use the new database file
2. **Upload to GitHub** (Vercel needs this)
3. **Deploy to Vercel**
4. **Set environment variables**

## Files to Modify

### Update config/config.php:
```php
// Replace the database include line:
require_once __DIR__ . '/database_supabase.php';
```

### Update includes/functions.php:
Add these lines at the top for serverless compatibility:
```php
// Enable CORS for API calls
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');
```

## Verification Steps Before Next Step:
1. ✅ vercel.json created in project root
2. ✅ database_supabase.php created
3. ✅ api/cart.php created
4. ✅ Ready to upload to GitHub

**Once you've reviewed these files and are ready for Step 6, let me know!**
