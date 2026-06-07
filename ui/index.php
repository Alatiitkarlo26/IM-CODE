<?php
// Start the session at the absolute top of the file
session_start();

// Go up one folder, then into the db folder to find the connection script
require_once '../db/db_connection.php'; 

$error_msg = "";

// Handle authentication request pipeline upon form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if (!empty($username) && !empty($password)) {
        
        // 1. Query the matching user profile signature
        $sql = "SELECT * FROM tbl_users WHERE username = ? LIMIT 1";
        $stmt = $conn->prepare($sql);
        
        if ($stmt === false) {
            die("SQL Preparation Failure Error: " . $conn->error);
        }

        // 2. Securely bind signatures and execute parameters
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows > 0) {
            $user = $result->fetch_assoc();

            // Verify plaintext credentials match your active database table entries
            if ($password === $user['password']) { 
                
                // Establish user_id session token
                $_SESSION['user_id'] = $user['user_id'] ?? $user['id'];
                $_SESSION['username'] = $user['username'];
                
                // Fallback architecture matching column permutations safely
                $detected_role = "";
                if (isset($user['role'])) { $detected_role = $user['role']; }
                if (isset($user['role_id'])) { $detected_role = $user['role_id']; }
                if (isset($user['user_type'])) { $detected_role = $user['user_type']; }
                
                // Clean input formatting string variations (e.g., matching 1/admin/Admin)
                $detected_role = trim(strtolower((string)$detected_role));

                // 3. Multi-Role Gateway Routing Engine (Saves explicitly standardized session keys)
                if ($detected_role === 'admin' || $detected_role === 'super admin' || $detected_role === '1') {
                    $_SESSION['role'] = 'Admin';
                    header("Location: dashboard.php");
                    exit();
                } elseif ($detected_role === 'staff' || $detected_role === 'member' || $detected_role === '2') {
                    $_SESSION['role'] = 'Staff';
                    header("Location: staff-terminal.php");
                    exit();
                } else {
                    $error_msg = "Unauthorized Access: Profile lacks an operational system role assignment.";
                }
            } else {
                $error_msg = "Invalid system credential authorization matrix match.";
            }
        } else {
            $error_msg = "Identity signature not found in system warehouse registries.";
        }
        $stmt->close();
    } else {
        $error_msg = "Please fill in all authorization fields.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Fretboard — Guitar Inventory Login</title>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,700;1,400&family=DM+Mono:wght@300;400;500&display=swap" rel="stylesheet" />
  <style>
    :root {
      --mahogany: #3b1a0e;
      --rosewood: #6b2d1a;
      --amber: #c8812a;
      --gold: #e4a94b;
      --cream: #f5eed8;
      --parchment: #ede0c4;
      --dark: #1a0c06;
      --string: rgba(228, 169, 75, 0.25);
      --danger: #ef4444;
    }

    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    body {
      min-height: 100vh;
      background-color: var(--dark);
      font-family: 'DM Mono', monospace;
      color: var(--cream);
      display: flex;
      align-items: center;
      justify-content: center;
      overflow: hidden;
      position: relative;
    }

    .bg-wood {
      position: absolute;
      inset: 0;
      background:
        repeating-linear-gradient(92deg, transparent 0px, transparent 20px, rgba(90,40,10,0.06) 20px, rgba(90,40,10,0.06) 21px),
        repeating-linear-gradient(86deg, transparent 0px, transparent 40px, rgba(60,20,5,0.05) 40px, rgba(60,20,5,0.05) 41px),
        linear-gradient(135deg, #1a0c06 0%, #2b1208 50%, #150803 100%);
      z-index: -2;
    }

    .strings { position: absolute; inset: 0; display: flex; flex-direction: column; justify-content: space-around; padding: 10vh 0; z-index: -1; pointer-events: none; opacity: 0.4; }
    .string { height: 1px; background: linear-gradient(90deg, transparent, var(--string) 20%, var(--string) 80%, transparent); position: relative; width: 100%; transition: transform 0.1s; }
    .string::after { content: ''; position: absolute; inset: 0; box-shadow: 0 0 8px var(--gold); opacity: 0; transition: opacity 0.3s; }
    .string:hover::after { opacity: 1; }

    .login-card {
      width: 100%;
      max-width: 420px;
      background: linear-gradient(145deg, rgba(42, 18, 9, 0.95), rgba(26, 12, 6, 0.98));
      border: 1px solid rgba(228, 169, 75, 0.25);
      border-radius: 4px;
      padding: 45px 40px;
      box-shadow: 0 25px 60px rgba(0,0,0,0.65), inset 0 1px 0 rgba(255,255,255,0.05);
      backdrop-filter: blur(10px);
      z-index: 10;
    }

    .card-header { text-align: center; margin-bottom: 35px; }
    .brand-title { font-family: 'Playfair Display', serif; font-size: 32px; font-weight: 700; color: var(--gold); letter-spacing: -0.02em; }
    .brand-subtitle { font-size: 9px; text-transform: uppercase; letter-spacing: 0.25em; color: rgba(245,238,216,0.45); margin-top: 5px; }

    .error-banner {
      background: rgba(239, 68, 68, 0.1);
      border: 1px solid rgba(239, 68, 68, 0.25);
      color: #fca5a5;
      padding: 12px;
      font-size: 11px;
      border-radius: 3px;
      margin-bottom: 25px;
      text-align: center;
    }

    .form-group { margin-bottom: 22px; position: relative; }
    .form-group label { display: block; font-size: 9px; text-transform: uppercase; letter-spacing: 0.12em; color: rgba(228, 169, 75, 0.65); margin-bottom: 8px; }
    
    .input-wrapper { position: relative; }
    .input-wrapper input {
      width: 100%;
      background: rgba(20, 10, 5, 0.4);
      border: 1px solid rgba(228, 169, 75, 0.18);
      border-radius: 3px;
      padding: 12px 14px 12px 42px;
      color: var(--cream);
      font-family: inherit;
      font-size: 13px;
      outline: none;
      transition: border-color 0.25s, box-shadow 0.25s;
    }
    .input-wrapper input:focus { border-color: var(--gold); box-shadow: 0 0 0 3px rgba(228,169,75,0.08); }
    .input-wrapper svg { position: absolute; left: 14px; top: 50%; transform: translateY(-50%); width: 16px; height: 16px; color: rgba(228, 169, 75, 0.35); pointer-events: none; }

    .btn {
      width: 100%;
      padding: 14px;
      background: linear-gradient(135deg, var(--amber), var(--rosewood) 90%);
      border: 1px solid rgba(228,169,75,0.3);
      border-radius: 3px;
      color: var(--cream);
      font-family: inherit;
      font-size: 11px;
      text-transform: uppercase;
      letter-spacing: 0.1em;
      font-weight: 500;
      cursor: pointer;
      margin-top: 10px;
      box-shadow: 0 4px 15px rgba(0,0,0,0.25);
    }
  </style>
</head>
<body>

  <div class="bg-wood"></div>
  <div class="strings">
    <div class="string"></div><div class="string"></div><div class="string"></div>
    <div class="string"></div><div class="string"></div><div class="string"></div>
  </div>

  <div class="login-card">
    <div class="card-header">
      <h1 class="brand-title">Fretboard</h1>
      <p class="brand-subtitle">Vault Control Registry Login</p>
    </div>

    <?php if (!empty($error_msg)): ?>
        <div class="error-banner"><?= htmlspecialchars($error_msg) ?></div>
    <?php endif; ?>

    <form method="POST" action="<?= htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
      <div class="form-group">
        <label for="username">Authorized Username</label>
        <div class="input-wrapper">
          <input type="text" name="username" id="username" placeholder="e.g., karlo_admin" required value="<?= isset($_POST['username']) ? htmlspecialchars($_POST['username']) : '' ?>" />
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" /><circle cx="12" cy="7" r="4" /></svg>
        </div>
      </div>

      <div class="form-group">
        <label for="password">Security Crypt-Key</label>
        <div class="input-wrapper">
          <input type="password" name="password" id="password" placeholder="••••••••" required />
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
        </div>
      </div>
      <button type="submit" class="btn">Authenticate Entry</button>
    </form>
  </div>

  <script>
    document.querySelectorAll('.string').forEach((s) => {
      s.addEventListener('mouseenter', () => {
        s.style.transform = 'translateY(-1.5px)';
        setTimeout(() => { s.style.transform = 'translateY(1.5px)'; }, 70);
        setTimeout(() => { s.style.transform = 'translateY(0)'; }, 140);
      });
    });
  </script>
</body>
</html>