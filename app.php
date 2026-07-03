<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="theme-color" content="#0B0E14">
  <title>Momentum</title>
  <link rel="manifest" href="manifest.json">
  <link rel="apple-touch-icon" href="assets/icon-192.png">
  <meta name="apple-mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
  <meta name="apple-mobile-web-app-title" content="Momentum">
  <link rel="stylesheet" href="styles.css">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
  <div class="app">
    <!-- Timer -->
    <div class="timer-bar">
      <div class="left">
        <span id="timerLabel" class="label">Focus</span>
        <span id="timerDisplay" class="time">0:00</span>
      </div>
      <div class="controls">
        <button id="timerPlay" class="timer-btn">▶</button>
        <button id="timerReset" class="timer-btn">↺</button>
        <select id="timerSelect">
          <option>Focus</option>
          <option>Deep Work</option>
          <option>Meeting</option>
          <option>Break</option>
          <option>Exercise</option>
        </select>
      </div>
    </div>

    <main>
      <!-- Dashboard -->
      <div id="dashboard" class="view active">
        <div class="dash-header">
          <div>
            <h1 id="greeting"></h1>
            <p id="dateText" class="date"></p>
          </div>
          <div id="streakBadge" class="streak" style="display:none"></div>
        </div>
        <div class="stats">
          <div class="stat"><span class="icon">😴</span><span id="sSleep" class="value">—</span><span class="label">Sleep</span></div>
          <div class="stat"><span class="icon">⚡</span><span id="sEnergy" class="value">—</span><span class="label">Energy</span></div>
          <div class="stat"><span class="icon">✓</span><span id="sHabits" class="value">0</span><span class="label">Habits</span></div>
          <div class="stat"><span id="sMoodIcon" class="icon">😐</span><span id="sMood" class="value" style="font-size:14px">—</span><span class="label">Mood</span></div>
        </div>
        <div id="focusCard" class="card" style="display:none"><h3>🎯 Today's Focus</h3><div id="focusList" class="planned"></div></div>
        <div class="card"><h3>📅 This Week</h3><div id="weekGrid" class="week"></div></div>
        <div class="card wisdom"><p>"Every action you take is a vote for the type of person you wish to become."</p><span>— Atomic Habits</span></div>
      </div>

      <!-- Daily Log -->
      <div id="log" class="view">
        <div class="header">
          <h2>Daily Voice Log</h2>
          <p>Record a 5-minute brain dump of your day, reflections, and tomorrow's plan</p>
        </div>
        
        <div id="voiceRecorder" class="voice-recorder">
          <button id="micBtn" class="mic-btn">🎙️</button>
          <p id="recStatus" class="rec-status">Tap to start recording</p>
          <div id="recTimer" class="rec-timer" style="display:none">0:00</div>
        </div>
        
        <div id="aiProcessing" class="ai-processing" style="display:none">
          <div class="spinner"></div>
          <p>AI is analyzing your day...</p>
        </div>

        <div id="aiResult" class="ai-result" style="display:none">
          <h3>Transcribed:</h3>
          <p id="transcriptText" class="transcript-text"></p>
          
          <h3>Extracted Data:</h3>
          <div id="extractedDataPre" class="extracted-data"></div>
          
          <button id="saveAiData" class="btn primary" style="margin-bottom: 8px;">Looks Good, Fill Log!</button>
          <button id="discardAiData" class="btn secondary">Discard</button>
        </div>
        <p id="trackNote" class="note"></p>

        <hr style="margin: 24px 0; border: none; border-top: 1px solid rgba(255,255,255,0.1);">

        <h3>Manual Adjustments</h3>
        <p style="color:rgba(255,255,255,0.6); font-size:14px; margin-bottom:16px;">The AI uses your voice to fill these out automatically.</p>

        <!-- Merged Forms Below -->
        <div class="reflect-card"><div class="top"><span class="icon">🎯</span><h3>Practical Summary</h3></div><textarea id="rPractical" rows="2"></textarea></div>
        <div class="reflect-card"><div class="top"><span class="icon">💜</span><h3>Emotional State</h3></div><textarea id="rEmotional" rows="2"></textarea></div>
        <div class="reflect-card"><div class="top"><span class="icon">🪞</span><h3>Identity Check</h3></div><textarea id="rIdentity" rows="2"></textarea></div>
        <div class="captures">
          <div><label>🏆 Today's win</label><input type="text" id="rWins"></div>
          <div><label>🌊 Challenge</label><input type="text" id="rChallenges"></div>
          <div><label>🙏 Gratitude</label><input type="text" id="rGratitude"></div>
        </div>

        <div class="header" style="margin-top: 32px;"><h2>Plan Tomorrow</h2></div>
        <div id="planCategories"></div>
        <div class="plan-summary" style="margin-top: 12px; margin-bottom: 24px;"><h3>Tomorrow (<span id="planCount">0</span>)</h3><div id="selectedList" class="list"></div></div>
        
        <button id="saveReflect" class="btn primary">Save Log & Plan</button>
      </div>

      <!-- History -->
      <div id="history" class="view">
        <div class="header"><h2>Your Journey</h2><p id="entryCount">0 entries</p></div>
        <div id="historyList"></div>
      </div>

      <!-- Settings -->
      <div id="settings" class="view">
        <div class="header"><h2>Settings</h2><p>Manage your account</p></div>
        <div class="settings-section">
          <h3>Account</h3>
          <div class="settings-row"><span>Username</span><span id="setUser">—</span></div>
          <div class="settings-row"><span>Member since</span><span id="setDate">—</span></div>
          <div class="settings-row"><span>Total entries</span><span id="setEntries">0</span></div>
        </div>
        <div class="settings-section">
          <h3>Appearance</h3>
          <button id="themeToggle" class="btn secondary">☀️ Switch to Light Mode</button>
        </div>
        <div class="settings-section">
          <h3>Change Password</h3>
          <div id="passError" class="error"></div>
          <div id="passSuccess" class="success"></div>
          <div class="captures">
            <div><label>Current Password</label><input type="password" id="curPass" placeholder="Enter current password"></div>
            <div><label>New Password</label><input type="password" id="newPass" placeholder="New password (min 6 chars)"></div>
            <div><label>Confirm New</label><input type="password" id="conPass" placeholder="Confirm new password"></div>
          </div>
          <button id="changePass" class="btn secondary">Change Password</button>
        </div>
        <button id="logout" class="btn danger">Sign Out</button>
      </div>
    </main>

    <!-- Nav -->
    <nav>
      <button class="active" data-view="dashboard"><span class="icon">📊</span><span class="text">Today</span></button>
      <button data-view="log"><span class="icon">🎙️</span><span class="text">Log</span></button>
      <button data-view="history"><span class="icon">📅</span><span class="text">History</span></button>
      <button data-view="settings"><span class="icon">⚙️</span><span class="text">Settings</span></button>
    </nav>
  </div>
  <script>
    if ('serviceWorker' in navigator) {
      window.addEventListener('load', () => {
        navigator.serviceWorker.register('sw.js');
      });
    }
  </script>
  <script src="app.js"></script>
</body>
</html>
