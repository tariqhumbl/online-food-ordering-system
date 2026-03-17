# Order Flow: Customer, Vendor & Rider

## Roles and links

| Role        | Who                         | Link to others                          |
|------------|-----------------------------|-----------------------------------------|
| **Customer** | Places orders               | Order has `user_id` (customer), `restaurant_id` (vendor’s restaurant) |
| **Vendor**   | Restaurant / hotel manager  | Order has `restaurant_id`; vendor manages menu and order status      |
| **Rider**    | Delivery person             | Order has `delivery_rider_id` (rider); rider delivers to customer    |

The **order** (`FoodOrder`) connects all three: **customer** (who ordered), **vendor** (restaurant), and **rider** (who delivers).

---

## Order lifecycle

1. **Customer** places order → status `pending`; customer and vendor get notifications.
2. **Vendor** updates status: `confirmed` → `preparing` → **`ready`** (ready for pickup).
3. When status is **`ready`**, the order appears for **riders** under *Available for delivery*; all riders get a notification.
4. **Rider** accepts (clicks “Take delivery”) → order is assigned to that rider (`delivery_rider_id` set), status → `out_for_delivery`.
5. **Rider** delivers and clicks “Mark delivered” → status `delivered`; customer gets a notification.

Riders can only:
- See orders that are **ready** and unassigned, or **assigned to them**.
- Set status to **`out_for_delivery`** (accept) or **`delivered`** (complete). Only the assigned rider can mark an order as delivered.

Vendors manage status from **pending** up to **ready**; riders manage **out_for_delivery** and **delivered**.
