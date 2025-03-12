# Event Ticketing API

A RESTful API for event ticketing system built with Laravel, featuring user authentication, event management, and order processing with age verification.

## Features

- 🔐 **JWT Authentication** using Laravel Sanctum
- 🎟️ **Event Management** with ticket categories/types
- 🛒 **Order System** with ticket validation:
  - Age verification for restricted tickets
  - ID verification requirements
  - Real-time quota management
- 👤 **User Management**:
  - Profile viewing
  - Order history
  - Secure logout
- ✅ **Validation & Error Handling**:
  - Structured JSON error responses
  - Rate limiting (3 requests/minute)
  - CSRF protection

## API Endpoints

### Authentication
| Method | Endpoint               | Description                |
|--------|------------------------|----------------------------|
| POST   | `/api/v1/auth/login`   | User login                 |
| POST   | `/api/v1/auth/register`| New user registration      |
| POST   | `/api/v1/users/{user}/logout` | User logout       |

### Events
| Method | Endpoint               | Description                |
|--------|------------------------|----------------------------|
| GET    | `/api/v1/events`       | List all events            |
| GET    | `/api/v1/events/{event}`| Get specific event details |

### Orders
| Method | Endpoint               | Description                |
|--------|------------------------|----------------------------|
| POST   | `/api/v1/orders/create`| Create new order           |
| PATCH  | `/api/v1/orders/{order}/pay` | Mark order as paid |

### Users
| Method | Endpoint               | Description                |
|--------|------------------------|----------------------------|
| GET    | `/api/v1/users/{users}`| Get user profile           |
| GET    | `/api/v1/users/{user}/orders` | Get user's orders |


## Getting Started
### Prerequisites

- PHP 8.1+
- Composer
- MySQL 5.7+

