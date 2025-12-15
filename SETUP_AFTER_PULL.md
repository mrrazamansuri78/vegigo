# Setup Instructions After Pulling Code

Yeh document batata hai ki code pull karne ke baad kya kya steps follow karne hain.

## 📋 Prerequisites

- PHP 8.1+ installed
- Composer installed
- MySQL/MariaDB running
- Node.js & NPM (agar frontend assets compile karne hain)

---

## 🚀 Step-by-Step Setup

### 1. **Dependencies Install Karein**

```bash
composer install
```

Yeh command install karega:
- Laravel framework dependencies
- Firebase PHP SDK (`kreait/firebase-php`)

---

### 2. **Environment File Setup**

`.env` file create karein (agar nahi hai):

```bash
cp .env.example .env
```

Ya manually `.env` file create karein with these required variables:

```env
APP_NAME=Vegigo
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=vegigo_db
DB_USERNAME=root
DB_PASSWORD=

# Google Maps API Key
GOOGLE_MAPS_API_KEY=AIzaSyBdP-I7KzDCZJwEnUBEpzLBkRXAstS2Yis

# Firebase Configuration
FIREBASE_CREDENTIALS_PATH=storage/app/firebase-credentials.json
FIREBASE_DATABASE_URL=https://vegigo-acf7d-default-rtdb.firebaseio.com/
```

**Application Key Generate Karein:**
```bash
php artisan key:generate
```

---

### 3. **Database Setup**

**Database Create Karein:**
MySQL mein manually database create karein:
```sql
CREATE DATABASE vegigo_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

**Migrations Run Karein:**
```bash
php artisan migrate
```

Yeh command create karega:
- `users` table (with `address`, `latitude`, `longitude` fields)
- `products` table
- `orders` table (with location fields)
- `otp_codes` table
- `notifications` table
- `farmer_profiles` table
- `delivery_boy_profiles` table
- `pickup_requests` table

---

### 4. **Firebase Setup**

**Firebase Credentials File Setup:**

1. Firebase Console se service account JSON file download karein
2. File ko `storage/app/firebase-credentials.json` path par save karein

**Ya manually create karein:**
```bash
# storage/app/firebase-credentials.json file create karein
# Firebase service account JSON content paste karein
```

**Note:** Agar Firebase setup nahi karna chahte, to application still chalega (FirebaseService gracefully handle karega missing credentials).

Detailed instructions: `FIREBASE_SETUP.md` file mein dekh sakte hain.

---

### 5. **Google Maps API Key**

`.env` file mein `GOOGLE_MAPS_API_KEY` already set hai, lekin verify karein:

```env
GOOGLE_MAPS_API_KEY=AIzaSyBdP-I7KzDCZJwEnUBEpzLBkRXAstS2Yis
```

**Important:** Google Cloud Console mein ensure karein ki:
- Maps JavaScript API enable hai
- API key ke liye proper restrictions set hain

Detailed instructions: `GOOGLE_MAPS_SETUP.md` file mein dekh sakte hain.

---

### 6. **Storage Link**

Public storage link create karein (product images ke liye):

```bash
php artisan storage:link
```

---

### 7. **Admin User Create Karein**

Admin panel access ke liye admin user create karein:

**Option 1: Tinker se**
```bash
php artisan tinker
```

Tinker mein:
```php
$admin = App\Models\User::create([
    'name' => 'Admin',
    'email' => 'admin@vegigo.com',
    'phone' => '+911234567890',
    'password' => bcrypt('admin123'),
    'role' => 'admin',
]);
```

**Option 2: Seeder se (agar banaya ho)**

---

### 8. **Config Cache Clear**

```bash
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
```

---

### 9. **Server Start Karein**

**Development Server:**
```bash
php artisan serve
```

Server start hoga: `http://localhost:8000`

**XAMPP/WAMP Use Kar Rahe Hain:**
- Document root: `public` folder
- URL: `http://localhost/Fruits-Vegetables-Backend/public`

---

## ✅ Verification Steps

### API Test Karein:

1. **Postman Collection Import Karein:**
   - File: `Vegigo.postman_collection.json`
   - Base URL set karein: `http://localhost/api` (ya aapka server URL)

2. **Test Endpoints:**
   - `POST /api/auth/send-otp` - OTP send karein
   - `POST /api/auth/verify-otp` - OTP verify karein
   - `GET /api/me` - User details (token required)

### Admin Panel Test Karein:

1. **Admin Login:**
   - URL: `http://localhost/admin/login`
   - Credentials: Admin user ka email/password

2. **Dashboard Check:**
   - Orders statistics
   - Live order tracking map
   - Products list

---

## 🔧 Common Issues & Solutions

### Issue 1: "Class not found" errors
**Solution:**
```bash
composer dump-autoload
```

### Issue 2: "Migration table not found"
**Solution:**
```bash
php artisan migrate:install
php artisan migrate
```

### Issue 3: "Storage link not working"
**Solution:**
```bash
php artisan storage:link
# Agar already exists, to delete karke phir se create karein
```

### Issue 4: "Firebase credentials not found"
**Solution:**
- `storage/app/firebase-credentials.json` file verify karein
- `.env` mein `FIREBASE_CREDENTIALS_PATH` check karein
- Application still chalega, bas Firebase features kaam nahi karenge

### Issue 5: "Google Maps not loading"
**Solution:**
- `.env` mein `GOOGLE_MAPS_API_KEY` verify karein
- Browser console mein errors check karein
- Google Cloud Console mein API enable hai ya nahi check karein

---

## 📝 Important Notes

1. **`.env` file kabhi git mein commit mat karo** - Already `.gitignore` mein add hai

2. **Firebase Credentials** - Production mein secure location par store karein

3. **API Keys** - Production mein environment variables use karein, hardcode mat karo

4. **Database Backup** - Regular backups lena yaad rakhein

5. **Logs** - `storage/logs/laravel.log` mein errors check kar sakte hain

---

## 🎯 Quick Setup Checklist

- [ ] `composer install` run kiya
- [ ] `.env` file create/configure kiya
- [ ] `php artisan key:generate` run kiya
- [ ] Database create kiya
- [ ] `php artisan migrate` run kiya
- [ ] Firebase credentials file setup kiya (optional)
- [ ] Google Maps API key verify kiya
- [ ] `php artisan storage:link` run kiya
- [ ] Admin user create kiya
- [ ] `php artisan config:clear` run kiya
- [ ] Server start kiya
- [ ] API test kiya (Postman se)
- [ ] Admin panel test kiya

---

## 📚 Additional Documentation

- **Admin Panel Setup:** `ADMIN_SETUP.md`
- **Firebase Setup:** `FIREBASE_SETUP.md`
- **Google Maps Setup:** `GOOGLE_MAPS_SETUP.md`
- **Postman Collection:** `Vegigo.postman_collection.json`

---

## 🆘 Help

Agar koi issue aaye to:
1. `storage/logs/laravel.log` check karein
2. Browser console check karein (admin panel ke liye)
3. API response check karein (Postman se)

**Happy Coding! 🚀**

