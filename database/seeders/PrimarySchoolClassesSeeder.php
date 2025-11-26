<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Rank;
use App\Models\Division;
use App\Models\Stream;

class PrimarySchoolClassesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get the Primary School division
        $division = Division::firstOrCreate(
            ['name' => 'Primary School'],
            ['activated' => true]
        );

        // Get existing streams or create default ones
        $streams = Stream::all();

        if ($streams->isEmpty()) {
            $streams = collect([
                Stream::create(['name' => 'Stream A', 'activated' => true]),
                Stream::create(['name' => 'Stream B', 'activated' => true]),
            ]);
        }

        // Define Kenyan primary school classes
        $classes = [
            'PP1',
            'PP2',
            'Grade 1',
            'Grade 2',
            'Grade 3',
            'Grade 4',
            'Grade 5',
            'Grade 6',
        ];

        $this->command->info('Creating Kenyan Primary School Classes...');

        foreach ($classes as $index => $className) {
            // Alternate between streams for variety
            $stream = $streams[$index % $streams->count()];

            $rank = Rank::firstOrCreate(
                [
                    'name' => $className,
                    'division_id' => $division->id,
                    'stream_id' => $stream->id,
                ],
                [
                    'activated' => true,
                ]
            );

            $this->command->info("✓ Created: {$rank->name} - {$stream->name}");
        }

        $this->command->info('Primary school classes created successfully!');
    }
}
