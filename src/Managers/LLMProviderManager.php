<?php

namespace Agents\GoodBuddy\Managers;

use Illuminate\Support\Manager;
use Agents\GoodBuddy\Drivers\RobotBrains\RobotBrains;
use Agents\GoodBuddy\Drivers\RobotBrains\LLMSpeakDriver;

class LLMProviderManager extends Manager
{
    /**
     * @param string $provider
     * @param array $parameters
     * @param string|null $driver
     * @return array
     */
    public function parseRequest(string $provider, array $parameters, ?string $driver = null): array
    {
        $driver = $driver ?? $this->getDefaultDriver();
        return $this->driver($driver)->parseRequest($provider, $parameters);
    }

    public function formatConvo(string $provider, array $conversation, ?string $driver = null): array
    {
        $driver = $driver ?? $this->getDefaultDriver();
        return $this->driver($driver)->formatConvo($provider, $conversation);
    }

    public function createLlmSpeakDriver(): RobotBrains
    {
        return new LLMSpeakDriver();
    }

    public function getDefaultDriver(): string
    {
        return config('agents.local-agents.providers.default', 'llm-speak');
    }

    public static function boot(): void
    {
        app()->singleton('brains', function($app) {
            $results = new static($app);

            foreach(config('agents.local-agents.providers.add-ons') as $name => $config) {
                $results = $results->extend($name, fn() => new $config['driver_class']($config));
            }

            return $results;
        });
    }
}
