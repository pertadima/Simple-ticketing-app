<?php

namespace Tests\Feature;

use App\Models\Events;
use App\Models\EventCategories;
use App\Models\Tickets;
use App\Models\TicketCategories;
use App\Models\TicketTypes;
use App\Models\Seats;
use App\Models\Users;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class EventsControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    private Users $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = Users::factory()->create();
    }

    public function test_index_returns_all_events_successfully()
    {
        $category = EventCategories::factory()->create();
        $events = Events::factory()->count(3)->create();
        
        // Attach category to events through many-to-many relationship
        foreach ($events as $event) {
            $event->categories()->attach($category->category_id);
        }

        $response = $this->getJson('/api/v1/events');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'events' => [
                        '*' => [
                            'event_id',
                            'name',
                            'description',
                            'location',
                            'date',
                            'time',
                        ]
                    ]
                ],
                'meta',
            ]);

        $this->assertCount(3, $response->json('data.events'));
    }

    public function test_index_with_pagination_returns_correct_structure()
    {
        $category = EventCategories::factory()->create();
        $events = Events::factory()->count(15)->create();
        
        // Attach category to events
        foreach ($events as $event) {
            $event->categories()->attach($category->category_id);
        }

        $response = $this->getJson('/api/v1/events?page=2');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'events'
                ],
                'meta' => [
                    'current_page',
                    'last_page',
                    'per_page',
                    'total',
                    'is_next_page',
                    'is_prev_page',
                ]
            ]);

        $this->assertEquals(2, $response->json('meta.current_page'));
        $this->assertEquals(15, $response->json('meta.total'));
    }

    public function test_index_with_search_query_uses_elasticsearch_path()
    {
        // This test will likely fail if Elasticsearch is not running,
        // but it tests the search path in the controller
        $response = $this->getJson('/api/v1/events?search=concert');

        // The response structure should be consistent regardless of Elasticsearch availability
        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'events'
                ],
                'meta' => [
                    'current_page',
                    'last_page', 
                    'per_page',
                    'total',
                    'is_next_page',
                    'is_prev_page',
                ]
            ]);
    }

    public function test_index_with_search_query_and_pagination()
    {
        $response = $this->getJson('/api/v1/events?search=music&page=2');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'events'
                ],
                'meta' => [
                    'current_page',
                    'last_page',
                    'per_page',
                    'total',
                    'is_next_page',
                    'is_prev_page',
                ]
            ]);

        $this->assertEquals(2, $response->json('meta.current_page'));
    }

    public function test_index_with_empty_search_query_falls_back_to_database()
    {
        $category = EventCategories::factory()->create();
        $events = Events::factory()->count(5)->create();
        
        // Attach category to events
        foreach ($events as $event) {
            $event->categories()->attach($category->category_id);
        }

        $response = $this->getJson('/api/v1/events?search=');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'events'
                ],
                'meta'
            ]);

        // With empty search, should fall back to database pagination
        $this->assertCount(5, $response->json('data.events'));
    }

    public function test_index_search_query_handles_special_characters()
    {
        $response = $this->getJson('/api/v1/events?search=' . urlencode('rock & roll'));

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'events'
                ],
                'meta'
            ]);
    }

    public function test_show_returns_event_with_tickets_successfully()
    {
        $category = EventCategories::factory()->create();
        $event = Events::factory()->create();
        $event->categories()->attach($category->category_id);

        $response = $this->getJson("/api/v1/events/{$event->event_id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'event' => [
                        'id',
                        'name',
                        'description',
                        'location',
                        'date'
                    ]
                ]
            ])
            ->assertJson([
                'data' => [
                    'event' => [
                        'id' => $event->event_id,
                        'name' => $event->name,
                    ]
                ]
            ]);
    }

    public function test_show_with_invalid_event_id_returns_not_found()
    {
        $response = $this->getJson('/api/v1/events/999999');
        $response->assertStatus(404);
    }

    public function test_show_available_seats_returns_seats_successfully()
    {
        $category = EventCategories::factory()->create();
        $event = Events::factory()->create();
        $event->categories()->attach($category->category_id);

        $ticketType = TicketTypes::factory()->create();

        // Create some seats directly with event_id and type_id
        Seats::factory()->count(3)->create([
            'event_id' => $event->event_id,
            'type_id' => $ticketType->type_id,
            'is_booked' => false,
        ]);

        $response = $this->getJson("/api/v1/events/{$event->event_id}/types/{$ticketType->type_id}/seats");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'seat_id',
                        'seat_number',
                        'is_booked',
                    ]
                ]
            ]);

        $this->assertCount(3, $response->json('data'));
    }

    public function test_show_available_seats_with_invalid_event_returns_empty_array()
    {
        $ticketType = TicketTypes::factory()->create();

        $response = $this->getJson("/api/v1/events/999999/types/{$ticketType->type_id}/seats");

        $response->assertStatus(200)
            ->assertJson([
                'data' => []
            ]);

        $this->assertCount(0, $response->json('data'));
    }
}
