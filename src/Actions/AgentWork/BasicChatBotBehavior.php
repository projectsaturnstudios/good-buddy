<?php

namespace Agents\GoodBuddy\Actions\AgentWork;

use Agents\GoodBuddy\Actions\Sagas\ChatBot\ContactLLMNode;
use Agents\GoodBuddy\Actions\Sagas\ChatBot\ProcessChatInputNode;
use Agents\GoodBuddy\Actions\Sagas\ChatBot\ProcessLLMOutputNode;
use Agents\GoodBuddy\Actions\Sagas\ChatBot\StartChatBotNode;
use Agents\GoodBuddy\GoodBuddy;
use Agents\GoodBuddy\Support\Facades\MemoryStores;
use Lorisleiva\Actions\Concerns\AsAction;

class BasicChatBotBehavior
{
    use AsAction;

    public function __construct(protected GoodBuddy $agent) {}

    public function handle(array $prompt): array
    {
        $shared = [
            'agent' => $this->agent,
            'conversation_history' => MemoryStores::full_convo($this->agent->session_id(), $this->agent->conversation_id(), $this->agent->memory_store()),
            'prompt_to_process' => $prompt,
        ];

        $bot = new StartChatBotNode();
        $bot->next(new ProcessChatInputNode(), 'input')
            ->next(new ContactLLMNode(), 'call')
            ->next(new ProcessLLMOutputNode(), 'output');

        return flow($bot, $shared);
    }
}
