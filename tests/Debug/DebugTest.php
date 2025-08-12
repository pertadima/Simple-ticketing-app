<?php

namespace Tests\Debug;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Events;
use App\Models\EventCategories;

class DebugTest extends TestCase
{
    use RefreshDatabase;

    public function test_basic_models_work()
    {
        $category = EventCategories::factory()->create();
        $this->assertNotNull($category);
        
        $event = Events::factory()->create();
        $this->assertNotNull($event);
        
        // Test relationship
        $event->categories()->attach($category->category_id);
        $this->assertCount(1, $event->categories);
    }
}
