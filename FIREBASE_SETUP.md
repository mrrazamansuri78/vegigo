# Firebase Configuration Complete ✅

## Configuration Details

### Firebase Project
- **Project ID:** `vegigo-acf7d`
- **Project Name:** Vegigo
- **Credentials File:** `storage/app/firebase-credentials.json` ✅

### Firebase Realtime Database
- **Database URL:** `https://vegigo-acf7d-default-rtdb.firebaseio.com`
- Add this to your `.env` file:
  ```
  FIREBASE_DATABASE_URL=https://vegigo-acf7d-default-rtdb.firebaseio.com
  ```

### Google Maps API
- **API Key:** `AIzaSyAXuCpsp6rAif03NtpSLZ4Z0MzlPn_PKU0` ✅
- Already configured in `config/services.php`

## Firebase Console Access

1. Go to: https://console.firebase.google.com/project/vegigo-acf7d
2. Navigate to **Realtime Database** section
3. Enable Realtime Database if not already enabled
4. Copy the database URL (usually: `https://vegigo-acf7d-default-rtdb.firebaseio.com`)

## Verify Configuration

Run this command to test Firebase connection:
```bash
php artisan tinker
```

Then test:
```php
$firebase = app(\App\Services\FirebaseService::class);
// If no error, Firebase is configured correctly!
```

## Next Steps

1. **Enable Realtime Database in Firebase Console:**
   - Go to Firebase Console
   - Click on "Realtime Database"
   - Click "Create Database"
   - Choose location (e.g., us-central1)
   - Start in test mode (you can secure it later)

2. **Update .env file:**
   ```
   FIREBASE_DATABASE_URL=https://vegigo-acf7d-default-rtdb.firebaseio.com
   ```

3. **Clear config cache:**
   ```bash
   php artisan config:clear
   ```

4. **Test Admin Panel:**
   - Go to: `http://localhost/admin/login`
   - Login with: `admin@farmlink.com` / `admin123`
   - Check if live order tracking works

## Firebase Security Rules (Optional)

For production, update Firebase Realtime Database rules:
```json
{
  "rules": {
    "orders": {
      ".read": "auth != null",
      ".write": "auth != null"
    }
  }
}
```

## Troubleshooting

### If Firebase still shows errors:
1. Verify credentials file exists: `storage/app/firebase-credentials.json`
2. Check database URL in `.env` file
3. Ensure Realtime Database is enabled in Firebase Console
4. Run: `php artisan config:clear`

### If Google Maps not loading:
1. Verify API key is correct
2. Check browser console for errors
3. Ensure Maps JavaScript API is enabled in Google Cloud Console

