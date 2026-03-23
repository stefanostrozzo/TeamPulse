# 🚀 TeamPulse

> **Team project management tool for development teams** — built with Laravel & Vue.js

![PHP](https://img.shields.io/badge/PHP-8.x-777BB4?style=flat-square&logo=php&logoColor=white)
![Laravel](https://img.shields.io/badge/Laravel-11.x-FF2D20?style=flat-square&logo=laravel&logoColor=white)
![Vue.js](https://img.shields.io/badge/Vue.js-3.x-4FC08D?style=flat-square&logo=vue.js&logoColor=white)
![Tailwind CSS](https://img.shields.io/badge/Tailwind-3.x-38BDF8?style=flat-square&logo=tailwindcss&logoColor=white)
![Status](https://img.shields.io/badge/Status-MVP-orange?style=flat-square)
![License](https://img.shields.io/badge/License-MIT-green?style=flat-square)

---

## 📋 Overview

**TeamPulse** is a web application designed to help development teams manage projects efficiently. It provides tools for task planning, team collaboration, reporting, and workflow customization — all in one place.

> ⚠️ **This project is currently in MVP phase.** Core features are functional but the application is still under active development.

---

## ✨ Features (MVP)

### 📌 Project & Task Management
- **Kanban Board** — Dynamic task visualization with drag-and-drop support across columns (To Do, In Progress, Done)
- Task filtering by priority, assignee, or tag
- Priority levels (high, medium, low) and custom tagging

### 💬 Collaboration
- **Real-time Messaging** — Instant team chat with public and private channels
- **Task Comments** — Dedicated comment section per task with instant notifications
- **Real-time Notifications** — Live updates on task changes, messages, and comments via WebSockets (Laravel Echo + Pusher)

### 📊 Dashboard
- Configurable widgets for project progress, workload, and recent activity

---

## 🗺️ Roadmap

### ✅ Phase 1 — MVP *(current)*
- [x] Kanban Board
- [x] Instant Messaging
- [x] Basic Dashboard

### 🔄 Phase 2 — Feature Expansion
- [ ] Gantt Chart
- [ ] Time Tracking (per-task time logging + weekly/monthly reports)
- [ ] Burndown Charts
- [ ] Integrated Video Calls (Jitsi / Zoom API)
- [ ] File Sharing

### 🔮 Phase 3 — Optimization & Gamification
- [ ] Dark Mode
- [ ] Custom Workflows (e.g. To Do → In Progress → Code Review → Done)
- [ ] Sprint Planning & Backlog Management
- [ ] Gamification (badges, points, leaderboard)
- [ ] Team Performance Analytics & KPIs

---

## 🛠️ Tech Stack

| Layer | Technology |
|---|---|
| Backend | Laravel 11 (PHP 8.x) |
| Frontend | Vue.js 3 + Tailwind CSS |
| Real-time | Laravel Echo + Pusher |
| Database | MySQL / PostgreSQL |
| Build Tool | Vite |
| Testing | PHPUnit + Vitest |

---

## ⚙️ Getting Started

### Prerequisites

- PHP >= 8.1
- Composer
- Node.js >= 18.x & npm
- MySQL or PostgreSQL
- A [Pusher](https://pusher.com/) account (for real-time features)

### Installation

```bash
# 1. Clone the repository
git clone https://github.com/stefanostrozzo/TeamPulse.git
cd TeamPulse

# 2. Install PHP dependencies
composer install

# 3. Install JS dependencies
npm install

# 4. Set up environment
cp .env.example .env
php artisan key:generate

# 5. Configure your database and Pusher credentials in .env

# 6. Run migrations
php artisan migrate

# 7. Start the development servers
php artisan serve
npm run dev
```

The application will be available at `http://localhost:8000`.

---

## 🧪 Running Tests

```bash
# PHP tests
php artisan test

# JavaScript tests
npm run test
```

---

## 📁 Project Structure

```
TeamPulse/
├── app/               # Laravel application (Models, Controllers, Services)
├── bootstrap/         # Laravel bootstrap files
├── config/            # Configuration files
├── database/          # Migrations and seeders
├── resources/
│   ├── js/            # Vue.js components and pages
│   └── views/         # Blade entry point
├── routes/            # API and web routes
├── tests/             # PHP test suites
└── public/            # Public assets
```

---

## 🤝 Contributing

This is a personal project in MVP phase. Feedback, ideas, and contributions are welcome!

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/your-feature`)
3. Commit your changes (`git commit -m 'Add some feature'`)
4. Push to the branch (`git push origin feature/your-feature`)
5. Open a Pull Request

---

## 📄 License

This project is licensed under the [MIT License](LICENSE).

---

## 👤 Author

**Stefano Strozzo**
- GitHub: [@stefanostrozzo](https://github.com/stefanostrozzo)
