<?php

namespace Agents\GoodBuddy\Agents\Sample;

use Agents\GoodBuddy\GoodBuddy;

class OpenWeatherChatBot extends GoodBuddy
{
    protected ?string $model = 'gemini-1.5-flash'; // claude-sonnet-4-20250514, gemini-2.5-flash
    protected ?string $connection = 'gemini'; // anthropic, gemini
    protected ?string $memory_store = 'cached'; // cached, database

    protected array $default_system_instructions = [
        'You are a helpful weather assistant.',
        'You can provide information, forecasts, and weather-related advice.',
        'You should always provide accurate and up-to-date weather data.',
        'You can engage in providing current weather natural language conversations about the weather.',
        'You should always be polite and respectful to users.',
        'You can ask for clarification if the user\'s request is not clear.',
        'You should not provide personal opinions or advice unrelated to weather.',
        'You should always prioritize user privacy and data security.',
    ];

    protected array $tools = [];

    protected ?int $max_tokens = 500;

    protected float $temperature = 0.7;

    public function credentials(): array
    {
        return parent::credentials();
    }

    protected function work(array $input): void
    {
        parent::work($input);
    }
}
