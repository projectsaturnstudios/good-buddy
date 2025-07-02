<?php

namespace Agents\GoodBuddy\Actions\Sagas\Tools;

use Agents\GoodBuddy\Support\Facades\AgentTools;
use ProjectSaturnStudios\PocketFlow\Node;

class ExecuteToolNode extends Node
{
    public function __construct(public readonly array $messages) {
        parent::__construct();
        $this->next(new ProcessToolResult, 'process-tool');
    }

    public function prep(mixed &$shared): mixed
    {
        return array_filter($this->messages, fn($entry) => $entry['type'] === 'tool_call');
    }

    public function exec(mixed $prep_res): mixed
    {
        $results = [];
        $tool_calls = $prep_res;
        foreach($tool_calls as $tool_call)
        {
            $results[] = array_merge(
                AgentTools::fire($tool_call['content']),
                ['id' => $tool_call['content']['id'], 'tool' => $tool_call['content']['tool']]
            );
        }

        return $results;
    }
    public function post(mixed &$shared, mixed $prep_res, mixed $exec_res): mixed
    {
        $shared['tool_results'] = $exec_res;
        return 'process-tool';
    }
}
