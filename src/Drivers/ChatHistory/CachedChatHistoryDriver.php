<?php

namespace Agents\GoodBuddy\Drivers\ChatHistory;

use Illuminate\Support\Facades\Cache;
use Agents\GoodBuddy\ChatHistory\SessionChat;

class CachedChatHistoryDriver extends ChatHistoryDriver
{
    public function save(SessionChat $session): SessionChat
    {
        Cache::put(
            "chat-history-{$session->session_id}",
            $session,
            //now()->addMinutes(config('chat-history.session_managers.available.cached.sessions_expire_in', 10))
        );

        return $session;
    }

    public function load(string $session_id): ?SessionChat
    {
        return Cache::get("chat-history-{$session_id}");
    }
}
