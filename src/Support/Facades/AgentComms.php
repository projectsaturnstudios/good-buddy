<?php

namespace Agents\GoodBuddy\Support\Facades;

use Agents\GoodBuddy\Managers\LLMCommunicationManager;
use Illuminate\Support\Facades\Facade;

/**
 * @method static array call_llm(array $options, ?string $driver = null)
 * @method static array get_default_credentials(string $connection, ?string $driver = null)
 * @see LLMCommunicationManager
 */
class AgentComms extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return 'good-buddy.llms';
    }
}
