# FarmLink Admin Panel Setup Guide

## Features
- ✅ Beautiful Admin Dashboard with Live Order Tracking
- ✅ Real-time Order Tracking using Google Maps
- ✅ Firebase Real-time Database Integration
- ✅ Product Management
- ✅ Order Management
- ✅ Google Maps API Integration

## Prerequisites
1. Firebase Project with Realtime Database enabled
2. Google Maps API Key (Already configured: `AIzaSyBdP-I7KzDCZJwEnUBEpzLBkRXAstS2Yis`)

## Firebase Setup

### Step 1: Create Firebase Project
1. Go to [Firebase Console](https://console.firebase.google.com/)
2. Create a new project or use existing
3. Enable **Realtime Database**

### Step 2: Get Firebase Credentials
1. Go to Project Settings > Service Accounts
2. Click "Generate New Private Key"
3. Download the JSON file
4. Save it as `storage/app/firebase-credentials.json`

### Step 3: Get Database URL
1. Go to Realtime Database in Firebase Console
2. Copy the database URL (e.g., `https://your-project.firebaseio.com`)
3. Add to `.env` file:
```
FIREBASE_DATABASE_URL=https://your-project.firebaseio.com
```

## Admin User Setup

### Create Admin User
Run this command in tinker or create a seeder:

```php
php artisan tinker

User::create([
    'name' => 'Admin User',
    'email' => 'admin@farmlink.com',
    'password' => bcrypt('admin123'),
    'phone' => '+911234567890',
    'role' => 'admin',
]);
```

## Access Admin Panel

1. Navigate to: `http://your-domain/admin/login`
2. Login with admin credentials
3. Access dashboard at: `http://your-domain/admin/dashboard`

## Admin Panel Features

### Dashboard
- Real-time statistics (Total Orders, Pending, Active, Earnings)
- Live Order Tracking Map with Google Maps
- Recent Orders List

### Products Management
- View all products
- Create new products
- Edit existing products
- Delete products
- Image upload support

### Orders Management
- View all orders with filters
- Filter by status, date range
- View order details
- Update order status
- Real-time tracking map for each order

## Real-time Tracking

The system automatically syncs order locations to Firebase when:
- Delivery boy updates location via API
- Order status changes (accepted, picked_up, delivered)
- Admin panel refreshes every 10 seconds to show live locations

## API Endpoints for Mobile

The mobile app should call these endpoints to sync with Firebase:

1. **Update Location** (Delivery Boy):
   ```
   POST /api/delivery/location
   {
     "current_latitude": 12.9716,
     "current_longitude": 77.5946,
     "current_speed_kmh": 45,
     "battery_percentage": 85
   }
   ```

2. **Accept Pickup**: Automatically syncs to Firebase
3. **Mark Picked Up**: Automatically syncs to Firebase
4. **Mark Delivered**: Automatically syncs to Firebase

## Troubleshooting

### Firebase Connection Issues
- Ensure `firebase-credentials.json` is in `storage/app/`
- Check database URL in `.env`
- Verify Firebase Realtime Database is enabled

### Google Maps Not Loading
- Verify API key is correct
- Check browser console for errors
- Ensure Maps JavaScript API is enabled in Google Cloud Console

### Admin Login Not Working
- Ensure user has `role = 'admin'` in database
- Check email and password are correct

## Notes

- Firebase credentials file should NOT be committed to git
- Google Maps API key is already configured
- Real-time updates happen automatically when delivery boy updates location
- Admin panel refreshes map every 10 seconds

