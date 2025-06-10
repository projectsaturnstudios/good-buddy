<?php

namespace Agents\GoodBuddy\Support\Facades;

use Illuminate\Support\Facades\Facade;
use Agents\GoodBuddy\Managers\LLMProviderManager;

/**
 * @method static driver(?string $name = null)
 * @method static parseRequest(string $provider, array $parameters, ?string $driver = null)
 * @method static formatConvo(string $provider, array $conversation, ?string $driver = null)
 *
 * @see LLMProviderManager
 */
class AIBrains extends Facade
{
    /**
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return 'brains';
    }
}
