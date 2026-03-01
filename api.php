<?php
session_start();
header('Content-Type: application/json');

// Database setup
$dbFile = __DIR__ . '/data/momentum.db';
$dbDir = __DIR__ . '/data';

if (!is_dir($dbDir)) {
    mkdir($dbDir, 0755, true);
}

try {
    $db = new PDO('sqlite:' . $dbFile);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Create tables
    $db->exec("
        CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            username TEXT UNIQUE NOT NULL,
            password TEXT NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );
        
        CREATE TABLE IF NOT EXISTS entries (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER NOT NULL,
            date TEXT NOT NULL,
            sleep INTEGER DEFAULT 5,
            energy INTEGER DEFAULT 5,
            mood TEXT DEFAULT 'neutral',
            food INTEGER DEFAULT 5,
            movement INTEGER DEFAULT 3,
            decisions INTEGER DEFAULT 2,
            habits TEXT DEFAULT '[]',
            reflection_practical TEXT DEFAULT '',
            reflection_emotional TEXT DEFAULT '',
            reflection_identity TEXT DEFAULT '',
            wins TEXT DEFAULT '',
            challenges TEXT DEFAULT '',
            gratitude TEXT DEFAULT '',
            completed INTEGER DEFAULT 0,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            UNIQUE(user_id, date)
        );
        
        CREATE TABLE IF NOT EXISTS planned_habits (
            user_id INTEGER PRIMARY KEY,
            habits TEXT DEFAULT '[]'
        );
    ");
} catch (PDOException $e) {
    die(json_encode(['error' => 'Database error: ' . $e->getMessage()]));
}

// Get request data
$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';
$input = json_decode(file_get_contents('php://input'), true) ?? [];

// Helper functions
function response($data) {
    echo json_encode($data);
    exit;
}

function requireAuth() {
    if (!isset($_SESSION['user_id'])) {
        http_response_code(401);
        response(['error' => 'Not authenticated']);
    }
    return $_SESSION['user_id'];
}

function getStreak($db, $userId) {
    $stmt = $db->prepare("SELECT date FROM entries WHERE user_id = ? AND completed = 1 ORDER BY date DESC");
    $stmt->execute([$userId]);
    $entries = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    if (empty($entries)) return 0;
    
    $streak = 0;
    $checkDate = new DateTime();
    
    for ($i = 0; $i < 365; $i++) {
        $dateStr = $checkDate->format('Y-m-d');
        $found = in_array($dateStr, $entries);
        
        if ($found) {
            $streak++;
        } elseif ($i > 0) {
            break;
        }
        $checkDate->modify('-1 day');
    }
    return $streak;
}

// Route handling
switch ($action) {
    
    // ============ AUTH ============
    
    case 'register':
        if ($method !== 'POST') response(['error' => 'Method not allowed']);
        
        $username = trim($input['username'] ?? '');
        $password = $input['password'] ?? '';
        
        if (strlen($username) < 3) {
            response(['error' => 'Username must be at least 3 characters']);
        }
        if (strlen($password) < 6) {
            response(['error' => 'Password must be at least 6 characters']);
        }
        
        // Check if exists
        $stmt = $db->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->execute([strtolower($username)]);
        if ($stmt->fetch()) {
            response(['error' => 'Username already taken']);
        }
        
        // Create user
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $db->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
        $stmt->execute([strtolower($username), $hash]);
        
        $userId = $db->lastInsertId();
        $_SESSION['user_id'] = $userId;
        
        response(['success' => true, 'user' => ['id' => $userId, 'username' => strtolower($username)]]);
        break;
    
    case 'login':
        if ($method !== 'POST') response(['error' => 'Method not allowed']);
        
        $username = trim($input['username'] ?? '');
        $password = $input['password'] ?? '';
        
        $stmt = $db->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([strtolower($username)]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$user || !password_verify($password, $user['password'])) {
            response(['error' => 'Invalid username or password']);
        }
        
        $_SESSION['user_id'] = $user['id'];
        response(['success' => true, 'user' => ['id' => $user['id'], 'username' => $user['username']]]);
        break;
    
    case 'logout':
        session_destroy();
        response(['success' => true]);
        break;
    
    case 'me':
        if (!isset($_SESSION['user_id'])) {
            response(['authenticated' => false]);
        }
        
        $stmt = $db->prepare("SELECT id, username, created_at FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$user) {
            response(['authenticated' => false]);
        }
        
        $user['streak'] = getStreak($db, $user['id']);
        response(['authenticated' => true, 'user' => $user]);
        break;
    
    case 'change-password':
        $userId = requireAuth();
        
        $currentPassword = $input['currentPassword'] ?? '';
        $newPassword = $input['newPassword'] ?? '';
        
        if (strlen($newPassword) < 6) {
            response(['error' => 'New password must be at least 6 characters']);
        }
        
        $stmt = $db->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!password_verify($currentPassword, $user['password'])) {
            response(['error' => 'Current password is incorrect']);
        }
        
        $newHash = password_hash($newPassword, PASSWORD_DEFAULT);
        $stmt = $db->prepare("UPDATE users SET password = ? WHERE id = ?");
        $stmt->execute([$newHash, $userId]);
        
        response(['success' => true]);
        break;
    
    // ============ ENTRIES ============
    
    case 'entries':
        $userId = requireAuth();
        
        $stmt = $db->prepare("SELECT * FROM entries WHERE user_id = ? ORDER BY date DESC");
        $stmt->execute([$userId]);
        $entries = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Parse habits JSON
        foreach ($entries as &$entry) {
            $entry['habits'] = json_decode($entry['habits'], true) ?? [];
        }
        
        response($entries);
        break;
    
    case 'today':
        $userId = requireAuth();
        $today = date('Y-m-d');
        
        $stmt = $db->prepare("SELECT * FROM entries WHERE user_id = ? AND date = ?");
        $stmt->execute([$userId, $today]);
        $entry = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$entry) {
            // Create today's entry
            $stmt = $db->prepare("INSERT INTO entries (user_id, date) VALUES (?, ?)");
            $stmt->execute([$userId, $today]);
            
            $stmt = $db->prepare("SELECT * FROM entries WHERE user_id = ? AND date = ?");
            $stmt->execute([$userId, $today]);
            $entry = $stmt->fetch(PDO::FETCH_ASSOC);
        }
        
        $entry['habits'] = json_decode($entry['habits'], true) ?? [];
        response($entry);
        break;
    
    case 'save-entry':
        $userId = requireAuth();
        
        $date = $input['date'] ?? date('Y-m-d');
        $habits = json_encode($input['habits'] ?? []);
        
        // Check if exists
        $stmt = $db->prepare("SELECT id FROM entries WHERE user_id = ? AND date = ?");
        $stmt->execute([$userId, $date]);
        $existing = $stmt->fetch();
        
        if ($existing) {
            $stmt = $db->prepare("UPDATE entries SET 
                sleep = ?, energy = ?, mood = ?, food = ?, movement = ?, decisions = ?,
                habits = ?, reflection_practical = ?, reflection_emotional = ?, reflection_identity = ?,
                wins = ?, challenges = ?, gratitude = ?, completed = ?
                WHERE user_id = ? AND date = ?");
            $stmt->execute([
                $input['sleep'] ?? 5,
                $input['energy'] ?? 5,
                $input['mood'] ?? 'neutral',
                $input['food'] ?? 5,
                $input['movement'] ?? 3,
                $input['decisions'] ?? 2,
                $habits,
                $input['reflection_practical'] ?? '',
                $input['reflection_emotional'] ?? '',
                $input['reflection_identity'] ?? '',
                $input['wins'] ?? '',
                $input['challenges'] ?? '',
                $input['gratitude'] ?? '',
                $input['completed'] ? 1 : 0,
                $userId,
                $date
            ]);
        } else {
            $stmt = $db->prepare("INSERT INTO entries 
                (user_id, date, sleep, energy, mood, food, movement, decisions, habits,
                reflection_practical, reflection_emotional, reflection_identity, wins, challenges, gratitude, completed)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $userId,
                $date,
                $input['sleep'] ?? 5,
                $input['energy'] ?? 5,
                $input['mood'] ?? 'neutral',
                $input['food'] ?? 5,
                $input['movement'] ?? 3,
                $input['decisions'] ?? 2,
                $habits,
                $input['reflection_practical'] ?? '',
                $input['reflection_emotional'] ?? '',
                $input['reflection_identity'] ?? '',
                $input['wins'] ?? '',
                $input['challenges'] ?? '',
                $input['gratitude'] ?? '',
                $input['completed'] ? 1 : 0
            ]);
        }
        
        // Return updated entry
        $stmt = $db->prepare("SELECT * FROM entries WHERE user_id = ? AND date = ?");
        $stmt->execute([$userId, $date]);
        $entry = $stmt->fetch(PDO::FETCH_ASSOC);
        $entry['habits'] = json_decode($entry['habits'], true) ?? [];
        
        response($entry);
        break;
    
    // ============ HABITS ============
    
    case 'planned':
        $userId = requireAuth();
        
        if ($method === 'GET') {
            $stmt = $db->prepare("SELECT habits FROM planned_habits WHERE user_id = ?");
            $stmt->execute([$userId]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            response($row ? json_decode($row['habits'], true) : []);
        } else {
            $habits = json_encode($input['habits'] ?? []);
            $stmt = $db->prepare("INSERT INTO planned_habits (user_id, habits) VALUES (?, ?)
                ON CONFLICT(user_id) DO UPDATE SET habits = ?");
            $stmt->execute([$userId, $habits, $habits]);
            response(['success' => true]);
        }
        break;
    
    default:
        response(['error' => 'Unknown action']);
}
