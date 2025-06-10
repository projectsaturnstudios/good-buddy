<?php

namespace Agents\GoodBuddy\Support\Facades;

use Illuminate\Support\Facades\Facade;
use Agents\GoodBuddy\Managers\ToolManager;

/**
 * @method static driver(?string $name = null)\
 *
 * @see ToolManager
 */
class ToolTime extends Facade
{
    /**
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return 'tool-time';
    }
}
