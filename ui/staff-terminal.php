<?php

  session_start();


  require_once __DIR__ . '/../db/db_connection.php';


  if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Staff') {
      header("Location: index.php");
      exit();
  }
  $current_user_id = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;


  $categoriesArr = [];
  $catResult = mysqli_query($conn, "SELECT * FROM tbl_categories");
  if ($catResult) {
      while ($row = mysqli_fetch_assoc($catResult)) {
          $categoriesArr[] = $row;
      }
  }


  $brandsArr = [];
  $brandResult = mysqli_query($conn, "SELECT * FROM tbl_brands");
  if ($brandResult) {
      while ($row = mysqli_fetch_assoc($brandResult)) {
          $brandsArr[] = $row;
      }
  }


  $productsArr = [];
  if ($conn) {
      $prodQuery = "SELECT p.*, c.category_name, b.brand_name 
                    FROM tbl_products p
                    LEFT JOIN tbl_categories c ON p.category_id = c.category_id
                    LEFT JOIN tbl_brands b ON p.brand_id = b.brand_id";
      $prodResult = mysqli_query($conn, $prodQuery);
      if ($prodResult) {
          while ($row = mysqli_fetch_assoc($prodResult)) {
              $row['product_id'] = (int)$row['product_id'];
              $row['unit_price'] = (float)$row['unit_price'];
              $row['quantity_on_hand'] = (int)$row['quantity_on_hand'];
              $productsArr[] = $row;
          }
      }
  }


  $metricsArr = [];
  $metricsQuery = "SELECT u.full_name, COUNT(sh.stockHistory_id) AS total_actions_performed
                   FROM tbl_users u
                   INNER JOIN tbl_stock_history sh ON u.user_id = sh.user_id
                   GROUP BY u.user_id, u.full_name
                   HAVING total_actions_performed > 0";
  $metricsResult = mysqli_query($conn, $metricsQuery);
  if ($metricsResult) {
      while ($row = mysqli_fetch_assoc($metricsResult)) {
          $metricsArr[] = $row;
      }
  }
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Fretboard — Staff Operations Terminal</title>
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
      --dark-card: linear-gradient(145deg, rgba(42,18,9,0.97), rgba(28,12,5,0.98));
      --string: rgba(228, 169, 75, 0.15);
      --danger: #ef4444;
      --success: #10b981;
    }

    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    body {
      min-height: 100vh;
      background-color: var(--dark);
      font-family: 'DM Mono', monospace;
      color: var(--cream);
      overflow-x: hidden;
      display: flex;
      flex-direction: column;
    }

    /* Fine Premium Wood Textured Backplane Layouts */
    .bg {
      position: fixed;
      inset: 0;
      background:
        repeating-linear-gradient(92deg, transparent 0px, transparent 18px, rgba(90,40,10,0.05) 18px, rgba(90,40,10,0.05) 19px),
        repeating-linear-gradient(88deg, transparent 0px, transparent 38px, rgba(60,20,5,0.04) 38px, rgba(60,20,5,0.04) 39px),
        linear-gradient(170deg, #1a0c06 0%, #2e130a 40%, #1d0e07 70%, #120804 100%);
      z-index: -2;
    }

    .bg-strings { position: fixed; inset: 0; z-index: -1; pointer-events: none; opacity: 0.35; }
    .bg-string {
      position: absolute; left: 0; right: 0; height: 1px;
      background: linear-gradient(90deg, transparent 0%, var(--string) 15%, var(--string) 85%, transparent 100%);
    }
    .bg-string:nth-child(1) { top: 15%; }
    .bg-string:nth-child(2) { top: 45%; height: 1.5px; }
    .bg-string:nth-child(3) { top: 75%; height: 2px; }

    .top-bar {
      background: rgba(26, 12, 6, 0.95);
      border-bottom: 1px solid rgba(228,169,75,0.22);
      padding: 15px 30px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      position: sticky;
      top: 0;
      z-index: 50;
      backdrop-filter: blur(8px);
    }

    .logo-row { display: flex; align-items: center; gap: 12px; }
    .brand-name { font-family: 'Playfair Display', serif; font-size: 20px; font-weight: 700; color: var(--gold); line-height: 1; }
    .brand-sub { font-size: 8px; color: rgba(228,169,75,0.45); letter-spacing: 0.2em; text-transform: uppercase; margin-top: 3px; }

    .user-profile { display: flex; align-items: center; gap: 16px; }
    .user-details { display: flex; flex-direction: column; align-items: flex-end; }
    .role-badge {
      background: rgba(228, 169, 75, 0.04); border: 1px solid rgba(228, 169, 75, 0.2);
      color: var(--parchment); padding: 3px 8px; border-radius: 3px; font-size: 10px; text-transform: uppercase; margin-top: 2px;
    }

    /* Workspace Framing aligning perfectly with dashboard search layout architectures */
    .workspace { display: grid; grid-template-columns: 290px 1fr; min-height: calc(100vh - 72px); }
    
    .sidebar { 
      background: rgba(20, 10, 5, 0.45); 
      border-right: 1px solid rgba(228,169,75,0.1); 
      padding: 35px 24px; 
      display: flex; 
      flex-direction: column; 
      gap: 28px; 
    }
    
    .sidebar-section-title { 
      font-size: 10px; 
      letter-spacing: 0.14em; 
      text-transform: uppercase; 
      color: #8c633c; 
      margin-bottom: 12px; 
      display: block; 
      font-weight: 500;
    }
    .sidebar-group { display: flex; flex-direction: column; gap: 24px; }
    
    .search-wrap { position: relative; }
    .search-wrap svg { position: absolute; left: 14px; top: 50%; transform: translateY(-50%); width: 14px; height: 14px; color: rgba(228,169,75,0.3); }
    
    .search-input {
      width: 100%; padding: 14px 16px 14px 42px; background: rgba(18, 9, 4, 0.6);
      border: 1px solid rgba(228,169,75,0.12); border-radius: 4px; color: var(--cream); font-family: inherit; font-size: 13px; outline: none;
      transition: border-color 0.2s;
    }
    .search-input:focus { border-color: rgba(228,169,75,0.3); }

    .filter-select { 
      width: 100%; 
      padding: 14px 16px; 
      background: rgba(18, 9, 4, 0.6); 
      border: 1px solid rgba(228,169,75,0.12); 
      border-radius: 4px; 
      color: var(--cream); 
      font-family: inherit; 
      font-size: 13px; 
      outline: none; 
      cursor: pointer;
      appearance: none;
      background-image: url("data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' width='24' height='24' viewBox='0 0 24 24' fill='none' stroke='%23f5eed8' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'><polyline points='6 9 12 15 18 9'></polyline></svg>");
      background-repeat: no-repeat;
      background-position: right 14px center;
      background-size: 14px;
    }
    .filter-select:focus { border-color: rgba(228,169,75,0.3); }

    .content-canvas { padding: 35px; display: flex; flex-direction: column; gap: 30px; }
    .action-row { display: flex; justify-content: space-between; align-items: center; }
    .panel-title { font-family: 'Playfair Display', serif; font-size: 22px; color: var(--parchment); }
    .btn-group { display: flex; gap: 12px; }

    .btn {
      padding: 10px 18px; background: linear-gradient(135deg, var(--amber), var(--rosewood) 80%);
      border: none; border-radius: 3px; color: var(--cream); font-family: inherit; font-size: 11px;
      letter-spacing: 0.05em; text-transform: uppercase; cursor: pointer; box-shadow: 0 4px 15px rgba(200,129,42,0.15);
      text-decoration: none; display: inline-flex; align-items: center; justify-content: center;
    }
    .btn-secondary { background: rgba(255,255,255,0.03); border: 1px solid rgba(228,169,75,0.25); box-shadow: none; }
    .btn-logout { border-color: rgba(239, 68, 68, 0.4); color: var(--danger); transition: all 0.2s; }
    .btn-logout:hover { background: rgba(239, 68, 68, 0.1); border-color: var(--danger); color: #fff; }

    .table-container { background: var(--dark-card); border: 1px solid rgba(228,169,75,0.18); border-radius: 4px; overflow: hidden; box-shadow: 0 20px 50px rgba(0,0,0,0.5); }
    table { width: 100%; border-collapse: collapse; text-align: left; font-size: 12px; }
    th { background: rgba(59, 26, 14, 0.6); padding: 14px 16px; font-size: 9px; letter-spacing: 0.15em; text-transform: uppercase; color: rgba(228,169,75,0.6); border-bottom: 1px solid rgba(228,169,75,0.2); }
    td { padding: 14px 16px; border-bottom: 1px solid rgba(228,169,75,0.08); color: var(--parchment); vertical-align: middle; }
    tr:hover td { background: rgba(228,169,75,0.02); }

    .status-high { color: var(--success); background: rgba(16, 185, 129, 0.1); padding: 2px 6px; border-radius: 3px; font-size: 11px; }
    .status-low { color: var(--danger); background: rgba(239, 68, 68, 0.1); padding: 2px 6px; border-radius: 3px; font-size: 11px; font-weight: bold; }
    .metric-badge { color: var(--gold); font-weight: bold; font-size: 13px; }

    /* Modal Styling */
    .modal-overlay { position: fixed; inset: 0; background: rgba(10, 5, 3, 0.85); backdrop-filter: blur(4px); z-index: 100; display: flex; align-items: center; justify-content: center; opacity: 0; pointer-events: none; transition: opacity 0.25s ease; }
    .modal-overlay.active { opacity: 1; pointer-events: auto; }
    .modal-card { width: 460px; background: linear-gradient(145deg, rgba(42,18,9,0.99), rgba(28,12,5,0.99)); border: 1px solid rgba(228,169,75,0.35); border-radius: 4px; padding: 40px; }
    .modal-header { font-family: 'Playfair Display', serif; font-size: 22px; color: var(--gold); margin-bottom: 25px; display: flex; justify-content: space-between; align-items: center; }
    .modal-close { background: none; border: none; color: rgba(228,169,75,0.5); font-size: 20px; cursor: pointer; }
    
    .form-field { margin-bottom: 18px; }
    .form-field label { display: block; font-size: 9px; letter-spacing: 0.1em; text-transform: uppercase; color: rgba(228,169,75,0.55); margin-bottom: 6px; }
    .form-field select, .form-field input {
      width: 100%; padding: 11px; background: rgba(255,255,255,0.04); border: 1px solid rgba(228,169,75,0.18); border-radius: 3px; color: var(--cream); font-family: inherit; font-size: 13px; outline: none;
    }
  </style>
</head>
<body>

  <div class="bg"></div>
  <div class="bg-strings"><div class="bg-string"></div><div class="bg-string"></div><div class="bg-string"></div></div>

  <header class="top-bar">
    <!-- <p style='color: #10b981; font-size: 11px; letter-spacing:0.05em;'>TERMINAL MODE: STAFF OPERATION MATRIX</p> -->
    <div class="logo-row">
      <div class="brand">
        <span class="brand-name">Fretboard</span>
        <span class="brand-sub">Staff Terminal Portal</span>
      </div>
    </div>
    <div class="user-profile">
      <div class="user-details">
        <span><?= isset($_SESSION['full_name']) ? htmlspecialchars($_SESSION['full_name']) : 'Operations Floor User' ?></span>
        <span class="role-badge">Inventory Clerk</span>
      </div>
      <a href="logout.php" class="btn btn-secondary btn-logout">🚪 Sign Out</a>
    </div>
  </header>

  <div class="workspace">
    <aside class="sidebar">
      <div>
        <span class="sidebar-section-title">Database Scope Filter</span>
        <div class="search-wrap">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
          <input type="text" id="globalSearch" class="search-input" placeholder="Query entities..." oninput="executeGlobalFilters()">
        </div>
      </div>
      
      <div class="sidebar-group">
        <div>
          <span class="sidebar-section-title">Category Assignment</span>
          <select id="categorySelect" class="filter-select" onchange="executeGlobalFilters()">
            <option value="ALL">All Categories</option>
            <?php foreach($categoriesArr as $cat): ?>
              <option value="<?php echo htmlspecialchars($cat['category_name']); ?>"><?php echo htmlspecialchars($cat['category_name']); ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <div>
          <span class="sidebar-section-title">Stock Threshold Level</span>
          <select id="stockThresholdSelect" class="filter-select" onchange="executeGlobalFilters()">
            <option value="ALL">All Stock Statuses</option>
            <option value="HIGH">High Stock (&ge; 10 units)</option>
            <option value="LOW">Low Stock (&lt; 10 units)</option>
          </select>
        </div>

        <div>
          <span class="sidebar-section-title">Brand Line</span>
          <select id="brandSelect" class="filter-select" onchange="executeGlobalFilters()">
            <option value="ALL">All Brands</option>
            <?php foreach($brandsArr as $b): ?>
              <option value="<?php echo htmlspecialchars($b['brand_name']); ?>"><?php echo htmlspecialchars($b['brand_name']); ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>
    </aside>

    <main class="content-canvas">
      <section class="action-row">
        <h2 class="panel-title">Active Instrument Floor Catalog</h2>
        <div class="btn-group">
          <button class="btn btn-secondary" onclick="toggleModal('metricsModal', true)">👥 View Productivity Metrics</button>
          <button class="btn" style="background: linear-gradient(135deg, #ef4444, #6b2d1a);" onclick="toggleModal('stockOutModal', true)">📉 Log Stock Out Disbursal</button>
        </div>
      </section>

      <section class="table-container">
        <table>
          <thead>
            <tr>
              <th>SKU ID</th>
              <th>Model Name</th>
              <th>Category Line</th>
              <th>Brand Origin</th>
              <th>Retail Value</th>
              <th style="text-align:center;">On-Hand Stock</th>
              <th>Showroom Grid Zone</th>
            </tr>
          </thead>
          <tbody id="table-products"></tbody>
        </table>
      </section>
    </main>
  </div>

  <div id="stockOutModal" class="modal-overlay">
    <div class="modal-card" style="border-color: var(--danger);">
      <div class="modal-header"><h3 style="color: var(--danger);">Log Stock-Out Disbursal</h3><button class="modal-close" onclick="toggleModal('stockOutModal', false)">&times;</button></div>
      <form onsubmit="handleStaffStockOut(event)">
        <div class="form-field">
          <label>Target Floor Asset</label>
          <select id="saleItemTarget" required></select>
        </div>
        <div class="form-field">
          <label>Units Disbursed Volume</label>
          <input type="number" id="saleQty" min="1" required value="1">
        </div>
        <button type="submit" class="btn" style="width: 100%; margin-top: 15px; background: linear-gradient(135deg, var(--danger), var(--rosewood));">Commit Disbursal Transaction</button>
      </form>
    </div>
  </div>

  <div id="metricsModal" class="modal-overlay">
    <div class="modal-card" style="width: 520px; border-color: var(--amber);">
      <div class="modal-header"><h3>Employee Productivity Leaderboard</h3><button class="modal-close" onclick="toggleModal('metricsModal', false)">&times;</button></div>
      <div class="table-container" style="max-height: 320px; overflow-y: auto;">
        <table>
          <thead>
            <tr>
              <th>Employee Full Name</th>
              <th style="text-align: right; padding-right: 25px;">Actions Logged</th>
            </tr>
          </thead>
          <tbody>
            <?php if(empty($metricsArr)): ?>
              <tr><td colspan="2" style="text-align:center; color:rgba(228,169,75,0.4); padding:20px;">No transaction history metrics found.</td></tr>
            <?php else: ?>
              <?php foreach($metricsArr as $metric): ?>
                <tr>
                  <td><b><?php echo htmlspecialchars($metric['full_name']); ?></b></td>
                  <td style="text-align: right; padding-right: 25px;"><span class="metric-badge"><?php echo (int)$metric['total_actions_performed']; ?> entries</span></td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
      <button class="btn btn-secondary" style="width: 100%; margin-top: 20px;" onclick="toggleModal('metricsModal', false)">Dismiss Window</button>
    </div>
  </div>

  <script>
    // Live client data state mirror
    let dbProducts = <?php echo json_encode($productsArr); ?>;

    document.addEventListener("DOMContentLoaded", () => {
      executeGlobalFilters();
    });

    function toggleModal(id, open) {
      const modal = document.getElementById(id);
      if(open) {
        modal.classList.add('active');
        if(id === 'stockOutModal') setupStockOutDropdown();
      } else {
        modal.classList.remove('active');
      }
    }

    function setupStockOutDropdown() {
      document.getElementById('saleItemTarget').innerHTML = dbProducts.map(p => `
        <option value="${p.product_id}">${p.brand_name || 'Generic'} — ${p.product_name} (${p.quantity_on_hand} available)</option>
      `).join('');
    }

    // Comprehensive client-side multi-filter processor
    function executeGlobalFilters() {
      const search = document.getElementById('globalSearch').value.toLowerCase();
      const category = document.getElementById('categorySelect').value;
      const stockThreshold = document.getElementById('stockThresholdSelect').value;
      const brand = document.getElementById('brandSelect').value;

      const filteredProducts = dbProducts.filter(p => {
        const textMatch = p.product_name.toLowerCase().includes(search) || 
                          (p.brand_name && p.brand_name.toLowerCase().includes(search)) ||
                          p.location.toLowerCase().includes(search);
        const catMatch = category === "ALL" || p.category_name === category;
        const brandMatch = brand === "ALL" || p.brand_name === brand;
        let stockMatch = stockThreshold === "ALL" || (stockThreshold === "HIGH" ? p.quantity_on_hand >= 10 : p.quantity_on_hand < 10);
        
        return textMatch && catMatch && brandMatch && stockMatch;
      });
      
      renderProductsRegistry(filteredProducts);
    }

    function renderProductsRegistry(records) {
      const body = document.getElementById('table-products');
      if(records.length === 0) {
        body.innerHTML = `<tr><td colspan="7" style="text-align:center; color:rgba(228,169,75,0.4); padding:30px;">No instruments match selected filters.</td></tr>`;
        return;
      }
      body.innerHTML = records.map(p => `
        <tr>
          <td style="color:var(--gold)">P-${p.product_id}</td>
          <td><b>${p.product_name}</b></td>
          <td>${p.category_name || 'Unassigned'}</td>
          <td>${p.brand_name || 'Generic'}</td>
          <td>₱${p.unit_price.toLocaleString(undefined, {minimumFractionDigits: 2})}</td>
          <td style="text-align:center;"><span class="${p.quantity_on_hand < 10 ? 'status-low' : 'status-high'}">${p.quantity_on_hand}</span></td>
          <td style="color:rgba(245,238,216,0.7); font-style:italic;">${p.location}</td>
        </tr>
      `).join('');
    }

    // Refined client action handler constrained explicitly to Stock-Out operations
    async function handleStaffStockOut(e) {
      e.preventDefault();
      const targetId = parseInt(document.getElementById("saleItemTarget").value);
      const units = parseInt(document.getElementById("saleQty").value);
      
      if (!targetId) return;

      const product = dbProducts.find(p => p.product_id === targetId);

      if (product) {
        if (product.quantity_on_hand < units) {
          alert(`Transaction Denied: Insufficient quantities on hand. System only has ${product.quantity_on_hand} available.`);
          return;
        }

        try {
          const response = await fetch('process_actions.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
              'action': 'EXECUTE_ADJUSTMENT',
              'product_id': targetId,
              'type': 'Stock-Out',
              'quantity': units
            })
          });
          const data = await response.json();
          if(data.status === 'success') {
              alert(`Disbursal Confirmed!\nTotal Transaction value: ₱${(units * product.unit_price).toLocaleString(undefined, {minimumFractionDigits:2})}`);
              location.reload(); 
          } else {
              alert('Database update failure: ' + data.message);
          }
        } catch (err) {
            alert("Connection lost to API processor router script.");
        }
      }
    }
  </script>
</body>
</html>