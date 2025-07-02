<?php

namespace Agents\GoodBuddy\Actions\Sagas\ChatBot;

use Agents\GoodBuddy\GoodBuddy;
use ProjectSaturnStudios\PocketFlow\Node;
use Agents\GoodBuddy\Support\Facades\AgentComms;
use Agents\GoodBuddy\MemoryStores\ChatConversation;

class ContactLLMNode extends Node
{
    public function __construct() {
        parent::__construct();
    }

    public function prep(mixed &$shared): mixed
    {
        $agent = $shared['agent'] /** @var GoodBuddy $agent */;
        $history = $shared['conversation_history']; /** @var ChatConversation $history */
        $options = [
            'model' => $agent->model(),
            'credentials' => $agent->credentials(),
            'system_instructions' => $agent->system_instructions(),
            'max_tokens' => $agent->max_tokens(),
            'temperature' => $agent->temperature(),
            'tools' => $agent->tools(),

            'connection' => $agent->connection(),
            'conversation' => $history->conversation,
        ];
        return array_filter($options, fn($val) => !empty($val));
    }

    public function exec(mixed $prep_res): mixed
    {
        return AgentComms::call_llm($prep_res);
    }
    public function post(mixed &$shared, mixed $prep_res, mixed $exec_res): mixed
    {
        $shared['llm_response'] = $exec_res;
        return 'output';
    }
}
