# Momentum - Daily Productivity Companion (PHP Version)

A productivity tracking app with user authentication, designed for shared PHP hosting.

## Features

- 🔐 User registration & login (password hashed with bcrypt)
- 📊 Daily check-ins (sleep, energy, mood, nutrition, movement)
- ✓ Habit tracking (12 pre-defined habits)
- 💭 Evening reflections
- 🎯 Tomorrow planning
- 📅 Personal history
- ⏱️ Focus timer
- 🔥 Streak tracking

## Requirements

- PHP 7.4+ with PDO SQLite extension (standard on most hosts)
- No MySQL needed - uses SQLite file database

## Files

```
momentum-php/
├── index.html      # Login/register page
├── app.php         # Main application (auth protected)
├── api.php         # Backend API
├── app.js          # Frontend JavaScript
├── styles.css      # Styles
├── .htaccess       # Security rules
└── data/           # Database folder (auto-created)
    └── momentum.db # SQLite database (auto-created)
```

## Deployment on cPanel

### Step 1: Upload Files

1. Log into cPanel
2. Open **File Manager**
3. Navigate to your domain folder (e.g., `public_html` or a subdomain folder)
4. Upload all files:
   - `index.html`
   - `app.php`
   - `api.php`
   - `app.js`
   - `styles.css`
   - `.htaccess`

### Step 2: Set Permissions

The `data` folder will be created automatically, but you may need to ensure the web server can write to it:

1. In File Manager, create a folder called `data`
2. Right-click the `data` folder → **Change Permissions**
3. Set to `755` or `775`

### Step 3: Test

1. Visit your domain (e.g., `https://yourdomain.com/momentum/` or just `https://yourdomain.com/` if in root)
2. Create an account
3. Start tracking!

## How It Works

- **Database**: SQLite stores everything in `data/momentum.db`
- **Sessions**: PHP sessions handle authentication
- **Security**: Passwords hashed with `password_hash()` (bcrypt)
- **Data**: Each user's entries are separate and private

## Troubleshooting

### "Database error" on first load
- Make sure PHP has PDO SQLite extension enabled
- Check that the web server can create files in the directory

### Can't create account
- Ensure the `data` folder has write permissions (755 or 775)
- Check PHP error logs in cPanel

### Blank page
- Check PHP version (needs 7.4+)
- Enable PHP error display temporarily to see errors

## Security Notes

1. The `.htaccess` file blocks direct access to the database
2. Never share or expose the `data/momentum.db` file
3. Passwords are securely hashed - not stored in plain text

## No Node.js Required!

This version is pure PHP - no npm, no build process, no command line needed.
Just upload and use.
