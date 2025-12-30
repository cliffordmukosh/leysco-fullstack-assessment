# L-SalesPro API Backend

## Project Overview

This is the backend RESTful API for **L-SalesPro**, a sales automation system built with Laravel. It manages:

- User authentication and role-based access
- Dashboard analytics
- Inventory and multi-warehouse stock management
- Sales orders with credit and stock validation
- Customer management with credit limits and mapping
- Warehouse stock transfers
- User notifications with queued emails

### Key Technologies

- Laravel 11.x
- Laravel Sanctum (token-based authentication)
- MySQL
- Redis (caching)
- Laravel Queues (database driver)
- Repository and Service pattern
- API Resources and Form Requests

---

## Setup Instructions

### 1. Clone Repository
```bash
git clone 
cd l-salespro-api-backend
```

### 2. Install Dependencies
```bash
composer install
```

### 3. Environment Configuration
```bash
cp .env.example .env
```
Update `.env` with database, Redis, and mail settings.

### 4. Generate Application Key
```bash
php artisan key:generate
```

### 5. Run Migrations and Seeders
```bash
php artisan migrate --seed
```

### 6. Start Development Server
```bash
php artisan serve
```

### 7. Run Queue Worker (Recommended)
```bash
php artisan queue:work
```

---

## Base API URL
```
http://localhost:8000/api/v1
```

## Authentication Routes

| Method | Endpoint | Controller |
|------|---------|------------|
| POST | `/auth/login` | AuthController@login |
| POST | `/auth/logout` | AuthController@logout |
| POST | `/auth/refresh` | AuthController@refresh |
| GET | `/auth/user` | AuthController@user |
| POST | `/auth/password/forgot` | AuthController@forgotPassword |
| POST | `/auth/password/reset` | AuthController@resetPassword |

---

## Customer Management

| Method | Endpoint | Controller |
|------|---------|------------|
| GET | `/customers` | CustomerController@index |
| POST | `/customers` | CustomerController@store |
| GET | `/customers/map-data` | CustomerController@mapData |
| GET | `/customers/{id}` | CustomerController@show |
| PUT | `/customers/{id}` | CustomerController@update |
| DELETE | `/customers/{id}` | CustomerController@destroy |
| GET | `/customers/{id}/credit-status` | CustomerController@creditStatus |
| GET | `/customers/{id}/orders` | CustomerController@orders |

---

## Dashboard & Analytics

| Method | Endpoint | Controller |
|------|---------|------------|
| GET | `/dashboard/summary` | DashboardController@summary |
| GET | `/dashboard/inventory-status` | DashboardController@inventoryStatus |
| GET | `/dashboard/sales-performance` | DashboardController@salesPerformance |
| GET | `/dashboard/top-products` | DashboardController@topProducts |

---

## Products & Inventory

| Method | Endpoint | Controller |
|------|---------|------------|
| GET | `/products` | ProductController@index |
| POST | `/products` | ProductController@store |
| GET | `/products/low-stock` | ProductController@lowStock |
| GET | `/products/{id}` | ProductController@show |
| PUT | `/products/{id}` | ProductController@update |
| DELETE | `/products/{id}` | ProductController@destroy |
| GET | `/products/{id}/stock` | ProductController@stock |
| POST | `/products/{id}/reserve` | ProductController@reserve |
| POST | `/products/{id}/release` | ProductController@release |

---

## Orders

| Method | Endpoint | Controller |
|------|---------|------------|
| GET | `/orders` | OrderController@index |
| POST | `/orders` | OrderController@store |
| POST | `/orders/calculate-total` | OrderController@calculateTotal |
| GET | `/orders/{id}` | OrderController@show |
| GET | `/orders/{id}/invoice` | OrderController@invoice |
| PUT | `/orders/{id}/status` | OrderController@updateStatus |

---

## Warehouses & Stock Transfers

| Method | Endpoint | Controller |
|------|---------|------------|
| GET | `/warehouses` | WarehouseController@index |
| GET | `/warehouses/{id}/inventory` | WarehouseController@inventory |
| GET | `/stock-transfers` | WarehouseController@transferHistory |
| POST | `/stock-transfers` | WarehouseController@storeTransfer |

---

## Notifications

| Method | Endpoint | Controller |
|------|---------|------------|
| GET | `/notifications` | NotificationController@index |
| GET | `/notifications/unread-count` | NotificationController@unreadCount |
| PUT | `/notifications/read-all` | NotificationController@markAllAsRead |
| PUT | `/notifications/{id}/read` | NotificationController@markAsRead |
| DELETE | `/notifications/{id}` | NotificationController@destroy |

---
### Authorization Header
```
Authorization: Bearer <token>
```

Token is obtained via:
```
POST /auth/login
```

---

## Consistent API Response Format
```json
{
  "success": true,
  "data": {},
  "message": "Success message",
  "errors": {}
}
```

### Common HTTP Status Codes

- 200 OK
- 201 Created
- 204 No Content
- 400 Bad Request
- 401 Unauthorized
- 403 Forbidden
- 404 Not Found
- 422 Unprocessable Entity
- 429 Too Many Requests
- 500 Server Error

---

## API Endpoints

## 1. Authentication

### POST /auth/login
**Sample Payload**
```json
{
  "email": "david.kariuki@leysco.co.ke",
  "password": "SecurePass123!"
}
```

**Success Response (200)**
```json
{
  "success": true,
  "data": {
    "token": "1|randomlongtokenhere",
    "user": {
      "id": 1,
      "username": "LEYS-1001",
      "role": "Sales Manager"
    }
  },
  "message": "Login successful"
}
```

**Errors:** 422 (Invalid credentials), 429 (Rate limited)

---

### POST /auth/logout
**Response (200)**
```json
{ "success": true, "message": "Logged out" }
```

### POST /auth/refresh
**Response (200)**
```json
{ "success": true, "data": { "token": "new-token" } }
```

### GET /auth/user
Returns authenticated user profile.

---

## 2. Dashboard Analytics

### GET /dashboard/summary
**Response (200)**
```json
{
  "success": true,
  "data": {
    "total_sales": 1500000,
    "total_orders": 320,
    "average_order_value": 4687.50,
    "inventory_turnover_rate": 2.4
  },
  "message": "Dashboard summary"
}
```

---

## 3. Inventory Management

### POST /products
**Sample Payload**
```json
{
  "sku": "NEW-PROD-10W40",
  "name": "Ultra Synthetic 10W-40",
  "category_id": 1,
  "subcategory": "Synthetic Oils",
  "description": "Premium full synthetic engine oil",
  "price": 6800.00,
  "tax_rate": 16.00,
  "unit": "Liter",
  "packaging": "4L Jug",
  "min_order_quantity": 2,
  "reorder_level": 25
}
```

**Response (201)**
```json
{
  "success": true,
  "data": {
    "id": 10,
    "sku": "NEW-OIL-10W40",
    "name": "Ultra Synthetic 10W-40"
  },
  "message": "Product created successfully"
}
```

---

## 4. Sales Order Management

### POST /orders
**Sample Payload**
```json
{
  "customer_id": 1,
  "items": [
    {
      "product_id": 1,
      "quantity": 5,
      "unit_price": 4500.00,
      "discount_type": "percentage",
      "discount_value": 10.0,
      "tax_rate": 16.0
    },
    {
      "product_id": 1,
      "quantity": 2,
      "unit_price": 7200.00,
      "discount_type": "fixed",
      "discount_value": 500.00,
      "tax_rate": 16.0
    }
  ],
  "discount": {
    "type": "percentage",
    "value": 5.0
  },
  "notes": "Deliver to Mombasa branch",
  "warehouse_id": 1     
}
```

**Response (201)**
```json
{
  "success": true,
  "data": {
    "order_number": "ORD-2025-06-001",
    "status": "Pending",
    "total_amount": 37362.00
  },
  "message": "Order created successfully"
}
```

---

## 5. Customer Management

### POST /customers
**Sample Payload**
```json
{
  "name": "Elite Motors Ltd",
  "type": "Dealership",
  "category": "A+",
  "contact_person": "Mary Wambui",
  "phone": "+254-722-987654",
  "email": "mary@elitemotors.co.ke",
  "tax_id": "P051987654S",
  "payment_terms": 45,
  "credit_limit": 1500000.00,
  "current_balance": 0.00,
  "latitude": -1.292066,
  "longitude": 36.821946,
  "address": "Uhuru Highway, Elite Towers, Nairobi",
  "territory": "Nairobi Central"
}
```

**Response (201)**
```json
{
  "success": true,
  "data": {
    "id": 5,
    "name": "Elite Motors Ltd"
  },
  "message": "Customer created successfully"
}
```

---

## 6. Warehouse Management

### POST /stock-transfers
**Sample Payload**
```json
{
  "from_warehouse_id": 1,
  "to_warehouse_id": 2,
  "product_id": 1,
  "quantity": 200,
  "notes": "Urgent transfer"
}
```
### POST /stock-transfers INITIAL CREATE (BASED ON ASSUMPTION TO CREATE INVENTORY)
**Sample Payload**
```json
{
  "from_warehouse_id": 1,
  "to_warehouse_id": null,
  "product_id": 1,
  "quantity": 200,
  "notes": "Urgent transfer"
}
```
**Response (201)**
```json
{
  "success": true,
  "data": {
    "transfer_id": 12,
    "status": "Completed"
  },
  "message": "Stock transferred successfully"
}
```

---

## 7. Notifications

### GET /notifications
**Response (200)**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "type": "low_stock",
      "message": "Product stock below reorder level",
      "read": false
    }
  ],
  "message": "Notifications retrieved"
}
```

---

## Postman Collection

Path:
```
https://www.postman.com/cliffordm/workspace/clifford-mukosh/collection/32692970-f6b80827-6f3d-4055-b7f4-3cd2f084916f?action=share&source=copy-link&creator=32692970
```

Environment variables:
- base_url
- token

---

## Troubleshooting

- 401 Unauthorized: Missing or invalid token
- 422 Validation Error: Check errors object
- Slow responses: Ensure Redis is running
- Emails not sending: CHECK LARAVEL LOGS 

---

© 2025 Cliff – Submitted for Leysco Backend Developer (Laravel) Assessment
