<?php

namespace Agents\GoodBuddy\MemoryStores\Drivers;

use Agents\GoodBuddy\MemoryStores\ChatConversation;
use Agents\GoodBuddy\MemoryStores\RobotBrain;
use Illuminate\Support\Facades\Cache;

class CachedMemoryStoreDriver extends RobotBrain
{
    public function create(string $session_id): string
    {
        /** @var ChatConversation[] $convos */
        $convos = Cache::get("good-buddy.conversations.{$session_id}", []);
        $convos[$uuid = new_uuid()] = new ChatConversation($uuid);
        Cache::put("good-buddy.conversations.{$session_id}", $convos);

        return $uuid;
    }

    public function load(string $session_id): array
    {
        $results = [
            'convos' => [],
            'human_convos' => [],
        ];
        /** @var ChatConversation[] $convos */
        $chats = Cache::get("good-buddy.conversations.{$session_id}", []);
        foreach($chats as $convo_id => $chat) {
            $results['convos'][$convo_id] = $chat;
            $results['human_convos'][$convo_id] = count($chat->conversation)  > 0 ? $chat->humanConversation() : [];
        }

        return $results;
    }

    public function save(string $session_id, string $conversation_id, ChatConversation $convo): bool
    {
        $convos = Cache::get("good-buddy.conversations.{$session_id}", []);
        $convos[$conversation_id] = $convo;
        return Cache::put("good-buddy.conversations.{$session_id}", $convos);
    }

    public function full_convo(string $session_id, string $conversation_id): ?ChatConversation
    {
        $results = null;
        $history = $this->load($session_id);

        if(array_key_exists($conversation_id, $history['convos'])) $results = $history['convos'][$conversation_id];

        return $results;
    }
}
