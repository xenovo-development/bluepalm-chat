<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Emincmg\ConvoLite\Facades\Convo;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);
        User::factory()->create([
            'name' => 'Test User 2',
            'email' => 'test2@example.com',
        ]);
        User::factory()->create([
            'name' => 'Test User 3',
            'email' => 'test3@example.com',
        ]);
        $titles = ['Dummy Title 1', 'Dummy Title 2', 'Dummy Title 3'];
        $messages = ['Dummy Message 1', 'Dummy Message 2', 'Dummy Message 3'];

        foreach ($titles as $title) {
            $convos = Convo::createConversation(1, [2, 3], $title);
            $conversation = $convos instanceof \Illuminate\Support\Collection ? $convos->first() : $convos;

            $id = 1; // starts at 1 for each conversation
            foreach ($messages as $message) {
                Convo::sendMessage(
                    conversation: $conversation,
                    user: $id,                 // <-- use $id (not $index)
                    messageContent: $message
                );
                $id++; // 1 -> 2 -> 3
            }
        }

    }
}
