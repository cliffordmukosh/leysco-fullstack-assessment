# L-SalesPro Fullstack System

## Project Overview

L-SalesPro is a fullstack sales automation system developed as part of the Leysco Technical Assessments.
It consists of a Laravel RESTful API backend and a Vue.js frontend application, designed to manage inventory,
sales orders, customers, analytics, and notifications.

This repository is structured as a monorepo, with each project containing its own detailed documentation.

---

## Repository Structure

- **leysco/**
  - **l-salespro-api-backend/**
    - [Backend README](./l-salespro-api-backend/README.md)
  - **l-salesview-mini/**
    - [Frontend README](./l-salesview-mini/README.md)
  - [Root README](./README.md)

---

## System Architecture

Frontend (Vue 3)

Backend (Laravel API)
- Authentication & Authorization
- Sales Orders & Credit Validation
- Inventory & Multi-Warehouse Stock
- Dashboard Analytics
- Customers & Mapping Data
- Notifications (Queued Emails)

---

## Projects Included

### 1. L-SalesPro API Backend

**Path**
```
l-salespro-api-backend/
```

A production-ready Laravel API responsible for business logic, authentication, data persistence,
analytics, and notifications.

Refer to `l-salespro-api-backend/README.md` for full documentation.

---

### 2. L-SalesView Mini (Frontend)

**Path**
```
l-salesview-mini/
```

A Vue 3 single-page application providing a modern and responsive sales automation interface.

Refer to `l-salesview-mini/README.md` for full documentation.

---

## Running the System

### Backend Setup

```
cd l-salespro-api-backend
composer install
php artisan migrate --seed
php artisan serve
```

**API Base URL**
```
http://localhost:8000/api/v1
```

---

### Frontend Setup

```
cd l-salesview-mini
npm install
npm run dev
```

**Application URL**
```
http://localhost:5173
```

---

## Author

**Clifford Mukosh**  
Fullstack Technical Assessment Submission  
Leysco Limited – 2025
