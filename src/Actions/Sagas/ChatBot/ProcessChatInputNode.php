<?php

namespace Agents\GoodBuddy\Actions\Sagas\ChatBot;

use Agents\GoodBuddy\GoodBuddy;
use ProjectSaturnStudios\PocketFlow\Node;
use Agents\GoodBuddy\Actions\AgentWork\SaveConversationHistory;

class ProcessChatInputNode extends Node
{
    public function __construct() {
        parent::__construct();
    }

    public function prep(mixed &$shared): mixed
    {
        // @todo - input validation?
        return $shared['prompt_to_process'] ?? [];
    }

    public function exec(mixed $prep_res): mixed
    {
        $results = [];
        foreach($prep_res as $prep_re)
        {
            if(array_key_exists('text', $prep_re)) $results[] = ['role' => 'user', 'content' => $prep_re['text']];
            // @todo - handle other types of input, like files, images, etc.
        }

        return $results;
    }
    public function post(mixed &$shared, mixed $prep_res, mixed $exec_res): mixed
    {
        foreach($exec_res as $result)
        {
            if(is_string($result['content'])) $shared['conversation_history'] = $shared['conversation_history']->addTextMessage(...$result);
            // @todo - whatever else needs to go here as things scale
        }

        $agent = $shared['agent']; /** @var GoodBuddy $agent */
        $shared['conversation_history'] = (new SaveConversationHistory($agent->session_id(), $agent->conversation_id()))->handle($shared['conversation_history'], $agent->memory_store());

        return 'call';
    }
}
