<?php

namespace Agents\GoodBuddy\Support\Facades;

use Agents\GoodBuddy\Managers\MemoryStoreManager;
use Agents\GoodBuddy\MemoryStores\ChatConversation;
use Illuminate\Support\Facades\Facade;

/**
 * @method static load(string $session_id, ?string $driver = null)
 * @method static string create(string $session_id, ?string $driver = null)
 * @method static array save(string $session_id, string $conversation_id, ChatConversation $convo, ?string $driver = null)
 * @method static ChatConversation|null full_convo(string $session_id, string $conversation_id, string $driver = null)
 *
 * @see MemoryStoreManager
 */
class MemoryStores extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return 'good-buddy.memory';
    }
}
