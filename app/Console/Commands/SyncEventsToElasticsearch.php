<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Events;
use Elastic\Elasticsearch\ClientBuilder;

class SyncEventsToElasticsearch extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'es:sync-events';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync events to Elasticsearch';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $client = ClientBuilder::create()
            ->setHosts(config('services.elasticsearch.hosts'))
            ->build();

        Events::with(['categories'])->chunk(100, function ($events) use ($client) {
            foreach ($events as $event) {
                $client->index([
                    'index' => 'events',
                    'id'    => $event->event_id,
                    'body'  => [
                        'name' => $event->name,
                        'description' => $event->description,
                        'date' => $event->date,
                        'location' => $event->location,
                        'categories' => $event->categories->pluck('name')->toArray(),
                    ]
                ]);
            }
        });

        $this->info('Events synced to Elasticsearch!');
    }
}
