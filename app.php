<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: index.html');
    exit;
}
?>
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

      <!-- Track -->
      <div id="track" class="view">
        <div class="header">
          <h2>Voice Journal</h2>
          <p>Record a 5-minute brain dump of your day</p>
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
          
          <button id="saveAiData" class="btn primary" style="margin-bottom: 8px;">Looks Good, Save It!</button>
          <button id="discardAiData" class="btn secondary">Discard</button>
        </div>
        <p id="trackNote" class="note"></p>
      </div>

      <!-- Reflect -->
      <div id="reflect" class="view">
        <div class="header"><h2>Evening Reflection</h2><p>Three dimensions of your day</p></div>
        <div class="reflect-card"><div class="top"><span class="icon">🎯</span><h3>Practical</h3></div><p>What actually happened today?</p><textarea id="rPractical" rows="3" placeholder="Today I accomplished..."></textarea></div>
        <div class="reflect-card"><div class="top"><span class="icon">💜</span><h3>Emotional</h3></div><p>How did you feel?</p><textarea id="rEmotional" rows="3" placeholder="I felt..."></textarea></div>
        <div class="reflect-card"><div class="top"><span class="icon">🪞</span><h3>Identity</h3></div><p>Who were you today?</p><textarea id="rIdentity" rows="3" placeholder="Today I was someone who..."></textarea></div>
        <div class="captures">
          <div><label>🏆 Today's win</label><input type="text" id="rWins" placeholder="One thing I'm proud of..."></div>
          <div><label>🌊 Challenge</label><input type="text" id="rChallenges" placeholder="Something hard..."></div>
          <div><label>🙏 Gratitude</label><input type="text" id="rGratitude" placeholder="I'm grateful for..."></div>
        </div>
        <button id="aiBtn" class="ai-btn">✨ Get AI Insight</button>
        <div id="aiInsight" class="ai-insight"></div>
        <button id="saveReflect" class="btn primary">Save Reflection</button>
      </div>

      <!-- Plan -->
      <div id="plan" class="view">
        <div class="header"><h2>Plan Tomorrow</h2><p>Choose 3-5 habits</p></div>
        <div class="tip"><span class="icon">💡</span><div><strong>Habit Stacking</strong><p>"After I [CURRENT], I will [NEW]"</p></div></div>
        <div id="planCategories"></div>
        <div class="plan-summary"><h3>Tomorrow (<span id="planCount">0</span>)</h3><div id="selectedList" class="list"></div></div>
        <div class="reminder"><strong>Never miss twice.</strong> If you slip up today, getting back tomorrow is what matters.</div>
        <button id="savePlan" class="btn primary">Save Plan</button>
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
      <button data-view="track"><span class="icon">✓</span><span class="text">Track</span></button>
      <button data-view="reflect"><span class="icon">💭</span><span class="text">Reflect</span></button>
      <button data-view="plan"><span class="icon">🎯</span><span class="text">Plan</span></button>
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
