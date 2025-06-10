<?php

namespace Agents\GoodBuddy\Providers;

use Agents\GoodBuddy\Managers\ChatHistoryManager;
use Agents\GoodBuddy\Managers\LLMProviderManager;
use Agents\GoodBuddy\Managers\ToolManager;
use Illuminate\Support\ServiceProvider;

class GoodBuddyAgentsServiceProvider extends ServiceProvider
{
    protected array $config = [
        'agents.local-agents' => __DIR__ .'/../../config/agents/local-agents.php',
    ];

    public function register(): void
    {
        $this->registerConfigs();
    }

    public function boot(): void
    {
        $this->publishConfigs();
        $this->registerManagers();
    }

    protected function registerManagers(): void
    {
        ChatHistoryManager::boot();
        LLMProviderManager::boot();
        ToolManager::boot();
    }

    protected function publishConfigs() : void
    {
        $this->publishes([
            $this->config['agents.local-agents'] => config_path('agents/local-agents.php'),
        ], 'agents.good-buddy');
    }

    protected function registerConfigs() : void
    {
        foreach ($this->config as $key => $path) {
            $this->mergeConfigFrom($path, $key);
        }
    }
}
