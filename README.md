# ScannoKart: AI-Powered Pantry & Inventory Management

ScannoKart is a smart inventory solution designed to bridge the gap between grocery shopping and food waste prevention. By leveraging **OpenOCR** for receipt digitization and **Google Gemini AI** for shelf-life prediction, ScannoKart helps users track their pantry in real-time.

---

## 🛠 Core Technologies
*   **OCR:** [OpenOCR](https://github.com/Topdu/OpenOCR) for high-accuracy text extraction.
*   **AI:** Google Gemini (Generative AI) for intelligent expiration estimation.
*   **Backend:** Node.js with Express, managed by **PM2**.
*   **Frontend:** Secure Dashboard with local browser-based authentication.
*   **Web Server:** Nginx 1.24.0.

---

## 🚀 Installation & Environment Setup

### 1. Repository & Dependencies
Clone the project and install the Node.js environment:
```sh
git clone https://github.com/it391team2/IT391.git
cd IT391
npm install @google/generative-ai express cors dotenv
```

### 2. OCR Engine Configuration
Install the OpenOCR engine by following the [Official Quick Start Guide](https://github.com/Topdu/OpenOCR/blob/main/docs/openocr.md#quick-start). This is required to process physical receipt images into text.

### 3. API Key Configuration
Create a `.env` file in the root directory:
```bash
GEMINI_API_KEY=<YOUR_GEMINI_KEY>
```

---

## 🖥 Deployment (Hosting with PM2)

We use **PM2** to ensure the estimation API runs as a background service and persists through system reboots.

```sh
# Install PM2 globally
sudo npm install -g pm2

# Start the AI Estimation service
pm2 start estimation.js --name scannokart-api

# Configure PM2 to start on boot
pm2 startup
pm2 save
```

---

## 🔄 The ScannoKart Workflow

### Step 1: Receipt Processing
Capture an image of your receipt and run the OCR task. The raw output is then passed through a cleaning script to format the data for the AI.
```sh
# 1. Extract text from image
openocr --task ocr --input_path ./receipt.jpg --is_vis

# 2. Clean and format the results
python3 clean.py system_results.txt
```

### Step 2: AI Shelf-Life Estimation
The cleaned data is sent to the Gemini-powered endpoint. The AI analyzes the items (e.g., "Apples") and returns a structured JSON response predicting how many days until expiration.
```sh
# Test the endpoint via CURL
curl -X POST http://localhost:3000/get-expiration \
     -H "Content-Type: application/json" \
     -d '{"items": [{"name":"Apples","qty":"8"}]}'
```

### Step 3: Dashboard Management
*   **Secure Access:** Users must create a local account to access the dashboard. 
*   **Session Visibility:** Once authenticated, the dashboard displays your active inventory and highlights items nearing their expiration date.

---

## 👥 Contributors (Team 2)
*   **Adam C.**
*   **Alex FP**
*   **Ethan Causa**
*   **William Slaughter**
*   **Jackson Newton**
*   **Juan Munoz**

---
*Developed for IT391 @ Illinois State University*
