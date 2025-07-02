<?php

namespace Agents\GoodBuddy\Tools\Drivers;

use MCP\Support\Facades\MCP;
use Agents\GoodBuddy\Tools\ToolRunner;
use MCP\Capabilities\Tools\RequestRouting\ToolRequest;

class SuperconductorToolsDriver extends ToolRunner
{
    public function fire(array $args): mixed
    {
        $request = new ToolRequest($args['tool'], $args['input']);
        return (MCP::action('server', 'tools', $args['tool'], $request))->toArray();
    }
}
