# RealityOS AI 🧠
### AI-Powered Decision Intelligence Platform

> Stop guessing. Simulate your decisions before you make them.

RealityOS AI compares your choices, evaluates risks, and delivers structured AI recommendations for real-world decisions — career, business, finance, and beyond.

---

## 📖 Project Overview

### 🚩 Problem Statement
Most individuals face "decision paralysis" when confronted with complex, non-obvious life choices. Standard decision-making tools (like simple pros/cons lists) are often too static and fail to account for:
- **Volatility:** How situational changes affect the outcome.
- **Risk Depth:** The difference between a "minor setback" and a "catastrophic failure."
- **Lack of Action:** Generic advice without a concrete execution roadmap.

### 💡 Solution Approach
RealityOS AI leverages the **Google Gemini 2.5 Flash** model to perform "Simulated Decision Intelligence." Unlike a standard chatbot, it uses a deterministic JSON-based prompting layer to extract high-precision metrics from the AI.
- **Structured Analysis:** Converting AI intuition into measurable "Growth" and "Risk" scores.
- **Divergent Paths:** Side-by-side simulation of two mutually exclusive futures.
- **Actionable Execution:** Automatically generating a 5-step roadmap based on the recommended path.

---

## 🚀 Quick Setup (XAMPP)

### Requirements
- [XAMPP](https://www.apachefriends.org/) (Apache + MySQL + PHP 8.0+)
- Google Gemini API Key ([Get one free](https://aistudio.google.com/))

---

### Step 1 — Clone / Place Files
Make sure the project is in: `C:\xampp\htdocs\RealityOSAI\`

---

### Step 2 — Configure API Key

Open `config/env.php` and replace the placeholder:

```php
define('GEMINI_API_KEY', 'YOUR_GEMINI_API_KEY_HERE');
```

Get your free API key at: https://aistudio.google.com/app/apikey

---

### Step 3 — Set Up Database

1. Start **XAMPP Control Panel**, enable **Apache** and **MySQL**
2. Open [phpMyAdmin](http://localhost/phpmyadmin)
3. Click **SQL** tab and run `schema.sql`:
   - Copy the file contents into the SQL editor and Execute
4. (Optional) Run `seed.sql` for demo data and a pre-built demo account

---

### Step 4 — Open the App

Visit: **[http://localhost/RealityOSAI](http://localhost/RealityOSAI)**

#### Demo Account (if seed.sql was run):
- **Email:** `demo@realityos.ai`  
- **Password:** `demo1234`

---

## 📁 Project Structure

```
RealityOSAI/
├── config/
│   ├── env.php              ← API keys & app settings
│   └── database.php         ← PDO database connection
├── api/
│   ├── register.php         ← POST: create account
│   ├── login.php            ← POST: authenticate
│   ├── logout.php           ← POST: end session
│   ├── profile.php          ← GET: user profile + stats
│   ├── analyze_decision.php ← POST: call Gemini AI
│   ├── create_simulation.php← POST: save to DB
│   ├── get_simulations.php  ← GET: list simulations
│   ├── get_simulation.php   ← GET: single simulation
│   └── delete_simulation.php← DELETE: remove simulation
├── classes/
│   └── GeminiService.php    ← Gemini API integration
├── includes/
│   └── helpers.php          ← Session, auth, sanitization
├── assets/
│   ├── css/style.css        ← Global design system
│   └── js/
│       ├── api.js           ← API client layer
│       └── app.js           ← Shared utilities
├── index.html               ← Landing page
├── login.html               ← Sign in
├── register.html            ← Create account
├── dashboard.html           ← User dashboard
├── new-simulation.html      ← Create simulation form
├── results.html             ← AI analysis results
├── simulations.html         ← Saved simulations list
├── profile.html             ← User profile
├── schema.sql               ← Database schema
└── seed.sql                 ← Demo data
```

---

## 🔑 API Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/api/register.php` | Register new user |
| POST | `/api/login.php` | Login |
| POST | `/api/logout.php` | Logout |
| GET  | `/api/profile.php` | Get user profile |
| POST | `/api/analyze_decision.php` | Run AI analysis |
| POST | `/api/create_simulation.php` | Save simulation |
| GET  | `/api/get_simulations.php` | List simulations |
| GET  | `/api/get_simulation.php?id=N` | Get single simulation |
| DELETE | `/api/delete_simulation.php?id=N` | Delete simulation |

---

## 🗄️ Database Schema

| Table | Key Fields |
|-------|-----------|
| `users` | id, full_name, email, password (bcrypt), created_at |
| `simulations` | id, user_id, decision_title, category, ai_response_json, confidence_score, ... |
| `activity_logs` | id, user_id, action_type, description, created_at |

---

## ✨ Key Features

- 🤖 **Gemini AI Engine v2** — High-precision JSON prompting with structured reasoning and situational analysis.
- ⚖️ **Dual-Option Comparison** — Side-by-side analysis with interactive pros/cons, risk levels, and growth potential.
- 📉 **Risk Analysis Meter** — Instant visual clarity with animated progress bars for Low/Medium/High risk assessments.
- 📊 **Animated Confidence System** — qualitative labels (Very High, High, Moderate) with SVG stroke animations.
- 🚀 **Execution Roadmap** — Detailed 5-step plans including preparation checklists and common mistakes to avoid.
- 📄 **Professional PDF Export** — One-click report generation with a custom print-optimized layout.
- 🔗 **Smart Sharing** — Clipboard-ready report links and structured AI summaries for quick communication.
- 📱 **Fully Responsive** — Fluid glassmorphism design optimized for Desktop, Tablet, and Mobile devices.
- 🛡️ **Security Hardened** — `.htaccess` directory blocking, bcrypt password hashing, and PDO prepared statements.

---

## 🎨 Tech Stack

| Layer | Technology |
|-------|-----------|
| Frontend | HTML5, Vanilla JS, Vanilla CSS (Custom Design System) |
| Backend | PHP 8.1+ (REST Architecture) |
| Database | MySQL (PDO) |
| AI | Google Gemini 2.5 Flash |
| Design | Glassmorphism, CSS Variables, SVG Animations |
| Fonts | Inter + Space Grotesk (Google Fonts) |

---

Built with ❤️ for Dev Annual Hackathon · Powered by Google Gemini API
