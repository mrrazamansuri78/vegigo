# Vegigo API Documentation (Hinglish Guide)

Yeh document aapko samjhayega ki Vegigo project ki APIs kese use karni hain, data flow kese kaam karta hai, aur alag-alag roles (Farmer, Admin, Customer, Delivery Boy) ke liye system kese design kiya gaya hai.

---

## 1. System Overview (System Kese Kaam Karta Hai)

Is system me 4 main users hain:
1.  **Farmer (Kisan)**: Apni fasal (supply) admin ko bechne ke liye request dalta hai.
2.  **Admin**: Farmer ki request accept karta hai (stock badhta hai), aur Vendors ko stock allocate karta hai.
3.  **Customer**: App se products order karta hai (stock kam hota hai).
4.  **Delivery Boy**: Order pick karke customer tak deliver karta hai (live tracking ke sath).

**Real-time Features**:
-   Jab Admin farmer ka order accept karta hai -> Stock turant update hota hai (Firebase).
-   Jab Customer order karta hai -> Admin ko turant notification milta hai.
-   Delivery Boy jab chalta hai -> Map par live location update hoti hai.

---

## 2. Authentication (Login/Register)

Sabse pehle user ko login karna padega taaki uska `token` mil sake. Har API request me `Authorization: Bearer <token>` header bhejna zaroori hai.

*   **Register**: `POST /api/register`
    *   Fields: `name`, `email`, `password`, `role` (farmer, customer, delivery_boy, vendor).
*   **Login**: `POST /api/login`
    *   Fields: `email`, `password`.
    *   **Response**: Aapko ek `token` milega. Isko save karke rakhein.

---

## 3. Farmer Flow (Kisan Ke Liye)

Farmer apne products (sabzi/fal) admin ko bechne ke liye "Supply Order" create karta hai.

### API: Create Supply Order
*   **Endpoint**: `POST /api/supply-orders`
*   **Header**: `Authorization: Bearer <farmer_token>`
*   **Body**:
    ```json
    {
        "product_id": 1,
        "quantity": 100,
        "unit": "kg"
    }
    ```
*   **Kya Hoga**:
    1.  Admin ke paas request jayegi (Status: `pending`).
    2.  Firebase pe `supply_orders` node me real-time entry aayegi.

---

## 4. Admin Inventory Management

Admin farmers ki request handle karta hai aur stock manage karta hai.

### API: Get All Supply Orders
*   **Endpoint**: `GET /api/admin/inventory/supply-orders`
*   **Use**: Pending orders dekhne ke liye.

### API: Update Supply Status (Accept/Reject)
*   **Endpoint**: `PUT /api/admin/inventory/supply-orders/{id}`
*   **Body**:
    ```json
    {
        "status": "accepted",  // ya "rejected"
        "admin_note": "Quality check passed"
    }
    ```
*   **Kya Hoga (Important)**:
    1.  Agar `accepted` kiya, to `products` table me **Stock Quantity increase** ho jayegi automatically.
    2.  Firebase pe `products/{id}` update hoga taaki customer ko naya stock dikhe.

### API: Allocate Stock to Vendor
*   **Endpoint**: `POST /api/admin/inventory/allocate`
*   **Body**:
    ```json
    {
        "vendor_id": 5,
        "product_id": 1,
        "quantity": 50
    }
    ```
*   **Kya Hoga**: Main stock me se quantity kam (decrement) ho jayegi aur Vendor ke naam par allocate ho jayegi.

---

## 5. Customer Flow (Order & Live Stock)

Customer ko wahi products dikhenge jo active hain aur stock me hain.

### API: Get Products (Real-time Stock)
*   **Endpoint**: `GET /api/products`
*   **Note**: Yeh list Firebase se sync rehti hai. Agar admin ne abhi stock badhaya, to customer ko refresh karne ki zaroorat nahi (agar frontend Firebase listener use kar raha hai).

### API: Place Order
*   **Endpoint**: `POST /api/orders`
*   **Body**:
    ```json
    {
        "items": [
            {"product_id": 1, "quantity": 2},
            {"product_id": 2, "quantity": 5}
        ],
        "address": "123 Green Street"
    }
    ```
*   **Kya Hoga**:
    1.  Stock kam ho jayega.
    2.  Admin ko `orders` node pe real-time notification milega.

---

## 6. Delivery Boy Flow (Live Tracking)

Delivery boy order deliver karega aur uski location live track hogi.

### API: Dashboard
*   **Endpoint**: `GET /api/delivery-boy/dashboard`
*   **Use**: Aaj ke active orders aur pickup requests dekhne ke liye.

### API: Update Location (Background Service)
*   **Endpoint**: `POST /api/delivery-boy/update-location`
*   **Body**:
    ```json
    {
        "current_latitude": 12.9716,
        "current_longitude": 77.5946
    }
    ```
*   **Kya Hoga**: Firebase pe `orders/{order_id}/location` update hoga. Admin map pe delivery boy ko chalte hue dekh payega.

### API: Update Order Status
*   **Endpoint**: `POST /api/delivery-boy/orders/{id}/picked-up` (Pickup ke waqt)
*   **Endpoint**: `POST /api/delivery-boy/orders/{id}/delivered` (Delivery ke waqt)

---

## 7. Technical Data Flow (Summary)

1.  **Database**: MySQL (Main data store).
2.  **Real-time**: Firebase Realtime Database.
    *   Jab bhi MySQL me koi change hota hai (Stock update, Order status), Backend automatically Firebase ko update kar deta hai.
3.  **Backend**: Laravel 12.

### Setup Instructions
1.  Project folder me `.env` file check karein aur Database credentials sahi karein.
2.  Migrations run karein: `php artisan migrate`.
3.  Server start karein: `php artisan serve`.
4.  App use karna shuru karein!

---
Agar koi dikkat aaye to `Laravel.log` file check karein ya `php artisan migrate:refresh` karke database reset karein.
