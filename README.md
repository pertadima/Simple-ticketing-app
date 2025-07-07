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
| Method | Endpoint                       | Description                        |
|--------|------------------------------- |------------------------------------|
| POST   | `/api/v1/auth/login`           | User login                         |
| POST   | `/api/v1/auth/register`        | New user registration              |
| POST   | `/api/v1/auth/reset-password`  | Request password reset OTP         |
| POST   | `/api/v1/auth/validate-otp`    | Validate OTP for password reset    |
| POST   | `/api/v1/auth/change-password` | Change password with OTP           |
| POST   | `/api/v1/users/{user}/logout`  | User logout (requires auth)        |

### Events
| Method | Endpoint                                         | Description                        |
|--------|--------------------------------------------------|------------------------------------|
| GET    | `/api/v1/events`                                 | List all events                    |
| GET    | `/api/v1/events/{event}`                         | Get specific event details         |
| GET    | `/api/v1/events/{event}/types/{type}/seats`      | Get available seats for event/type |

### Categories
| Method | Endpoint                                         | Description                        |
|--------|--------------------------------------------------|------------------------------------|
| GET    | `/api/v1/categories`                             | List all categories                |
| GET    | `/api/v1/categories/{category}/events`           | List events by category            |

### Orders
| Method | Endpoint                                         | Description                        |
|--------|--------------------------------------------------|------------------------------------|
| POST   | `/api/v1/orders/create`                          | Create new order (requires auth)   |
| PATCH  | `/api/v1/orders/{order}/pay`                     | Mark order as paid (requires auth) |

### Users
| Method | Endpoint                                         | Description                        |
|--------|--------------------------------------------------|------------------------------------|
| GET    | `/api/v1/users/{users}`                          | Get user profile (requires auth)   |
| GET    | `/api/v1/users/{user}/orders`                    | Get user's orders (requires auth)  |

## Getting Started
### Prerequisites

- PHP 8.1+
- Composer
- MySQL 5.7+

