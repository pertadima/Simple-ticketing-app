<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Http\Controllers\API\EventsCategoryController;
use App\Models\EventCategories;
use App\Models\Events;
use App\Http\Resources\EventsCategoryResource;
use App\Http\Resources\EventsResource;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Mockery;

class EventsCategoryControllerUnitTest extends TestCase
{
    use RefreshDatabase;

    protected $controller;

    protected function setUp(): void
    {
        parent::setUp();
        $this->controller = new EventsCategoryController();
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /**
     * Test index method returns correct JSON structure
     */
    public function test_index_method_returns_correct_structure()
    {
        // Create test categories
        $categories = EventCategories::factory()->count(3)->create();

        $request = new Request();
        $response = $this->controller->index($request);

        $this->assertEquals(200, $response->getStatusCode());
        
        $responseData = json_decode($response->getContent(), true);
        
        $this->assertArrayHasKey('data', $responseData);
        $this->assertArrayHasKey('categories', $responseData['data']);
        $this->assertCount(3, $responseData['data']['categories']);

        // Verify structure of each category
        foreach ($responseData['data']['categories'] as $category) {
            $this->assertArrayHasKey('id', $category);
            $this->assertArrayHasKey('name', $category);
        }
    }

    /**
     * Test index method with empty categories
     */
    public function test_index_method_with_empty_categories()
    {
        $request = new Request();
        $response = $this->controller->index($request);

        $this->assertEquals(200, $response->getStatusCode());
        
        $responseData = json_decode($response->getContent(), true);
        
        $this->assertArrayHasKey('data', $responseData);
        $this->assertArrayHasKey('categories', $responseData['data']);
        $this->assertCount(0, $responseData['data']['categories']);
    }

    /**
     * Test eventsByCategory method returns correct structure
     */
    public function test_events_by_category_method_returns_correct_structure()
    {
        $category = EventCategories::factory()->create();
        $events = Events::factory()->count(2)->create();
        
        // Associate events with category
        foreach ($events as $event) {
            $event->categories()->attach($category->category_id);
        }

        $response = $this->controller->eventsByCategory($category->category_id);

        $this->assertEquals(200, $response->getStatusCode());
        
        $responseData = json_decode($response->getContent(), true);
        
        // Check main structure
        $this->assertArrayHasKey('data', $responseData);
        $this->assertArrayHasKey('meta', $responseData);
        
        // Check data structure
        $this->assertArrayHasKey('events', $responseData['data']);
        
        // Check meta structure
        $metaKeys = ['current_page', 'last_page', 'per_page', 'total', 'is_next_page', 'is_prev_page'];
        foreach ($metaKeys as $key) {
            $this->assertArrayHasKey($key, $responseData['meta']);
        }
    }

    /**
     * Test eventsByCategory method with nonexistent category
     */
    public function test_events_by_category_with_nonexistent_category()
    {
        $response = $this->controller->eventsByCategory(999);

        $this->assertEquals(200, $response->getStatusCode());
        
        $responseData = json_decode($response->getContent(), true);
        
        $this->assertArrayHasKey('data', $responseData);
        $this->assertArrayHasKey('events', $responseData['data']);
        $this->assertCount(0, $responseData['data']['events']);
    }

    /**
     * Test pagination metadata calculation
     */
    public function test_pagination_metadata_calculation()
    {
        $category = EventCategories::factory()->create();
        
        // Create more events than pagination limit (10)
        $events = Events::factory()->count(15)->create();
        
        foreach ($events as $event) {
            $event->categories()->attach($category->category_id);
        }

        $response = $this->controller->eventsByCategory($category->category_id);
        $responseData = json_decode($response->getContent(), true);
        
        $meta = $responseData['meta'];
        
        // Test pagination calculations
        $this->assertEquals(1, $meta['current_page']);
        $this->assertEquals(2, $meta['last_page']);
        $this->assertEquals(10, $meta['per_page']);
        $this->assertEquals(15, $meta['total']);
        $this->assertTrue($meta['is_next_page']);
        $this->assertFalse($meta['is_prev_page']);
    }

    /**
     * Test events are correctly filtered by category
     */
    public function test_events_filtered_by_category()
    {
        $musicCategory = EventCategories::factory()->create(['name' => 'Music']);
        $sportsCategory = EventCategories::factory()->create(['name' => 'Sports']);
        
        $musicEvent = Events::factory()->create(['name' => 'Concert']);
        $sportsEvent = Events::factory()->create(['name' => 'Football']);
        
        $musicEvent->categories()->attach($musicCategory->category_id);
        $sportsEvent->categories()->attach($sportsCategory->category_id);

        // Get events for music category
        $response = $this->controller->eventsByCategory($musicCategory->category_id);
        $responseData = json_decode($response->getContent(), true);
        
        $this->assertCount(1, $responseData['data']['events']);
        $this->assertEquals('Concert', $responseData['data']['events'][0]['name']);
    }

    /**
     * Test events are ordered by date ascending
     */
    public function test_events_ordered_by_date_ascending()
    {
        $category = EventCategories::factory()->create();
        
        $laterEvent = Events::factory()->create([
            'name' => 'Later Event',
            'date' => '2025-12-31'
        ]);
        
        $earlierEvent = Events::factory()->create([
            'name' => 'Earlier Event',
            'date' => '2025-01-01'
        ]);
        
        $laterEvent->categories()->attach($category->category_id);
        $earlierEvent->categories()->attach($category->category_id);

        $response = $this->controller->eventsByCategory($category->category_id);
        $responseData = json_decode($response->getContent(), true);
        
        $events = $responseData['data']['events'];
        
        $this->assertEquals('Earlier Event', $events[0]['name']);
        $this->assertEquals('Later Event', $events[1]['name']);
    }

    /**
     * Test EventsCategoryResource is used correctly
     */
    public function test_events_category_resource_usage()
    {
        $category = EventCategories::factory()->create([
            'name' => 'Test Category'
        ]);

        $request = new Request();
        $response = $this->controller->index($request);
        $responseData = json_decode($response->getContent(), true);
        
        $categoryData = $responseData['data']['categories'][0];
        
        // Check that the resource transformation is correct
        $this->assertEquals($category->category_id, $categoryData['id']);
        $this->assertEquals('Test Category', $categoryData['name']);
    }

    /**
     * Test EventsResource is used correctly
     */
    public function test_events_resource_usage()
    {
        $category = EventCategories::factory()->create();
        $event = Events::factory()->create([
            'name' => 'Test Event',
            'date' => '2025-08-15',
            'location' => 'Test Location',
            'description' => 'Test Description'
        ]);
        
        $event->categories()->attach($category->category_id);

        $response = $this->controller->eventsByCategory($category->category_id);
        $responseData = json_decode($response->getContent(), true);
        
        $eventData = $responseData['data']['events'][0];
        
        // Check that the resource transformation is correct
        $this->assertEquals($event->event_id, $eventData['id']);
        $this->assertEquals('Test Event', $eventData['name']);
        $this->assertEquals('2025-08-15T00:00:00.000000Z', $eventData['date']); // Updated to match actual datetime format
        $this->assertEquals('Test Location', $eventData['location']);
        $this->assertEquals('Test Description', $eventData['description']);
        
        // Check that arrays are present
        $this->assertIsArray($eventData['images']);
        $this->assertIsArray($eventData['categories']);
    }

    /**
     * Test query includes correct relationships
     */
    public function test_query_includes_relationships()
    {
        $category = EventCategories::factory()->create();
        $event = Events::factory()->create();
        
        $event->categories()->attach($category->category_id);

        $response = $this->controller->eventsByCategory($category->category_id);
        $responseData = json_decode($response->getContent(), true);
        
        $eventData = $responseData['data']['events'][0];
        
        // Verify that relationships are loaded
        $this->assertArrayHasKey('categories', $eventData);
        $this->assertArrayHasKey('images', $eventData);
    }

    /**
     * Test pagination uses correct parameters
     */
    public function test_pagination_uses_correct_parameters()
    {
        $category = EventCategories::factory()->create();
        Events::factory()->count(25)->create()->each(function ($event) use ($category) {
            $event->categories()->attach($category->category_id);
        });

        $response = $this->controller->eventsByCategory($category->category_id);
        $responseData = json_decode($response->getContent(), true);
        
        // Should paginate with 10 items per page
        $this->assertCount(10, $responseData['data']['events']);
        $this->assertEquals(10, $responseData['meta']['per_page']);
        $this->assertEquals(25, $responseData['meta']['total']);
        $this->assertEquals(3, $responseData['meta']['last_page']);
    }

    /**
     * Test controller method signatures
     */
    public function test_controller_method_signatures()
    {
        $this->assertTrue(method_exists($this->controller, 'index'));
        $this->assertTrue(method_exists($this->controller, 'eventsByCategory'));
        
        $reflection = new \ReflectionClass($this->controller);
        
        // Test index method signature
        $indexMethod = $reflection->getMethod('index');
        $this->assertEquals(1, $indexMethod->getNumberOfParameters());
        
        // Test eventsByCategory method signature
        $eventsByCategoryMethod = $reflection->getMethod('eventsByCategory');
        $this->assertEquals(1, $eventsByCategoryMethod->getNumberOfParameters());
    }
}
