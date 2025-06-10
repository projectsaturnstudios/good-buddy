<?php

namespace Agents\GoodBuddy\Drivers\RobotBrains;

use Agents\GoodBuddy\Support\Facades\ToolTime;
use Illuminate\Support\Str;
use LLMSpeak\Schema\LLMResponse;
use LLMSpeak\Support\Facades\LLMs;
use LLMSpeak\Schema\Conversations\UserTalks;
use Agents\GoodBuddy\ChatHistory\ChatHistory;
use LLMSpeak\Exceptions\LLMSpeakRequestException;
use LLMSpeak\Schema\Conversations\AssistantSpeaks;
use Symfony\Component\VarDumper\VarDumper;

/**
 * @note - this is the only implementation in GoodBuddy that can use LLMSpeak methods and classes
 */
class LLMSpeakDriver extends RobotBrains
{
    private ?string $tool_user_id = null;
    private ?string $tool_name = null;

    public function formatConvo(string $provider, array $conversation): array
    {
        try {
            return array_map(function($segment) use($provider) {
                if($segment['role'] == 'user')
                {
                    if(is_string($segment['content']))
                    {
                        return (new UserTalks($segment['content']))->toProviderArray($provider);
                    }
                    else//(is_array($segment['content']))
                    {
                        if($provider == "anthropic") return $segment;
                        if($provider == "gemini") return $segment;
                        else return (new UserTalks($segment['content']['content']))->toProviderArray($provider);
                    }

                }
                else
                {
                    if(is_string($segment['content']))
                    {
                        return (new AssistantSpeaks($segment['content']))->toProviderArray($provider);
                    }
                    else//if(is_array($segment['content']))
                    {
                        if($provider == "anthropic") return $segment;
                        if($provider == "gemini") return $segment;
                        if($provider == "open-router") return $segment;
                        else return (new AssistantSpeaks($segment['content']['content']))->toProviderArray($provider);
                    }
                }

            }, $conversation);

        }
        catch(\TypeError $e)
        {
            dd([
                'provider' => $provider,
                'conversation' => $conversation,
            ], $e->getMessage());
        }
    }

    public function compileAndSend(
        string $provider, string $model,
        array $instructions, array $convo,
        array $tools, ChatHistory $chat
    ): array
    {
        $request = LLMs::prepare($provider, $model);
        $request = $request->prepareInstructions($instructions);
        foreach($convo as $segment)
        {
            if(array_key_exists('parts', $segment))
            {
                foreach($segment['parts'] as $part)
                {
                    if(array_key_exists('text', $part)) $request = $request->addTextToConversation($segment['role'], $part['text']);
                }
            }
            elseif(is_string($segment['content']))
            {
                if(!array_key_exists('role', $segment)) dd($segment);
                $request = $request->addTextToConversation($segment['role'], $segment['content']);
            }
            elseif(is_array($segment['content']))
            {
                if($provider == "anthropic") $request = $request->addArrayToConversation($segment['role'], $segment['content']);
                elseif($provider == "gemini") $request = $request->addArrayToConversation($segment['role'], $segment['content']);
                elseif($provider == "open-router")
                {
                    $segment['content'] = json_encode($segment['content']);
                    $request = $request->addWholeObjectToConversation($segment['role'], $segment);
                }
                else $request = $request->addTextToConversation($segment['role'], $segment['content']['content']);
            }
        }

        if(!empty($tools))
        {
            foreach($tools as $tool)
            {
                $request = $request->addTools($tool);
            }
        }

        if($response = $request->send())
        {
            VarDumper::dump('Agent Response');
            VarDumper::dump($response->toArray(), 'Agent Response');
            if(!empty(ltrim($response->message))) $chat = $chat->addToConversation('assistant', $response->message);
            if($response->hasToolRequest())
            {
                $payload = $this->parseToolRequest($provider, $response->tool_request, $chat);
                $chat = $chat::load($chat->session_id);
                $tool_result = ToolTime::execute($payload);
                VarDumper::dump('Executed tool');
                VarDumper::dump($tool_result, 'Executed tool');
                $chat = $this->processToolResult($provider, $tool_result, $chat);
                try {
                    return $this->compileAndSend($provider, $model, $instructions, $chat->toTranscription($provider, $model), $tools, $chat);
                }
                catch(\TypeError $e)
                {
                    dd($e);
                }

            }
            else
            {
                dd($chat->toTranscription($provider, $model));
                return $response->toArray();

                // @todo - post processing
                // @todo - deserialize into an array
            }
        }

        return [];
    }

    protected function processToolResult(string $provider, array $tool_result, ChatHistory $chat): ChatHistory
    {
        if($tool_result['content'])
        {
            foreach($tool_result['content'] as $result)
            {
                if($result['type'] == 'text')
                {
                    switch($provider)
                    {
                        case 'gemini':
                            $chat = $chat->addObjectToConversation('function', [
                                'functionResponse' => [
                                    'name' => $this->tool_user_id,
                                    'response' => ['payload' => json_decode($result['text'], true)]
                                ]
                            ]);
                            break;

                        case 'open-ai':
                            $chat = $chat->addObjectToConversation('tool', [
                                'role' => 'tool',
                                'tool_call_id'=> $this->tool_user_id ?? Str::uuid()->toString(),
                                'content' => $result['text']
                            ]);
                            break;

                        case 'open-router':
                            $chat = $chat->addWholeObjectToConversation('tool', [
                                'tool_call_id'=> $this->tool_user_id ?? Str::uuid()->toString(),
                                'name' => $this->tool_name ?? '',
                                'content' => json_decode($result['text'], true)
                            ]);
                            break;

                        case 'anthropic':
                            $chat = $chat->addObjectToConversation('user', [
                                'type' => 'tool_result',
                                'tool_use_id'=> $this->tool_user_id ?? Str::uuid()->toString(),
                                'content' => $result['text']
                            ]);
                            break;
                    }

                }
            }
        }

        return $chat->save();
    }

    protected function parseToolRequest(string $provider, array $tool_call, ChatHistory $chat): array
    {
        switch($provider)
        {
            case 'open-ai':
                $this->tool_user_id = $tool_call['id'];
                $payload = [
                    'tool' => $tool_call['function']['name'],
                    'params' => json_decode($tool_call['function']['arguments'], true),
                ];
                break;

            case 'open-router':
                $this->tool_user_id = $tool_call['id'];
                $this->tool_name = $tool_call['function']['name'];
                $payload = [
                    'tool' => $tool_call['function']['name'],
                    'params' => json_decode($tool_call['function']['arguments'], true),
                ];
                //$chat->addObjectToConversation('tool', $tool_call);
                break;

            case 'anthropic':
                $this->tool_user_id = $tool_call['id'];
                $chat->addObjectToConversation('assistant', $tool_call);
                $payload = [
                    'tool' => $tool_call['name'],
                    'params' => $tool_call['input']
                ];
                break;

            case 'gemini':
                $this->tool_user_id = $tool_call['name'];
                $chat->addObjectToConversation('function', ['functionCall' => $tool_call]);
                $payload = [
                    'tool' => $tool_call['name'],
                    'params' => $tool_call['args']
                ];
                break;

            default:
                $payload = [];
        }

        return $payload;

    }

    protected function process(LLMResponse $response): LLMResponse
    {
        dd($response);
    }


    /**
     * @param string $provider
     * @param string $model
     * @param array $parameters
     * @return array
     * @throws LLMSpeakRequestException
     */
    public function parseRequest(string $provider, string $model, array $parameters): array
    {
        $results = [];

        $request_object = LLMs::prepare($provider, $model);

        return $results;
    }
}
