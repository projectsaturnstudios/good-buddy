<?php

namespace Agents\GoodBuddy\Actions\AgentWork;

use Agents\GoodBuddy\MemoryStores\ChatConversation;
use Agents\GoodBuddy\Support\Facades\MemoryStores;
use Lorisleiva\Actions\Concerns\AsAction;

class SaveConversationHistory
{
    use AsAction;

    public function __construct(
        protected string $session_id,
        protected string $conversation_id,
    ) {}

    public function handle(ChatConversation $conversation, ?string $driver = null): ChatConversation
    {

        if(MemoryStores::save($this->session_id, $this->conversation_id, $conversation, $driver))
        {
            // @todo - fire an event or something
            $conversation = MemoryStores::full_convo($this->session_id, $this->conversation_id, $driver);
        }
        return $conversation;

    }
}
