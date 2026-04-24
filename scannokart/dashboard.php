<?php
// Start the session to check if the user is logged in
session_start();
header('Content-Type: text/html; charset=utf-8');
// If the session variable 'user_id' is not set, they aren't logged in. Redirect to login.
if (!isset($_SESSION['user_id'])){
        echo "<pre>";
print_r($_SESSION);
echo "</pre>";


    //header("Location: index.html");
    exit();
}

// Get the user's name from the session
$userName = $_SESSION['user_name'];

// Calculate initials for the avatar (e.g., "John Doe" -> "JD")
$nameParts = explode(" ", $userName);
$initials = "";
foreach ($nameParts as $part) {
    $initials .= strtoupper($part[0]);
}
$initials = substr($initials, 0, 2);
/*Dashboard page setup: Enconding, viewport, title and stylesheets*/
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
    body.dashboard-page {
      background-color: #f4f7f6;
    }
    .container {
      max-width: 800px;
      margin: 40px auto;
      padding: 20px;
      text-align: center;
    }
    .page-header {
      text-align: left;
      margin-bottom: 20px;
    }
    .page-header h1 {
      font-size: 28px;
      color: #333;
      font-weight: 700;
    }
    .predict-container {
      display: flex;
      justify-content: flex-end;
      margin-top: 20px;
      margin-bottom: 20px;
    }
    .predict-btn {
      background-color: #2bb39a;
      color: white;
      border: none;
      padding: 10px 18px;
      border-radius: 8px;
      font-weight: 600;
      cursor: pointer;
      display: flex;
      align-items: center;
      gap: 6px;
      font-size: 14px;
    }
    .empty-state {
      margin-top: 60px;
    }
    .empty-icon {
      font-size: 30px;
      margin-bottom: 10px;
    }
    .empty-state h2 {
      font-size: 22px;
      color: #333;
      margin-bottom: 5px;
    }
    .empty-state p {
      color: #777;
      font-size: 14px;
    }
    .link-reset {
      color: #2bb39a;
      text-decoration: none;
      font-weight: 600;
      font-size: 14px;
    }
  </style>
</head>

<body class="dashboard-page">
//Navigation Bar
  <header class="navbar">
    <div class="nav-left">
//Website logo and app name
      <div class="logo-circle">SK</div>
      <span class="logo-text">ScannoKart</span>
    </div>
//Right side: Logged in user info
<div class="nav-right" style="display: flex; align-items: center; gap: 15px;">
        <span class="user-name" id="userName">
                <?php echo $userName; ?>
        </span>
//Avatar Circle showing user initials
        <div class="user-avatar" id="userAvatar">
                <?php echo $initials; ?>
        </div>
</div>
</header>
  <main class="container">
    <div class="page-header">
      <h1>Your Groceries</h1>
    </div>
/* Pantry items display / empty state fallback*/
    <div id="pantryDisplay">
      <div id="emptyState" class="empty-state">
        <div class="empty-icon">📦</div>
        <h2>Your pantry is empty</h2>
        <p>Items added here are saved to your browser cookies.</p>
        <a href="#" class="link-reset" onclick="resetPantry()">Clear All Data</a>
      </div>
//AI expiration predicition trigger
      <div class="predict-container">
        <button id="predictBtn" class="predict-btn" onclick="updatePantryExpirations()">
          ✨ Predict Expirations
        </button>
      </div>
//Experation report forecast results
<div id="expiration-report" style="display:none; background: #e0f2f1; padding: 20px; border-radius: 12px; margin-bottom: 30px; text-align: left; border: 1px solid #2bb39a;">
  <h3 style="margin-top:0; color: #00897b;">AI Expiration Forecast</h3>
  <ul id="expiration-list" style="list-style: none; padding: 0; margin: 0;"></ul>
</div>
      <ul id="pantryList"></ul>
    </div>
  </main>
//Section for Manual Input of items
  <div class="fab-container">
    <div class="fab-menu" id="fabMenu">
      <button class="menu-item" onclick="openManualInput()">
        <span>Manual Input</span>
        <span class="icon">✍️</span>
      </button>
//Section for uploading image
      <button class="menu-item" id="uploadImageBtn" onclick="openPhotoModal()">
        <span>Upload Image</span>
        <span class="icon">📷</span>
      </button>
    </div>



//Manual input design
    <input type="file" id="hiddenFileInput" accept="image/*" style="display: none;">
    <button class="fab-add" id="fabAdd">+</button>
  </div>
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
        <button class="btn-save" onclick="saveManualInput()">Add to List</button>
      </div>
    </div>
  </div>


//Upload input design
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
      </
div>
    </div>
  </div>

  <script>
    const fabAdd = document.getElementById('fabAdd');
    const fabMenu = document.getElementById('fabMenu');
    const pantryList = document.getElementById('pantryList');
    const emptyState = document.getElementById('emptyState');
    const hiddenFileInput = document.getElementById('hiddenFileInput');

    // --- FAB Logic ---
    fabAdd.addEventListener('click', (e) => {
      e.stopPropagation();
      fabMenu.classList.toggle('show');
    });
    document.addEventListener('click', () => { fabMenu.classList.remove('show'); });

    // --- Photo Modal Logic ---
    function openPhotoModal() {
      fabMenu.classList.remove('show');
      document.getElementById('photoModal').style.display = 'flex';
    }
    function closePhotoModal() { document.getElementById('photoModal').style.display = 'none'; }
    function triggerFileUpload() { closePhotoModal(); hiddenFileInput.click(); }

    // --- Pantry Functions ---
    let myItems = getPantryCookie();
    window.onload = renderPantry;

    function setPantryCookie(data) {
      const d = new Date();
      d.setTime(d.getTime() + (30 * 24 * 60 * 60 * 1000));
      document.cookie = "sk_pantry_data=" + JSON.stringify(data) + ";expires=" + d.toUTCString() + ";path=/";
    }

    function getPantryCookie() {
      let name = "sk_pantry_data=";
      let ca = decodeURIComponent(document.cookie).split(';');
      for(let i = 0; i < ca.length; i++) {
        let c = ca[i].trim();
        if (c.indexOf(name) == 0) return JSON.parse(c.substring(name.length, c.length));
      }
      return [];
    }

    function renderPantry() {
      pantryList.innerHTML = '';
      if (myItems.length === 0) {
        emptyState.style.display = 'block';
      } else {
        emptyState.style.display = 'none';
        myItems.forEach((item, index) => {
          const li = document.createElement('li');
          li.className = 'pantry-item';
          li.innerHTML = `
            <div class="pantry-item-info">
              <span class="pantry-item-name">${item.name}</span><br>
              <span class="pantry-item-details">Qty: ${item.qty} ${item.type || ''}</span>
            </div>
            <button class="btn-delete" onclick="deleteItem(${index})">🗑️</button>
          `;
          pantryList.appendChild(li);
        });
      }
    }

    function openManualInput() {
        document.getElementById('manualModal').style.display = 'flex';
        fabMenu.classList.remove('show');
    }
    function closeManualInput() { document.getElementById('manualModal').style.display = 'none'; }

    function saveManualInput() {
      const name = document.getElementById('foodItem').value;
      const qty = document.getElementById('foodAmount').value;
      const type = document.getElementById('foodType').value;
      if (name && qty) {
        myItems.push({ name, qty, type });
        setPantryCookie(myItems);
        renderPantry();
        closeManualInput();
        document.getElementById('foodItem').value = '';
        document.getElementById('foodAmount').value = '';
        document.getElementById('foodType').value = '';
      }
    }

    function deleteItem(index) {
      myItems.splice(index, 1);
      setPantryCookie(myItems);
      renderPantry();
    }

    function resetPantry() {
      myItems = [];
      setPantryCookie(myItems);
      renderPantry();
    }

    // -- Prediction Logic --
const API_URL = "api/get-expiration";
async function updatePantryExpirations() {
  const listElement = document.getElementById('expiration-list');
  const reportContainer = document.getElementById('expiration-report');
  const predictBtn = document.getElementById('predictBtn');

  reportContainer.style.display = 'block';
  listElement.innerHTML = '<li>🪄 ScannoKart AI is calculating...</li>';
  predictBtn.disabled = true;

  try {
    const itemsFromCookie = getPantryCookie();
    if (itemsFromCookie.length === 0) {
      listElement.innerHTML = '<li>Your pantry is empty! Add items first.</li>';
      predictBtn.disabled = false;
      return;
    }

    // Change 1: Ensure this fetch is unique
    const predictionResponse = await fetch(API_URL, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ items: itemsFromCookie })
    });

    if (!predictionResponse.ok) throw new Error("Server error");
    const predictions = await predictionResponse.json();

    listElement.innerHTML = '';
    predictions.forEach(item => {
      const li = document.createElement('li');
      li.style.padding = "8px 0";
      li.style.borderBottom = "1px solid #cde8e3";
      const isUrgent = item.daysToExpiration <= 3;
      const color = isUrgent ? "#ef4444" : "#444";
      const icon = isUrgent ? "⚠️" : "🗓️";
      li.innerHTML = `<span style="color: ${color}; font-weight: ${isUrgent ? 'bold' : 'normal'}">${icon} ${item.name}: ~${item.daysToExpiration} days remaining</span>`;
      listElement.appendChild(li);
    });
  } catch (error) {
    listElement.innerHTML = '<li style="color: red;">Could not connect to the AI server.</li>';
  } finally {
    predictBtn.disabled = false;
  }
}
// --- Auth Check & Header Population ---
const CURRENT_KEY = "sk_current_user";
const user = JSON.parse(localStorage.getItem(CURRENT_KEY));

if (false) {
  // If no session exists, send them back to login
  window.location.href = "index.html";
} else {
  // This takes the data from your login session and puts it in the header

// --- NEW UPLOAD LOGIC STARTS HERE ---
hiddenFileInput.addEventListener('change', async (event) => {
    const file = event.target.files[0];
    if (!file) return;

    const formData = new FormData();
    formData.append('file', file);

    try {
        // 1. Send image to PHP for OCR
        const uploadResponse = await fetch('upload.php', { method: 'POST', body: formData });
        const result = await uploadResponse.json();

        if (result.status === "success" && result.ocr_data) {
            // 2. Send OCR text to Gemini to "beautify" it
            const geminiResponse = await fetch('api/process-receipt', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ raw_text: result.ocr_data })
            });

            const cleanItems = await geminiResponse.json();

            // 3. Add the clean items to your pantry
            cleanItems.forEach(item => {
                myItems.push({
                    name: item.name,
                    qty: item.qty || 1,
                    type: item.type || "Grocery"
                });
            });

            setPantryCookie(myItems);
            renderPantry();
            alert("Items added successfully!");
        } else {
            alert("OCR failed: " + result.message);
        }
    } catch (error) {
        console.error("Workflow Error:", error);
    }
    hiddenFileInput.value = '';
});
}
  </script>
</body>
</html>
