// State
const state = { user: null, entries: [], today: null, planned: [], timer: 0, timerOn: false, timerInt: null };

// Data
const HABITS = [
  { id: 'h1', name: 'Morning routine', cat: 'foundation', icon: '🌅' },
  { id: 'h2', name: 'Exercise', cat: 'energy', icon: '💪' },
  { id: 'h3', name: 'Read 10 pages', cat: 'growth', icon: '📚' },
  { id: 'h4', name: 'Meditate', cat: 'mindset', icon: '🧘' },
  { id: 'h5', name: 'Healthy eating', cat: 'energy', icon: '🥗' },
  { id: 'h6', name: 'No phone first hour', cat: 'focus', icon: '📵' },
  { id: 'h7', name: 'Deep work block', cat: 'focus', icon: '🎯' },
  { id: 'h8', name: 'Gratitude practice', cat: 'mindset', icon: '🙏' },
  { id: 'h9', name: 'Walk outside', cat: 'energy', icon: '🚶' },
  { id: 'h10', name: 'Journal', cat: 'growth', icon: '✍️' },
  { id: 'h11', name: 'Connect with someone', cat: 'connection', icon: '💬' },
  { id: 'h12', name: 'Plan tomorrow', cat: 'foundation', icon: '📋' }
];
const MOODS = [
  { id: 'great', emoji: '😊', label: 'Great' },
  { id: 'good', emoji: '🙂', label: 'Good' },
  { id: 'neutral', emoji: '😐', label: 'Okay' },
  { id: 'low', emoji: '😔', label: 'Low' },
  { id: 'struggling', emoji: '😞', label: 'Hard' }
];
const MOVEMENTS = ['Sedentary', 'Light', 'Moderate', 'Active', 'Intense'];
const DECISIONS = ['Light', 'Normal', 'Heavy', 'Exhausting'];
const CATS = { foundation: '🏠 Foundation', energy: '⚡ Energy', focus: '🎯 Focus', growth: '📈 Growth', mindset: '🧠 Mindset', connection: '💬 Connection' };
const AI_INSIGHTS = [
  "Your sleep and energy correlation looks positive. Consider maintaining your current routine.",
  "Each habit you complete is a vote for the person you're becoming. Keep going!",
  "Notice how your mood connects to movement. Small walks can make a big difference.",
  "Great job tracking today! Consistency in reflection builds self-awareness.",
  "Compare yourself to who you were yesterday, not to who someone else is today.",
  "The compound effect of small habits is remarkable. You're on the right path."
];

// Helpers
const $ = id => document.getElementById(id);
const $$ = sel => document.querySelectorAll(sel);

async function api(action, method = 'GET', data = null) {
  const opts = { method, headers: { 'Content-Type': 'application/json' } };
  if (data) opts.body = JSON.stringify(data);
  const res = await fetch('api.php?action=' + action, opts);
  return res.json();
}

const fmtTime = s => { const m = Math.floor(s / 60), sec = s % 60; return m + ':' + String(sec).padStart(2, '0'); };
const fmtDate = d => new Date(d).toLocaleDateString('en-US', { weekday: 'short', month: 'short', day: 'numeric' });
const getGreeting = () => { const h = new Date().getHours(); return h < 12 ? 'Good morning' : h < 17 ? 'Good afternoon' : 'Good evening'; };
const getMoodEmoji = m => (MOODS.find(x => x.id === m) || {}).emoji || '😐';
const scoreColor = s => s >= 7 ? '#10b981' : s >= 5 ? '#f59e0b' : '#ef4444';

// Init
async function init() {
  try {
    const auth = await api('me');
    if (!auth.authenticated) {
      window.location.href = 'index.html';
      return;
    }
    state.user = auth.user;
    await loadData();
    setupUI();
    render();
  } catch (err) {
    console.error('Init error:', err);
    window.location.href = 'index.html';
  }
}

async function loadData() {
  state.today = await api('today');
  state.entries = await api('entries');
  state.planned = await api('planned');
}

function setupUI() {
  // Nav
  $$('nav button').forEach(b => {
    b.onclick = () => {
      $$('nav button').forEach(x => x.classList.remove('active'));
      b.classList.add('active');
      $$('.view').forEach(v => v.classList.remove('active'));
      $(b.dataset.view).classList.add('active');
    };
  });

  // Timer
  $('timerPlay').onclick = () => {
    state.timerOn = !state.timerOn;
    $('timerPlay').textContent = state.timerOn ? '⏸' : '▶';
    $('timerPlay').classList.toggle('active', state.timerOn);
    if (state.timerOn) {
      state.timerInt = setInterval(() => {
        state.timer++;
        $('timerDisplay').textContent = fmtTime(state.timer);
      }, 1000);
    } else {
      clearInterval(state.timerInt);
    }
  };
  
  $('timerReset').onclick = () => {
    state.timerOn = false;
    state.timer = 0;
    clearInterval(state.timerInt);
    $('timerPlay').textContent = '▶';
    $('timerPlay').classList.remove('active');
    $('timerDisplay').textContent = '0:00';
  };
  
  $('timerSelect').onchange = e => $('timerLabel').textContent = e.target.value;

  // Track sliders
  $('sleepSlider').oninput = e => { state.today.sleep = +e.target.value; $('sleepVal').textContent = e.target.value + '/10'; };
  $('energySlider').oninput = e => { state.today.energy = +e.target.value; $('energyVal').textContent = e.target.value + '/10'; };
  $('foodSlider').oninput = e => { state.today.food = +e.target.value; $('foodVal').textContent = e.target.value + '/10'; };

  // Moods
  $('moodGrid').innerHTML = MOODS.map(m => '<button data-mood="' + m.id + '"><span class="emoji">' + m.emoji + '</span><span>' + m.label + '</span></button>').join('');
  $$('#moodGrid button').forEach(b => {
    b.onclick = () => {
      $$('#moodGrid button').forEach(x => x.classList.remove('active'));
      b.classList.add('active');
      state.today.mood = b.dataset.mood;
    };
  });

  // Movement
  $('movementOpts').innerHTML = MOVEMENTS.map((m, i) => '<button data-val="' + (i + 1) + '">' + m + '</button>').join('');
  $$('#movementOpts button').forEach(b => {
    b.onclick = () => {
      $$('#movementOpts button').forEach(x => x.classList.remove('active'));
      b.classList.add('active');
      state.today.movement = +b.dataset.val;
    };
  });

  // Decisions
  $('decisionOpts').innerHTML = DECISIONS.map((d, i) => '<button data-val="' + (i + 1) + '">' + d + '</button>').join('');
  $$('#decisionOpts button').forEach(b => {
    b.onclick = () => {
      $$('#decisionOpts button').forEach(x => x.classList.remove('active'));
      b.classList.add('active');
      state.today.decisions = +b.dataset.val;
    };
  });

  // Habits
  $('habitsGrid').innerHTML = HABITS.map(h => '<button data-id="' + h.id + '"><span class="icon">' + h.icon + '</span><span class="name">' + h.name + '</span><span class="check">✓</span></button>').join('');
  $$('#habitsGrid button').forEach(b => {
    b.onclick = () => {
      if (!state.today.habits) state.today.habits = [];
      const i = state.today.habits.indexOf(b.dataset.id);
      if (i === -1) state.today.habits.push(b.dataset.id);
      else state.today.habits.splice(i, 1);
      b.classList.toggle('active', state.today.habits.includes(b.dataset.id));
      $('habitCount').textContent = state.today.habits.length;
    };
  });

  // Reflect inputs
  $('rPractical').oninput = e => state.today.reflection_practical = e.target.value;
  $('rEmotional').oninput = e => state.today.reflection_emotional = e.target.value;
  $('rIdentity').oninput = e => state.today.reflection_identity = e.target.value;
  $('rWins').oninput = e => state.today.wins = e.target.value;
  $('rChallenges').oninput = e => state.today.challenges = e.target.value;
  $('rGratitude').oninput = e => state.today.gratitude = e.target.value;

  // Plan categories
  const cats = {};
  HABITS.forEach(h => { if (!cats[h.cat]) cats[h.cat] = []; cats[h.cat].push(h); });
  $('planCategories').innerHTML = Object.entries(cats).map(([c, hs]) => 
    '<div class="category"><h4>' + CATS[c] + '</h4><div class="list">' +
    hs.map(h => '<button data-id="' + h.id + '"><span class="icon">' + h.icon + '</span><span class="name">' + h.name + '</span><span class="check">✓</span></button>').join('') +
    '</div></div>'
  ).join('');
  
  $$('#planCategories button').forEach(b => {
    b.onclick = () => {
      const i = state.planned.indexOf(b.dataset.id);
      if (i === -1) state.planned.push(b.dataset.id);
      else state.planned.splice(i, 1);
      b.classList.toggle('active', state.planned.includes(b.dataset.id));
      renderPlanSummary();
    };
  });

  // Save buttons
  $('saveTrack').onclick = saveEntry;
  $('saveReflect').onclick = saveEntry;
  $('savePlan').onclick = async () => {
    await api('planned', 'POST', { habits: state.planned });
    renderDashboard();
  };

  // AI
  $('aiBtn').onclick = () => {
    $('aiBtn').textContent = '✨ Generating...';
    $('aiBtn').disabled = true;
    setTimeout(() => {
      $('aiInsight').textContent = AI_INSIGHTS[Math.floor(Math.random() * AI_INSIGHTS.length)];
      $('aiInsight').style.display = 'block';
      $('aiBtn').textContent = '✨ Get AI Insight';
      $('aiBtn').disabled = false;
    }, 1000);
  };

  // Password
  $('changePass').onclick = async () => {
    $('passError').style.display = 'none';
    $('passSuccess').style.display = 'none';
    
    const cur = $('curPass').value;
    const nw = $('newPass').value;
    const con = $('conPass').value;
    
    if (nw !== con) {
      $('passError').textContent = 'Passwords do not match';
      $('passError').style.display = 'block';
      return;
    }
    if (nw.length < 6) {
      $('passError').textContent = 'Password must be at least 6 characters';
      $('passError').style.display = 'block';
      return;
    }
    
    const res = await api('change-password', 'POST', { currentPassword: cur, newPassword: nw });
    
    if (res.success) {
      $('passSuccess').textContent = 'Password changed!';
      $('passSuccess').style.display = 'block';
      $('curPass').value = $('newPass').value = $('conPass').value = '';
    } else {
      $('passError').textContent = res.error || 'Failed to change password';
      $('passError').style.display = 'block';
    }
  };

  // Logout
  $('logout').onclick = async () => {
    await api('logout', 'POST');
    window.location.href = 'index.html';
  };
}

async function saveEntry() {
  state.today.completed = true;
  state.today.date = new Date().toISOString().split('T')[0];
  
  const res = await api('save-entry', 'POST', state.today);
  state.today = res;
  state.entries = await api('entries');
  
  const auth = await api('me');
  state.user = auth.user;
  
  renderDashboard();
  renderHistory();
  renderSettings();
  
  $('trackNote').textContent = '✓ Saved!';
  $('saveTrack').textContent = '✓ Saved';
}

function render() {
  renderDashboard();
  renderTrack();
  renderReflect();
  renderPlan();
  renderHistory();
  renderSettings();
}

function renderDashboard() {
  $('greeting').textContent = getGreeting() + ', ' + state.user.username;
  $('dateText').textContent = new Date().toLocaleDateString('en-US', { weekday: 'long', month: 'long', day: 'numeric' });
  
  if (state.user.streak > 0) {
    $('streakBadge').style.display = 'block';
    $('streakBadge').textContent = '🔥 ' + state.user.streak + ' day' + (state.user.streak > 1 ? 's' : '');
  } else {
    $('streakBadge').style.display = 'none';
  }

  const t = state.today || {};
  $('sSleep').textContent = t.sleep || '—';
  $('sEnergy').textContent = t.energy || '—';
  $('sHabits').textContent = (t.habits || []).length;
  $('sMood').textContent = t.mood || '—';
  $('sMoodIcon').textContent = getMoodEmoji(t.mood);

  if (state.planned.length > 0) {
    $('focusCard').style.display = 'block';
    $('focusList').innerHTML = state.planned.map(id => {
      const h = HABITS.find(x => x.id === id);
      const done = (t.habits || []).includes(id);
      return h ? '<div class="habit ' + (done ? 'done' : '') + '">' + h.icon + ' ' + h.name + (done ? ' ✓' : '') + '</div>' : '';
    }).join('');
  } else {
    $('focusCard').style.display = 'none';
  }

  let week = '';
  const todayStr = new Date().toISOString().split('T')[0];
  for (let i = 0; i < 7; i++) {
    const d = new Date();
    d.setDate(d.getDate() - (6 - i));
    const ds = d.toISOString().split('T')[0];
    const e = state.entries.find(x => x.date === ds);
    const isToday = ds === todayStr;
    week += '<div class="day ' + (isToday ? 'today' : '') + ' ' + (e && e.completed ? 'done' : '') + '">' +
      '<span class="name">' + d.toLocaleDateString('en-US', { weekday: 'short' }) + '</span>' +
      '<span class="num">' + d.getDate() + '</span>' +
      (e && e.completed ? '<span class="check">✓</span>' : '') +
      '</div>';
  }
  $('weekGrid').innerHTML = week;
}

function renderTrack() {
  const t = state.today || {};
  $('sleepSlider').value = t.sleep || 5;
  $('sleepVal').textContent = (t.sleep || 5) + '/10';
  $('energySlider').value = t.energy || 5;
  $('energyVal').textContent = (t.energy || 5) + '/10';
  $('foodSlider').value = t.food || 5;
  $('foodVal').textContent = (t.food || 5) + '/10';
  
  $$('#moodGrid button').forEach(b => b.classList.toggle('active', b.dataset.mood === t.mood));
  $$('#movementOpts button').forEach(b => b.classList.toggle('active', +b.dataset.val === t.movement));
  $$('#decisionOpts button').forEach(b => b.classList.toggle('active', +b.dataset.val === t.decisions));
  
  const habits = t.habits || [];
  $$('#habitsGrid button').forEach(b => b.classList.toggle('active', habits.includes(b.dataset.id)));
  $('habitCount').textContent = habits.length;
  
  if (t.completed) {
    $('saveTrack').textContent = '✓ Saved';
    $('trackNote').textContent = 'Progress saved!';
  }
}

function renderReflect() {
  const t = state.today || {};
  $('rPractical').value = t.reflection_practical || '';
  $('rEmotional').value = t.reflection_emotional || '';
  $('rIdentity').value = t.reflection_identity || '';
  $('rWins').value = t.wins || '';
  $('rChallenges').value = t.challenges || '';
  $('rGratitude').value = t.gratitude || '';
}

function renderPlan() {
  $$('#planCategories button').forEach(b => b.classList.toggle('active', state.planned.includes(b.dataset.id)));
  renderPlanSummary();
}

function renderPlanSummary() {
  $('planCount').textContent = state.planned.length;
  if (state.planned.length === 0) {
    $('selectedList').innerHTML = '<span style="color:rgba(255,255,255,0.5)">Select habits above</span>';
  } else {
    $('selectedList').innerHTML = state.planned.map(id => {
      const h = HABITS.find(x => x.id === id);
      return h ? '<span class="item">' + h.icon + ' ' + h.name + '</span>' : '';
    }).join('');
  }
}

function renderHistory() {
  $('entryCount').textContent = state.entries.length + ' entries';
  
  if (state.entries.length === 0) {
    $('historyList').innerHTML = '<div class="empty"><span class="icon">📝</span><p>Start tracking to see history</p></div>';
    return;
  }
  
  $('historyList').innerHTML = state.entries.map((e, i) => {
    const avg = ((e.sleep || 0) + (e.energy || 0)) / 2;
    const habits = e.habits || [];
    return '<div class="history-card">' +
      '<button class="top" onclick="toggleHistory(' + i + ')">' +
        '<div class="left">' +
          '<span class="score" style="background:' + scoreColor(avg) + '">' + avg.toFixed(1) + '</span>' +
          '<div><div class="date">' + fmtDate(e.date) + '</div><div class="meta">' + habits.length + ' habits • ' + (e.mood || 'no mood') + '</div></div>' +
        '</div>' +
        '<span class="arrow">▶</span>' +
      '</button>' +
      '<div id="hd' + i + '" class="details">' +
        '<div class="row"><span>😴 Sleep:</span><span>' + e.sleep + '/10</span></div>' +
        '<div class="row"><span>⚡ Energy:</span><span>' + e.energy + '/10</span></div>' +
        '<div class="row"><span>🍽️ Nutrition:</span><span>' + e.food + '/10</span></div>' +
        (e.wins ? '<div class="text"><strong>🏆</strong> ' + e.wins + '</div>' : '') +
        (e.gratitude ? '<div class="text"><strong>🙏</strong> ' + e.gratitude + '</div>' : '') +
        (habits.length > 0 ? '<div class="text"><strong>✓ Habits:</strong><div class="habit-list">' +
          habits.map(id => { const h = HABITS.find(x => x.id === id); return h ? '<span class="habit-tag">' + h.icon + ' ' + h.name + '</span>' : ''; }).join('') +
          '</div></div>' : '') +
      '</div>' +
    '</div>';
  }).join('');
}

function renderSettings() {
  $('setUser').textContent = state.user.username;
  $('setDate').textContent = state.user.created_at ? new Date(state.user.created_at).toLocaleDateString() : '—';
  $('setEntries').textContent = state.entries.length;
}

window.toggleHistory = function(i) {
  $('hd' + i).classList.toggle('open');
};

// Start
init();
