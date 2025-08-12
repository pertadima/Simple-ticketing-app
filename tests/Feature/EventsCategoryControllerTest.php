<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\EventCategories;
use App\Models\Events;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;

class EventsCategoryControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /**
     * Test getting all categories
     */
    public function test_index_returns_all_categories()
    {
        // Create test categories
        $category1 = EventCategories::factory()->create(['name' => 'Music']);
        $category2 = EventCategories::factory()->create(['name' => 'Sports']);
        $category3 = EventCategories::factory()->create(['name' => 'Technology']);

        $response = $this->getJson('/api/v1/categories');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'data' => [
                        'categories' => [
                            '*' => [
                                'id',
                                'name'
                            ]
                        ]
                    ]
                ])
                ->assertJsonCount(3, 'data.categories');

        // Assert that the categories are in the response
        $responseData = $response->json('data.categories');
        $categoryIds = collect($responseData)->pluck('id')->toArray();
        
        $this->assertContains($category1->category_id, $categoryIds);
        $this->assertContains($category2->category_id, $categoryIds);
        $this->assertContains($category3->category_id, $categoryIds);
    }

    /**
     * Test getting all categories when no categories exist
     */
    public function test_index_returns_empty_when_no_categories()
    {
        $response = $this->getJson('/api/v1/categories');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'data' => [
                        'categories' => []
                    ]
                ])
                ->assertJsonCount(0, 'data.categories');
    }

    /**
     * Test getting events by category with valid category ID
     */
    public function test_events_by_category_returns_events_for_valid_category()
    {
        // Create category and events
        $category = EventCategories::factory()->create(['name' => 'Music']);
        
        $event1 = Events::factory()->create([
            'name' => 'Rock Concert',
            'date' => '2025-08-15',
            'location' => 'Stadium A'
        ]);
        
        $event2 = Events::factory()->create([
            'name' => 'Jazz Festival',
            'date' => '2025-08-20',
            'location' => 'Park B'
        ]);

        // Associate events with category
        $event1->categories()->attach($category->category_id);
        $event2->categories()->attach($category->category_id);

        $response = $this->getJson("/api/v1/categories/{$category->category_id}/events");

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'data' => [
                        'events' => [
                            '*' => [
                                'id',
                                'name',
                                'date',
                                'location',
                                'description',
                                'images',
                                'categories'
                            ]
                        ]
                    ],
                    'meta' => [
                        'current_page',
                        'last_page',
                        'per_page',
                        'total',
                        'is_next_page',
                        'is_prev_page'
                    ]
                ])
                ->assertJsonCount(2, 'data.events');

        // Assert events are ordered by date
        $events = $response->json('data.events');
        $this->assertEquals('Rock Concert', $events[0]['name']);
        $this->assertEquals('Jazz Festival', $events[1]['name']);
    }

    /**
     * Test getting events by category with no events
     */
    public function test_events_by_category_returns_empty_when_no_events()
    {
        $category = EventCategories::factory()->create(['name' => 'Empty Category']);

        $response = $this->getJson("/api/v1/categories/{$category->category_id}/events");

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'data' => [
                        'events' => []
                    ],
                    'meta' => [
                        'current_page',
                        'last_page',
                        'per_page',
                        'total',
                        'is_next_page',
                        'is_prev_page'
                    ]
                ])
                ->assertJsonCount(0, 'data.events')
                ->assertJson([
                    'meta' => [
                        'total' => 0,
                        'current_page' => 1,
                        'is_next_page' => false,
                        'is_prev_page' => false
                    ]
                ]);
    }

    /**
     * Test getting events by category with nonexistent category ID
     */
    public function test_events_by_category_with_nonexistent_category()
    {
        $response = $this->getJson('/api/v1/categories/999/events');

        $response->assertStatus(200)
                ->assertJsonCount(0, 'data.events');
    }

    /**
     * Test pagination for events by category
     */
    public function test_events_by_category_pagination()
    {
        $category = EventCategories::factory()->create(['name' => 'Popular Category']);

        // Create 15 events (more than the pagination limit of 10)
        for ($i = 1; $i <= 15; $i++) {
            $event = Events::factory()->create([
                'name' => "Event {$i}",
                'date' => now()->addDays($i)->format('Y-m-d')
            ]);
            $event->categories()->attach($category->category_id);
        }

        // Test first page
        $response = $this->getJson("/api/v1/categories/{$category->category_id}/events");

        $response->assertStatus(200)
                ->assertJsonCount(10, 'data.events')
                ->assertJson([
                    'meta' => [
                        'current_page' => 1,
                        'last_page' => 2,
                        'per_page' => 10,
                        'total' => 15,
                        'is_next_page' => true,
                        'is_prev_page' => false
                    ]
                ]);

        // Test second page
        $response = $this->getJson("/api/v1/categories/{$category->category_id}/events?page=2");

        $response->assertStatus(200)
                ->assertJsonCount(5, 'data.events')
                ->assertJson([
                    'meta' => [
                        'current_page' => 2,
                        'last_page' => 2,
                        'per_page' => 10,
                        'total' => 15,
                        'is_next_page' => false,
                        'is_prev_page' => true
                    ]
                ]);
    }

    /**
     * Test events are ordered by date ascending
     */
    public function test_events_by_category_ordered_by_date()
    {
        $category = EventCategories::factory()->create(['name' => 'Ordered Category']);

        $futureEvent = Events::factory()->create([
            'name' => 'Future Event',
            'date' => '2025-12-01'
        ]);

        $nearEvent = Events::factory()->create([
            'name' => 'Near Event',
            'date' => '2025-08-15'
        ]);

        $todayEvent = Events::factory()->create([
            'name' => 'Today Event',
            'date' => now()->format('Y-m-d')
        ]);

        // Associate all events with category
        $futureEvent->categories()->attach($category->category_id);
        $nearEvent->categories()->attach($category->category_id);
        $todayEvent->categories()->attach($category->category_id);

        $response = $this->getJson("/api/v1/categories/{$category->category_id}/events");

        $response->assertStatus(200);

        $events = $response->json('data.events');
        
        // Events should be ordered by date ascending
        $this->assertEquals('Today Event', $events[0]['name']);
        $this->assertEquals('Near Event', $events[1]['name']);
        $this->assertEquals('Future Event', $events[2]['name']);
    }

    /**
     * Test events include relationships (categories, tickets)
     */
    public function test_events_by_category_includes_relationships()
    {
        $category = EventCategories::factory()->create(['name' => 'Music']);
        $secondCategory = EventCategories::factory()->create(['name' => 'Festival']);

        $event = Events::factory()->create([
            'name' => 'Multi-Category Event',
            'date' => '2025-08-15'
        ]);

        // Associate event with multiple categories
        $event->categories()->attach([$category->category_id, $secondCategory->category_id]);

        $response = $this->getJson("/api/v1/categories/{$category->category_id}/events");

        $response->assertStatus(200);

        $eventData = $response->json('data.events.0');
        
        // Check that categories are included
        $this->assertArrayHasKey('categories', $eventData);
        $this->assertIsArray($eventData['categories']);
        
        // Check that images are included (even if empty)
        $this->assertArrayHasKey('images', $eventData);
        $this->assertIsArray($eventData['images']);
    }

    /**
     * Test API endpoints are accessible
     */
    public function test_api_endpoints_are_accessible()
    {
        $category = EventCategories::factory()->create();

        // Test categories index endpoint
        $response = $this->getJson('/api/v1/categories');
        $response->assertStatus(200);

        // Test events by category endpoint
        $response = $this->getJson("/api/v1/categories/{$category->category_id}/events");
        $response->assertStatus(200);
    }

    /**
     * Test category with events from different categories doesn't leak
     */
    public function test_events_by_category_only_returns_events_for_specified_category()
    {
        $musicCategory = EventCategories::factory()->create(['name' => 'Music']);
        $sportsCategory = EventCategories::factory()->create(['name' => 'Sports']);

        $musicEvent = Events::factory()->create(['name' => 'Music Event']);
        $sportsEvent = Events::factory()->create(['name' => 'Sports Event']);

        // Associate events with their respective categories
        $musicEvent->categories()->attach($musicCategory->category_id);
        $sportsEvent->categories()->attach($sportsCategory->category_id);

        // Request music category events
        $response = $this->getJson("/api/v1/categories/{$musicCategory->category_id}/events");

        $response->assertStatus(200)
                ->assertJsonCount(1, 'data.events');

        $eventData = $response->json('data.events.0');
        $this->assertEquals('Music Event', $eventData['name']);
        $this->assertNotEquals('Sports Event', $eventData['name']);
    }
}
