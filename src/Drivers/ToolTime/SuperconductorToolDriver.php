<?php

namespace Agents\GoodBuddy\Drivers\ToolTime;

use Superconductor\Schema\Definitions\V20250326\Tools\CallToolRequest;
use Superconductor\Support\Facades\MCPCapabilities;

/**
 * @note - this is the only implementation in GoodBuddy that can use Superconductor methods and classes
 */
class SuperconductorToolDriver extends ToolManagementDriver
{
    public function execute($tool_call): array
    {
        $incoming_message = new CallToolRequest(1, $tool_call['tool'], $tool_call['params']);
        return MCPCapabilities::dispatch('tools', $incoming_message);
    }

}
