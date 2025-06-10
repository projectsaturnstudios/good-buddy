<?php

namespace Agents\GoodBuddy\Managers;

use Agents\GoodBuddy\ChatHistory\ChatHistory;
use Agents\GoodBuddy\ChatHistory\SessionChat;
use Agents\GoodBuddy\Drivers\ChatHistory\CachedChatHistoryDriver;
use Agents\GoodBuddy\Drivers\ChatHistory\ChatHistoryDriver;
use Illuminate\Support\Manager;

class ChatHistoryManager extends Manager
{
    public function save(ChatHistory $session, ?string $driver = null): ChatHistory
    {
        $driver = $driver ?? $this->getDefaultDriver();
        return $this->driver($driver)->save($session);
    }

    public function load(string|int $session_id, ?string $driver = null): ?ChatHistory
    {
        $driver = $driver ?? $this->getDefaultDriver();
        return $this->driver($driver)->load($session_id);
    }

    public function createOrLoad(?string $session_id = null, ?string $driver = null): ChatHistory
    {
        $driver = $driver ?? $this->getDefaultDriver();
        if($session_id) {
            $session = $this->driver($driver)->load($session_id);
            if(!empty($session))  return $session;
        }

        $session_class = config('agents.local-agents.chat-history.history_class', SessionChat::class);
        return new $session_class($session_id);
    }

    public function createCachedDriver(): ChatHistoryDriver
    {
        return new CachedChatHistoryDriver();
    }

    public function getDefaultDriver(): string
    {
        return config('agents.local-agents.chat-history.default', 'cached');
    }

    public static function boot(): void
    {
        app()->singleton('chat-manager', function ($app) {
            $results = new ChatHistoryManager($app);

            foreach(config('agents.local-agents.chat-history.add-ons', []) as $driver => $config) {
                $results = $results->extend($driver, fn() => new $config['driver_class']($config));
            }

            return $results;
        });
    }
}
