<?php
session_start();
header('Content-Type: text/html; charset=utf-8');
if (!isset($_SESSION['user_id'])) {
    header("Location: index.html");
    exit();
}
$userName  = $_SESSION['user_name'];
$nameParts = explode(" ", $userName);
$initials  = "";
foreach ($nameParts as $part) {
    $initials .= strtoupper($part[0]);
}
$initials = substr($initials, 0, 2);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>ScannoKart Dashboard</title>
  <link rel="stylesheet" href="style.css">
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
  <style>
    body.dashboard-page { background-color: #f4f7f6; }
    .container { max-width: 800px; margin: 40px auto; padding: 20px; text-align: center; }
    .page-header { text-align: left; margin-bottom: 20px; }
    .page-header h1 { font-size: 28px; color: #333; font-weight: 700; }
    .predict-container { display: flex; justify-content: flex-end; margin-top: 20px; margin-bottom: 20px; }
    .predict-btn { background-color: #2bb39a; color: white; border: none; padding: 10px 18px; border-radius: 8px; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 6px; font-size: 14px; }
    .empty-state { margin-top: 60px; }
    .empty-icon { font-size: 30px; margin-bottom: 10px; }
    .empty-state h2 { font-size: 22px; color: #333; margin-bottom: 5px; }
    .empty-state p { color: #777; font-size: 14px; }
    .link-reset { color: #2bb39a; text-decoration: none; font-weight: 600; font-size: 14px; }
    .loading-spinner { text-align: center; padding: 40px; color: #777; font-size: 14px; }
  </style>
</head>
<body class="dashboard-page">
  <header class="navbar">
    <div class="nav-left">
      <div class="logo-circle">SK</div>
      <span class="logo-text">ScannoKart</span>
    </div>
    <div class="nav-right" style="display: flex; align-items: center; gap: 15px;">
      <span class="user-name" id="userName"><?php echo htmlspecialchars($userName); ?></span>
      <div class="user-avatar" id="userAvatar"><?php echo htmlspecialchars($initials); ?></div>
    </div>
  </header>

  <main class="container">
    <div class="page-header"><h1>Your Groceries</h1></div>
    <div id="pantryDisplay">
      <div id="loadingState" class="loading-spinner">Loading your pantry…</div>
      <div id="emptyState" class="empty-state" style="display: none;">
        <div class="empty-icon">📦</div>
        <h2>Your pantry is empty</h2>
        <p>Add items using the button below — they're saved to your account.</p>
        <a href="#" class="link-reset" onclick="clearAllItems()">Clear All Items</a>
      </div>
      <div class="predict-container">
        <button id="predictBtn" class="predict-btn" onclick="updatePantryExpirations()">
          ✨ Predict Expirations
        </button>
      </div>
      <div id="expiration-report" style="display:none; background: #e0f2f1; padding: 20px; border-radius: 12px; margin-bottom: 30px; text-align: left; border: 1px solid #2bb39a;">
        <h3 style="margin-top:0; color: #00897b;">AI Expiration Forecast</h3>
        <ul id="expiration-list" style="list-style: none; padding: 0; margin: 0;"></ul>
      </div>
      <ul id="pantryList"></ul>
    </div>
  </main>

  <!-- FAB -->
  <div class="fab-container">
    <div class="fab-menu" id="fabMenu">
      <button class="menu-item" onclick="openManualInput()">
        <span>Manual Input</span><span class="icon">✍️</span>
      </button>
      <button class="menu-item" id="uploadImageBtn" onclick="openPhotoModal()">
        <span>Upload Image</span><span class="icon">📷</span>
      </button>
    </div>
    <input type="file" id="hiddenFileInput" accept="image/*" style="display: none;">
    <button class="fab-add" id="fabAdd">+</button>
  </div>

  <!-- Manual Add Modal -->
  <div id="manualModal" class="modal-overlay">
    <div class="modal-content">
      <h3>Add New Item</h3>
      <div class="form-group">
        <label>Food Item</label>
        <input type="text" id="foodItem" placeholder="e.g. Apples" required>
      </div>
      <div class="form-group">
        <label>Amount (Qty)</label>
        <input type="number" id="foodAmount" placeholder="e.g. 6" required>
      </div>
      <div class="form-group">
        <label>Unit/Type (Optional)</label>
        <input type="text" id="foodType" placeholder="e.g. Pack of 12">
      </div>
      <div class="modal-actions">
        <button class="btn-cancel" onclick="closeManualInput()">Cancel</button>
        <button class="btn-save" id="saveBtn" onclick="saveManualInput()">Add to List</button>
      </div>
    </div>
  </div>

  <!-- Photo Modal -->
  <div id="photoModal" class="modal-overlay" style="display: none;">
    <div class="modal-content">
      <div style="text-align: center; margin-bottom: 20px;">
        <span class="material-icons" style="font-size: 48px; color: #2bb39a;">add_a_photo</span>
        <h2 style="margin-top: 10px;">Photo Integration</h2>
        <p style="color: #666; font-size: 14px;">Select an option to add an item via photo.</p>
      </div>
      <div style="display: grid; grid-template-columns: 1fr; gap: 15px; margin-bottom: 20px; justify-items: center;">
        <button class="secondary-btn" onclick="triggerFileUpload()" style="flex-direction: column; height: auto; padding: 20px; background: #f9f9f9; display: flex; align-items: center; justify-content: center;">
          <span class="material-icons" style="font-size: 32px; margin-bottom: 10px; color: #2bb39a;">upload_file</span>
          <span>Upload Image</span>
        </button>
      </div>
      <div style="text-align: center;">
        <button class="btn-cancel" onclick="closePhotoModal()">Cancel</button>
      </div>
    </div>
  </div>

  <script>
    // ── DOM refs ───────────────────────────────────────────────────────────────
    const fabAdd        = document.getElementById('fabAdd');
    const fabMenu       = document.getElementById('fabMenu');
    const pantryList    = document.getElementById('pantryList');
    const emptyState    = document.getElementById('emptyState');
    const loadingState  = document.getElementById('loadingState');
    const hiddenFileInput = document.getElementById('hiddenFileInput');

    const API = 'pantry_api.php'; // same directory as dashboard.php

    // ── In-memory cache of items (mirrors the DB) ──────────────────────────────
    // Each item: { id, name, qty, type }
    let myItems = [];

    // ── FAB toggle ─────────────────────────────────────────────────────────────
    fabAdd.addEventListener('click', (e) => {
      e.stopPropagation();
      fabMenu.classList.toggle('show');
    });
    document.addEventListener('click', () => { fabMenu.classList.remove('show'); });

    // ── Photo modal ────────────────────────────────────────────────────────────
    function openPhotoModal()  { fabMenu.classList.remove('show'); document.getElementById('photoModal').style.display = 'flex'; }
    function closePhotoModal() { document.getElementById('photoModal').style.display = 'none'; }
    function triggerFileUpload() { closePhotoModal(); hiddenFileInput.click(); }

    // ── Render ─────────────────────────────────────────────────────────────────
    function renderPantry() {
      pantryList.innerHTML = '';
      if (myItems.length === 0) {
        emptyState.style.display = 'block';
      } else {
        emptyState.style.display = 'none';
        myItems.forEach((item) => {
          const li = document.createElement('li');
          li.className = 'pantry-item';
          li.innerHTML = `
            <div class="pantry-item-info">
              <span class="pantry-item-name">${escHtml(item.name)}</span><br>
              <span class="pantry-item-details">Qty: ${escHtml(String(item.qty))} ${escHtml(item.type || '')}</span>
            </div>
            <button class="btn-delete" onclick="deleteItem(${item.id})">🗑️</button>
          `;
          pantryList.appendChild(li);
        });
      }
    }

    function escHtml(str) {
      return str.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }

    // ── Load all items from DB on page load ────────────────────────────────────
    async function loadPantry() {
      try {
        const res = await fetch(API);
        if (!res.ok) throw new Error('Server error');
        myItems = await res.json();
      } catch (e) {
        console.error('Failed to load pantry:', e);
        myItems = [];
      } finally {
        loadingState.style.display = 'none';
        renderPantry();
      }
    }

    window.addEventListener('load', loadPantry);

    // ── Manual add modal ───────────────────────────────────────────────────────
    function openManualInput() {
      document.getElementById('manualModal').style.display = 'flex';
      fabMenu.classList.remove('show');
    }
    function closeManualInput() {
      document.getElementById('manualModal').style.display = 'none';
    }

    async function saveManualInput() {
      const name = document.getElementById('foodItem').value.trim();
      const qty  = parseFloat(document.getElementById('foodAmount').value) || 1;
      const type = document.getElementById('foodType').value.trim();

      if (!name) { alert('Please enter a food item name.'); return; }

      const saveBtn = document.getElementById('saveBtn');
      saveBtn.disabled = true;
      saveBtn.textContent = 'Saving…';

      try {
        const res  = await fetch(API, {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ name, qty, type })
        });
        if (!res.ok) throw new Error('Server error');
        const saved = await res.json();
        myItems.push(saved);
        renderPantry();
        closeManualInput();
        document.getElementById('foodItem').value  = '';
        document.getElementById('foodAmount').value = '';
        document.getElementById('foodType').value  = '';
      } catch (e) {
        alert('Could not save item. Please try again.');
        console.error(e);
      } finally {
        saveBtn.disabled = false;
        saveBtn.textContent = 'Add to List';
      }
    }

    // ── Delete a single item ───────────────────────────────────────────────────
    async function deleteItem(id) {
      try {
        const res = await fetch(`${API}?id=${id}`, { method: 'DELETE' });
        if (!res.ok) throw new Error('Server error');
        myItems = myItems.filter(item => item.id !== id);
        renderPantry();
      } catch (e) {
        alert('Could not delete item. Please try again.');
        console.error(e);
      }
    }

    // ── Clear all items for this user ──────────────────────────────────────────
    async function clearAllItems() {
      if (!confirm('Remove all items from your pantry?')) return;
      // Delete each item sequentially (avoids needing a separate bulk-delete endpoint)
      const ids = myItems.map(i => i.id);
      for (const id of ids) {
        await fetch(`${API}?id=${id}`, { method: 'DELETE' });
      }
      myItems = [];
      renderPantry();
    }

    // ── AI Expiration Forecast ─────────────────────────────────────────────────
    const EXPIRATION_API = "api/get-expiration";

    async function updatePantryExpirations() {
      const listEl        = document.getElementById('expiration-list');
      const reportContainer = document.getElementById('expiration-report');
      const predictBtn    = document.getElementById('predictBtn');

      reportContainer.style.display = 'block';
      listEl.innerHTML = '<li>🪄 ScannoKart AI is calculating…</li>';
      predictBtn.disabled = true;

      try {
        if (myItems.length === 0) {
          listEl.innerHTML = '<li>Your pantry is empty! Add items first.</li>';
          return;
        }
        const res = await fetch(EXPIRATION_API, {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ items: myItems })
        });
        if (!res.ok) throw new Error('Server error');
        const predictions = await res.json();

        listEl.innerHTML = '';
        predictions.forEach(item => {
          const li = document.createElement('li');
          li.style.padding = "8px 0";
          li.style.borderBottom = "1px solid #cde8e3";
          const isUrgent = item.daysToExpiration <= 3;
          const color    = isUrgent ? "#ef4444" : "#444";
          const icon     = isUrgent ? "⚠️" : "🗓️";
          li.innerHTML = `<span style="color:${color}; font-weight:${isUrgent ? 'bold' : 'normal'}">${icon} ${escHtml(item.name)}: ~${item.daysToExpiration} days remaining</span>`;
          listEl.appendChild(li);
        });
      } catch (e) {
        listEl.innerHTML = '<li style="color:red;">Could not connect to the AI server.</li>';
      } finally {
        predictBtn.disabled = false;
      }
    }

    // ── Image / OCR upload ─────────────────────────────────────────────────────
    hiddenFileInput.addEventListener('change', async (event) => {
      const file = event.target.files[0];
      if (!file) return;
	
	const cleanBlob = await new Promise((resolve) => {
    const reader = new FileReader();
    reader.readAsDataURL(file);
    reader.onload = (e) => {
      const img = new Image();
      img.src = e.target.result;
      img.onload = () => {
        const canvas = document.createElement('canvas');
        // Optional: Resize to a max width to speed up OCR
        const maxDim = 1200; 
        let width = img.width;
        let height = img.height;

        if (width > height && width > maxDim) {
          height *= maxDim / width;
          width = maxDim;
        } else if (height > maxDim) {
          width *= maxDim / height;
          height = maxDim;
        }

        canvas.width = width;
        canvas.height = height;
        const ctx = canvas.getContext('2d');
        ctx.drawImage(img, 0, 0, width, height);
        
        // Export as clean JPEG (strips all EXIF automatically)
        canvas.toBlob((blob) => resolve(blob), 'image/jpeg', 0.85);
      };
    };
  });
	
      const formData = new FormData();
      formData.append('file', cleanBlob, 'upload.jpg');

      try {
        const uploadRes = await fetch('upload.php', { method: 'POST', body: formData });
        const result    = await uploadRes.json();

        if (result.status === 'success' && result.ocr_data) {
          const geminiRes = await fetch('api/process-receipt', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ raw_text: result.ocr_data })
          });
          const cleanItems = await geminiRes.json();

          // Bulk-save to DB via POST (send array)
          const saveRes = await fetch(API, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(cleanItems.map(i => ({
              name: i.name,
              qty:  i.qty  || 1,
              type: i.type || 'Grocery'
            })))
          });
          if (!saveRes.ok) throw new Error('Save failed');
          const saved = await saveRes.json();

          // saved may be a single object or array depending on item count
          const savedArr = Array.isArray(saved) ? saved : [saved];
          myItems.push(...savedArr);
          renderPantry();
          alert('Items added successfully!');
        } else {
          alert('OCR failed: ' + result.message);
        }
      } catch (e) {
        console.error('Workflow Error:', e);
        alert('An error occurred while processing the image.');
      }

      hiddenFileInput.value = '';
    });
  </script>
</body>
</html>
