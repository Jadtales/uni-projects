# Web Systems Design - Project 12: Figma UI/UX Design System & Documentation

**Student:** Amjad Massaoud  
**Student ID:** 78706  
**Course:** Web Systems Design  
**Date:** 30.08.2026  

---

## 1. System Overview
Project 12 documents the complete UI/UX architecture and design system for the full application developed across all laboratory sessions:
- **Tasks Management:** CRUD and summary metrics (`/api/78706/v1/tasks`).
- **URL Shortener:** Link generation, Base62 encoding, and redirect tracker (`/api/78706/v1/short-links`, `/r/{code}`).
- **Nearby Restaurants:** Proximity search using Haversine formula (`/api/78706/v1/restaurants/nearby`).
- **Photo Upload Service:** Media upload, storage abstraction, and processing status (`/api/78706/v1/photos`).
- **News Feed Service:** Social graph follow/unfollow and cursor-paginated feed (`/api/78706/v1/users/{id}/follow`, `/api/78706/v1/feed`).
- **Streaming Platform Service:** Video catalog, watch history, continue watching, and genre recommendations (`/api/78706/v1/videos`, `/api/78706/v1/continue-watching`, `/api/78706/v1/recommendations`, `/api/78706/v1/watchlist`).

---

## 2. Design System Tokens
* **Primary Color:** `#2563EB` (Blue)
* **Secondary Color:** `#14B8A6` (Teal)
* **Background Color:** `#F8FAFC` (Light Gray / Slate)
* **Text Color:** `#111827` (Dark Gray)
* **Success Color:** `#22C55E` (Green)
* **Warning Color:** `#F59E0B` (Amber)
* **Error Color:** `#EF4444` (Red)

### Typography Hierarchy
* **Page Title:** 32px Bold
* **Section Heading:** 24px SemiBold
* **Body Text:** 16px Regular
* **Small Text:** 12px Regular
* **Button Text:** 14px SemiBold

### Target Responsive Breakpoints
* **Desktop:** 1440 × 1024 px
* **Tablet:** 768 × 1024 px
* **Mobile:** 390 × 844 px
