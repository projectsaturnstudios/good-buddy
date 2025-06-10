<?php

namespace Agents\GoodBuddy\Support\Facades;

use Agents\GoodBuddy\ChatHistory\ChatHistory;
use Agents\GoodBuddy\Managers\ChatHistoryManager;
use Illuminate\Support\Facades\Facade;

/**
 * @method static driver(?string $name = null);
 * @method static save(ChatHistory $session, ?string $driver = null)
 * @method static load(string|int $session_id, ?string $driver = null)
 * @method static createOrLoad(?string $session_id = null, ?string $driver = null)
 *
 * @see ChatHistoryManager
 */
class ChatManager extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'chat-manager';
    }
}
