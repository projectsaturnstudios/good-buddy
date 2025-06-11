<?php

namespace Agents\GoodBuddy\Console\Commands;

use Illuminate\Console\GeneratorCommand;
use Symfony\Component\Console\Attribute\AsCommand;

//#[AsCommand('make:local-agent', "Generates a GoodBuddy Agent that can use locally-defined tools")]
class MakeLocalAgentCommand extends GeneratorCommand
{
    protected $signature = 'make:local-agent {name}
                            {--model=claude-3-5-haiku-20241022 : The AI model to use}
                            {--provider=anthropic : The AI provider}
                            {--session-provider=cached : Chat session provider}
                            ';

    protected $description = "Generates a GoodBuddy Agent that can use locally-defined tools";

    protected $type = 'Agent';

    public function handle(): void
    {
        parent::handle();

        $this->rewriteToSyncAgent();
    }

    protected function rewriteToSyncAgent(): void
    {
        $name = $this->qualifyClass($this->getNameInput());
        $path = $this->getPath($name);
        $content = file_get_contents($path);

        // Replace all the agent tokens
        $content = str_replace('{{ model }}', $this->option('model'), $content);
        $content = str_replace('{{ provider }}', $this->option('provider'), $content);
        $content = str_replace('{{ memory }}', 'proprietary', $content);
        $content = str_replace('{{ chat_session_provider }}', $this->option('session-provider'), $content);
        $content = str_replace('{{ tools_array }}', '', $content);
        $content = str_replace('{{ instructions }}', '', $content);

        file_put_contents($path, $content);

        $this->components->info("Agent created successfully!");
        $this->components->warn("Next steps:");
        $this->components->bulletList([
            "Add tool imports and configure the tools array",
            "Customize the agent instructions for your use case",
            "Register any new MCP tools in your config/mcp.php if needed"
        ]);
    }

    /**
     * Get the stub file for the generator.
     */
    protected function getStub(): string
    {
        return $this->resolveStubPath('/stubs/agent.stub');
    }

    /**
     * Resolve the fully-qualified path to the stub.
     */
    protected function resolveStubPath(string $stub): string
    {
        return file_exists($customPath = $this->laravel->basePath(trim($stub, '/')))
            ? $customPath
            : __DIR__ . "/../../..{$stub}";
    }

    /**
     * Get the default namespace for the class.
     */
    protected function getDefaultNamespace($rootNamespace): string
    {
        return $rootNamespace.'\AiAgents';
    }
}
