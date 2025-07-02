<?php

namespace Agents\GoodBuddy\Providers;

use Agents\GoodBuddy\Console\Commands\AgentTestCommand;
use Agents\GoodBuddy\Console\Commands\CreateAgentCommand;
use Agents\GoodBuddy\Managers\LLMCommunicationManager;
use Agents\GoodBuddy\Managers\MemoryStoreManager;
use Agents\GoodBuddy\Managers\ToolExecutionManager;
use Illuminate\Support\ServiceProvider;

class GoodBuddyAgentsServiceProvider extends ServiceProvider
{
    protected array $config = [
        'agents.local' => __DIR__ .'/../../config/agents/local.php',
    ];

    protected array $commands = [
        CreateAgentCommand::class,
        AgentTestCommand::class,
    ];

    public function register(): void
    {
        $this->registerConfigs();
    }

    public function boot(): void
    {
        $this->publishConfigs();
        $this->registerManagers();
        $this->commands($this->commands);
    }

    protected function registerManagers(): void
    {
        MemoryStoreManager::boot();
        ToolExecutionManager::boot();
        LLMCommunicationManager::boot();
    }

    protected function publishConfigs() : void
    {
        $this->publishes([
            $this->config['agents.local'] => config_path('agents/local.php'),
        ], 'agents.good-buddy');
    }

    protected function registerConfigs() : void
    {
        foreach ($this->config as $key => $path) {
            $this->mergeConfigFrom($path, $key);
        }
    }
}
