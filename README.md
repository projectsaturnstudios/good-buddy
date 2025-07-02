```php

use  Agents\GoodBuddy\Agents\Sample\OpenWeatherChatBot;

// Initialize your agent
$agent = OpenWeatherChatBot::onConnection('anthropic')
    ->whereModel('claude-sonnet-4-20250514')
    ->whereSessionId('session-id-123')
    ->first();
    
// Set configurations that aren't already hardcoded (or overwrite them)
$agent = $agent->setMaxTokens($max_tokens)
    ->setTemperature($temperature)
    ->setMemoryStore($memory_store)
    ->with('system_instructions', $system_instructions)
    ->with('tools', $tools);
    
// Fire away
$agent->create([
    ['text' => $text]
    |['image' => $image]
    |['file' => $file]
]);
```
