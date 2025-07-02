<?php

namespace Agents\GoodBuddy\Actions\Sagas\Tools;

use Agents\GoodBuddy\Actions\AgentWork\SaveConversationHistory;
use Agents\GoodBuddy\MemoryStores\ChatConversation;
use ProjectSaturnStudios\PocketFlow\Node;
use Agents\GoodBuddy\Actions\Sagas\ChatBot\ContactLLMNode;
use Agents\GoodBuddy\Actions\Sagas\ChatBot\ProcessLLMOutputNode;
use Symfony\Component\VarDumper\VarDumper;

class ProcessToolResult extends Node
{
    public function __construct() {
        parent::__construct();
    }

    public function prep(mixed &$shared): mixed
    {
        return [
            'tool_results' => $shared['tool_results'] ?? [],
            'session_id' => $shared['agent']->session_id(),
            'memory_store' => $shared['agent']->memory_store(),
            'conversation_history' => $shared['conversation_history'] ?? null,
            'agent' => $shared['agent'],
        ];
    }

    public function exec(mixed $prep_res): mixed
    {
        /** @var ChatConversation $convo */
        $convo = $prep_res['conversation_history'];
        foreach($prep_res['tool_results'] as $tool_result)
        {
            $convo->addToolResult($tool_result);
        }

        return $convo;
    }
    public function post(mixed &$shared, mixed $prep_res, mixed $exec_res): mixed
    {
        $shared['conversation_history'] = (new SaveConversationHistory($prep_res['session_id'], $shared['conversation_history']->conversation_id))->handle($exec_res, $prep_res['memory_store']);
        VarDumper::dump('Starting the loop over calling the llm!');
        $this->next(new ContactLLMNode(), 'call')
            ->next(new ProcessLLMOutputNode(), 'output');

        return 'call';
    }
}
