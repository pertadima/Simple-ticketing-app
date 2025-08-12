# EventsCategoryController Test Documentation

## Overview
Comprehensive test suite for the `EventsCategoryController` including both Feature and Unit tests.

## Test Files Created

### 1. Feature Tests (`tests/Feature/EventsCategoryControllerTest.php`)
Tests the controller through HTTP requests, simulating real user interactions.

#### Test Cases:
- **`test_index_returns_all_categories()`**: Verifies that the index endpoint returns all categories correctly
- **`test_index_returns_empty_when_no_categories()`**: Tests behavior when no categories exist
- **`test_events_by_category_returns_events_for_valid_category()`**: Tests retrieving events for a valid category
- **`test_events_by_category_returns_empty_when_no_events()`**: Tests behavior when category has no events
- **`test_events_by_category_with_nonexistent_category()`**: Tests behavior with non-existent category ID
- **`test_events_by_category_pagination()`**: Tests pagination functionality (10 items per page)
- **`test_events_by_category_ordered_by_date()`**: Verifies events are ordered by date ascending
- **`test_events_by_category_includes_relationships()`**: Tests that relationships (categories, images) are included
- **`test_api_endpoints_are_accessible()`**: Basic accessibility test
- **`test_events_by_category_only_returns_events_for_specified_category()`**: Tests category filtering

### 2. Unit Tests (`tests/Unit/EventsCategoryControllerUnitTest.php`)
Tests the controller methods in isolation, focusing on logic and data structures.

#### Test Cases:
- **`test_index_method_returns_correct_structure()`**: Tests JSON response structure
- **`test_index_method_with_empty_categories()`**: Tests empty categories response
- **`test_events_by_category_method_returns_correct_structure()`**: Tests response structure
- **`test_events_by_category_with_nonexistent_category()`**: Tests non-existent category handling
- **`test_pagination_metadata_calculation()`**: Tests pagination metadata calculation
- **`test_events_filtered_by_category()`**: Tests category filtering logic
- **`test_events_ordered_by_date_ascending()`**: Tests date ordering
- **`test_events_category_resource_usage()`**: Tests resource transformation
- **`test_events_resource_usage()`**: Tests event resource transformation
- **`test_query_includes_relationships()`**: Tests relationship loading
- **`test_pagination_uses_correct_parameters()`**: Tests pagination parameters
- **`test_controller_method_signatures()`**: Tests method signatures

## Supporting Files Created

### 3. EventCategoriesFactory (`database/factories/EventCategoriesFactory.php`)
Factory for creating test EventCategories with realistic data.

#### Features:
- Predefined realistic category names
- State methods for specific categories (music, sports, technology)
- Proper timestamps

### 4. Updated EventCategories Model
Added `HasFactory` trait to enable factory usage.

## API Endpoints Tested

### GET `/api/v1/categories`
Returns all event categories.

**Expected Response:**
```json
{
    "data": {
        "categories": [
            {
                "id": 1,
                "name": "Music"
            }
        ]
    }
}
```

### GET `/api/v1/categories/{categoryId}/events`
Returns paginated events for a specific category.

**Expected Response:**
```json
{
    "data": {
        "events": [
            {
                "id": 1,
                "name": "Event Name",
                "date": "2025-08-15",
                "location": "Location",
                "description": "Description",
                "images": [],
                "categories": []
            }
        ]
    },
    "meta": {
        "current_page": 1,
        "last_page": 1,
        "per_page": 10,
        "total": 1,
        "is_next_page": false,
        "is_prev_page": false
    }
}
```

## Running the Tests

### Run All Tests:
```bash
./test-events-category.sh
```

### Run Feature Tests Only:
```bash
XDEBUG_MODE=coverage /opt/homebrew/bin/php vendor/bin/phpunit tests/Feature/EventsCategoryControllerTest.php
```

### Run Unit Tests Only:
```bash
XDEBUG_MODE=coverage /opt/homebrew/bin/php vendor/bin/phpunit tests/Unit/EventsCategoryControllerUnitTest.php
```

### Run with Coverage:
```bash
XDEBUG_MODE=coverage /opt/homebrew/bin/php vendor/bin/phpunit tests/Feature/EventsCategoryControllerTest.php tests/Unit/EventsCategoryControllerUnitTest.php --coverage-html coverage/html
```

## Test Database Setup
Tests use the `RefreshDatabase` trait, which:
- Creates a fresh database for each test
- Runs migrations automatically
- Cleans up after each test

## Key Testing Concepts Covered

1. **HTTP Testing**: Feature tests make actual HTTP requests
2. **Database Testing**: Using factories and assertions
3. **Resource Testing**: Verifying API resource transformations
4. **Pagination Testing**: Testing Laravel pagination
5. **Relationship Testing**: Testing Eloquent relationships
6. **Error Handling**: Testing edge cases and error conditions
7. **JSON Structure Testing**: Verifying API response formats

## Coverage Areas

- ✅ Controller methods (index, eventsByCategory)
- ✅ API endpoints and routes
- ✅ Database queries and relationships
- ✅ Resource transformations
- ✅ Pagination logic
- ✅ Data filtering and ordering
- ✅ Error handling and edge cases
- ✅ JSON response structures

## Dependencies
- Laravel Testing Framework
- PHPUnit
- Factory classes for EventCategories and Events
- Database migrations for events, event_categories, and category_event tables
