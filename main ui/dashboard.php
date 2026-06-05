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
      background: rgba(228, 169, 75, 0.08); border: 1px solid rgba(228, 169, 75, 0.3);
      color: var(--gold); padding: 3px 8px; border-radius: 3px; font-size: 10px; text-transform: uppercase;
    }

    /* ─── Section Configuration Tabs ─── */
    .nav-tabs { display: flex; background: rgba(15, 7, 3, 0.6); border-bottom: 1px solid rgba(228,169,75,0.12); padding: 0 30px; }
    .tab-btn {
      padding: 14px 20px; background: none; border: none; border-bottom: 2px solid transparent;
      color: rgba(245,238,216,0.45); font-family: inherit; font-size: 11px; text-transform: uppercase;
      letter-spacing: 0.08em; cursor: pointer; transition: all 0.2s;
    }
    .tab-btn:hover { color: var(--gold); }
    .tab-btn.active { color: var(--gold); border-bottom-color: var(--amber); background: rgba(228,169,75,0.04); }

    /* ─── Structural Workspace Grid Splitter ─── */
    .workspace { display: grid; grid-template-columns: 280px 1fr; min-height: calc(100vh - 110px); }
    .sidebar { background: rgba(28, 12, 5, 0.6); border-right: 1px solid rgba(228,169,75,0.15); padding: 30px 20px; display: flex; flex-direction: column; gap: 25px; }
    .sidebar-section-title { font-size: 9px; letter-spacing: 0.15em; text-transform: uppercase; color: rgba(228,169,75,0.55); margin-bottom: 10px; display: block; }
    
    .search-wrap { position: relative; }
    .search-wrap svg { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); width: 14px; height: 14px; color: rgba(228,169,75,0.35); }
    .search-input {
      width: 100%; padding: 10px 12px 10px 36px; background: rgba(255,255,255,0.03);
      border: 1px solid rgba(228,169,75,0.15); border-radius: 3px; color: var(--cream); font-family: inherit; font-size: 12px; outline: none;
    }

    .filter-select { width: 100%; padding: 10px; background: #2a1209; border: 1px solid rgba(228,169,75,0.18); border-radius: 3px; color: var(--cream); font-family: inherit; font-size: 12px; outline: none; }

    /* ─── Display Panels Core Viewports ─── */
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
    }
    .btn-secondary { background: rgba(255,255,255,0.03); border: 1px solid rgba(228,169,75,0.25); box-shadow: none; }
    .btn-danger-link { background: none; border: none; color: var(--danger); font-family: inherit; font-size: 11px; cursor: pointer; text-transform: uppercase; font-weight: bold; }
    .btn-danger-link:hover { text-decoration: underline; }

    /* ─── Structural Core Table Frameworks ─── */
    .table-container { background: var(--dark-card); border: 1px solid rgba(228,169,75,0.22); border-radius: 4px; overflow: hidden; box-shadow: 0 20px 50px rgba(0,0,0,0.5); }
    table { width: 100%; border-collapse: collapse; text-align: left; font-size: 12px; }
    th { background: rgba(59, 26, 14, 0.6); padding: 14px 16px; font-size: 9px; letter-spacing: 0.15em; text-transform: uppercase; color: rgba(228,169,75,0.6); border-bottom: 1px solid rgba(228,169,75,0.2); }
    td { padding: 14px 16px; border-bottom: 1px solid rgba(228,169,75,0.08); color: var(--parchment); vertical-align: middle; }
    tr:hover td { background: rgba(228,169,75,0.02); }

    /* ─── Active Architectural Toggle Switches ─── */
    .switch-label { display: inline-flex; align-items: center; gap: 8px; cursor: pointer; font-size: 11px; user-select: none; }
    .switch-box { position: relative; width: 34px; height: 18px; background: rgba(255,255,255,0.1); border-radius: 9px; border: 1px solid rgba(228,169,75,0.3); transition: background 0.2s; }
    .switch-box::after { content: ''; position: absolute; top: 2px; left: 2px; width: 12px; height: 12px; background: rgba(228,169,75,0.6); border-radius: 50%; transition: transform 0.2s, background 0.2s; }
    input[type="checkbox"] { display: none; }
    input[type="checkbox"]:checked + .switch-box { background: rgba(16, 185, 129, 0.15); border-color: var(--success); }
    input[type="checkbox"]:checked + .switch-box::after { transform: translateX(16px); background: var(--success); }

    .badge-status { padding: 2px 6px; border-radius: 2px; font-size: 10px; text-transform: uppercase; font-weight: 500; }
    .status-instock { background: rgba(16, 185, 129, 0.12); color: var(--success); }
    .status-disabled { background: rgba(255,255,255,0.05); color: rgba(245,238,216,0.35); border: 1px dashed rgba(255,255,255,0.1); }
    .tx-in { color: var(--success); font-weight: bold; }
    .tx-out { color: var(--danger); font-weight: bold; }

    /* ─── Management Modals Base ─── */
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
    <?php
      // You can now write queries anywhere inside the HTML body!
      // Example: Testing if the connection works
      if($conn) {
          echo "<p style='color: green;'>Successfully connected to the database!</p>";
      }
    ?>
    <div class="logo-row">
      <div class="brand">
        <span class="brand-name">Fretboard</span>
        <span class="brand-sub">System Enterprise Console</span>
      </div>
    </div>
    <div class="user-profile">
      <span>Jian Karlo H. Alatiit</span>
      <span class="role-badge">Super Admin</span>
    </div>
  </header>

  <nav class="nav-tabs">
    <button class="tab-btn active" onclick="switchView('products')">🎸 Products Matrix</button>
    <button class="tab-btn" onclick="switchView('brands')">🏷️ Brand Lifecycle</button>
    <button class="tab-btn" onclick="switchView('suppliers')">📦 Suppliers Profiles</button>
    <button class="tab-btn" onclick="switchView('history')">📜 Transaction Ledger</button>
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
      <div>
        <span class="sidebar-section-title">Category Assignment</span>
        <select id="categorySelect" class="filter-select" onchange="executeGlobalFilters()">
          <option value="ALL">All Categories</option>
          <option value="Guitars">Guitars</option>
          <option value="Amplifiers">Amplifiers</option>
          <option value="Pedals">Pedals</option>
        </select>
      </div>
    </aside>

    <main class="content-canvas">
      
      <div id="pane-products" class="view-pane active">
        <section class="action-row">
          <h2 class="panel-title">Master Production Product Matrix</h2>
          <div class="btn-group">
            <button class="btn btn-secondary" onclick="toggleModal('adjustmentModal', true)">🔄 Balance Modification</button>
            <button class="btn" onclick="toggleModal('productModal', true)">➕ Provision New Product</button>
          </div>
        </section>
        <section class="table-container">
          <table>
            <thead>
              <tr><th>SKU ID</th><th>Model Name</th><th>Category Line</th><th>Brand Origin</th><th>Price</th><th>Stock</th><th>Operational State</th><th style="text-align:right;">Data Controls</th></tr>
            </thead>
            <tbody id="table-products"></tbody>
          </table>
        </section>
      </div>

      <div id="pane-brands" class="view-pane">
        <section class="action-row">
          <h2 class="panel-title">Brand Manufacturer Configuration Directory</h2>
          <button class="btn" onclick="toggleModal('brandModal', true)">➕ Provision Manufacturer Brand</button>
        </section>
        <section class="table-container">
          <table>
            <thead>
              <tr><th>Brand ID</th><th>Brand Designation</th><th>Equipment Type Specialty</th><th>Global Active Scope</th><th style="text-align:right;">Data Controls</th></tr>
            </thead>
            <tbody id="table-brands"></tbody>
          </table>
        </section>
      </div>

      <div id="pane-suppliers" class="view-pane">
        <section class="action-row">
          <h2 class="panel-title">Registered Business Wholesale Entities</h2>
          <button class="btn" onclick="toggleModal('supplierModal', true)">➕ Authorize Supplier Node</button>
        </section>
        <section class="table-container">
          <table>
            <thead>
              <tr><th>Supplier ID</th><th>Company Legal Entity</th><th>Contact Phone</th><th>Corporate Email</th><th style="text-align:right;">Data Controls</th></tr>
            </thead>
            <tbody id="table-suppliers"></tbody>
          </table>
        </section>
      </div>

      <div id="pane-history" class="view-pane">
        <section class="action-row">
          <h2 class="panel-title">System Transaction History Log</h2>
          <div style="display:flex; align-items:center; gap:10px;">
            <span style="font-size:11px; color:rgba(228,169,75,0.6)">Filter Ledger State:</span>
            <select id="txFilter" class="filter-select" style="width:180px;" onchange="renderHistoryRegistry()">
              <option value="ALL">Show All Ledger Entries</option>
              <option value="IN">Isolate Inbound (STOCK-IN)</option>
              <option value="OUT">Isolate Outbound (STOCK-OUT)</option>
            </select>
          </div>
        </section>
        <section class="table-container">
          <table>
            <thead>
              <tr><th>Log-ID</th><th>Target Product</th><th>Authorized User</th><th>Associated Supplier</th><th>Ledger State Direction</th><th>Unit Delta</th><th>Timestamp Logged</th></tr>
            </thead>
            <tbody id="table-history"></tbody>
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
          <div class="form-field"><label>Category Line</label><select id="p-cat"><option value="Guitars">Guitars</option><option value="Amplifiers">Amplifiers</option><option value="Pedals">Pedals</option></select></div>
          <div class="form-field"><label>Brand Origin</label><select id="p-brand-select"></select></div>
          <div class="form-field"><label>Retail Price (₱)</label><input type="number" id="p-price" min="0" step="0.01" required></div>
          <div class="form-field"><label>Initial Balance Stock</label><input type="number" id="p-qty" min="0" required></div>
          <div class="form-field full"><label>Fulfillment Grid Location</label><input type="text" id="p-loc" required placeholder="Rack A-1"></div>
        </div>
        <button type="submit" class="btn" style="width: 100%; margin-top: 15px;">Commit Record to Database</button>
      </form>
    </div>
  </div>

  <div id="brandModal" class="modal-overlay">
    <div class="modal-card">
      <div class="modal-header"><h3>Provision New Manufacturing Entity</h3><button class="modal-close" onclick="toggleModal('brandModal', false)">&times;</button></div>
      <form onsubmit="createNewBrand(event)">
        <div class="form-field"><label>Brand Corporate Name</label><input type="text" id="b-name" required placeholder="e.g., Ibanez"></div>
        <div class="form-field"><label>Product Specialty Type</label><input type="text" id="b-type" required placeholder="e.g., Solid-Body Electric Guitars"></div>
        <button type="submit" class="btn" style="width: 100%; margin-top: 15px;">Instantiate Brand Node</button>
      </form>
    </div>
  </div>

  <div id="supplierModal" class="modal-overlay">
    <div class="modal-card">
      <div class="modal-header"><h3>Provision New Corporate Supplier</h3><button class="modal-close" onclick="toggleModal('supplierModal', false)">&times;</button></div>
      <form onsubmit="createNewSupplier(event)">
        <div class="form-grid">
          <div class="form-field full"><label>Company Legal Designation</label><input type="text" id="s-name" required></div>
          <div class="form-field"><label>Contact Telephone</label><input type="text" id="s-phone" required></div>
          <div class="form-field"><label>Corporate Endpoint Email</label><input type="email" id="s-email" required></div>
        </div>
        <button type="submit" class="btn" style="width: 100%; margin-top: 15px;">Authorize Supplier Profile</button>
      </form>
    </div>
  </div>

  <div id="adjustmentModal" class="modal-overlay">
    <div class="modal-card">
      <div class="modal-header"><h3>Execute Stock Balance Entry</h3><button class="modal-close" onclick="toggleModal('adjustmentModal', false)">&times;</button></div>
      <form onsubmit="executeAdjustment(event)">
        <div class="form-field"><label>Target Asset Record</label><select id="adj-product"></select></div>
        <div class="form-grid">
          <div class="form-field"><label>Delta Direct State</label><select id="adj-type"><option value="IN">STOCK-IN (Inbound Supply)</option><option value="OUT">STOCK-OUT (Outbound Disbursal)</option></select></div>
          <div class="form-field"><label>Units Volume</label><input type="number" id="adj-qty" min="1" required value="1"></div>
          <div class="form-field full"><label>Sourced Vendor Entity</label><select id="adj-supplier"></select></div>
        </div>
        <button type="submit" class="btn" style="width: 100%; margin-top: 15px;">Commit Log Entry & Modify Balances</button>
      </form>
    </div>
  </div>

  <script>
    // State Architecture Mapped to Document Structural Definitions
    let tableProducts = [
      { id: 101, name: "Affinity Series Stratocaster", category: "Guitars", brand: "Fender", price: 18500.00, qty: 14, location: "Rack A-1", isActive: true },
      { id: 102, name: "Les Paul Standard '60s", category: "Guitars", brand: "Gibson", price: 145000.00, qty: 3, location: "Vault Display", isActive: true },
      { id: 103, name: "Katana 50 MkII Amplifier", category: "Amplifiers", brand: "Boss", price: 16500.00, qty: 22, location: "Row B-2", isActive: false }
    ];

    let tableBrands = [
      { id: 301, name: "Fender", type: "Guitars & Amps", isActive: true },
      { id: 302, name: "Gibson", type: "Premium Electric Guitars", isActive: true },
      { id: 303, name: "Boss", type: "Effects Pedals & Solid-State Amps", isActive: true }
    ];

    let tableSuppliers = [
      { id: 501, name: "Yupangco Music Corp", phone: "0288911161", email: "info@yupangco.com" },
      { id: 502, name: "JB Music Philippines", phone: "0284260341", email: "b2b@jbmusic.com.ph" }
    ];

    let tableHistory = [
      { txId: 9001, productName: "Katana 50 MkII Amplifier", user: "Admin (Karlo)", supplier: "Yupangco Music Corp", type: "IN", quantity: 10, date: "2026-06-02 14:22" },
      { txId: 9002, productName: "Affinity Series Stratocaster", user: "Staff (Lacao)", supplier: "N/A (Customer)", type: "OUT", quantity: 1, date: "2026-06-04 09:10" }
    ];

    document.addEventListener("DOMContentLoaded", () => {
      refreshInterfaceViews();
    });

    function switchView(viewName) {
      document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
      document.querySelectorAll('.view-pane').forEach(p => p.classList.remove('active'));
      event.target.classList.add('active');
      document.getElementById(`pane-${viewName}`).classList.add('active');
    }

    function toggleModal(id, open) {
      const modal = document.getElementById(id);
      if(open) {
        modal.classList.add('active');
        if(id === 'adjustmentModal') setupAdjustmentDropdowns();
        if(id === 'productModal') populateBrandDropdownMenu();
      } else {
        modal.classList.remove('active');
      }
    }

    function setupAdjustmentDropdowns() {
      // Filter out products whose brand is inactive to prevent transactions on discontinued brands
      const availableProducts = tableProducts.filter(p => {
        const parentBrand = tableBrands.find(b => b.name === p.brand);
        return parentBrand ? parentBrand.isActive : true;
      });

      document.getElementById('adj-product').innerHTML = availableProducts.map(p => `<option value="${p.id}">${p.brand} — ${p.name} (${p.qty} units available)</option>`).join('');
      document.getElementById('adj-supplier').innerHTML = `<option value="0">Retail Customer Disbursal (No Supplier Link)</option>` + tableSuppliers.map(s => `<option value="${s.id}">${s.name}</option>`).join('');
    }

    function populateBrandDropdownMenu() {
      // Dynamic rendering ensures you can only select active brands when declaring new items
      const activeBrands = tableBrands.filter(b => b.isActive);
      document.getElementById('p-brand-select').innerHTML = activeBrands.map(b => `<option value="${b.name}">${b.name}</option>`).join('');
    }

    /* ─── Global State Rendering Pipelines ─── */
    function refreshInterfaceViews() {
      renderProductsRegistry();
      renderBrandsRegistry();
      renderSuppliersRegistry();
      renderHistoryRegistry();
    }

    // Feature 2: Product Availability Render & Feature 4: Delete Node helper
    function renderProductsRegistry(records = tableProducts) {
      const body = document.getElementById('table-products');
      body.innerHTML = records.map(p => `
        <tr style="${!p.isActive ? 'opacity: 0.55;' : ''}">
          <td style="color:var(--gold)">P-${p.id}</td>
          <td><b>${p.name}</b></td>
          <td>${p.category}</td>
          <td>${p.brand}</td>
          <td>₱${p.price.toLocaleString(undefined, {minimumFractionDigits: 2})}</td>
          <td style="text-align:center; font-weight:bold;">${p.qty}</td>
          <td>
            <label class="switch-label">
              <input type="checkbox" ${p.isActive ? 'checked' : ''} onchange="toggleProductAvailability(${p.id}, this.checked)">
              <div class="switch-box"></div>
              <span>${p.isActive ? 'Active' : 'Inactive'}</span>
            </label>
          </td>
          <td style="text-align:right;"><button class="btn-danger-link" onclick="deleteProductRecord(${p.id})">Purge</button></td>
        </tr>
      `).join('');
    }

    // Feature 3: Brand Management System Lifecycle Switch
    function renderBrandsRegistry() {
      const body = document.getElementById('table-brands');
      body.innerHTML = tableBrands.map(b => `
        <tr style="${!b.isActive ? 'opacity: 0.55;' : ''}">
          <td style="color:var(--gold)">B-${b.id}</td>
          <td><b>${b.name}</b></td>
          <td>${b.type}</td>
          <td>
            <label class="switch-label">
              <input type="checkbox" ${b.isActive ? 'checked' : ''} onchange="toggleBrandLifecycle(${b.id}, this.checked)">
              <div class="switch-box"></div>
              <span>${b.isActive ? 'Deployed' : 'Decommissioned'}</span>
            </label>
          </td>
          <td style="text-align:right;"><button class="btn-danger-link" onclick="deleteBrandRecord(${b.id})">Purge</button></td>
        </tr>
      `).join('');
    }

    function renderSuppliersRegistry() {
      const body = document.getElementById('table-suppliers');
      body.innerHTML = tableSuppliers.map(s => `
        <tr>
          <td>S-${s.id}</td>
          <td><b>${s.name}</b></td>
          <td>${s.phone}</td>
          <td>${s.email}</td>
          <td style="text-align:right;"><button class="btn-danger-link" onclick="deleteSupplierRecord(${s.id})">Purge</button></td>
        </tr>
      `).join('');
    }

    // Feature 1: Status filtering: Stock-ins and outs processing logic
    function renderHistoryRegistry() {
      const body = document.getElementById('table-history');
      const filterMode = document.getElementById('txFilter').value;

      const itemsToRender = tableHistory.filter(h => {
        if(filterMode === "ALL") return true;
        return h.type === filterMode;
      });

      if(itemsToRender.length === 0) {
        body.innerHTML = `<tr><td colspan="7" style="text-align:center; color:rgba(228,169,75,0.4); padding:30px;">No transaction entries matches selected ledger parameters.</td></tr>`;
        return;
      }

      body.innerHTML = itemsToRender.map(h => `
        <tr>
          <td>TX-${h.txId}</td>
          <td>${h.productName}</td>
          <td>${h.user}</td>
          <td style="color:rgba(245,238,216,0.6)">${h.supplier}</td>
          <td><span class="${h.type === 'IN' ? 'tx-in' : 'tx-out'}">${h.type === 'IN' ? '📈 INBOUND' : '📉 OUTBOUND'}</span></td>
          <td><b>${h.quantity}</b></td>
          <td style="color:rgba(245,238,216,0.4); font-size:11px;">${h.date}</td>
        </tr>
      `).join('');
    }

    /* ─── State Management Mutation Nodes ─── */
    function toggleProductAvailability(id, isChecked) {
      const prod = tableProducts.find(p => p.id === id);
      if(prod) {
        prod.isActive = isChecked;
        refreshInterfaceViews();
      }
    }

    function toggleBrandLifecycle(id, isChecked) {
      const brand = tableBrands.find(b => b.id === id);
      if(brand) {
        brand.isActive = isChecked;
        // Cascading control rules: If a brand is marked inactive, immediately turn down all products cataloged under it
        if(!isChecked) {
          tableProducts.forEach(p => {
            if(p.brand === brand.name) p.isActive = false;
          });
        }
        refreshInterfaceViews();
      }
    }

    /* Feature 4: Safe Explicit Deletion Operations Cascade Routines */
    function deleteProductRecord(id) {
      if(confirm("CRITICAL WARNING:\n\nYou are about to permanently clear this product spec file from memory. This change cannot be undone. Proceed?")) {
        tableProducts = tableProducts.filter(p => p.id !== id);
        refreshInterfaceViews();
      }
    }

    function deleteBrandRecord(id) {
      const brand = tableBrands.find(b => b.id === id);
      if(!brand) return;

      const dependencyCheck = tableProducts.some(p => p.brand === brand.name);
      if(dependencyCheck) {
        alert("Operation Blocked: Cannot delete brand entry while active catalog product specifications depend on its string namespace definition.");
        return;
      }

      if(confirm(`Confirm deletion of manufacturer parameter: ${brand.name}?`)) {
        tableBrands = tableBrands.filter(b => b.id !== id);
        refreshInterfaceViews();
      }
    }

    function deleteSupplierRecord(id) {
      if(confirm("Confirm removal of this authorized supplier profile? This action breaks history lookups referencing this entity.")) {
        tableSuppliers = tableSuppliers.filter(s => s.id !== id);
        refreshInterfaceViews();
      }
    }

    /* ─── Data Entry Submission Pipeline Nodes ─── */
    function createNewProduct(e) {
      e.preventDefault();
      tableProducts.push({
        id: tableProducts.length ? Math.max(...tableProducts.map(p => p.id)) + 1 : 101,
        name: document.getElementById('p-name').value,
        category: document.getElementById('p-cat').value,
        brand: document.getElementById('p-brand-select').value,
        price: parseFloat(document.getElementById('p-price').value),
        qty: parseInt(document.getElementById('p-qty').value),
        location: document.getElementById('p-loc').value,
        isActive: true
      });
      e.target.reset();
      toggleModal('productModal', false);
      refreshInterfaceViews();
    }

    function createNewBrand(e) {
      e.preventDefault();
      tableBrands.push({
        id: tableBrands.length ? Math.max(...tableBrands.map(b => b.id)) + 1 : 301,
        name: document.getElementById('b-name').value,
        type: document.getElementById('b-type').value,
        isActive: true
      });
      e.target.reset();
      toggleModal('brandModal', false);
      refreshInterfaceViews();
    }

    function createNewSupplier(e) {
      e.preventDefault();
      tableSuppliers.push({
        id: tableSuppliers.length ? Math.max(...tableSuppliers.map(s => s.id)) + 1 : 501,
        name: document.getElementById('s-name').value,
        phone: document.getElementById('s-phone').value,
        email: document.getElementById('s-email').value
      });
      e.target.reset();
      toggleModal('supplierModal', false);
      refreshInterfaceViews();
    }

    function executeAdjustment(e) {
      e.preventDefault();
      const pId = parseInt(document.getElementById('adj-product').value);
      const sId = parseInt(document.getElementById('adj-supplier').value);
      const type = document.getElementById('adj-type').value;
      const val = parseInt(document.getElementById('adj-qty').value);

      const prod = tableProducts.find(p => p.id === pId);
      const vendor = tableSuppliers.find(s => s.id === sId);

      if(type === "OUT" && prod.qty < val) {
        alert("Operation Aborted: Insufficient stock assets available to perform specified balance disbursal.");
        return;
      }

      prod.qty += (type === "IN" ? val : -val);

      tableHistory.unshift({
        txId: tableHistory.length ? Math.max(...tableHistory.map(h => h.txId)) + 1 : 9001,
        productName: prod.name,
        user: "Admin (Karlo)",
        supplier: vendor ? vendor.name : "N/A (Retail Terminal Outbound Sale)",
        type: type,
        quantity: val,
        date: new Date().toISOString().replace('T', ' ').substring(0, 16)
      });

      e.target.reset();
      toggleModal('adjustmentModal', false);
      refreshInterfaceViews();
    }

    function executeGlobalFilters() {
      const search = document.getElementById('globalSearch').value.toLowerCase();
      const category = document.getElementById('categorySelect').value;

      const filtered = tableProducts.filter(p => {
        const textMatch = p.name.toLowerCase().includes(search) || p.brand.toLowerCase().includes(search);
        const catMatch = category === "ALL" || p.category === category;
        return textMatch && catMatch;
      });

      renderProductsRegistry(filtered);
    }
  </script>
</body>
</html>