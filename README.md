# Order Processing System

## Overview

The **Order Processing System** is a RESTful API built with Laravel 12, designed
to manage order creation, inventory updates, and stock alerts for a foodtech
application. It processes orders for products (e.g., burgers), deducts
ingredient stocks, and queues email notifications when stock levels drop below
50% of their initial values. The system adheres to **Clean Architecture**
principles, ensuring scalability, testability, and compliance with SOLID
principles, tailored for large-scale, robust backend services.

This project showcases a practical foodtech solution with a focus on high
availability, low latency, and data consistency—key requirements for modern
backend systems.

## Why Clean Architecture?

- **SOLID Principles**:
  - **Single Responsibility**: Use cases (e.g., `PlaceOrderUseCase`) encapsulate
    distinct business logic.
  - **Dependency Inversion**: Interfaces (`OrderRepositoryInterface`,
    `StockNotifierInterface`) decouple domain logic from infrastructure.
- **Scalability**: Separated layers support future enhancements like caching or
  distributed queues.
- **Testability**: Unit tests for business logic (`PlaceOrderUseCaseTest`) and
  feature tests for API flows (`OrderTest`) ensure reliability.
- **High Availability**: Queued email alerts minimize latency and enhance fault
  tolerance.
- **Foodtech Fit**: Built for a burger order system, extensible for complex
  foodtech workflows (e.g., multi-restaurant inventory).

## Setup

### Prerequisites

- PHP 8.2+
- Composer 2.x
- Laravel 12.x
- Database (e.g., MySQL, SQLite)
- Mail service (e.g., Mailtrap for testing)
- Queue driver (e.g., `database` for email queuing)

### Installation

1. **Clone the Repository**:

   ```bash
   git clone git@github.com:moabuelnour/order-system.git
   cd order-processing-system
   ```

2. **Install Dependencies**:

   ```bash
   composer install
   ```

3. **Configure Environment**:

   Copy `.env.example` to `.env`:

   ```bash
   cp .env.example .env
   ```

   Update `.env` with database, mail, and queue settings:

   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=food-order
   DB_USERNAME=your-database-username
   DB_PASSWORD=your-adatabase-password

   MAIL_MAILER=smtp
   MAIL_HOST=smtp.mailtrap.io
   MAIL_PORT=2525
   MAIL_USERNAME=your-mailtrap-username
   MAIL_PASSWORD=your-mailtrap-password
   MAIL_FROM_ADDRESS="inventory@order-system.com"
   MAIL_FROM_NAME="Order System"

   QUEUE_CONNECTION=database
   ```

4. **Generate Application Key**:

   ```bash
   php artisan key:generate
   ```

5. **Run Migrations**:

   ```bash
   php artisan migrate
   ```

6. **Seed Initial Data**:

   Run the seeder:

   ```bash
   php artisan db:seed
   ```

7. **Start Up Queue**:

   ```bash
   php artisan queue:work
   ```

8. **Start the Server**:

   ```bash
   php artisan serve
   ```

   The API will be available at http://localhost:8000.

---

## Key Features

- **Order Creation**: Processes orders via `POST /api/orders`, deducting
  ingredient stock atomically using database transactions.
- **Stock Management**: Updates ingredient stocks (e.g., Beef -150g per burger)
  with sufficiency checks.
- **Low Stock Alerts**: Queues a single email when stock falls below 50% of
  initial value, tracked via `stock_alert_sent`.
- **API Resources**: Uses `OrderResource` and `ProductResource` for structured,
  reusable JSON responses.
- **Unified Error Handling**: Consistent JSON error responses for validation,
  domain, and server errors.
- **Tests**: Comprehensive unit and feature tests for business logic and API
  workflows.

---

## API Endpoints

### `POST /api/orders`

**Description**: Creates an order, updates stock levels, and returns detailed
order data.

**Request Body**:

```json
{
  "products": [
    {
      "product_id": 1,
      "quantity": 2
    }
  ]
}
```

**Success Response (201)**:

```json
{
    "data" {
        "id": 1,
        "products": [
            {
                "product_id": 1,
                "quantity": 2,
                "name": "Burger"
            }
        ]
    }
}
```

**Error Response (422)**:

```json
{
  "message": "Insufficient stock for Onion",
  "errors": {
    "stock": ["Insufficient stock for Onion"]
  }
}
```

---

## Testing

### Running Tests

**Configure Test Environment**:

Copy `.env` to `.env.testing` and adjust (e.g., use SQLite in-memory):

```env
DB_CONNECTION=sqlite
DB_DATABASE=:memory:
```

**Execute Tests**:

```bash
php artisan test
```

### Test Suite

- **Unit Tests** (`tests/Unit/PlaceOrderUseCaseTest.php`):
  - Tests `PlaceOrderUseCase` for stock updates, email triggering, and
    insufficient stock exceptions.
- **Feature Tests** (`tests/Feature/OrderTest.php`):
  - Verifies API behavior:
    - Order creation with full order data response.
    - Stock updates on successful orders.
    - Email queuing for low stock.
    - No duplicate emails for alerted ingredients.
    - Rejection of orders with insufficient stock.
    - Validation errors for invalid inputs.

> **Note**: The `RefreshDatabase` trait ensures test isolation by resetting the
> database between tests.

```
   PASS  Tests\Unit\PlaceOrderUseCaseTest
  ✓ place order updates stock and notifies                               0.08s  
  ✓ insufficient stock throws exception                                  0.01s  

   PASS  Tests\Feature\OrderTest
  ✓ order is stored and stock updated                                    1.12s  
  ✓ email queued when stock below 50 percent                             0.03s  
  ✓ no duplicate email below 50 percent                                  0.03s  
  ✓ order fails with insufficient stock                                  0.03s
```

---

## Error Handling

Errors are returned in a consistent JSON format:

```json
{
  "message": "Error description",
  "errors": {
    "field_or_context": ["Detailed message"]
  }
}
```

### Validation Error

```json
{
  "message": "The selected products.0.product_id is invalid.",
  "errors": {
    "products.0.product_id": ["The selected products.0.product_id is invalid."]
  }
}
```

### Domain Exception

```json
{
  "message": "Insufficient stock for Onion",
  "errors": {
    "stock": ["Insufficient stock for Onion"]
  }
}
```

### Server Error

```json
{
  "message": "An unexpected error occurred",
  "errors": {
    "server": ["Internal server error"]
  }
}
```
