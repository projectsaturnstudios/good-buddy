<?php

namespace Agents\GoodBuddy\Console\Commands;

use Agents\GoodBuddy\Agents\Sample\OpenWeatherChatBot;
use Illuminate\Console\Command;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand('agent:test', description: 'Test basic agent functionality with turn-based conversation.')]
class AgentTestCommand extends Command
{
    protected $signature = 'agent:test {session_id : The session ID for the agent} {prompt? : The prompt to send to the agent}';
    public function handle(): int
    {
        $this->info("Running Agent Test Command...");
        $agent = (new OpenWeatherChatBot)
            ->whereSessionId($this->argument('session_id'))
            ->first();

        if(is_null($agent))
        {
            $this->info("No agent found for session ID: {$this->argument('session_id')}. Creating a new agent.");
            $agent = (new OpenWeatherChatBot)
                ->whereSessionId($this->argument('session_id'))
                ->create();
        }

        $response = $agent->update([
            ['text' => $this->argument('prompt') ?: 'Hello! Lovely to meet you!']
        ]);

        foreach($response->toArray() as $message)
        {
            if($message['type'] == 'text') $this->info("Agent Response: " . $message['content']);
            else $this->warn("Unsupported output. Skipping");
        }

        $this->info('fin');
        return 0;
    }
}
