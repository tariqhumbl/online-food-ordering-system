# Online Food Ordering System – Project Structure & Flow

## Overview

**Project Name:** Online Food Ordering System  
**Tech Stack:** Laravel 11 (Backend API) + Vue 3 (Frontend SPA) + MySQL/PostgreSQL  
**Architecture:** RESTful API with SPA frontend (Vite + Vue Router + Vuex)

The app allows customers to browse restaurants and menus, place orders, and track deliveries. Admins manage restaurants and orders; vendors manage their restaurant and menu; delivery riders manage delivery status.

---

## Admin vs manager (vendor) restaurant flow

- **Admin creates restaurant + manager:** In Admin → Restaurants → Add Restaurant, the admin enters restaurant details **and** the manager’s name and email. The system creates a new **Vendor** user, creates the restaurant linked to that user, and sends an email to the manager with **login credentials** (email + temporary password). The manager can then log in, go to the manager portal, add the menu, and receive orders. This is the recommended flow when the admin onboard a new restaurant.
- **Manager self-registration:** A user can register as Vendor and then create their own restaurant in Manager portal → My Restaurant (no email sent; they already have an account).

## Landing page ↔ Admin & vendor restaurants

- **Landing page** (`http://127.0.0.1:8000/`) shows **Featured restaurants** from the public API `GET /api/restaurants`, which returns only restaurants with **status = active**.
- **Admin** (Admin → Restaurants): can create restaurants (with manager email → credentials sent) and set **Status** to **Active** when editing. Only **Active** restaurants appear on the landing.
- **Vendor** (Manager portal → My Restaurant): can create their restaurant and add **Menu** (categories & items). New restaurants are created with status **Pending** and do **not** appear on the landing until an **admin** sets them to **Active** (Admin → Restaurants → Edit → Status: Active).
- **Link flow:** Landing → each restaurant card links to **`/restaurant/{id}`** (public restaurant detail). That page loads the same restaurant and its **menu** (categories & items) via `GET /api/restaurants/{id}`. So the menu the vendor added in Manager → Menu is what visitors see and can add to cart from.

**Summary:** When admin creates a restaurant and enters the manager’s email, credentials are sent by email; the manager logs in and manages menu and orders. To show a restaurant on the landing, set **Status** to **Active** in Admin → Restaurants.

---

## User Roles

| role_id | Role            | Path prefix | Description                          |
|--------:|-----------------|-------------|--------------------------------------|
| 1       | Admin           | /admin      | Manage restaurants, orders, settings |
| 2       | Vendor          | /vendor     | Manage own restaurant, menu, orders   |
| 3       | Customer        | /customer   | Browse, order, pay, track food        |
| 4       | Delivery Rider  | /rider      | View and update delivery status       |

---

## Backend (Laravel)

### API Routes (`routes/api.php`)

- **Auth:** `/api/auth/*` (login, register, forgot/reset password, profile, logout, change-password, get-user-role, OAuth).
- **Public:** `GET /api/restaurants`, `GET /api/restaurants/{id}` (list & show with menu).
- **Protected (auth:sanctum):**
  - Restaurants: POST/PUT/DELETE `/api/restaurants`, GET `/api/restaurants/{id}/menu`
  - Menu: POST/PUT/DELETE menu-categories and menu-items under a restaurant
  - Orders: GET/POST `/api/orders`, GET `/api/orders/{id}`, PATCH `/api/orders/{id}/status`
  - Payments: POST `/api/payments`

### Models

- **User** (role_id, restaurant_id for vendors), **Role**
- **Restaurant**, **MenuCategory**, **MenuItem**
- **FoodOrder**, **FoodOrderItem**, **Payment**

### Migrations

- `2026_02_28_100000_create_restaurants_table`
- `2026_02_28_100001_create_menu_categories_table`
- `2026_02_28_100002_create_menu_items_table`
- `2026_02_28_100003_create_food_orders_table`
- `2026_02_28_100004_create_food_order_items_table`
- `2026_02_28_100005_create_payments_table`
- `2026_02_28_100006_add_restaurant_id_to_users_table`

Run: `php artisan migrate` (and `php artisan db:seed --class=RoleSeeder` for roles).

### Controllers

- **Auth:** `AuthController`, `SocialiteController`
- **API:** `App\Http\Controllers\API\RestaurantController`, `MenuCategoryController`, `MenuItemController`, `FoodOrderController`, `PaymentController`

---

## Frontend (Vue 3)

### Router

- **Public:** `/`, `/login`, `/register`, `/forgot-request`, `/reset-password`, `/change-password`, `/update-profile`
- **Dashboard redirect:** `/dashboard` → redirects by role to admin/vendor/customer/rider dashboard
- **Admin:** `/admin/*` (dashboard, restaurants, orders)
- **Vendor:** `/vendor/*` (dashboard, restaurant, menu, orders)
- **Customer:** `/customer/*` (dashboard, restaurants, restaurants/:id menu, cart, checkout, orders, orders/:id tracking)
- **Rider:** `/rider/*` (dashboard, deliveries)

### Services

- **auth_service.js** – login, register, token/role/user (roleMapping: 1=admin, 2=vendor, 3=customer, 4=rider)
- **http_service.js** – axios with Bearer token, `store.state.apiURL`
- **api_service.js** – `API.restaurants`, `API.orders`, `API.payments` for frontend calls

### Views (implemented)

- **Admin:** Dashboard, Restaurants (full CRUD, filters, pagination), Orders (list, view, update status)
- **Vendor:** Dashboard, Restaurant (create/edit), Menu (categories + items CRUD), Orders (list, view, update status)
- **Customer:** Dashboard, RestaurantList (browse), RestaurantMenu (add to cart), Cart, Checkout (place order), Orders, OrderTracking
- **Rider:** Dashboard, Deliveries (my deliveries + available, take delivery, mark delivered)

### Sidebar

- Role-based menu items (Restaurants, Orders, Menu, My Restaurant, etc.) with correct links per role.

---

## Implemented features

- **Vuex cart:** state, add/update/remove items, persist to localStorage, clear on order place.
- **Customer flow:** browse restaurants, view menu, add to cart (with replace-cart confirm), cart page, checkout (delivery address, place order, optional payment record), orders list with filter, order tracking with status steps.
- **Vendor flow:** create/edit restaurant, menu categories and items CRUD, orders list with view and status update.
- **Rider flow:** list my deliveries and available orders, take delivery (out_for_delivery), mark delivered.

## Next Steps (optional)

1. Payment gateway integration (Stripe etc.); currently cash/placeholder.
2. Notifications (email/SMS/push).
3. Admin dashboard with real metrics and reports.
4. Admin user management.
5. Tests (PHPUnit, feature tests) and deploy.

---

## Security & performance (from doc)

- Laravel Sanctum for API auth
- Input validation on all requests
- Rate limiting and caching (Redis) can be added
- Queue jobs for emails/notifications

This structure follows the **Online Food Ordering System** documentation and keeps your existing auth and project flow.
