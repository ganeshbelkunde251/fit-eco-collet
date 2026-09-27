# EcoCollect — Smart Waste Collection Management Platform

> **"Dispose Responsibly. Collect Smartly."**

EcoCollect is a complete, production-ready, software-based waste collection management platform built to connect residents with municipal/private waste collection operators.

---

## 🌟 Features & Highlights

- **Resident Portal**: Create waste collection requests, select schedule dates, and track status live using visual timeline steppers.
- **AI Camera Waste Scanner**: Instant visual scanner powered by **Google Gemini 1.5 Flash Vision AI** to classify items via device camera or photo upload.
- **AI Text Assistant**: Categorization and safety guidance tool.
- **Admin Control Center**: Real-time pickup management, search/filters, status updates, and Chart.js analytics.
- **100% Real Database**: Powered by MySQL with pre-seeded demo users and realistic Pune locality requests.

---

## 🛠️ Local XAMPP Setup Instructions

### Local Project Location
```text
C:\xampp\htdocs\west
```

### Local Application URL
```text
http://localhost/west/
```

### Step-by-Step Installation

1. **Start XAMPP Services**:
   Open XAMPP Control Panel and start **Apache** and **MySQL**.

2. **Database Setup**:
   - **Method A (Automated 1-Click Seeder)**:
     Open your browser and navigate to:
     ```text
     http://localhost/west/seed_admin.php
     ```
     Click **"Initialize Database & Seed Demo Data"**. This automatically creates the `ecocollect` database, tables, and 16 realistic pickup requests!

   - **Method B (phpMyAdmin Manual Import)**:
     1. Open `http://localhost/phpmyadmin/`.
     2. Create a database named `ecocollect`.
     3. Import `database/database.sql`.
     4. Import `database/demo-data.sql`.

3. **Configure Environment Variables (`.env`)**:
   Create or edit the `.env` file in the root folder (`c:\Users\Ganesh\OneDrive\Desktop\west\.env`):
   ```env
   APP_NAME=EcoCollect
   APP_URL=http://localhost/west

   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_NAME=ecocollect
   DB_USER=root
   DB_PASSWORD=

   # Add your Gemini API Key from Google AI Studio
   GEMINI_API_KEY=YOUR_GEMINI_API_KEY_HERE
   ```

4. **Open Application**:
   Navigate to `http://localhost/west/` in your web browser.

---

## 🔑 Demo Login Credentials

| Role | Name | Email | Password |
| :--- | :--- | :--- | :--- |
| **Admin** | EcoCollect Admin | `admin@ecocollect.com` | `Admin@123` |
| **User 1** | Rahul Sharma | `rahul@ecocollect.com` | `User@123` |
| **User 2** | Priya Patil | `priya@ecocollect.com` | `User@123` |

---

## 🤖 Gemini API Configuration Steps

1. Get a free API Key from [Google AI Studio](https://aistudio.google.com/).
2. Paste the API key into `.env`:
   ```env
   GEMINI_API_KEY=AIzaSy...YourActualKey...
   ```
3. **Security**: The API key remains strictly on the PHP backend (`api/ai-classify.php` & `api/ai-image-classify.php`) and is **never** exposed in client-side HTML/JavaScript.
4. **Fallback Safety**: If no key is set or the API is offline, the scanner displays a clear notice while allowing manual waste selection.

---

## 📷 Exact Steps to Preview the AI Camera Scanner

1. Open `http://localhost/west/` in your browser.
2. Click **Sign In** and log in with:
   - Email: `rahul@ecocollect.com`
   - Password: `User@123`
3. On the **User Dashboard**, locate the **"AI Waste Scanner"** card.
4. **Camera Scan**:
   - Click **[ Open Camera ]**.
   - Allow camera permission when prompted.
   - Point your camera at a waste item (e.g. laptop, plastic bottle, cardboard).
   - Click **[ Scan Waste ]**.
5. **Image Upload Fallback**:
   - Click **Choose File** under *"Or Upload Image File (JPG, JPEG, PNG)"*.
   - Select a photo of any waste item.
6. **AI Results**:
   - View the structured breakdown: Object Name, Waste Category, Waste Type, Recyclable (Yes/No), Recommended Disposal Method, Safety Warning, and AI Confidence.
   - Click **[ Schedule Pickup ]** to automatically populate a pickup request with the scanned category!

---

## 📋 Technology Stack

- **Frontend**: HTML5, CSS3, Bootstrap 5, Vanilla JavaScript ES6+, Bootstrap Icons, Chart.js.
- **Backend**: PHP 8+, PDO Database Abstraction, Native Sessions.
- **Database**: MySQL (`ecocollect`).
- **Deployment**: Google Cloud Run ready with Dockerfile.
