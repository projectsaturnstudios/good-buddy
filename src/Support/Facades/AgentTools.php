<?php

namespace Agents\GoodBuddy\Support\Facades;

use Agents\GoodBuddy\Managers\ToolExecutionManager;
use Illuminate\Support\Facades\Facade;

/**
 * @@method static mixed fire(array $args, ?string $driver = null)
 * @see ToolExecutionManager
 */
class AgentTools extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return 'good-buddy.tools';
    }
}
