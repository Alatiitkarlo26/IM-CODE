<?php
  // Go up one folder, then into the db folder to find the connection script
  require_once '../db/db_connection.php'; 
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Fretboard — Staff Terminal Workspace</title>
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

    /* ─── Fine Premium Wood Textured Backplane ─── */
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

    /* ─── Global System Layout Header ─── */
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

    .role-badge {
      background: rgba(228, 169, 75, 0.08); border: 1px solid rgba(160, 160, 160, 0.3);
      color: #ede0c4; padding: 3px 8px; border-radius: 3px; font-size: 10px; text-transform: uppercase;
    }

    /* ─── Structural Workspace Grid Splitter ─── */
    .workspace { display: grid; grid-template-columns: 280px 1fr; min-height: calc(100vh - 65px); }
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

    /* ─── Display Panels Core Viewports ─── */
    .content-canvas { padding: 30px; display: flex; flex-direction: column; gap: 30px; }
    .action-row { display: flex; justify-content: space-between; align-items: center; }
    .panel-title { font-family: 'Playfair Display', serif; font-size: 22px; color: var(--parchment); }
    
    .btn {
      padding: 10px 18px; background: linear-gradient(135deg, var(--amber), var(--rosewood) 80%);
      border: none; border-radius: 3px; color: var(--cream); font-family: inherit; font-size: 11px;
      letter-spacing: 0.05em; text-transform: uppercase; cursor: pointer; box-shadow: 0 4px 15px rgba(200,129,42,0.15);
    }

    /* ─── Structural Core Table Frameworks ─── */
    .table-container { background: var(--dark-card); border: 1px solid rgba(228,169,75,0.22); border-radius: 4px; overflow: hidden; box-shadow: 0 20px 50px rgba(0,0,0,0.5); }
    table { width: 100%; border-collapse: collapse; text-align: left; font-size: 12px; }
    th { background: rgba(59, 26, 14, 0.6); padding: 14px 16px; font-size: 9px; letter-spacing: 0.15em; text-transform: uppercase; color: rgba(228,169,75,0.6); border-bottom: 1px solid rgba(228,169,75,0.2); }
    td { padding: 14px 16px; border-bottom: 1px solid rgba(228,169,75,0.08); color: var(--parchment); vertical-align: middle; }
    tr:hover td { background: rgba(228,169,75,0.02); }

    .status-high { color: var(--success); background: rgba(16, 185, 129, 0.1); padding: 2px 6px; border-radius: 3px; font-size: 11px; }
    .status-low { color: var(--danger); background: rgba(239, 68, 68, 0.1); padding: 2px 6px; border-radius: 3px; font-size: 11px; font-weight: bold; }

    /* ─── Management Modals Base ─── */
    .modal-overlay { position: fixed; inset: 0; background: rgba(10, 5, 3, 0.85); backdrop-filter: blur(4px); z-index: 100; display: flex; align-items: center; justify-content: center; opacity: 0; pointer-events: none; transition: opacity 0.25s ease; }
    .modal-overlay.active { opacity: 1; pointer-events: auto; }
    .modal-card { width: 440px; background: linear-gradient(145deg, rgba(42,18,9,0.99), rgba(28,12,5,0.99)); border: 1px solid rgba(228,169,75,0.35); border-radius: 4px; padding: 40px; }
    .modal-header { font-family: 'Playfair Display', serif; font-size: 22px; color: var(--gold); margin-bottom: 25px; display: flex; justify-content: space-between; align-items: center; }
    .modal-close { background: none; border: none; color: rgba(228,169,75,0.5); font-size: 20px; cursor: pointer; }
    
    .form-field { margin-bottom: 18px; }
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
    <?php
      if($conn) {
          echo "<p style='color: #10b981; font-size: 11px; letter-spacing:0.05em;'>CONNECTIVITY STATE: NODE ONLINE</p>";
      }
    ?>
    <div class="logo-row">
      <div class="brand">
        <span class="brand-name">Fretboard</span>
        <span class="brand-sub">Staff Terminal Workspace</span>
      </div>
    </div>
    <div class="user-profile">
      <span style="color: rgba(245,238,216,0.65);">Terminal Agent:</span>
      <span style="font-weight: bold; padding-right: 5px;">Gian Rizen A. Lacao</span>
      <span class="role-badge">Staff</span>
    </div>
  </header>

  <div class="workspace">
    <aside class="sidebar">
      <div>
        <span class="sidebar-section-title">Search Assets</span>
        <div class="search-wrap">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
          <input type="text" id="searchBar" class="search-input" placeholder="Search model or brand..." oninput="executeFilters()">
        </div>
      </div>
      
      <div class="sidebar-group">
        <div>
          <span class="sidebar-section-title">Category Assignment</span>
          <select id="categoryFilter" class="filter-select" onchange="executeFilters()">
            <option value="ALL">All Categories</option>
            <option value="Guitars">Guitars</option>
            <option value="Amplifiers">Amplifiers</option>
            <option value="Pedals">Pedals</option>
          </select>
        </div>

        <div>
          <span class="sidebar-section-title">Stock Threshold Level</span>
          <select id="stockThresholdFilter" class="filter-select" onchange="executeFilters()">
            <option value="ALL">All Stock Statuses</option>
            <option value="HIGH">High Stock (≥ 10 units)</option>
            <option value="LOW">Low Stock (< 10 units)</option>
          </select>
        </div>

        <div>
          <span class="sidebar-section-title">Brand Line</span>
          <select id="brandFilter" class="filter-select" onchange="executeFilters()">
            <option value="ALL">All Brands</option>
            <option value="Fender">Fender</option>
            <option value="Gibson">Gibson</option>
            <option value="Boss">Boss</option>
          </select>
        </div>
      </div>
    </aside>

    <main class="content-canvas">
      <div class="action-row">
        <h2 class="panel-title">Fulfillment Inventory Registry</h2>
        <button class="btn" onclick="toggleModal('saleModal', true)">🛒 Register Terminal Sale</button>
      </div>

      <section class="table-container">
        <table>
          <thead>
            <tr>
              <th>SKU ID</th>
              <th>Model Designation</th>
              <th>Category</th>
              <th>Brand Line</th>
              <th>Retail Unit Price</th>
              <th style="text-align:center;">Stock Level</th>
              <th>Fulfillment Grid Location</th>
            </tr>
          </thead>
          <tbody id="table-staff-products"></tbody>
        </table>
      </section>
    </main>
  </div>

  <div id="saleModal" class="modal-overlay">
    <div class="modal-card">
      <div class="modal-header">
        <h3>Log Point-of-Sale Disbursal</h3>
        <button class="modal-close" onclick="toggleModal('saleModal', false)">&times;</button>
      </div>
      <form onsubmit="handleStaffSale(event)">
        <div class="form-field">
          <label>Target Inventory Asset</label>
          <select id="saleItemTarget"></select>
        </div>
        <div class="form-field">
          <label>Units Quantity Disbursed</label>
          <input type="number" id="saleQty" min="1" required value="1">
        </div>
        <button type="submit" class="btn" style="width:100%; margin-top:10px;">Authorize Sale Ledger Update</button>
      </form>
    </div>
  </div>

  <script>
    // Local memory sync representations matching the system specification models
    let dbProducts = [
      { id: 101, name: "Affinity Series Stratocaster", category: "Guitars", brand: "Fender", price: 18500.00, qty: 14, location: "Rack A-1", isActive: true },
      { id: 102, name: "Les Paul Standard '60s", category: "Guitars", brand: "Gibson", price: 145000.00, qty: 3, location: "Vault Display", isActive: true },
      { id: 103, name: "Katana 50 MkII Amplifier", category: "Amplifiers", brand: "Boss", price: 16500.00, qty: 22, location: "Row B-2", isActive: true }
    ];

    document.addEventListener("DOMContentLoaded", () => {
      renderStaffRegistry();
    });

    function toggleModal(id, open) {
      const modal = document.getElementById(id);
      if (open) {
        modal.classList.add('active');
        if(id === 'saleModal') populateProductDropdown();
      } else {
        modal.classList.remove('active');
      }
    }

    function populateProductDropdown() {
      const dropdown = document.getElementById("saleItemTarget");
      // Only display active products with stock quantities greater than 0
      const availableItems = dbProducts.filter(p => p.isActive && p.qty > 0);
      
      dropdown.innerHTML = availableItems.map(p => 
        `<option value="${p.id}">${p.brand} — ${p.name} (₱${p.price.toLocaleString()} | ${p.qty} Available)</option>`
      ).join('');
    }

    function renderStaffRegistry(records = dbProducts) {
      const body = document.getElementById("table-staff-products");
      
      // Filter out products deactivated by admins
      const activeRecords = records.filter(p => p.isActive);

      if (activeRecords.length === 0) {
        body.innerHTML = `<tr><td colspan="7" style="text-align:center; color:rgba(228,169,75,0.4); padding:30px;">No matches found in active database scope.</td></tr>`;
        return;
      }

      body.innerHTML = activeRecords.map(item => `
        <tr>
          <td style="color:var(--gold)">P-${item.id}</td>
          <td><b>${item.name}</b></td>
          <td>${item.category}</td>
          <td>${item.brand}</td>
          <td>₱${item.price.toLocaleString(undefined, {minimumFractionDigits: 2})}</td>
          <td style="text-align:center;">
            <span class="${item.qty < 10 ? 'status-low' : 'status-high'}">${item.qty} units</span>
          </td>
          <td style="color:rgba(245,238,216,0.7); font-style:italic;">${item.location}</td>
        </tr>
      `).join('');
    }

    function executeFilters() {
      const searchVal = document.getElementById("searchBar").value.toLowerCase();
      const categoryVal = document.getElementById("categoryFilter").value;
      const stockThresholdVal = document.getElementById("stockThresholdFilter").value;
      const brandVal = document.getElementById("brandFilter").value;

      const records = dbProducts.filter(item => {
        const matchesSearch = item.name.toLowerCase().includes(searchVal) || item.brand.toLowerCase().includes(searchVal);
        const matchesCategory = (categoryVal === "ALL" || item.category === categoryVal);
        const matchesBrand = (brandVal === "ALL" || item.brand === brandVal);
        
        // Stock Status evaluation criteria matches Admin dashboard parameters
        let matchesStockThreshold = true;
        if (stockThresholdVal === "HIGH") {
          matchesStockThreshold = item.qty >= 10;
        } else if (stockThresholdVal === "LOW") {
          matchesStockThreshold = item.qty < 10;
        }

        return matchesSearch && matchesCategory && matchesBrand && matchesStockThreshold;
      });

      renderStaffRegistry(records);
    }

    function handleStaffSale(e) {
      e.preventDefault();
      const targetId = parseInt(document.getElementById("saleItemTarget").value);
      const units = parseInt(document.getElementById("saleQty").value);

      const product = dbProducts.find(p => p.id === targetId);
      if (product) {
        if (product.qty < units) {
          alert(`Error: Insufficient stock. Only ${product.qty} units available.`);
          return;
        }

        product.qty -= units;
        alert(`Sale Registered Successfully!\n\nReceipt Ledger Update:\n${units}x ${product.brand} ${product.name}\nTotal Charge: ₱${(units * product.price).toLocaleString()}`);
        
        e.target.reset();
        toggleModal('saleModal', false);
        executeFilters(); // Refresh display preserving active filter values
      }
    }
  </script>
</body>
</html>