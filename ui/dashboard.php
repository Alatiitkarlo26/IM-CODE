<?php
  // Go up one folder, then into the db folder to find the connection script
  require_once '../db/db_connection.php'; 

  // ─── PHP BACKEND INITIAL RETRIEVAL PIPELINES ───
  // Fetch Active Categories
  $categoriesArr = [];
  $catResult = mysqli_query($conn, "SELECT * FROM tbl_categories");
  while ($row = mysqli_fetch_assoc($catResult)) {
      $categoriesArr[] = $row;
  }

  // Fetch Active Brands
  $brandsArr = [];
  $brandResult = mysqli_query($conn, "SELECT * FROM tbl_brands");
  while ($row = mysqli_fetch_assoc($brandResult)) {
      $brandsArr[] = $row;
  }

  // Fetch Products with Relational Parent Joins
  $productsArr = [];
  $prodQuery = "SELECT p.*, c.category_name, b.brand_name 
                FROM tbl_products p
                LEFT JOIN tbl_categories c ON p.category_id = c.category_id
                LEFT JOIN tbl_brands b ON p.brand_id = b.brand_id";
  $prodResult = mysqli_query($conn, $prodQuery);
  while ($row = mysqli_fetch_assoc($prodResult)) {
      $row['product_id'] = (int)$row['product_id'];
      $row['category_id'] = (int)$row['category_id'];
      $row['brand_id'] = (int)$row['brand_id'];
      $row['unit_price'] = (float)$row['unit_price'];
      $row['quantity_on_hand'] = (int)$row['quantity_on_hand'];
      $productsArr[] = $row;
  }

  // Fetch System Transaction History Ledger Rows
  $historyArr = [];
  $histQuery = "SELECT h.*, p.product_name, u.full_name 
                FROM tbl_stock_history h
                LEFT JOIN tbl_products p ON h.product_id = p.product_id
                LEFT JOIN tbl_users u ON h.user_id = u.user_id
                ORDER BY h.stockHistory_date DESC";
  $histResult = mysqli_query($conn, $histQuery);
  while ($row = mysqli_fetch_assoc($histResult)) {
      $historyArr[] = $row;
  }

  // Query 6: Count how many inventory transactions each employee has performed
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
  <title>Fretboard — Enterprise Admin Console</title>
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

    .user-profile {
      display: flex;
      align-items: center;
      gap: 16px;
    }
    .user-details {
      display: flex;
      flex-direction: column;
      align-items: flex-end;
    }
    .role-badge {
      background: rgba(228, 169, 75, 0.08); border: 1px solid rgba(228, 169, 75, 0.3);
      color: var(--gold); padding: 3px 8px; border-radius: 3px; font-size: 10px; text-transform: uppercase; margin-top: 2px;
    }

    .nav-tabs { display: flex; background: rgba(15, 7, 3, 0.6); border-bottom: 1px solid rgba(228,169,75,0.12); padding: 0 30px; }
    .tab-btn {
      padding: 14px 20px; background: none; border: none; border-bottom: 2px solid transparent;
      color: rgba(245,238,216,0.45); font-family: inherit; font-size: 11px; text-transform: uppercase;
      letter-spacing: 0.08em; cursor: pointer; transition: all 0.2s;
    }
    .tab-btn:hover { color: var(--gold); }
    .tab-btn.active { color: var(--gold); border-bottom-color: var(--amber); background: rgba(228,169,75,0.04); }

    .workspace { display: grid; grid-template-columns: 280px 1fr; min-height: calc(100vh - 110px); }
    .sidebar { background: rgba(28, 12, 5, 0.6); border-right: 1px solid rgba(228,169,75,0.15); padding: 30px 20px; display: flex; flex-direction: column; gap: 25px; }
    .sidebar-section-title { font-size: 9px; letter-spacing: 0.15em; text-transform: uppercase; color: rgba(228,169,75,0.55); margin-bottom: 10px; display: block; }
    .sidebar-group { display: flex; flex-direction: column; gap: 15px; }
    
    .search-wrap { position: relative; }
    .search-wrap svg { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); width: 14px; height: 14px; color: rgba(228,169,75,0.35); }
    .search-input {
      width: 100%; padding: 10px 12px 10px 36px; background: rgba(255,255,255,0.03);
      border: 1px solid rgba(228,169,75,0.15); border-radius: 3px; color: var(--cream); font-family: inherit; font-size: 12px; outline: none;
    }

    .filter-select { width: 100%; padding: 10px; background: #2a1209; border: 1px solid rgba(228,169,75,0.18); border-radius: 3px; color: var(--cream); font-family: inherit; font-size: 12px; outline: none; }

    .content-canvas { padding: 30px; display: flex; flex-direction: column; gap: 30px; }
    .view-pane { display: none; flex-direction: column; gap: 30px; }
    .view-pane.active { display: flex; }

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
    
    .btn-logout {
      padding: 6px 12px;
      font-size: 10px;
      border-color: rgba(239, 68, 68, 0.4);
      color: var(--danger);
      transition: all 0.2s ease;
    }
    .btn-logout:hover {
      background: rgba(239, 68, 68, 0.1);
      border-color: var(--danger);
      color: #fff;
    }

    .table-container { background: var(--dark-card); border: 1px solid rgba(228,169,75,0.22); border-radius: 4px; overflow: hidden; box-shadow: 0 20px 50px rgba(0,0,0,0.5); }
    table { width: 100%; border-collapse: collapse; text-align: left; font-size: 12px; }
    th { background: rgba(59, 26, 14, 0.6); padding: 14px 16px; font-size: 9px; letter-spacing: 0.15em; text-transform: uppercase; color: rgba(228,169,75,0.6); border-bottom: 1px solid rgba(228,169,75,0.2); }
    td { padding: 14px 16px; border-bottom: 1px solid rgba(228,169,75,0.08); color: var(--parchment); vertical-align: middle; }
    tr:hover td { background: rgba(228,169,75,0.02); }

    .status-high { color: var(--success); background: rgba(16, 185, 129, 0.1); padding: 2px 6px; border-radius: 3px; font-size: 11px; }
    .status-low { color: var(--danger); background: rgba(239, 68, 68, 0.1); padding: 2px 6px; border-radius: 3px; font-size: 11px; font-weight: bold; }
    .tx-in { color: var(--success); font-weight: bold; }
    .tx-out { color: var(--danger); font-weight: bold; }
    .metric-badge { color: var(--gold); font-weight: bold; font-size: 13px; }

    .modal-overlay { position: fixed; inset: 0; background: rgba(10, 5, 3, 0.85); backdrop-filter: blur(4px); z-index: 100; display: flex; align-items: center; justify-content: center; opacity: 0; pointer-events: none; transition: opacity 0.25s ease; }
    .modal-overlay.active { opacity: 1; pointer-events: auto; }
    .modal-card { width: 460px; background: linear-gradient(145deg, rgba(42,18,9,0.99), rgba(28,12,5,0.99)); border: 1px solid rgba(228,169,75,0.35); border-radius: 4px; padding: 40px; }
    .modal-header { font-family: 'Playfair Display', serif; font-size: 22px; color: var(--gold); margin-bottom: 25px; display: flex; justify-content: space-between; align-items: center; }
    .modal-close { background: none; border: none; color: rgba(228,169,75,0.5); font-size: 20px; cursor: pointer; }
    
    .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; }
    .form-field { margin-bottom: 18px; }
    .form-field.full { grid-column: span 2; }
    .form-field label { display: block; font-size: 9px; letter-spacing: 0.1em; text-transform: uppercase; color: rgba(228,169,75,0.55); margin-bottom: 6px; }
    .form-field input, .form-field select {
      width: 100%; padding: 11px; background: rgba(255,255,255,0.04); border: 1px solid rgba(228,169,75,0.18); border-radius: 3px; color: var(--cream); font-family: inherit; font-size: 13px; outline: none;
    }
  </style>
</head>
<body>

  <div class="bg"></div>
  <div class="bg-strings"><div class="bg-string"></div><div class="bg-string"></div><div class="bg-string"></div></div>

  <header class="top-bar">
    <?php if(isset($conn) && $conn): ?>
      <p style='color: #10b981; font-size: 11px; letter-spacing:0.05em;'>CONNECTIVITY STATE: guitarinventory_db ONLINE</p>
    <?php else: ?>
      <p style='color: #ef4444; font-size: 11px; letter-spacing:0.05em;'>CONNECTIVITY STATE: DISCONNECTED</p>
    <?php endif; ?>
    <div class="logo-row">
      <div class="brand">
        <span class="brand-name">Fretboard</span>
        <span class="brand-sub">System Enterprise Console</span>
      </div>
    </div>
    <div class="user-profile">
      <div class="user-details">
        <span>Jian Karlo H. Alatiit</span>
        <span class="role-badge">Super Admin</span>
      </div>
      <a href="logout.php" class="btn btn-secondary btn-logout">🚪 Sign Out</a>
    </div>
  </header>

  <nav class="nav-tabs">
    <button class="tab-btn active" onclick="switchView('products', event)">🎸 Products Matrix</button>
    <button class="tab-btn" onclick="switchView('brands', event)">🏷️ Brand Lifecycle</button>
    <button class="tab-btn" onclick="switchView('suppliers', event)">📦 Suppliers Profiles</button>
    <button class="tab-btn" onclick="switchView('history', event)">📜 Transaction Ledger</button>
    <button class="tab-btn" onclick="switchView('metrics', event)">👥 Employee Metrics</button>
  </nav>

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
      
      <div id="pane-products" class="view-pane active">
        <section class="action-row">
          <h2 class="panel-title">Master Production Product Matrix (tbl_products)</h2>
          <div class="btn-group">
            <button class="btn btn-secondary" onclick="toggleModal('adjustmentModal', true)">🔄 Balance Modification</button>
            <button class="btn" onclick="toggleModal('productModal', true)">➕ Provision New Product</button>
          </div>
        </section>
        <section class="table-container">
          <table>
            <thead>
              <tr><th>SKU ID</th><th>Model Name</th><th>Category Line</th><th>Brand Origin</th><th>Price</th><th>Stock</th><th>Stock Zone</th><th style="text-align:right;">Data Controls</th></tr>
            </thead>
            <tbody id="table-products"></tbody>
          </table>
        </section>
      </div>

      <div id="pane-brands" class="view-pane">
        <section class="action-row">
          <h2 class="panel-title">Brand Manufacturer Directory (tbl_brands)</h2>
          <button class="btn" onclick="toggleModal('brandModal', true)">➕ Provision Manufacturer Brand</button>
        </section>
        <section class="table-container">
          <table>
            <thead>
              <tr><th>Brand ID</th><th>Brand Designation</th><th>Address Location</th><th style="text-align:right;">Data Controls</th></tr>
            </thead>
            <tbody id="table-brands"></tbody>
          </table>
        </section>
      </div>

      <div id="pane-suppliers" class="view-pane">
        <section class="action-row">
          <h2 class="panel-title">Registered Business Wholesale Entities (Suppliers Profiles)</h2>
        </section>
        <section class="table-container">
          <table>
            <thead>
              <tr><th>Supplier ID</th><th>Company Legal Entity</th><th>Contact Phone</th><th>Corporate Email</th><th>Corporate Address</th></tr>
            </thead>
            <tbody id="table-suppliers"></tbody>
          </table>
        </section>
      </div>

      <div id="pane-history" class="view-pane">
        <section class="action-row">
          <h2 class="panel-title">System Transaction History Log (tbl_stock_history)</h2>
          <div style="display:flex; align-items:center; gap:10px;">
            <span style="font-size:11px; color:rgba(228,169,75,0.6)">Filter Ledger State:</span>
            <select id="txFilter" class="filter-select" style="width:180px;" onchange="renderHistoryRegistry()">
              <option value="ALL">Show All Ledger Entries</option>
              <option value="Stock-In">Isolate Inbound (Stock-In)</option>
              <option value="Stock-Out">Isolate Outbound (Stock-Out)</option>
            </select>
          </div>
        </section>
        <section class="table-container">
          <table>
            <thead>
              <tr><th>Log-ID</th><th>Target Product</th><th>Authorized User</th><th>Ledger State Direction</th><th>Unit Delta</th><th>Timestamp Logged</th></tr>
            </thead>
            <tbody id="table-history"></tbody>
          </table>
        </section>
      </div>

      <div id="pane-metrics" class="view-pane">
        <section class="action-row">
          <h2 class="panel-title">Employee Productivity Matrix</h2>
          <span style="font-size: 11px; color: rgba(228,169,75,0.65);">System Actions Auditing Profile Summary</span>
        </section>
        <section class="table-container">
          <table>
            <thead>
              <tr>
                <th>Employee Full Name</th>
                <th style="text-align: right; padding-right: 40px;">Total Actions Performed</th>
              </tr>
            </thead>
            <tbody id="table-metrics">
              <?php if(empty($metricsArr)): ?>
                <tr><td colspan="2" style="text-align:center; color:rgba(228,169,75,0.4); padding:30px;">No operational transactions linked to an active user account yet.</td></tr>
              <?php else: ?>
                <?php foreach($metricsArr as $metric): ?>
                  <tr>
                    <td><b><?php echo htmlspecialchars($metric['full_name']); ?></b></td>
                    <td style="text-align: right; padding-right: 40px;"><span class="metric-badge"><?php echo (int)$metric['total_actions_performed']; ?> entries</span></td>
                  </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </section>
      </div>

    </main>
  </div>

  <div id="productModal" class="modal-overlay">
    <div class="modal-card">
      <div class="modal-header"><h3>Catalog New Product Specification</h3><button class="modal-close" onclick="toggleModal('productModal', false)">&times;</button></div>
      <form onsubmit="createNewProduct(event)">
        <div class="form-grid">
          <div class="form-field full"><label>Model Name</label><input type="text" id="p-name" required></div>
          <div class="form-field"><label>Category Assignment</label>
            <select id="p-cat">
              <?php foreach($categoriesArr as $cat): ?>
                <option value="<?php echo $cat['category_id']; ?>"><?php echo htmlspecialchars($cat['category_name']); ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-field"><label>Brand Origin</label>
            <select id="p-brand-select">
               <?php foreach($brandsArr as $b): ?>
                <option value="<?php echo $b['brand_id']; ?>"><?php echo htmlspecialchars($b['brand_name']); ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-field"><label>Retail Price (₱)</label><input type="number" id="p-price" min="0" step="0.01" required></div>
          <div class="form-field"><label>Initial Balance Stock</label><input type="number" id="p-qty" min="0" required></div>
          <div class="form-field full"><label>Fulfillment Grid Location</label><input type="text" id="p-loc" required placeholder="Showroom Wall A"></div>
        </div>
        <button type="submit" class="btn" style="width: 100%; margin-top: 15px;">Commit Record to Database</button>
      </form>
    </div>
  </div>

  <div id="brandModal" class="modal-overlay">
    <div class="modal-card">
      <div class="modal-header"><h3>Provision New Manufacturing Entity</h3><button class="modal-close" onclick="toggleModal('brandModal', false)">&times;</button></div>
      <form onsubmit="createNewBrand(event)">
        <div class="form-field"><label>Brand Corporate Name</label><input type="text" id="b-name" required placeholder="e.g., PRS Guitars"></div>
        <div class="form-field"><label>Contact Hotline Phone</label><input type="text" id="b-phone" required placeholder="e.g., 09123456789"></div>
        <div class="form-field"><label>Corporate Email Endpoint</label><input type="email" id="b-email" required placeholder="contact@prs.com"></div>
        <div class="form-field"><label>Global Headquarters Address</label><input type="text" id="b-address" required placeholder="Stevensville, Maryland, USA"></div>
        <button type="submit" class="btn" style="width: 100%; margin-top: 15px;">Instantiate Brand Node</button>
      </form>
    </div>
  </div>

  <div id="adjustmentModal" class="modal-overlay">
    <div class="modal-card">
      <div class="modal-header"><h3>Execute Stock Balance Entry</h3><button class="modal-close" onclick="toggleModal('adjustmentModal', false)">&times;</button></div>
      <form onsubmit="executeAdjustment(event)">
        <div class="form-field"><label>Target Asset Record</label><select id="adj-product"></select></div>
        <div class="form-grid">
          <div class="form-field"><label>Delta Direct State</label><select id="adj-type"><option value="Stock-In">STOCK-IN (Inbound Supply)</option><option value="Stock-Out">STOCK-OUT (Outbound Disbursal)</option></select></div>
          <div class="form-field"><label>Units Volume</label><input type="number" id="adj-qty" min="1" required value="1"></div>
        </div>
        <button type="submit" class="btn" style="width: 100%; margin-top: 15px;">Commit Log Entry & Modify Balances</button>
      </form>
    </div>
  </div>

  <script>
    // Server-populated arrays loaded directly from MySQL schema definitions
    let tableProducts = <?php echo json_encode($productsArr); ?>;
    let tableBrands = <?php echo json_encode($brandsArr); ?>;
    let tableHistory = <?php echo json_encode($historyArr); ?>;

    document.addEventListener("DOMContentLoaded", () => {
      refreshInterfaceViews();
    });

    function switchView(viewName, e) {
      document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
      document.querySelectorAll('.view-pane').forEach(p => p.classList.remove('active'));
      e.target.classList.add('active');
      document.getElementById(`pane-${viewName}`).classList.add('active');
    }

    function toggleModal(id, open) {
      const modal = document.getElementById(id);
      if(open) {
        modal.classList.add('active');
        if(id === 'adjustmentModal') setupAdjustmentDropdowns();
      } else {
        modal.classList.remove('active');
      }
    }

    function setupAdjustmentDropdowns() {
      document.getElementById('adj-product').innerHTML = tableProducts.map(p => `
        <option value="${p.product_id}">${p.brand_name || 'Generic'} — ${p.product_name} (${p.quantity_on_hand} units available)</option>
      `).join('');
    }

    function refreshInterfaceViews() {
      executeGlobalFilters(); 
      renderHistoryRegistry();
    }

    function renderProductsRegistry(records = tableProducts) {
      const body = document.getElementById('table-products');
      if(records.length === 0) {
        body.innerHTML = `<tr><td colspan="8" style="text-align:center; color:rgba(228,169,75,0.4); padding:30px;">No product records matched selected parameters.</td></tr>`;
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
          <td style="text-align:right;"><span style="color:rgba(228,169,75,0.3); font-size:10px;">Enforced</span></td>
        </tr>
      `).join('');
    }

    function renderBrandsRegistry(records = tableBrands) {
      const body = document.getElementById('table-brands');
      if(records.length === 0) {
        body.innerHTML = `<tr><td colspan="4" style="text-align:center; color:rgba(228,169,75,0.4); padding:30px;">No brand records matched selected filter scope.</td></tr>`;
        return;
      }
      body.innerHTML = records.map(b => `
        <tr>
          <td style="color:var(--gold)">B-${b.brand_id}</td>
          <td><b>${b.brand_name}</b></td>
          <td>${b.address}</td>
          <td style="text-align:right;"><span style="color:rgba(228,169,75,0.3); font-size:10px;">Enforced</span></td>
        </tr>
      `).join('');
    }

    function renderSuppliersRegistry(records = tableBrands) {
      const body = document.getElementById('table-suppliers');
      if(records.length === 0) {
        body.innerHTML = `<tr><td colspan="5" style="text-align:center; color:rgba(228,169,75,0.4); padding:30px;">No supplier profiles match the query.</td></tr>`;
        return;
      }
      body.innerHTML = records.map(s => `
        <tr>
          <td>S-${s.brand_id}</td>
          <td><b>${s.brand_name} Corporate Logistics</b></td>
          <td>${s.phone}</td>
          <td><a href="mailto:${s.email}" style="color:var(--gold); text-decoration:none;">${s.email}</a></td>
          <td style="color:rgba(245,238,216,0.7); font-style:italic;">${s.address}</td>
        </tr>
      `).join('');
    }

    function renderHistoryRegistry() {
      const body = document.getElementById('table-history');
      const filterMode = document.getElementById('txFilter').value;
      const itemsToRender = tableHistory.filter(h => filterMode === "ALL" || h.stock_type === filterMode);

      if(itemsToRender.length === 0) {
        body.innerHTML = `<tr><td colspan="6" style="text-align:center; color:rgba(228,169,75,0.4); padding:30px;">No transaction entries found in history.</td></tr>`;
        return;
      }
      body.innerHTML = itemsToRender.map(h => `
        <tr>
          <td>TX-${h.stockHistory_id}</td>
          <td><b>${h.product_name || 'Deleted Asset'}</b></td>
          <td>${h.full_name || 'System User'}</td>
          <td><span class="${h.stock_type === 'Stock-In' ? 'tx-in' : 'tx-out'}">${h.stock_type === 'Stock-In' ? '📈 STOCK-IN' : '📉 STOCK-OUT'}</span></td>
          <td><b>${h.quantity} units</b></td>
          <td style="color:rgba(245,238,216,0.4); font-size:11px;">${h.stockHistory_date}</td>
        </tr>
      `).join('');
    }

    // ─── ASYNC BACKEND EXECUTION ROUTERS ───
    async function createNewProduct(e) {
      e.preventDefault();
      const payload = {
        action: 'CREATE_PRODUCT',
        product_name: document.getElementById('p-name').value,
        category_id: document.getElementById('p-cat').value,
        brand_id: document.getElementById('p-brand-select').value,
        unit_price: parseFloat(document.getElementById('p-price').value),
        quantity_on_hand: parseInt(document.getElementById('p-qty').value),
        location: document.getElementById('p-loc').value
      };

      try {
        const response = await fetch('process_actions.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify(payload)
        });
        const result = await response.json();
        if (result.status === 'success') {
          alert(result.message);
          location.reload();
        } else {
          alert(`Rejected: ${result.message}`);
        }
      } catch (err) {
        alert("Connection lost to API processor router script.");
      }
    }

    async function createNewBrand(e) {
      e.preventDefault();
      const payload = {
        action: 'CREATE_BRAND',
        brand_name: document.getElementById('b-name').value,
        phone: document.getElementById('b-phone').value,
        email: document.getElementById('b-email').value,
        address: document.getElementById('b-address').value
      };

      try {
        const response = await fetch('process_actions.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify(payload)
        });
        const result = await response.json();
        if (result.status === 'success') {
          alert(result.message);
          location.reload();
        } else {
          alert(`Rejected: ${result.message}`);
        }
      } catch (err) {
        alert("Connection lost to API processor router script.");
      }
    }

    async function executeAdjustment(e) {
      e.preventDefault();
      const payload = {
        action: 'EXECUTE_ADJUSTMENT',
        product_id: parseInt(document.getElementById('adj-product').value),
        type: document.getElementById('adj-type').value,
        quantity: parseInt(document.getElementById('adj-qty').value)
      };

      try {
        const response = await fetch('process_actions.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify(payload)
        });
        const result = await response.json();
        if (result.status === 'success') {
          alert(result.message);
          location.reload();
        } else {
          alert(`Transaction Denied: ${result.message}`);
        }
      } catch (err) {
        alert("Connection link failure.");
      }
    }

    function executeGlobalFilters() {
      const search = document.getElementById('globalSearch').value.toLowerCase();
      const category = document.getElementById('categorySelect').value;
      const stockThreshold = document.getElementById('stockThresholdSelect').value;
      const brand = document.getElementById('brandSelect').value;

      const filteredProducts = tableProducts.filter(p => {
        const textMatch = p.product_name.toLowerCase().includes(search) || 
                          (p.brand_name && p.brand_name.toLowerCase().includes(search)) ||
                          p.location.toLowerCase().includes(search);
        const catMatch = category === "ALL" || p.category_name === category;
        const brandMatch = brand === "ALL" || p.brand_name === brand;
        let stockMatch = stockThreshold === "ALL" || (stockThreshold === "HIGH" ? p.quantity_on_hand >= 10 : p.quantity_on_hand < 10);
        return textMatch && catMatch && brandMatch && stockMatch;
      });
      renderProductsRegistry(filteredProducts);

      const filteredBrands = tableBrands.filter(b => {
        return (brand === "ALL" || b.brand_name === brand) && (b.brand_name.toLowerCase().includes(search) || b.address.toLowerCase().includes(search));
      });
      renderBrandsRegistry(filteredBrands);

      const filteredSuppliers = tableBrands.filter(s => {
        return s.brand_name.toLowerCase().includes(search) || s.phone.toLowerCase().includes(search) || s.email.toLowerCase().includes(search);
      });
      renderSuppliersRegistry(filteredSuppliers);
    }
  </script>
</body>
</html>