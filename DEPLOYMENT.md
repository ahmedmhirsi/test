# Deployment Guide (Railway)

We will use **Railway** to host your site. It is modern, compatible with Symfony, and easy to connect to GitHub.

## Step 1: Push Code to GitHub
Ensure all your latest changes (including `Procfile` and `ads.txt`) are committed and pushed.

```bash
git add .
git commit -m "Prepare for deployment"
git push origin Marketing
```

## Step 2: Create Railway Account
1. Go to [railway.app](https://railway.app/).
2. Login with **GitHub**.

## Step 3: Create Project
1. Customer "New Project" -> "Deploy from GitHub repo".
2. Select your repository (`Marketing`).
3. Click "Deploy Now".

## Step 4: Configure Variables
1. Go to your project dashboard in Railway.
2. Click on the "Variables" tab.
3. Add the following:
   - `APP_ENV` = `prod`
   - `APP_SECRET` = (Generate a random string, e.g., `cc673e4078a705294565789`)
   - `DATABASE_URL` = (Railway will provide this if you add a Database service)

## Step 5: Add Database (Required)
1. In Railway, right-click the canvas -> "New Service" -> "Database" -> "PostgreSQL".
2. Railway will automatically inject `DATABASE_URL` into your app.
3. **Redeploy** your app (it usually happens automatically).

## Step 6: Connect Domain
1. In Railway, go to "Settings" -> "Public Networking".
2. Click "Custom Domain" and enter your domain (e.g., `smartnexus.com`).
3. Follow the DNS instructions (CNAME record) at your domain registrar (Godaddy, OTP, Namecheap...).

## Final Step: Verification
Once the site is live at `https://your-domain.com`:
- Check `https://your-domain.com/ads.txt`.
- Go to AdSense and click "Verify".
