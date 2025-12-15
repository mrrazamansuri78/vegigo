# Google Maps API Setup Guide

## Current API Key
**API Key:** `AIzaSyAXuCpsp6rAif03NtpSLZ4Z0MzlPn_PKU0`

## Enable Required APIs

1. Go to [Google Cloud Console](https://console.cloud.google.com/)
2. Select your project (or create a new one)
3. Go to **APIs & Services** > **Library**
4. Enable these APIs:
   - ✅ **Maps JavaScript API** (Required)
   - ✅ **Places API** (Optional, for autocomplete)
   - ✅ **Geocoding API** (Optional, for address conversion)
   - ✅ **Directions API** (Optional, for route display)

## API Key Restrictions (Recommended for Production)

1. Go to **APIs & Services** > **Credentials**
2. Click on your API key
3. Under **API restrictions**, select "Restrict key"
4. Select:
   - Maps JavaScript API
   - Places API (if using)
   - Geocoding API (if using)
   - Directions API (if using)

5. Under **Application restrictions**:
   - For development: Select "None" or "HTTP referrers"
   - For production: Add your domain:
     ```
     http://localhost/*
     https://yourdomain.com/*
     ```

## Test API Key

Open browser console (F12) and check for errors:
- If you see "This API key is not authorized", enable the APIs above
- If you see "RefererNotAllowedMapError", add your domain to restrictions
- If you see "ApiNotActivatedMapError", enable Maps JavaScript API

## Quick Fix Checklist

- [ ] Maps JavaScript API is enabled
- [ ] API key is correct: `AIzaSyAXuCpsp6rAif03NtpSLZ4Z0MzlPn_PKU0`
- [ ] No domain restrictions blocking localhost (for development)
- [ ] Billing is enabled (Google requires billing for Maps API)
- [ ] Check browser console for specific error messages

## Billing Note

Google Maps API requires a billing account, but they provide $200 free credit per month which covers most small to medium usage.

## Verify in Browser

1. Open admin panel: `http://localhost/admin/login`
2. Login and go to dashboard
3. Open browser console (F12)
4. Check for any Google Maps errors
5. The map should load with your API key

