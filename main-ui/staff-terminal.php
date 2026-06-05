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

    /* ─── Ambient Wood Grain Backing ─── */
    .bg {
      position: fixed;
      inset: 0;
      background:
        repeating-linear-gradient(
          92deg,
          transparent 0px, transparent 18px,
          rgba(90,40,10,0.05) 18px, rgba(90,40,10,0.05) 19px
        ),
        repeating-linear-gradient(
          88deg,
          transparent 0px, transparent 38px,
          rgba(60,20,5,0.04) 38px, rgba(60,20,5,0.04) 39px
        ),
        linear-gradient(170deg, #1a0c06 0%, #2e130a 40%, #1d0e07 70%, #120804 100%);
      z-index: -2;
    }

    /* ─── Ambient Fret Strings ─── */
    .bg-strings {
      position: fixed;
      inset: 0;
      z-index: -1;
      pointer-events: none;
      opacity: 0.4;
    }
    .bg-string {
      position: absolute;
      left: 0; right: 0; height: 1px;
      background: linear-gradient(90deg, transparent 0%, var(--string) 15%, var(--string) 85%, transparent 100%);
    }
    .bg-string:nth-child(1) { top: 20%; }
    .bg-string:nth-child(2) { top: 50%; height: 1.5px; }
    .bg-string:nth-child(3) { top: 80%; height: 2px; }

    /* ─── Restricted Top Bar Navigation ─── */
    .top-bar {
      background: rgba(26, 12, 6, 0.95);
      border-bottom: 1px solid rgba(228,169,75,0.2);
      padding: 15px 30px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      position: sticky;
      top: 0;
      z-index: 50;
      backdrop-filter: blur(8px);
    }

    .logo-row {
      display: flex;
      align-items: center;
      gap: 12px;
    }
    .logo-icon { width: 34px; height: 34px; }
    .brand { display: flex; flex-direction: column; }
    .brand-name {
      font-family: 'Playfair Display', serif;
      font-size: 20px;
      font-weight: 700;
      color: var(--gold);
      line-height: 1;
    }
    .brand-sub {
      font-size: 8px;
      color: rgba(228,169,75,0.45);
      letter-spacing: 0.2em;
      text-transform: uppercase;
      margin-top: 3px;
    }

    .user-profile {
      display: flex;
      align-items: center;
      gap: 10px;
      font-size: 12px;
    }
    .role-badge {
      background: rgba(255, 255, 255, 0.05);
      border: 1px solid rgba(255, 255, 255, 0.2);
      color: var(--parchment);
      padding: 3px 8px;
      border-radius: 3px;
      font-size: 10px;
      text-transform: uppercase;
      letter-spacing: 0.05em;
    }

    /* ─── Grid Workspace Core ─── */
    .workspace {
      flex: 1;
      display: grid;
      grid-template-columns: 280px 1fr;
      min-height: calc(100vh - 65px);
    }

    /* ─── Sidebar Filtering Nodes ─── */
    .sidebar {
      background: rgba(28, 12, 5, 0.6);
      border-right: 1px solid rgba(228,169,75,0.15);
      padding: 30px 20px;
      display: flex;
      flex-direction: column;
      gap: 25px;
    }

    .sidebar-section-title {
      font-size: 9px;
      letter-spacing: 0.2em;
      text-transform: uppercase;
      color: rgba(228,169,75,0.55);
      margin-bottom: 10px;
      display: block;
    }

    .search-wrap { position: relative; }
    .search-wrap svg {
      position: absolute;
      left: 12px; top: 50%;
      transform: translateY(-50%);
      width: 14px; height: 14px;
      color: rgba(228,169,75,0.35);
    }
    .search-input {
      width: 100%;
      padding: 10px 12px 10px 36px;
      background: rgba(255,255,255,0.03);
      border: 1px solid rgba(228,169,75,0.15);
      border-radius: 3px;
      color: var(--cream);
      font-family: inherit;
      font-size: 12px;
      outline: none;
    }
    .search-input:focus {
      border-color: rgba(228,169,75,0.4);
      background: rgba(255,255,255,0.06);
    }

    .filter-select {
      width: 100%;
      padding: 10px;
      background: #2a1209;
      border: 1px solid rgba(228,169,75,0.18);
      border-radius: 3px;
      color: var(--cream);
      font-family: inherit;
      font-size: 12px;
      outline: none;
      cursor: pointer;
    }

    /* ─── Canvas Subsystems ─── */
    .content-canvas {
      padding: 30px;
      display: flex;
      flex-direction: column;
      gap: 30px;
      overflow-y: auto;
    }

    /* Floor Staff Informational Metrics */
    .metrics-row {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
      gap: 20px;
    }
    .metric-card {
      background: var(--dark-card);
      border: 1px solid rgba(228,169,75,0.12);
      border-radius: 4px;
      padding: 20px;
      box-shadow: 0 10px 30px rgba(0,0,0,0.4);
    }
    .metric-label {
      font-size: 9px;
      text-transform: uppercase;
      letter-spacing: 0.15em;
      color: rgba(228,169,75,0.45);
    }
    .metric-val {
      font-size: 24px;
      font-weight: 700;
      color: var(--gold);
      margin-top: 5px;
    }

    /* Clean Staff Action Headers */
    .action-row {
      display: flex;
      justify-content: space-between;
      align-items: center;
    }
    .panel-title {
      font-family: 'Playfair Display', serif;
      font-size: 22px;
      color: var(--parchment);
    }
    .btn {
      padding: 12px 24px;
      background: linear-gradient(135deg, var(--amber), var(--rosewood) 80%);
      border: none;
      border-radius: 3px;
      color: var(--cream);
      font-family: inherit;
      font-size: 11px;
      letter-spacing: 0.1em;
      text-transform: uppercase;
      cursor: pointer;
      transition: all 0.2s;
      box-shadow: 0 4px 15px rgba(200,129,42,0.2);
    }
    .btn:hover { transform: translateY(-1px); opacity: 0.95; }

    /* Main Inquiry Stock Matrix Sheet */
    .table-container {
      background: var(--dark-card);
      border: 1px solid rgba(228,169,75,0.2);
      border-radius: 4px;
      overflow: hidden;
      box-shadow: 0 20px 50px rgba(0,0,0,0.5);
    }
    table {
      width: 100%;
      border-collapse: collapse;
      text-align: left;
      font-size: 12px;
    }
    th {
      background: rgba(59, 26, 14, 0.6);
      padding: 14px 16px;
      font-size: 9px;
      letter-spacing: 0.15em;
      text-transform: uppercase;
      color: rgba(228,169,75,0.6);
      border-bottom: 1px solid rgba(228,169,75,0.2);
    }
    td {
      padding: 14px 16px;
      border-bottom: 1px solid rgba(228,169,75,0.08);
      color: var(--parchment);
    }
    tr:hover td {
      background: rgba(228,169,75,0.03);
    }

    .badge-status {
      padding: 2px 6px;
      border-radius: 2px;
      font-size: 10px;
      text-transform: uppercase;
    }
    .status-instock { background: rgba(16, 185, 129, 0.15); color: var(--success); }
    .status-low { background: rgba(239, 68, 68, 0.15); color: var(--danger); }
    .status-out { background: rgba(239, 68, 68, 0.35); color: #ffffff; font-weight: bold;}

    /* ─── Modular Sales Interface Overlay ─── */
    .modal-overlay {
      position: fixed;
      inset: 0;
      background: rgba(10, 5, 3, 0.85);
      backdrop-filter: blur(4px);
      z-index: 100;
      display: flex;
      align-items: center;
      justify-content: center;
      opacity: 0;
      pointer-events: none;
      transition: opacity 0.3s ease;
    }
    .modal-overlay.active {
      opacity: 1;
      pointer-events: auto;
    }
    .modal-card {
      width: 440px;
      background: linear-gradient(145deg, rgba(42,18,9,0.99), rgba(28,12,5,0.99));
      border: 1px solid rgba(228,169,75,0.35);
      border-radius: 4px;
      padding: 40px;
      box-shadow: 0 30px 70px rgba(0,0,0,0.8);
      transform: translateY(15px);
      transition: transform 0.3s ease;
    }
    .modal-overlay.active .modal-card { transform: translateY(0); }
    .modal-header {
      font-family: 'Playfair Display', serif;
      font-size: 22px;
      color: var(--gold);
      margin-bottom: 25px;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }
    .modal-close {
      background: none; border: none; color: rgba(228,169,75,0.5);
      font-size: 20px; cursor: pointer;
    }

    .form-field { margin-bottom: 18px; }
    .form-field label {
      display: block; font-size: 9px; letter-spacing: 0.15em;
      text-transform: uppercase; color: rgba(228,169,75,0.55);
      margin-bottom: 6px;
    }
    .form-field input, .form-field select {
      width: 100%; padding: 11px;
      background: rgba(255,255,255,0.04);
      border: 1px solid rgba(228,169,75,0.18);
      border-radius: 3px; color: var(--cream);
      font-family: inherit; font-size: 13px; outline: none;
    }
    .form-field input:focus, .form-field select:focus {
      border-color: var(--gold); background: rgba(255,255,255,0.07);
    }
  </style>
</head>
<body>

  <div class="bg"></div>
  <div class="bg-strings">
    <div class="bg-string"></div>
    <div class="bg-string"></div>
    <div class="bg-string"></div>
  </div>

  <header class="top-bar">
    <?php
      // You can now write queries anywhere inside the HTML body!
      // Example: Testing if the connection works
      if($conn) {
          echo "<p style='color: green;'>Successfully connected to the database!</p>";
      }
    ?>
    <div class="logo-row">
      <svg class="logo-icon" viewBox="0 0 44 44" fill="none" xmlns="http://www.w3.org/2000/svg">
        <rect x="18" y="2" width="8" height="28" rx="4" fill="#6b2d1a" stroke="#c8812a" stroke-width="1"/>
        <rect x="20" y="26" width="4" height="16" rx="2" fill="#3b1a0e" stroke="#c8812a" stroke-width="0.8"/>
        <circle cx="22" cy="40" r="3" fill="#c8812a" opacity="0.8"/>
        <rect x="13" y="6" width="18" height="3" rx="1.5" fill="#e4a94b" opacity="0.7"/>
        <circle cx="12" cy="10" r="3" fill="#c8812a" stroke="#e4a94b" stroke-width="0.8"/>
        <circle cx="32" cy="10" r="3" fill="#c8812a" stroke="#e4a94b" stroke-width="0.8"/>
      </svg>
      <div class="brand">
        <span class="brand-name">Fretboard</span>
        <span class="brand-sub">Staff Terminal Module</span>
      </div>
    </div>
    <div class="user-profile">
      <span>Gian Rizen A. Lacao</span>
      <span class="role-badge">Floor Staff</span>
    </div>
  </header>

  <div class="workspace">
    
    <aside class="sidebar">
      <div>
        <span class="sidebar-section-title">Availability Inquiry</span>
        <div class="search-wrap">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
          <input type="text" id="searchBar" class="search-input" placeholder="Type model or type..." oninput="filterRegistry()">
        </div>
      </div>

      <div>
        <span class="sidebar-section-title">Category Scope</span>
        <select id="categoryFilter" class="filter-select" onchange="filterRegistry()">
          <option value="ALL">All Categories</option>
          <option value="Guitars">Guitars</option>
          <option value="Amplifiers">Amplifiers</option>
          <option value="Pedals">Pedals & FX</option>
          <option value="Accessories">Accessories</option>
        </select>
      </div>

      <div>
        <span class="sidebar-section-title">Brand Scope</span>
        <select id="brandFilter" class="filter-select" onchange="filterRegistry()">
          <option value="ALL">All Brands</option>
          <option value="Fender">Fender</option>
          <option value="Gibson">Gibson</option>
          <option value="Ibanez">Ibanez</option>
          <option value="Boss">Boss</option>
        </select>
      </div>
    </aside>

    <main class="content-canvas">
      
      <section class="metrics-row">
        <div class="metric-card">
          <div class="metric-label">Active Active Stock Count</div>
          <div class="metric-val" id="metricFloorTotal">0 Units</div>
        </div>
        <div class="metric-card">
          <div class="metric-label">Critical Shortages (&lt; 5)</div>
          <div class="metric-val" id="metricFloorLow" style="color: var(--danger);">0 Items</div>
        </div>
      </section>

      <section class="action-row">
        <h2 class="panel-title">Live Inventory Lookup Grid</h2>
        <button class="btn" onclick="toggleModal('saleModal', true)">🛒 Record Customer Sale</button>
      </section>

      <section class="table-container">
        <table>
          <thead>
            <tr>
              <th>SKU ID</th>
              <th>Description / Model</th>
              <th>Category</th>
              <th>Brand</th>
              <th>Retail Unit Price</th>
              <th style="text-align: center;">Stock Available</th>
              <th>Floor Location</th>
              <th>Status Badge</th>
            </tr>
          </thead>
          <tbody id="staffTableBody">
            </tbody>
        </table>
      </section>
    </main>
  </div>

  <div id="saleModal" class="modal-overlay">
    <div class="modal-card">
      <div class="modal-header">
        <h3>Customer Receipt Checkout</h3>
        <button class="modal-close" onclick="toggleModal('saleModal', false)">&times;</button>
      </div>
      <form onsubmit="handleStaffSale(event)">
        <div class="form-field">
          <label>Select Item Redeemed</label>
          <select id="saleItemTarget">
            </select>
        </div>
        <div class="form-field">
          <label>Quantity Disbursed</label>
          <input type="number" id="saleQty" min="1" value="1" required />
        </div>
        <button type="submit" class="btn" style="width: 100%; margin-top: 15px;">Deduct & Print Receipt</button>
      </form>
    </div>
  </div>

  <script>
    // Shared structural dataset representation
    let dbProducts = [
      { id: 101, name: "Affinity Series Stratocaster", category: "Guitars", brand: "Fender", price: 18500.00, qty: 14, location: "Rack A-1" },
      { id: 102, name: "Les Paul Standard '60s", category: "Guitars", brand: "Gibson", price: 145000.00, qty: 2, location: "Vault Display" },
      { id: 103, name: "Katana 50 MkII Amplifier", category: "Amplifiers", brand: "Boss", price: 16500.00, qty: 22, location: "Row B-2" },
      { id: 104, name: "DS-1 Distortion Pedal", category: "Pedals", brand: "Boss", price: 4200.00, qty: 0, location: "Counter Glass" },
      { id: 105, name: "RG421 Electric Guitar", category: "Guitars", brand: "Ibanez", price: 21000.00, qty: 5, location: "Rack A-3" }
    ];

    document.addEventListener("DOMContentLoaded", () => {
      renderStaffRegistry();
    });

    function toggleModal(modalId, visible) {
      const modal = document.getElementById(modalId);
      if (visible) {
        modal.classList.add('active');
        populateSaleDropdown();
      } else {
        modal.classList.remove('active');
      }
    }

    function populateSaleDropdown() {
      const select = document.getElementById("saleItemTarget");
      // Only display items that actually have physical units available for selection
      select.innerHTML = dbProducts
        .filter(p => p.qty > 0)
        .map(p => `<option value="${p.id}">${p.brand} — ${p.name} (₱${p.price.toLocaleString()} | ${p.qty} available)</option>`)
        .join("");
    }

    function renderStaffRegistry(filtered = dbProducts) {
      const tbody = document.getElementById("staffTableBody");
      
      if(filtered.length === 0) {
        tbody.innerHTML = `<tr><td colspan="8" style="text-align: center; color: rgba(228,169,75,0.4);">No items match active structural filters.</td></tr>`;
        return;
      }

      tbody.innerHTML = filtered.map(p => {
        let structuralBadge = "status-instock";
        let statusLabel = "Available";

        if (p.qty === 0) {
          structuralBadge = "status-out";
          statusLabel = "Out of Stock";
        } else if (p.qty <= 5) {
          structuralBadge = "status-low";
          statusLabel = "Critical Low";
        }

        return `
          <tr>
            <td style="color: var(--gold); font-weight: 500;">P-${p.id}</td>
            <td style="font-family: system-ui, sans-serif; font-weight: 500; font-size:13px;">${p.name}</td>
            <td>${p.category}</td>
            <td>${p.brand}</td>
            <td style="font-weight: 500;">₱${p.price.toLocaleString(undefined, {minimumFractionDigits: 2})}</td>
            <td style="font-weight: 500; text-align: center;">${p.qty}</td>
            <td style="color: rgba(245,238,216,0.6);">${p.location}</td>
            <td><span class="badge-status ${structuralBadge}">${statusLabel}</span></td>
          </tr>
        `;
      }).join("");

      // Update aggregate informational data indicators
      document.getElementById("metricFloorTotal").textContent = `${dbProducts.reduce((acc, curr) => acc + curr.qty, 0)} Units`;
      document.getElementById("metricFloorLow").textContent = `${dbProducts.filter(i => i.qty <= 5 && i.qty > 0).length} Items`;
    }

    function filterRegistry() {
      const searchVal = document.getElementById("searchBar").value.toLowerCase();
      const categoryVal = document.getElementById("categoryFilter").value;
      const brandVal = document.getElementById("brandFilter").value;

      const records = dbProducts.filter(item => {
        const matchesSearch = item.name.toLowerCase().includes(searchVal);
        const matchesCategory = (categoryVal === "ALL" || item.category === categoryVal);
        const matchesBrand = (brandVal === "ALL" || item.brand === brandVal);
        return matchesSearch && matchesCategory && matchesBrand;
      });

      renderStaffRegistry(records);
    }

    // Process Transaction Safe Point Deductions
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
        renderStaffRegistry();
      }
    }
  </script>
</body>
</html>