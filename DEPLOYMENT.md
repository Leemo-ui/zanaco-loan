# ZANACO Loan - Deployment Guide for Render

This guide walks you through deploying the ZANACO Loan application to Render with a managed MySQL database.

## Overview

- **Web App Host**: Render (free tier available, auto-deploys from GitHub)
- **Database**: Render PostgreSQL/MySQL (managed, with backups)
- **SMTP**: Gmail App Password or SendGrid
- **Total Setup Time**: ~20-30 minutes
- **Estimated Cost**: $7-12/month (free tier available; $7 for MySQL, $5.50+ for web service after free trial)

---

## Step 1: Prepare Your Repository

### 1.1 Remove local `.env` from Git

Never commit `.env` with credentials. Remove it from version control:

```bash
cd /home/johnlimo/zanaco-loan
git rm --cached backend/handlers/.env
echo "backend/handlers/.env" >> .gitignore
git add .gitignore
git commit -m "Remove .env from tracking and add to .gitignore"
git push origin master
```

### 1.2 Verify files are in repo

Check that the following files exist in your GitHub repo:
- `Dockerfile` ✓ (just created)
- `.dockerignore` ✓ (just created)
- `.env.example` ✓ (just created)
- `backend/config/db.php` ✓ (uses env vars)
- `backend/handlers/submit_loan.php` ✓
- `database/schema.sql` ✓
- All frontend files (HTML, CSS, JS) ✓

```bash
# Verify and push
git add Dockerfile .dockerignore .env.example
git commit -m "Add deployment configuration files"
git push origin master
```

---

## Step 2: Create a Render Account & Connect GitHub

1. Go to **https://render.com** and sign up (free account)
2. Click **"Dashboard"** → **"New +"** → **"Web Service"**
3. Select **"Connect a repository"** → Choose your GitHub repo: `zanaco-loan`
4. Authorize Render to access your GitHub account

---

## Step 3: Provision a MySQL Database on Render

1. In Render Dashboard, click **"New +"** → **"MySQL"**
2. Fill in:
   - **Name**: `zanaco-loan-db` (or similar)
   - **Database**: `zanaco_loan`
   - **User**: `zanaco_admin` (choose a username)
   - **Region**: Select closest to your target users (e.g., London, Ireland, or US)
   - **Plan**: Free tier or Starter ($7/month)
3. Click **"Create Database"**
4. **Wait 2-3 minutes** for the database to initialize
5. Once ready, you'll see connection details. **Copy**:
   - **Hostname** (External Database URL, e.g., `dpg-abc123.render.com`)
   - **Database Name**: `zanaco_loan`
   - **Username**: `zanaco_admin`
   - **Password** (shown in Connections section, copy immediately)

### 3.1 Initialize the Database Schema

Once the database is created:

1. Click on the database in Render → **"Connections"** tab
2. Copy the **External Database URL** (starts with `mysql://`)
3. In your local terminal, run:

```bash
# Example: Replace with your actual credentials
mysql -h dpg-abc123.render.com -u zanaco_admin -p"YOUR_PASSWORD" zanaco_loan < database/schema.sql
```

Or, use Render's built-in SQL editor:
1. In Render Dashboard, go to your MySQL instance
2. Click **"Connect"** → **"Using external tools"** or paste the URL into MySQL Workbench
3. Run the SQL from `database/schema.sql` manually

---

## Step 4: Configure the Web Service on Render

1. Back in Render Dashboard, click **"New +"** → **"Web Service"**
2. Select your GitHub repo (`zanaco-loan`)
3. Fill in:
   - **Name**: `zanaco-loan` (or similar)
   - **Environment**: `Docker`
   - **Region**: Same region as your MySQL database (important for low latency)
   - **Plan**: Free tier or Starter ($5.50/month minimum after free trial)
4. Click **"Create Web Service"** (Render will build the Docker image and deploy)
5. **Wait 2-3 minutes** for the first deployment

---

## Step 5: Set Environment Variables

### 5.1 On Render Dashboard

1. Go to your Web Service → **"Environment"** tab
2. Add the following variables (get values from Step 3):

```
DB_HOST=dpg-abc123.render.com
DB_PORT=3306
DB_USER=zanaco_admin
DB_PASSWORD=YOUR_DB_PASSWORD
DB_NAME=zanaco_loan

MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-specific-password
MAIL_FROM=your-email@gmail.com
MAIL_FROM_NAME=Zanaco Loans

APP_ENV=production
APP_DEBUG=false
```

3. Click **"Save"** → Render will redeploy automatically

### 5.2 Gmail App Password Setup (Optional but Recommended)

If using Gmail for SMTP, use an **App Password**, not your regular password:

1. Enable 2-Factor Authentication on your Google account
2. Go to **https://myaccount.google.com/apppasswords**
3. Select **"Mail"** and **"Windows/Linux"** (or your OS)
4. Copy the generated 16-character password
5. Use this in `MAIL_PASSWORD` on Render

---

## Step 6: Deploy & Test

### 6.1 Auto-Deploy from GitHub

- Every push to `master` branch will trigger a new build and deploy on Render
- Check deployment status in Render Dashboard → Web Service → **"Logs"**
- Once deployed, your app will be live at: `https://zanaco-loan.onrender.com` (or custom domain)

### 6.2 Test the Application

1. Open **https://zanaco-loan.onrender.com** in your browser
2. Test the loan calculator → apply form → Airtel login flow
3. Submit a test loan application
4. Check the database to verify data was saved:

```bash
# Connect to your remote database and check
mysql -h dpg-abc123.render.com -u zanaco_admin -p"YOUR_PASSWORD" zanaco_loan
mysql> SELECT * FROM loan_applications LIMIT 1;
```

### 6.3 Check Logs for Errors

- In Render Dashboard: Web Service → **"Logs"** tab
- Look for PHP errors, database connection issues, or SMTP failures
- Common issues:
  - **"Cannot connect to DB"**: Check DB credentials and network access
  - **"SMTP failed"**: Check Gmail app password and "Less secure apps" setting
  - **"File not found"**: Check paths in `db.php` and `submit_loan.php`

---

## Step 7: Set Up a Custom Domain (Optional)

1. Go to your Web Service → **"Settings"** → **"Custom Domain"**
2. Add your domain (e.g., `loans.example.com`)
3. Follow Render's DNS instructions to point your domain to Render
4. Render provides free SSL/HTTPS automatically

---

## Step 8: Enable Backups & Monitoring

### 8.1 Database Backups

On Render MySQL instance:
- Go to your MySQL DB → **"Backups"** tab
- Backups are automatic (free tier: 7-day retention)
- No action needed, but ensure it's enabled

### 8.2 Monitoring & Alerts

On Render:
1. Web Service → **"Settings"** → **"Notifications"**
2. Enable email alerts for deployment failures
3. Optionally add error tracking service (Sentry, Logflare)

---

## Troubleshooting

### Issue: "Deployment failed"
- Check **Logs** → Look for build errors
- Ensure `Dockerfile` exists and `EXPOSE 8080` is set
- Verify all required files are in repo

### Issue: "Cannot connect to database"
- Verify `DB_HOST`, `DB_USER`, `DB_PASSWORD` are correct
- Check MySQL instance is in the same region as web service
- Ensure database schema was initialized (`database/schema.sql` was run)

### Issue: "Email not sending"
- Check `MAIL_USERNAME` and `MAIL_PASSWORD` are correct
- For Gmail: Verify App Password is used (not account password)
- Check **Logs** for SMTP error messages

### Issue: "Page shows 500 error"
- Check **Logs** for PHP errors
- Verify `.env` file is loaded correctly in `db.php`
- Ensure `backend/config/db.php` has proper error handling

---

## Cost Breakdown (Render Pricing)

| Service | Free Tier | Paid Tier | Notes |
|---------|-----------|-----------|-------|
| Web Service | 750 hrs/month | $5.50+/month | Auto-sleep after 15 min inactivity on free tier |
| MySQL Database | None | $7/month | 1 GB storage, automated backups |
| **Total** | **Free (limited)** | **~$12-15/month** | Suitable for production small-to-medium apps |

---

## Next Steps (Production Hardening)

- [ ] Set up error logging (Sentry, Papertrail)
- [ ] Add API rate limiting to prevent abuse
- [ ] Implement CORS security headers
- [ ] Enable database backups to S3 (optional)
- [ ] Add uptime monitoring (StatusPage, UptimeRobot)
- [ ] Set up automated email digest of loan applications for admin

---

## Quick Reference: Git & Deployment Commands

```bash
# Remove .env from tracking
git rm --cached backend/handlers/.env
echo "backend/handlers/.env" >> .gitignore
git commit -m "Remove .env from tracking"

# Add deployment files
git add Dockerfile .dockerignore .env.example
git commit -m "Add deployment configuration"

# Push to GitHub (triggers Render auto-deploy)
git push origin master

# Check remote database
mysql -h dpg-abc123.render.com -u zanaco_admin -p"PASSWORD" zanaco_loan -e "SELECT * FROM loan_applications;"
```

---

**Estimated deployment time: 20-30 minutes**
**Your app will be live at: `https://zanaco-loan.onrender.com`**

Good luck! 🚀
