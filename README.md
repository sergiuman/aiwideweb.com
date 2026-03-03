# Momentum - Daily Productivity Companion (PHP Version)

A productivity tracking app with user authentication, designed for shared PHP hosting.

## Capabilities & Features

- 🎙️ **5-Minute Voice Journaling**: Speak freely about your day and let AI analyze it!
- 🧠 **AI-Powered Data Extraction**: Automatically extracts daily metrics like sleep quality, energy levels, and mood from your voice transcript (Powered by OpenAI Whisper & GPT-4o-mini).
- 🔐 **User Registration & Login**: Secure user accounts (password hashed with bcrypt).
- 📊 **Daily Dashboard**: Visualize your habits, energy, and sleep metrics over time.
- ✓ **Habit Tracking**: Define and track 12 core daily habits (e.g. Exercise, Deep Work, Reading).
- 🎯 **Tomorrow Planning**: Select up to 5 habits to focus on for the next day.
- 📅 **Personal History**: Look back at past days, complete with AI reflection summaries.
- ⏱️ **Focus Timer**: Built-in Pomodoro-style timer to stay on track.
- 🔥 **Streak Tracking**: Keep your momentum going with continuous daily check-ins.

## Requirements

- PHP 7.4+ with PDO SQLite extension and `curl` enabled (standard on most hosts)
- No MySQL needed - uses SQLite file database
- **OpenAI API Key**: Required for the Voice Journaling functionality (Whisper & GPT algorithms).

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

- **Microphone Capture**: The browser's native `MediaRecorder` API captures your voice in the frontend and sends the audio payload to the backend API.
- **AI Processing Pipeline**: 
  1. The PHP backend receives the audio and securely passes it to **OpenAI's Whisper API**.
  2. The raw text transcription is then forwarded to **GPT-4o-mini**.
  3. The LLM extracts the unstructured journal text into a strict JSON schema representing your daily metrics and habits.
- **Database**: SQLite stores the extracted user metrics, reflections, and habits in `data/momentum.db`
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
