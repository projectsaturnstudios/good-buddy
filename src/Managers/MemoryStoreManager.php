<?php

namespace Agents\GoodBuddy\Managers;

use Agents\GoodBuddy\MemoryStores\ChatConversation;
use Illuminate\Support\Manager;
use Agents\GoodBuddy\MemoryStores\RobotBrain;
use Agents\GoodBuddy\MemoryStores\Drivers\CachedMemoryStoreDriver;

class MemoryStoreManager extends Manager
{
    public function create(string $session_id, string $driver = null): string
    {
        $driver = $driver ?: $this->getDefaultDriver();
        return $this->driver($driver)->create($session_id);
    }

    public function full_convo(string $session_id, string $conversation_id, string $driver = null): ?ChatConversation
    {
        $driver = $driver ?: $this->getDefaultDriver();
        return $this->driver($driver)->full_convo($session_id, $conversation_id);
    }

    public function save(string $session_id, string $conversation_id, ChatConversation $convo, string $driver = null) : bool
    {
        $driver = $driver ?: $this->getDefaultDriver();
        return $this->driver($driver)->save($session_id, $conversation_id, $convo);
    }

    public function createCachedDriver(): RobotBrain
    {
        return new CachedMemoryStoreDriver();
    }

    public function getDefaultDriver(): string
    {
        return config('agents.local.memory_store.default', 'cached');
    }

    public static function boot(): void
    {
        app()->singleton('good-buddy.memory', function ($app) {
            $results = new static($app);

            $add_ons = config('agents.local.memory_stores.stores', []);
            foreach($add_ons as $name => $add_on) {
                $results->extend($name, fn() => new $add_on['class']($add_on));
            }

            return $results;
        });
    }
}
