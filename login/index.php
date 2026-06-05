<?php
  // Go up one folder, then into the db folder to find the connection script
  require_once '../db/db_connection.php'; 
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Fretboard — Guitar Inventory</title>
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
    }

    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    body {
      min-height: 100vh;
      background-color: var(--dark);
      font-family: 'DM Mono', monospace;
      display: flex;
      align-items: center;
      justify-content: center;
      overflow: hidden;
      position: relative;
    }

    /* ─── Wood grain background ─── */
    .bg {
      position: fixed;
      inset: 0;
      background:
        repeating-linear-gradient(
          92deg,
          transparent 0px, transparent 18px,
          rgba(90,40,10,0.08) 18px, rgba(90,40,10,0.08) 19px
        ),
        repeating-linear-gradient(
          88deg,
          transparent 0px, transparent 38px,
          rgba(60,20,5,0.06) 38px, rgba(60,20,5,0.06) 39px
        ),
        linear-gradient(170deg, #1a0c06 0%, #2e130a 40%, #1d0e07 70%, #120804 100%);
      z-index: 0;
    }

    /* ─── Guitar strings (horizontal) ─── */
    .strings {
      position: fixed;
      inset: 0;
      z-index: 1;
      pointer-events: none;
    }
    .string {
      position: absolute;
      left: 0; right: 0;
      height: 1px;
      background: linear-gradient(90deg, transparent 0%, var(--string) 20%, var(--string) 80%, transparent 100%);
      transform-origin: center;
    }
    .string:nth-child(1) { top: 18%; opacity: 0.6; }
    .string:nth-child(2) { top: 29%; opacity: 0.5; }
    .string:nth-child(3) { top: 40%; opacity: 0.5; height: 1.5px; }
    .string:nth-child(4) { top: 51%; opacity: 0.6; height: 2px; }
    .string:nth-child(5) { top: 62%; opacity: 0.55; height: 2.5px; }
    .string:nth-child(6) { top: 73%; opacity: 0.5; height: 3px; }

    /* ─── Fret markers ─── */
    .fret {
      position: fixed;
      top: 15%;
      bottom: 20%;
      width: 2px;
      background: linear-gradient(180deg, transparent, rgba(228,169,75,0.2) 30%, rgba(228,169,75,0.2) 70%, transparent);
      z-index: 1;
    }
    .fret:nth-child(7)  { left: 12%; }
    .fret:nth-child(8)  { left: 22%; }
    .fret:nth-child(9)  { left: 32%; }
    .fret:nth-child(10) { left: 42%; }
    .fret:nth-child(11) { left: 52%; }
    .fret:nth-child(12) { left: 62%; }
    .fret:nth-child(13) { left: 72%; }
    .fret:nth-child(14) { left: 82%; }
    .fret:nth-child(15) { left: 91%; }

    /* ─── Inlay dot ─── */
    .inlay {
      position: fixed;
      width: 10px; height: 10px;
      border-radius: 50%;
      background: radial-gradient(circle at 35% 35%, rgba(228,169,75,0.5), rgba(200,129,42,0.15));
      border: 1px solid rgba(228,169,75,0.2);
      z-index: 1;
    }
    .inlay:nth-child(16) { left: 36.5%; top: 44%; }
    .inlay:nth-child(17) { left: 56.5%; top: 44%; }
    .inlay:nth-child(18) { left: 66.5%; top: 38%; }
    .inlay:nth-child(19) { left: 66.5%; top: 50%; }

    /* ─── Card ─── */
    .card {
      position: relative;
      z-index: 10;
      width: 420px;
      background: linear-gradient(145deg, rgba(42,18,9,0.97), rgba(28,12,5,0.98));
      border: 1px solid rgba(228,169,75,0.3);
      border-radius: 4px;
      padding: 52px 48px 44px;
      box-shadow:
        0 0 0 1px rgba(0,0,0,0.8),
        0 40px 80px rgba(0,0,0,0.7),
        inset 0 1px 0 rgba(228,169,75,0.15),
        inset 0 -1px 0 rgba(0,0,0,0.4);
      animation: cardIn 0.8s cubic-bezier(0.16, 1, 0.3, 1) both;
    }

    @keyframes cardIn {
      from { opacity: 0; transform: translateY(24px) scale(0.97); }
      to   { opacity: 1; transform: translateY(0)    scale(1); }
    }

    /* ─── Header ─── */
    .logo-row {
      display: flex;
      align-items: center;
      gap: 14px;
      margin-bottom: 36px;
    }

    .logo-icon {
      width: 44px;
      height: 44px;
      flex-shrink: 0;
    }

    .brand {
      display: flex;
      flex-direction: column;
    }
    .brand-name {
      font-family: 'Playfair Display', serif;
      font-size: 26px;
      font-weight: 700;
      color: var(--gold);
      letter-spacing: 0.03em;
      line-height: 1;
    }
    .brand-sub {
      font-size: 9px;
      color: rgba(228,169,75,0.45);
      letter-spacing: 0.25em;
      text-transform: uppercase;
      margin-top: 5px;
    }

    /* ─── Divider ─── */
    .divider {
      height: 1px;
      background: linear-gradient(90deg, transparent, rgba(228,169,75,0.25) 40%, rgba(228,169,75,0.25) 60%, transparent);
      margin-bottom: 36px;
    }

    /* ─── Form ─── */
    .field {
      margin-bottom: 22px;
    }
    label {
      display: block;
      font-size: 9px;
      letter-spacing: 0.2em;
      text-transform: uppercase;
      color: rgba(228,169,75,0.55);
      margin-bottom: 8px;
    }
    .input-wrap {
      position: relative;
    }
    .input-wrap svg {
      position: absolute;
      left: 14px;
      top: 50%;
      transform: translateY(-50%);
      width: 16px;
      height: 16px;
      color: rgba(228,169,75,0.35);
      transition: color 0.2s;
    }
    input {
      width: 100%;
      padding: 13px 14px 13px 42px;
      background: rgba(255,255,255,0.04);
      border: 1px solid rgba(228,169,75,0.18);
      border-radius: 3px;
      color: var(--cream);
      font-family: 'DM Mono', monospace;
      font-size: 13px;
      outline: none;
      transition: border-color 0.2s, background 0.2s, box-shadow 0.2s;
      letter-spacing: 0.04em;
    }
    input::placeholder { color: rgba(245,238,216,0.2); }
    input:focus {
      border-color: rgba(228,169,75,0.5);
      background: rgba(255,255,255,0.07);
      box-shadow: 0 0 0 3px rgba(228,169,75,0.07);
    }
    input:focus + svg,
    .input-wrap:focus-within svg { color: rgba(228,169,75,0.7); }

    
    .input-wrap input { order: 0; }
    .input-wrap svg   { order: 1; }

    .forgot {
      display: block;
      text-align: right;
      font-size: 10px;
      letter-spacing: 0.1em;
      color: rgba(228,169,75,0.4);
      text-decoration: none;
      margin-top: 8px;
      transition: color 0.2s;
    }
    .forgot:hover { color: var(--gold); }

    /* ─── Button ─── */
    .btn {
      width: 100%;
      margin-top: 30px;
      padding: 15px;
      background: linear-gradient(135deg, var(--amber), var(--rosewood) 80%);
      border: none;
      border-radius: 3px;
      color: var(--cream);
      font-family: 'DM Mono', monospace;
      font-size: 11px;
      letter-spacing: 0.25em;
      text-transform: uppercase;
      cursor: pointer;
      position: relative;
      overflow: hidden;
      transition: opacity 0.2s, transform 0.15s;
      box-shadow: 0 4px 20px rgba(200,129,42,0.3);
    }
    .btn::before {
      content: '';
      position: absolute;
      inset: 0;
      background: linear-gradient(135deg, rgba(255,255,255,0.12), transparent);
      opacity: 0;
      transition: opacity 0.2s;
    }
    .btn:hover { opacity: 0.92; transform: translateY(-1px); }
    .btn:hover::before { opacity: 1; }
    .btn:active { transform: translateY(0); opacity: 1; }

    /* ─── Footer ─── */
    .card-footer {
      margin-top: 28px;
      text-align: center;
      font-size: 10px;
      letter-spacing: 0.12em;
      color: rgba(228,169,75,0.25);
    }
    .card-footer a {
      color: rgba(228,169,75,0.5);
      text-decoration: none;
      transition: color 0.2s;
    }
    .card-footer a:hover { color: var(--gold); }

    /* ─── String vibration on focus ─── */
    @keyframes vibrate {
      0%,100% { transform: scaleY(1); }
      20% { transform: scaleY(1.6); }
      40% { transform: scaleY(0.8); }
      60% { transform: scaleY(1.3); }
      80% { transform: scaleY(0.95); }
    }

    input:focus ~ .vibe-trigger ~ .strings .string:nth-child(3) {
      animation: vibrate 0.4s ease-out;
    }
  </style>
</head>
<body>


  <div class="bg"></div>

  <div class="strings">
    <div class="string"></div>
    <div class="string"></div>
    <div class="string"></div>
    <div class="string"></div>
    <div class="string"></div>
    <div class="string"></div>
  </div>

  <div class="fret"></div>
  <div class="fret"></div>
  <div class="fret"></div>
  <div class="fret"></div>
  <div class="fret"></div>
  <div class="fret"></div>
  <div class="fret"></div>
  <div class="fret"></div>
  <div class="fret"></div>

  <div class="inlay"></div>
  <div class="inlay"></div>
  <div class="inlay"></div>
  <div class="inlay"></div>

  <div class="card">
<?php
      // You can now write queries anywhere inside the HTML body!
      // Example: Testing if the connection works
      if($conn) {
          echo "<p style='color: green;'>Successfully connected to the database!</p>";
      }
    ?>
    <div class="logo-row">
      <!-- Guitar headstock SVG icon -->
      <svg class="logo-icon" viewBox="0 0 44 44" fill="none" xmlns="http://www.w3.org/2000/svg">
        <rect x="18" y="2" width="8" height="28" rx="4" fill="#6b2d1a" stroke="#c8812a" stroke-width="1"/>
        <rect x="20" y="26" width="4" height="16" rx="2" fill="#3b1a0e" stroke="#c8812a" stroke-width="0.8"/>
        <circle cx="22" cy="40" r="3" fill="#c8812a" opacity="0.8"/>
        <rect x="13" y="6" width="18" height="3" rx="1.5" fill="#e4a94b" opacity="0.7"/>
        <!-- Tuning pegs -->
        <circle cx="12" cy="10" r="3" fill="#c8812a" stroke="#e4a94b" stroke-width="0.8"/>
        <circle cx="32" cy="10" r="3" fill="#c8812a" stroke="#e4a94b" stroke-width="0.8"/>
        <circle cx="12" cy="18" r="3" fill="#c8812a" stroke="#e4a94b" stroke-width="0.8"/>
        <circle cx="32" cy="18" r="3" fill="#c8812a" stroke="#e4a94b" stroke-width="0.8"/>
      </svg>
      <div class="brand">
        <span class="brand-name">Fretboard</span>
        <span class="brand-sub">Guitar Inventory System</span>
      </div>
    </div>

    <div class="divider"></div>

    <form onsubmit="handleLogin(event)">
      <div class="field">
        <label for="username">Username</label>
        <div class="input-wrap">
          <input
            id="username"
            type="text"
            placeholder="username"
            autocomplete="username"
            required
          />
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
            <circle cx="12" cy="7" r="4"/>
          </svg>
        </div>
      </div>

      <div class="field">
        <label for="password">Password</label>
        <div class="input-wrap">
          <input
            id="password"
            type="password"
            placeholder="••••••••"
            autocomplete="current-password"
            required
          />
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
            <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
            <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
          </svg>
        </div>
        <a href="#" class="forgot">Forgot password?</a>
      </div>

      <button type="submit" class="btn">Sign In to Inventory</button>
    </form>

    <div class="card-footer">
      No account? <a href="#">Request Access</a>
    </div>

  </div>

  <script>
    function handleLogin(e) {
      e.preventDefault();
      const btn = e.target.querySelector('.btn');
      btn.textContent = 'Tuning in…';
      btn.style.opacity = '0.7';
      setTimeout(() => {
        btn.textContent = 'Sign In to Inventory';
        btn.style.opacity = '';
        alert('Login submitted! Connect to your backend to authenticate.');
      }, 1600);
    }

    // Subtle string shimmer on hover
    document.querySelectorAll('.string').forEach((s, i) => {
      s.addEventListener('mouseenter', () => {
        s.style.animation = 'none';
        requestAnimationFrame(() => {
          s.style.transition = 'opacity 0.3s';
          s.style.opacity = '0.9';
        });
      });
      s.addEventListener('mouseleave', () => {
        s.style.opacity = '';
      });
    });
  </script>
</body>
</html>