# Online Food Ordering System

**Final Year Project — Technical Documentation (Thesis Support Document)**

*Institution: [Your University Name]*  
*Author: [Your Name]*  
*Supervisor: [Supervisor Name]*  
*Academic Year: [e.g. 2025–2026]*

**Companion document (use cases, state machine, Mermaid ER):** `docs/THESIS_USE_CASES_AND_ER_DIAGRAM.md`

---

## Abstract

This document describes the design and implementation of a web-based **Online Food Ordering System**. The system separates concerns into a **REST API** built with the Laravel framework and a **single-page application (SPA)** built with Vue.js. End users can discover restaurants, browse menus, maintain a shopping cart, place orders, record payments, and track order status. Restaurant operators manage menus and order workflows. Administrators manage restaurants, delivery riders, and global visibility of orders. Delivery riders accept orders that are ready for pickup and update delivery status. The document is aligned with the source code in this repository and is intended to be adapted into formal thesis chapters (introduction, literature review, methodology, system design, implementation, testing, conclusion).

**Keywords:** food ordering, Laravel, Vue.js, REST API, role-based access, Sanctum, SPA.

---

## Table of Contents

1. [Introduction](#1-introduction)  
2. [Technology Stack](#2-technology-stack)  
3. [System Architecture and Patterns](#3-system-architecture-and-patterns)  
4. [User Roles and Responsibilities](#4-user-roles-and-responsibilities)  
5. [Functional Modules](#5-functional-modules)  
6. [Business and Technical Flows](#6-business-and-technical-flows)  
7. [Data Model Overview](#7-data-model-overview)  
8. [API Surface (Summary)](#8-api-surface-summary)  
9. [User Interface Structure](#9-user-interface-structure)  
10. [Security Considerations](#10-security-considerations)  
11. [Limitations and Recommended Improvements](#11-limitations-and-recommended-improvements)  
12. [Conclusion](#12-conclusion)  
13. [References and Further Reading](#13-references-and-further-reading)

---

## 1. Introduction

### 1.1 Problem Statement

Traditional telephone-based food ordering is error-prone, offers limited visibility into order status, and scales poorly for restaurants with high order volume. A centralized digital platform improves accuracy, provides audit trails, and enables real-time notifications to customers, kitchens, and couriers.

### 1.2 Project Objectives

- Provide a **customer-facing** experience for browsing restaurants, building a cart, and placing orders.  
- Provide **vendor (restaurant)** tools for menu management and order status updates.  
- Provide an **administrative** console for restaurants, riders, and cross-cutting order visibility.  
- Provide **delivery rider** workflows for assignment and status updates.  
- Use **industry-standard** web technologies suitable for deployment on typical PHP hosting (e.g. WAMP/XAMPP) or cloud VMs.

### 1.3 Scope of This Document

The description below reflects the **implemented** behaviour in the repository: routes in `routes/api.php`, controllers under `app/Http/Controllers`, models under `app/Models`, and the Vue application under `resources/js`. Third-party payment gateways are **not** deeply integrated; payments are recorded as entities with a chosen method (see `PaymentController`).

---

## 2. Technology Stack

### 2.1 Backend

| Technology | Role in project |
|------------|-----------------|
| **PHP 8.2+** | Runtime for the server application. |
| **Laravel 11** (`laravel/framework` ^11.31) | Web framework: routing, ORM (Eloquent), validation, mail, configuration. |
| **Laravel Sanctum** | API token authentication (`auth:sanctum`); login issues personal access tokens with named abilities (`admin`, `vendor`, `customer`, `rider`). |
| **MySQL / PostgreSQL** | Supported by Laravel; schema defined in migrations under `database/migrations`. |
| **Laravel Socialite** | Optional OAuth redirect/callback (`SocialiteController`); stateless driver usage. |
| **DomPDF (barryvdh/laravel-dompdf)** | PDF generation for invoices (e.g. order invoice mail). |
| **L5-Swagger (darkaonline/l5-swagger)** | OpenAPI documentation generation from controller annotations. |

*Note:* The project `README.md` may mention “Laravel 12”; the **declared** framework version in `composer.json` is **Laravel 11**. The thesis should state the version actually locked by Composer.

### 2.2 Frontend

| Technology | Role in project |
|------------|-----------------|
| **Vue 3** | Component-based SPA (`resources/js`). |
| **Vue Router** | Client-side routing; nested layouts per persona (`/admin`, `/vendor`, `/customer`, `/rider`). |
| **Vuex** | Global state: theme, cart persistence, profile snapshot, API base URL. |
| **Vite 6** | Dev server and production bundling (`vite.config.js`, `@vitejs/plugin-vue`). |
| **Axios** | HTTP client; `Authorization: Bearer` header from `auth_service.js`. |
| **Bootstrap 5** | Layout and components. |
| **vue-toastification** | Non-blocking user feedback. |
| **Tailwind CSS** (dev) | Utility styling where configured in the build pipeline. |

### 2.3 Assets and Theming

- Global styles: `resources/css/style.css`, `resources/css/theme-override.css`, Bootstrap import in `app.js`.  
- **Light/dark theme** persisted via `resources/js/utils/theme.js` and Vuex `SET_THEME`.  
- Large legacy asset bundles exist under `public/assets/` (charts, plugins); the primary application screens are Vue-driven.

### 2.4 Deployment Pattern

- **Single Laravel application** serves the SPA: `routes/web.php` returns `index` view for all non-API paths (`/{any}`), so the Vue router handles navigation.  
- **API** is prefixed by Laravel’s `api` routing (typically `/api/...`).

---

## 3. System Architecture and Patterns

### 3.1 High-Level Architecture

The system follows a **client–server** architecture:

1. **Browser (Vue SPA)** — presentation, routing, local cart state, token storage.  
2. **Laravel HTTP layer** — `routes/api.php` maps URIs to controller actions.  
3. **Domain / persistence** — Eloquent models and migrations represent restaurants, menus, orders, payments, notifications, users, roles.

This is a **monolithic** deployment: frontend assets are built into the same application; there is no separate Node server required in production beyond the build step.

### 3.2 Architectural Style

- **RESTful API** for machine-oriented operations (JSON request/response).  
- **SPA** for human-oriented interaction (HTML rendered once; subsequent navigation is client-side).  
- **RBAC (role-based access control)** using numeric `role_id` on the `users` table, enforced in controllers (imperative checks) and in the SPA via **route meta** guards.

### 3.3 Design Patterns Used

| Pattern | Where it appears |
|---------|------------------|
| **MVC (Model–View–Controller)** | Laravel: Models ↔ Controllers; “View” for API is JSON; Blade views exist for emails/PDF. |
| **Front Controller** | `public/index.php` dispatches all HTTP traffic. |
| **Service layer (light)** | `resources/js/services/*_service.js` encapsulate HTTP calls (`http_service.js` centralizes Axios). |
| **State container** | Vuex store for cart, theme, profile hydration. |
| **Guard / middleware** | `auth:sanctum` on API groups; Vue `router.beforeEach` for `requiresAdmin`, `requiresVendor`, etc. |
| **Active Record** | Eloquent models (`User`, `Restaurant`, `FoodOrder`, …). |

### 3.4 Authentication Flow (API)

1. Client calls `POST /api/auth/login` with credentials.  
2. Laravel validates credentials; revokes existing tokens (`tokens()->delete()`).  
3. A new **personal access token** is created with an ability name derived from `role_id` (`admin`, `vendor`, `customer`, `rider`).  
4. Client stores `token`, serialized `user`, and a string `user_role` in **localStorage** (`auth_service.js`).  
5. Subsequent API calls send `Authorization: Bearer <token>`.  
6. `GET /api/auth/logout` invalidates server-side token; client clears local storage.

For login, the client also requests `/sanctum/csrf-cookie` first (`withCredentials: true`) to align with Laravel’s CSRF expectations for cookie-aware flows.

---

## 4. User Roles and Responsibilities

The application logic assumes **fixed role identifiers** when seeding the database with `RoleSeeder`:

| `role_id` | Role name (seeded) | Primary responsibility |
|-----------|-------------------|-------------------------|
| **1** | Admin | Full system oversight: restaurants list (all statuses), creating restaurants with manager accounts, rider CRUD, viewing orders. |
| **2** | Vendor | Restaurant linked user (`restaurant_id`): manage restaurant profile (where authorized), **menu categories/items**, update **order status** for their restaurant’s orders. |
| **3** | Customer | Browse public restaurant/menu endpoints; cart and checkout; create orders; pay (record payment); track own orders; notifications. |
| **4** | Delivery Rider | See orders that are **ready** and unassigned, or assigned to self; set status to **out_for_delivery** (self-assign) and **delivered**. |

### 4.1 “Manager” vs “Vendor” (Business Wording)

When an **admin** creates a restaurant via `POST /api/admin/restaurants`, the implementation creates a user referred to in the UI and email copy as a **restaurant manager**, but the database assigns **`role_id = 2` (Vendor)** and links `restaurant_id` after the restaurant row is created (`RestaurantController::adminStore`). That user logs in through the same **vendor** dashboard routes as a self-registered vendor.

### 4.2 Seeding Note (Important for Thesis / Deployment)

- **`RoleSeeder`** inserts roles with **explicit IDs** 1–4 (recommended baseline for consistency with controllers).  
- **`AdminSeeder`** creates roles with **auto-increment** names (`admin`, `manager`, `user`) which **do not** match the fixed `role_id` assumptions in `FoodOrderController`, `AuthController`, etc. For a coherent deployment, the thesis should recommend: **run `RoleSeeder` before creating users**, or document a single canonical seeding strategy.

### 4.3 Sanctum Token Abilities

Token abilities are assigned at login by `role_id` (`AuthController::login`). Fine-grained policy middleware for abilities is **not** uniformly applied across all routes; many endpoints rely on **`auth:sanctum` plus manual `role_id` checks** inside controllers. This is acceptable for a academic project but is a known area for hardening (see Section 11).

---

## 5. Functional Modules

### 5.1 Authentication and Profile

- Registration: `POST /api/auth/registerUser` (default `role_id` **3** if omitted — customer).  
- Login, logout, profile read/update, password reset request/reset, change password (`AuthController`).  
- Optional OAuth routes under `/api/auth/{provider}` (callback redirects; integration with SPA may require alignment of redirect targets).

### 5.2 Restaurant Discovery

- **Public** (no token): `GET /api/restaurants`, `GET /api/restaurants/{id}` — active restaurants, menu with availability filter on show.

### 5.3 Restaurant Administration

- **Admin-only**: `GET /api/admin/restaurants`, `POST /api/admin/restaurants` (create + email manager credentials).  
- **Authenticated**: create/update/delete restaurant where `authorizeRestaurant` permits (admin or owning vendor).

### 5.4 Menu Management

- Categories: list/create/update/delete under `restaurants/{id}/menu-categories`.  
- Items: create/update/delete under nested routes (`MenuItemController`).  
- Authorization: admin **or** vendor who owns the restaurant (`MenuCategoryController`).

### 5.5 Orders

- List with **role-scoped** query (`FoodOrderController::index`).  
- Create order with line items, pricing, delivery fee (`store`).  
- Status transitions (`patch` status) with different allowed statuses for **vendor** vs **rider**.  
- Notifications on create, status change, and “ready for delivery” broadcast to riders.  
- Email invoice on **delivered** (`OrderInvoiceMail`).

### 5.6 Payments

- `POST /api/payments` records amount, method (`cash`, `card`, `online`, `wallet`), optional `transaction_id`; order must belong to authenticated customer.

### 5.7 Notifications

- Paginated inbox per user; mark one or all as read (`NotificationController`).  
- Stored in `notifications` table; factory helper `Notification::createForUser`.

### 5.8 Riders (Admin)

- `GET /api/admin/riders`, `POST /api/admin/riders` — list and create riders (`RiderController`); email optional credentials.

---

## 6. Business and Technical Flows

### 6.1 Customer Order Lifecycle (Implemented Statuses)

Typical progression (vendor-driven unless cancelled):

`pending` → `confirmed` → `preparing` → `ready` → `out_for_delivery` → `delivered`

Additional status: `cancelled`.

**Rider rules (simplified):**

- When status is `ready` and **no** `delivery_rider_id`, eligible riders see the order in listings.  
- Rider moving to `out_for_delivery` sets `delivery_rider_id` to the rider’s user id.  
- Only assigned rider should mark `delivered` (enforced in controller).

### 6.2 Notification Flow

1. **Order placed:** customer receives confirmation; all vendor users (`role_id == 2`) for that `restaurant_id` receive “new order”.  
2. **Status change:** customer notified on any status change.  
3. **Ready:** all users with `role_id == 4` notified that an order is ready for pickup.

### 6.3 Cart and Checkout (Frontend)

- Vuex holds `cart` (restaurant id/name, line items).  
- Cart is **persisted to `localStorage`** (`food_order_cart`) for session survivability.  
- Checkout builds payload compatible with `POST /api/orders` (`cartOrderPayload` getter).

### 6.4 Admin Creates Restaurant

1. Admin submits restaurant fields + manager name/email.  
2. Backend creates user with `role_id = 2`, random password, creates `restaurants` row (`status = pending`), links `restaurant_id`.  
3. `ManagerCredentialsMail` sends credentials (implementation detail: user is vendor role in DB).

---

## 7. Data Model Overview

Key entities (see migrations for full columns):

- **roles** — id, name.  
- **users** — credentials, `role_id`, optional `restaurant_id`, OAuth fields.  
- **restaurants** — metadata, `user_id` (owner/manager link), `status`, fees.  
- **menu_categories**, **menu_items** — nested under restaurant.  
- **food_orders** — pricing snapshot fields, `status`, `delivery_rider_id`.  
- **food_order_items** — line items with denormalized `item_name`, `unit_price`.  
- **payments** — linked to `food_orders`.  
- **notifications** — per-user messages with JSON `data`.

Relationships are expressed in Eloquent (`User::restaurant()`, `FoodOrder::items()`, etc.).

---

## 8. API Surface (Summary)

| Area | Method | Endpoint (relative to API prefix) | Auth |
|------|--------|-------------------------------------|------|
| Auth | POST | `/auth/registerUser` | No |
| Auth | POST | `/auth/login` | No |
| Auth | GET/POST | profile, logout, change-password, etc. | Sanctum |
| Restaurants | GET | `/restaurants`, `/restaurants/{id}` | No |
| Restaurants | GET | `/admin/restaurants` | Sanctum (admin) |
| Restaurants | POST | `/admin/restaurants` | Sanctum (admin) |
| Riders | GET/POST | `/admin/riders` | Sanctum (admin) |
| Menu | various | `/restaurants/{id}/menu...` | Sanctum |
| Orders | GET/POST | `/orders` | Sanctum |
| Orders | PATCH | `/orders/{id}/status` | Sanctum |
| Payments | POST | `/payments` | Sanctum |
| Notifications | GET/POST | `/notifications`, `/notifications/read` | Sanctum |

*Swagger:* generated docs under L5-Swagger configuration (`storage/api-docs` when published).

---

## 9. User Interface Structure

### 9.1 Route Groups (Vue Router)

| Prefix | Layout component | Meta guard |
|--------|------------------|------------|
| `/` | `views/website/Home.vue` | Public |
| `/restaurant/:id` | `RestaurantDetail.vue` | Public |
| `/admin/*` | `views/admin/Home.vue` | `requiresAuth`, `requiresAdmin` |
| `/vendor/*` | `views/vendor/Home.vue` | `requiresAuth`, `requiresVendor` |
| `/customer/*` | `views/customer/Home.vue` | `requiresAuth`, `requiresCustomer` |
| `/rider/*` | `views/rider/Home.vue` | `requiresAuth`, `requiresRider` |
| `/login`, `/register`, … | Auth views | Public |

**Role-to-string mapping** in `auth_service.js`: `1 → admin`, `2 → vendor`, `3 → customer`, `4 → rider`. The router compares `getUserRole()` to these strings.

### 9.2 Shared Components

- `Loading.vue` — initial app shell.  
- `CartSidebar.vue` — global cart access.  
- `Notifications.vue`, `Settings.vue` — reused across personas with role-aware navigation.

### 9.3 Dashboard Redirect

`/dashboard` redirect reads `role_id` from the stored user object and sends users to the correct persona home.

---

## 10. Security Considerations

- **Password hashing** via Laravel casts (`'password' => 'hashed'` on `User`).  
- **Token revocation** on login to limit concurrent sessions (project choice).  
- **HTTPS** should be enforced in production; tokens in localStorage are vulnerable to XSS—mitigate with strict CSP, input sanitization, and dependency updates.  
- **Authorization** is partially duplicated (SPA guards + server checks); server-side checks are authoritative and must remain complete.  
- **Mass assignment** controlled via `$fillable` on models.

---

## 11. Limitations and Recommended Improvements

1. **Role ID consistency:** Align all seeders with `RoleSeeder` fixed IDs; avoid duplicate/conflicting `AdminSeeder` role rows in production databases.  
2. **Payment integration:** Current `PaymentController` records a completed payment; integrating Stripe/PayPal would require async confirmation, webhooks, and idempotency keys.  
3. **Socialite + SPA:** Callback uses server `redirect('/user/dashboard')` which may not match Vue routes; align OAuth success with token issuance for SPA.  
4. **Policies / Gates:** Replace scattered `role_id` integer checks with Laravel Policies for maintainability.  
5. **Real-time updates:** Notifications are pull-based (API polling); WebSockets (Laravel Echo + Pusher/Soketi) could improve rider and kitchen UX.  
6. **Legacy template files:** Some `views/manager/*` or “tailor” naming in older components may be residual from a starter template; exclude or refactor for thesis clarity.

---

## 12. Conclusion

The Online Food Ordering System demonstrates a complete **full-stack** workflow from catalogue browsing to order fulfilment and delivery status. The stack (Laravel + Vue + Sanctum) is widely documented and suitable for academic evaluation of **REST design**, **separation of concerns**, and **role-based workflows**. The present document can be split into standard thesis chapters: **Introduction**, **Related Work / Technologies**, **Requirements Analysis**, **System Design**, **Implementation**, **Testing & Results**, and **Conclusion & Future Work**.

---

## 13. References and Further Reading

*(Add formal citations in your institution’s required format.)*

- Laravel Documentation: https://laravel.com/docs  
- Vue.js Documentation: https://vuejs.org/  
- Laravel Sanctum: https://laravel.com/docs/sanctum  
- Fielding, R. T. *Architectural Styles and the Design of Network-based Software Architectures* (REST dissertation, 2000) — for theoretical REST grounding.

---

## Appendix A — Suggested Thesis Chapter Outline

1. **Introduction** — background, problem, objectives, report structure.  
2. **Literature Review** — existing food platforms, e-commerce patterns, security in SPAs.  
3. **Requirements** — functional/non-functional, use cases (UML use case diagram).  
4. **Methodology** — agile iterations, tools, version control.  
5. **System Design** — architecture diagrams (DFD Level 0/1), ER diagram from migrations, sequence diagrams for “place order” and “update status”.  
6. **Implementation** — screenshots of each role’s UI, key algorithms (pricing, status rules).  
7. **Testing** — manual test matrix, optional PHPUnit feature tests.  
8. **Conclusion** — achievements vs objectives, future work.

---

## Appendix B — Figures to Generate (Checklist)

- **Context diagram** — Customer, Vendor, Admin, Rider, System, Email/PDF.  
- **ER diagram** — users, roles, restaurants, menus, orders, payments, notifications.  
- **Order state machine** — nodes and legal transitions per role.  
- **Deployment diagram** — WAMP/Apache + MySQL + browser.

---

*End of document. Replace bracketed placeholders with your institutional metadata before submission.*
