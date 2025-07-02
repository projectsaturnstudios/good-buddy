<?php

namespace Agents\GoodBuddy\LLMs\Drivers;

use LLMSpeak\Schema\Chat\ChatResult;
use LLMSpeak\Schema\Conversation\ToolCall;
use LLMSpeak\Schema\Conversation\ToolResult;
use LLMSpeak\Support\Facades\LLM;
use LLMSpeak\Schema\Conversation\TextMessage;
use Agents\GoodBuddy\LLMs\LanguageModelLiason;
use LLMSpeak\Support\Facades\CreateChatRequest;
use Symfony\Component\VarDumper\VarDumper;

class LlmSpeakLanguageModelDriver extends LanguageModelLiason
{
    /**
     * @param array $options
     * @return array
     * @throws \Exception
     */
    public function call_llm(array $options): array
    {
        $results = ['success' => false, 'message' => 'Could not connect to LLMSpeak'];

        $request = CreateChatRequest::usingModel($options['model'])
            ->supplyCredentials($options['credentials']);

        $llm_driver = LLM::driver($options['connection']);

        $converted_system_instructions = $llm_driver::convertSystemPrompt(array_map(fn($instruction) => new TextMessage('system', $instruction), $options['system_instructions']));
        $converted_conversation = $llm_driver::convertConversation(array_map(function($entry){
            if($entry['type'] == 'text') return new TextMessage($entry['role'], $entry['content']);
            if($entry['type'] == 'tool_request') return new ToolCall($entry['content']['tool'], $entry['content']['input'], $entry['content']['id']);
            if($entry['type'] == 'tool_result') return new ToolResult('user',$entry['content']['tool'], [$entry['content']['results']] , $entry['content']['id']);
        }, $options['conversation']));

        if(array_key_exists('system_instructions', $options))  $request = $request->instillASystemPrompt($converted_system_instructions);
        if(array_key_exists('max_tokens', $options))  $request = $request->limitTokens($options['max_tokens']);
        if(array_key_exists('temperature', $options))  $request = $request->setTemperature($options['temperature']);
        if(array_key_exists('tools', $options))  $request = $request->allowAccessToTools($options['tools']);
        if(array_key_exists('conversation', $options))  $request = $request->includeMessages($converted_conversation);

        $request = $request->create();

        if($response = $llm_driver->text($request))
        {
            $results['success'] = true;
            $results['message'] = 'LLMSpeak call successful';
            $results['response'] = static::convertChatResultToArray($response);
            $results['tool_calls'] = $response->hasAToolCall();
            $results['text_messages'] = $response->hasATextMessage();
        }

        return $results;
    }

    public function get_default_credentials(string $connection): array
    {
        return LLM::driver($connection)::defaultCredentials();
    }

    public static function convertChatResultToArray(ChatResult $result): array
    {
        $results = $result->toArray();

        $results['messages'] = array_map(function($message){
            if($message instanceof TextMessage) return ['type' => 'text', 'role' => $message->role, 'content' => $message->content];
            elseif($message instanceof ToolCall) return ['type' => 'tool_call', 'role' => 'assistant', 'content' => $message->toArray()];
            elseif($message instanceof ToolResult) dd($message);
            else throw new \Exception('Unknown message type: ' . get_class($message));
        }, $results['messages']);

        VarDumper::dump(['convertChatResultToArray - results', $results]);

        return $results;
    }

}
