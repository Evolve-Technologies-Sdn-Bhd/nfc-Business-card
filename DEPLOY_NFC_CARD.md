# 🔗 NFC Card Deployment Guide

## Problem
Your NFC card is programmed with `http://localhost:3000` or similar, which only works on your computer. To work from any device, you need a **public URL**.

---

## ✅ Solution 1: Quick Testing with ngrok (Temporary Public URL)

### Step 1: Install ngrok

1. Download ngrok: https://ngrok.com/download
2. Create free account: https://dashboard.ngrok.com/signup
3. Extract ngrok.exe to a folder (e.g., `C:\ngrok\`)

### Step 2: Setup ngrok

```powershell
# Navigate to ngrok folder
cd C:\ngrok

# Add your auth token (get from https://dashboard.ngrok.com/get-started/your-authtoken)
.\ngrok config add-authtoken YOUR_AUTH_TOKEN_HERE
```

### Step 3: Start Your Dev Server

```powershell
cd C:\Users\tanye\nfc-business-card
npm run dev
```

### Step 4: Create Public Tunnel

Open a **new PowerShell window**:

```powershell
cd C:\ngrok
.\ngrok http 3000
```

You'll see output like:
```
Forwarding   https://abc123.ngrok.io -> http://localhost:3000
```

### Step 5: Reprogram Your NFC Card

Use the ngrok URL to reprogram your NFC card:
```
https://abc123.ngrok.io/LandingPage/profile
```

**Note:** Free ngrok URLs change each time you restart ngrok. For a permanent URL, upgrade to ngrok paid plan or deploy to production.

---

## ✅ Solution 2: Deploy to Production (Permanent)

### Frontend Deployment (Vercel - Recommended)

#### Step 1: Push Code to GitHub
```powershell
cd C:\Users\tanye\nfc-business-card
git add .
git commit -m "Prepare for deployment"
git push origin main
```

#### Step 2: Deploy to Vercel

1. Go to https://vercel.com
2. Sign up/login with GitHub
3. Click "New Project"
4. Import your `nfc-business-card` repository
5. Configure:
   - **Root Directory:** `frontend`
   - **Framework Preset:** Nuxt.js
   - **Build Command:** `npm run build`
   - **Output Directory:** `.output/public`

6. Add Environment Variables:
   ```
   NUXT_PUBLIC_API_BASE_URL=https://your-backend-url.com/api
   NUXT_PUBLIC_APP_URL=https://your-app.vercel.app
   ```

7. Click "Deploy"

#### Step 3: Get Your Production URL
After deployment, you'll get a URL like:
```
https://nfc-business-card.vercel.app
```

### Backend Deployment Options

#### Option A: DigitalOcean App Platform

1. Go to https://cloud.digitalocean.com
2. Create new App
3. Connect GitHub repository
4. Select `backend` folder
5. Set environment variables from `.env`
6. Deploy

#### Option B: Laravel Forge (Easiest for Laravel)

1. Go to https://forge.laravel.com
2. Connect your server (DigitalOcean, AWS, etc.)
3. Create new site
4. Deploy from GitHub
5. Configure environment variables

#### Option C: Heroku

```powershell
# Install Heroku CLI
# https://devcenter.heroku.com/articles/heroku-cli

# Login and create app
heroku login
cd backend
heroku create your-app-name

# Add MySQL addon
heroku addons:create jawsdb:kitefin

# Deploy
git push heroku main

# Set environment variables
heroku config:set APP_ENV=production
heroku config:set APP_DEBUG=false
# ... add other env variables
```

### Step 4: Update NFC Card

Once deployed, update your NFC card with the production URL:
```
https://nfc-business-card.vercel.app/LandingPage/profile
```

---

## ✅ Solution 3: Use Local Network (No Internet Required)

If you want NFC to work only on your local WiFi network:

### Step 1: Update NFC Card URL

Program your NFC card with your local IP:
```
http://192.168.68.126:3000/LandingPage/profile
```

**Limitations:**
- Only works when connected to same WiFi
- IP may change if router reassigns addresses
- Requires your dev server to be running

### Step 2: Add Firewall Rule

```powershell
# Run as Administrator
New-NetFirewallRule -DisplayName "Nuxt Dev Server" -Direction Inbound -Protocol TCP -LocalPort 3000 -Action Allow
```

### Step 3: Start Server

```powershell
npm run dev
```

---

## 📋 Comparison

| Solution | Cost | Permanence | Setup Time | Best For |
|----------|------|------------|------------|----------|
| **ngrok** | Free (limited) | Temporary | 5 min | Quick testing |
| **Production Deploy** | Varies | Permanent | 30-60 min | Production use |
| **Local Network** | Free | Works locally | 5 min | Testing only |

---

## 🎯 Recommended Approach

**For Testing:**
1. Use ngrok for immediate testing
2. Start with: `ngrok http 3000`
3. Update NFC card with ngrok URL

**For Production:**
1. Deploy frontend to Vercel (free tier available)
2. Deploy backend to DigitalOcean or Laravel Forge
3. Update NFC card with production URL
4. **This URL won't change** ✅

---

## 🔧 Troubleshooting

### NFC Card Still Shows Error

1. **Clear browser cache** on phone
2. **Verify URL** is correct on NFC card
3. **Test URL** in browser first before tapping NFC
4. **Check server** is running

### ngrok Tunnel Closed

- Free ngrok tunnels close after 2 hours
- Restart with: `ngrok http 3000`
- URL will change (need to reprogram NFC card)

### Production Deployment Issues

1. **Backend not connecting:**
   - Check CORS settings in `backend/config/cors.php`
   - Update `FRONTEND_URL` in backend `.env`

2. **Frontend not loading:**
   - Verify `NUXT_PUBLIC_API_BASE_URL` points to backend
   - Check build logs in Vercel

---

## 📞 Next Steps

**Choose your path:**

1. **Quick Test** → Use ngrok (5 minutes)
2. **Production Ready** → Deploy to Vercel + DigitalOcean (1 hour)
3. **Local Only** → Use local IP (5 minutes, WiFi only)

Let me know which solution you'd like to proceed with!
