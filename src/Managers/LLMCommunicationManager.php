<?php

namespace Agents\GoodBuddy\Managers;

use Agents\GoodBuddy\LLMs\Drivers\LlmSpeakLanguageModelDriver;
use Agents\GoodBuddy\LLMs\LanguageModelLiason;
use Illuminate\Support\Manager;

class LLMCommunicationManager extends Manager
{
    public function call_llm(array $options, ?string $driver = null): array
    {
        /** @var LanguageModelLiason $driver */
        $driver = $driver ?: $this->getDefaultDriver();
        return $this->driver($driver)->call_llm($options);
    }

    public function get_default_credentials(string $connection, ?string $driver = null) : array
    {
        /** @var LanguageModelLiason $driver */
        $driver = $driver ?: $this->getDefaultDriver();
        return $this->driver($driver)->get_default_credentials($connection);
    }

    public function createLlmSpeakDriver(): LanguageModelLiason
    {
        return new LlmSpeakLanguageModelDriver();
    }

    public function getDefaultDriver(): string
    {
        return config('agents.local.llm_communicator.default', 'llm-speak');
    }

    public static function boot(): void
    {
        app()->singleton('good-buddy.llms', function ($app) {
            $results = new static($app);

            $add_ons = config('agents.local.llm_communicator.communicators', []);
            foreach($add_ons as $name => $add_on) {
                $results->extend($name, fn() => new $add_on['class']($add_on));
            }

            return $results;
        });
    }
}
