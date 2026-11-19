# 🏆 SportSync

A modern full-stack sports tracking web application featuring live scores, match management, cricket scorecards, football lineups, personalized favorites, news carousel, feedback, and a full admin dashboard.

---

## 🛠 Tech Stack

| Layer | Technology |
|-------|-----------|
| **Frontend** | React 18 + Vite |
| **Styling** | Pure Vanilla CSS (zero Tailwind / Bootstrap) |
| **Backend** | NestJS + TypeORM |
| **Database** | PostgreSQL |
| **Auth** | JWT (bcrypt password hashing) |
| **Email** | Nodemailer (Gmail SMTP) |
| **Icons** | Lucide React |

---

## 📁 Project Structure

```
SportSync/
├── frontend/                   ← React + Vite (runs on :3000)
│   ├── src/
│   │   ├── pages/              ← 13 page components
│   │   │   ├── Home.jsx
│   │   │   ├── Football.jsx
│   │   │   ├── Cricket.jsx
│   │   │   ├── LiveScores.jsx
│   │   │   ├── Scoreboard.jsx
│   │   │   ├── Login.jsx
│   │   │   ├── Signup.jsx
│   │   │   ├── Dashboard.jsx
│   │   │   ├── DeleteAccount.jsx
│   │   │   ├── FavoriteMatches.jsx
│   │   │   ├── Feedback.jsx
│   │   │   ├── ThankYou.jsx
│   │   │   └── Admin.jsx
│   │   ├── components/         ← Shared components
│   │   │   ├── Navbar.jsx
│   │   │   ├── Footer.jsx
│   │   │   ├── MatchCard.jsx
│   │   │   ├── NewsCarousel.jsx
│   │   │   └── ProtectedRoute.jsx
│   │   ├── styles/             ← Pure vanilla CSS (one file per page/component)
│   │   ├── services/api.js     ← Axios instance with JWT interceptors
│   │   └── context/AuthContext.jsx
│   └── vite.config.js          ← Proxies /api → localhost:5000
│
└── backend-nest/               ← NestJS API server (runs on :5000)
    └── src/
        ├── auth/               ← register, login, JWT guards, roles
        ├── users/              ← profile, change-password, delete-account
        ├── matches/            ← CRUD, filters, live matches, winner auto-calc
        ├── players/            ← CRUD with auto match-score sync
        ├── favorites/          ← toggle, list (filtered)
        ├── news/               ← CRUD (admin), latest (public)
        ├── feedback/           ← submit (public), manage (admin)
        ├── subscriptions/      ← newsletter subscribe/list
        ├── mail/               ← Nodemailer SMTP service
        └── database/           ← TypeORM entities + seed script
```

---

## ⚡ Quick Start

### Prerequisites
- Node.js ≥ 18
- PostgreSQL ≥ 14 running on `localhost:5432`

---

### 1. Clone the repo

```bash
git clone https://github.com/varunjoshi84/SportSync.git
cd SportSync
```

---

### 2. Backend Setup

```bash
cd backend-nest
npm install
```

Create your environment file:

```bash
cp .env.example .env
```

Edit `.env` with your values:

```env
DB_HOST=localhost
DB_PORT=5432
DB_USERNAME=postgres
DB_PASSWORD=your_db_password
DB_NAME=sport_sync

JWT_SECRET=your_super_secret_key
JWT_EXPIRES_IN=7d

SMTP_HOST=smtp.gmail.com
SMTP_PORT=587
SMTP_USER=your_email@gmail.com
SMTP_PASS=your_app_password
SMTP_FROM=SportSync <your_email@gmail.com>
```

Create the database, then seed it:

```bash
psql -U postgres -c "CREATE DATABASE sport_sync;"
npm run seed
```

Start the server:

```bash
npm run start
```

> API available at **http://localhost:5000/api**

---

### 3. Frontend Setup

```bash
cd frontend
npm install
npm run dev
```

> App available at **http://localhost:3000**

---

## 🔑 Seed Credentials

After running `npm run seed`:

| Role | Email | Password |
|------|-------|----------|
| Admin | `admin@sportsync.com` | `admin123` |
| Admin | `varunjoshi@gmail.com` | `admin123` |
| User | `test@example.com` | `password123` |

---

## ✨ Features

### 🌐 Public
| Feature | Description |
|---------|-------------|
| **Home** | Hero section, auto-cycling News Carousel, Live Matches grid |
| **Football** | Match listing with All / Live / Upcoming / Completed filters |
| **Cricket** | Match listing with All / Live / Upcoming / Completed filters |
| **Live Scores** | All live matches filtered by sport |
| **Scoreboard** | Full cricket scorecard tables + football lineups, stats & events |
| **Feedback** | Contact form → Thank You redirect |
| **Newsletter** | Footer email subscription |

### 🔐 Authenticated Users
| Feature | Description |
|---------|-------------|
| **Dashboard** | Update favorite sport/team, change password |
| **Favorite Matches** | AJAX favorite toggle on match cards (no page reload), dual sport+status filters |
| **Delete Account** | 2-step confirmation with password verification |

### 🛡 Admin Only
| Feature | Description |
|---------|-------------|
| **Admin → Matches** | Full CRUD with player roster management per match |
| **Admin → News** | Add/edit/delete news articles |
| **Admin → Feedback** | View, update status (pending/read/responded), reply via mailto |
| **Scoreboard** | Live player score updater widget visible to admins |

---

## 🔌 API Reference

### Authentication
| Method | Endpoint | Auth | Description |
|--------|----------|------|-------------|
| `POST` | `/api/auth/register` | — | Register new user |
| `POST` | `/api/auth/login` | — | Login → JWT |
| `GET` | `/api/auth/me` | JWT | Current user |

### Matches
| Method | Endpoint | Auth | Description |
|--------|----------|------|-------------|
| `GET` | `/api/matches` | Optional | List (filter: `sport`, `status`, `search`) |
| `GET` | `/api/matches/live` | Optional | Live matches only |
| `GET` | `/api/matches/:id` | Optional | Detail + players |
| `POST` | `/api/matches` | Admin | Create match |
| `PUT` | `/api/matches/:id` | Admin | Update match (auto-determines winner on complete) |
| `DELETE` | `/api/matches/:id` | Admin | Delete match |

### Players
| Method | Endpoint | Auth | Description |
|--------|----------|------|-------------|
| `GET` | `/api/players?match_id=X` | Optional | Players for a match |
| `POST` | `/api/players` | Admin | Add player (auto-syncs match score) |
| `PUT` | `/api/players/:id/score` | Admin | Update score (diff-based sync) |
| `DELETE` | `/api/players/:id` | Admin | Delete player (deducts from match score) |

### Other Endpoints
| Method | Endpoint | Auth | Description |
|--------|----------|------|-------------|
| `POST` | `/api/favorites/toggle` | JWT | Toggle favorite match |
| `GET` | `/api/favorites` | JWT | Favorited matches (filter: sport, status) |
| `GET` | `/api/news` | — | Latest news (filter: sport, limit) |
| `GET` | `/api/news/all` | Admin | All news articles |
| `POST/PUT/DELETE` | `/api/news` | Admin | CRUD news |
| `POST` | `/api/feedback` | — | Submit feedback |
| `GET/PUT/DELETE` | `/api/feedback` | Admin | Manage feedback |
| `POST` | `/api/subscriptions` | — | Subscribe to newsletter |
| `GET` | `/api/subscriptions` | Admin | List subscriptions |
| `PUT` | `/api/users/preferences` | JWT | Update favorite sport/team |
| `POST` | `/api/users/change-password` | JWT | Change password |
| `DELETE` | `/api/users/account` | JWT | Delete account |

---

## 🏗 Architecture Notes

- **Score Auto-Sync** — Adding/deleting a player automatically recalculates the team's total score on the match record using score differentials.
- **Winner Auto-Determination** — Setting match status to `completed` automatically sets the winner field (`team1_name`, `team2_name`, or `"Draw"`).
- **Role-Based Access** — `RolesGuard` + `@Roles('admin')` decorator protects admin-only routes. `OptionalJwtAuthGuard` enriches public routes with user context when a token is present.
- **Email Failures are Silent** — SMTP errors are caught and logged; feedback/subscription still saves to DB even if the confirmation email fails.

---

## 🚀 Production Build

```bash
# Frontend
cd frontend && npm run build
# Static files output to frontend/dist/ — serve with any static host or CDN

# Backend
cd backend-nest && npm run build && npm run start:prod
```

---

## 📸 Screenshots

> _Coming soon_

---

## 📄 License

MIT
