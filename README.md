# 🚀 TeamPulse

> **Team project management tool for development teams** — built with Laravel & Vue.js[cite: 1].
> Design system tailored for a dark-mode-first experience.

![PHP](https://img.shields.io/badge/PHP-8.x-777BB4?style=flat-square&logo=php&logoColor=white)[cite: 1]
![Laravel](https://img.shields.io/badge/Laravel-12.x-FF2D20?style=flat-square&logo=laravel&logoColor=white)
![Vue.js](https://img.shields.io/badge/Vue.js-3.x-4FC08D?style=flat-square&logo=vue.js&logoColor=white)[cite: 1]
![Tailwind CSS](https://img.shields.io/badge/Tailwind-4.x-38BDF8?style=flat-square&logo=tailwindcss&logoColor=white)
![Status](https://img.shields.io/badge/Status-MVP-orange?style=flat-square)[cite: 1]
![License](https://img.shields.io/badge/License-MIT-green?style=flat-square)[cite: 1]

---

## 📋 Overview

**TeamPulse** is a single-product SaaS web app designed to help development teams manage projects efficiently[cite: 1]. It provides tools for task planning, team collaboration, reporting, and workflow customization — all in one place[cite: 1].

> ⚠️ **This project is currently in MVP phase.** Core features are functional but the application is still under active development[cite: 1].

---

## ✨ Features

### 📌 Project & Task Management
- **Kanban Board** — Dynamic task visualization with drag-and-drop support across columns (To Do, In Progress, Done)[cite: 1].
- **Task Filtering** — Filter by priority, assignee, or tag[cite: 1].
- **Priority Levels** — High, medium, low, and custom tagging[cite: 1].
- **Project Grid** — Featuring status, priority, and progress indicators.

### 💬 Collaboration & Teams
- **Teams** — Invite management, member roles, and pending invites.
- **Real-time Messaging** — Instant team chat with public and private channels[cite: 1].
- **Task Comments** — Dedicated comment section per task with instant notifications[cite: 1].
- **Real-time Notifications** — Live updates on task changes and messages via WebSockets (Laravel Echo + Pusher)[cite: 1].

### 📊 Dashboard
- Configurable widgets for project progress, workload, and recent activity[cite: 1].
- Visual stats powered by ApexCharts.

---

## 🗺️ Roadmap

### ✅ Phase 1 — MVP *(current)*
- [x] Kanban Board[cite: 1]
- [x] Instant Messaging[cite: 1]
- [x] Basic Dashboard[cite: 1]

### 🔄 Phase 2 — Feature Expansion
- [ ] Gantt Chart[cite: 1]
- [ ] Time Tracking (per-task time logging + weekly/monthly reports)[cite: 1]
- [ ] Burndown Charts[cite: 1]
- [ ] Integrated Video Calls (Jitsi / Zoom API)[cite: 1]
- [ ] File Sharing[cite: 1]

### 🔮 Phase 3 — Optimization & Gamification
- [ ] Light Mode (App currently runs exclusively in Dark Mode)
- [ ] Custom Workflows (e.g. To Do → In Progress → Code Review → Done)[cite: 1]
- [ ] Sprint Planning & Backlog Management[cite: 1]
- [ ] Gamification (badges, points, leaderboard)[cite: 1]
- [ ] Team Performance Analytics & KPIs[cite: 1]

---

## 🛠️ Tech Stack & Architecture

| Layer | Technology |
|---|---|
| Backend | Laravel 12 (PHP 8.x) |
| Frontend | Vue 3 (Composition API) + Inertia.js + Pinia |
| UI & Styling | Tailwind CSS 4 + PrimeVue 4 |
| Real-time | Laravel Echo + Pusher[cite: 1] |
| Database | MySQL / PostgreSQL[cite: 1] |

---

## 🎨 Design System & Conventions

### Content Fundamentals
- **UI strings (user-facing)**: Italian. All buttons, labels, and toasts are in Italian (e.g., `"Accedi"`, `"I miei Progetti"`).
- **Code comments**: English (professional).
- **Tone**: Warm but professional, direct, and instructional.

### Visual Foundations
- **Colors**: Runs exclusively in Dark Mode. Surfaces use `gray-950` to `gray-700`. The primary brand color is a vivid cyan-blue (`#07b4f6`). Semantic colors handle success (`green-400`), warning (`yellow-400`), and danger (`red-400`).
- **Typography**: Tailwind's default sans stack (`Inter`).
- **Borders & Radii**: Cards use `rounded-xl` (12px), Modals use `rounded-2xl`/`rounded-3xl`, and Buttons use `rounded-xl`.
- **Iconography**: Font Awesome 6 Free (`fas` / `far`).

---

## ⚙️ Getting Started

### Prerequisites
- PHP >= 8.1[cite: 1]
- Composer[cite: 1]
- Node.js >= 18.x & npm[cite: 1]
- MySQL or PostgreSQL[cite: 1]
- A [Pusher](https://pusher.com/) account (for real-time features)[cite: 1]

### Installation

```bash
# 1. Clone the repository
git clone https://github.com/stefanostrozzo/TeamPulse.git[cite: 1]
cd TeamPulse[cite: 1]

# 2. Install PHP dependencies
composer install[cite: 1]

# 3. Install JS dependencies
npm install[cite: 1]

# 4. Set up environment
cp .env.example .env[cite: 1]
php artisan key:generate[cite: 1]

# 5. Configure your database and Pusher credentials in .env[cite: 1]

# 6. Run migrations
php artisan migrate[cite: 1]

# 7. Start the development servers
php artisan serve[cite: 1]
npm run dev[cite: 1]
```

The application will be available at `http://localhost:8000`[cite: 1].

---

## 🧪 Running Tests

```bash
# PHP tests
php artisan test[cite: 1]

# JavaScript tests
npm run test[cite: 1]
```

---

## 📁 Project Structure

```text
TeamPulse/
├── app/               # Laravel application (Models, Controllers, Services)[cite: 1]
├── config/            # Configuration files[cite: 1]
├── database/          # Migrations and seeders[cite: 1]
├── resources/
│   ├── js/            # Vue.js components, Pages, and Layouts (Inertia)
│   └── views/         # Blade entry point[cite: 1]
├── routes/            # API and web routes[cite: 1]
├── tests/             # PHP test suites[cite: 1]
└── public/            # Public assets[cite: 1]
```

---

## 🤝 Contributing

This is a personal project in MVP phase. Feedback, ideas, and contributions are welcome![cite: 1]

1. Fork the repository[cite: 1]
2. Create a feature branch (`git checkout -b feature/your-feature`)[cite: 1]
3. Commit your changes (`git commit -m 'Add some feature'`)[cite: 1]
4. Push to the branch (`git push origin feature/your-feature`)[cite: 1]
5. Open a Pull Request[cite: 1]

---

## 📄 License

This project is licensed under the [GNU License](LICENSE).

---

## 👤 Author

**Stefano Strozzo**[cite: 1]
- GitHub: [@stefanostrozzo](https://github.com/stefanostrozzo)[cite: 1]
