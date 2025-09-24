<?php

namespace SprykerCommunity\Yves\SymfonyAI\Model;

final class ToolRegistry
{
    /** @var array<string, callable> */
    private array $tools;

    public function __construct()
    {
        // Define minimal tools
        $this->tools = [
            'say_hello' => function (array $args): array {
                $name = isset($args['name']) ? (string)$args['name'] : 'world';
                return ['message' => "Hello {$name}!"];
            },
        ];
    }

    /** Simple discovery answer */
    public function listTools(): array
    {
        return [
            'tools' => [
                [
                    'name' => 'say_hello',
                    'description' => 'Return a friendly greeting',
                    'inputSchema' => [
                        'type' => 'object',
                        'properties' => [
                            'name' => ['type' => 'string'],
                        ],
                        'additionalProperties' => false,
                    ],
                ],
            ],
            'server' => [
                'name' => 'Spryker Yves MCP Server',
                'version' => '1.0.0',
            ],
        ];
    }

    /** @throws \InvalidArgumentException */
    public function call(string $method, array $params): array
    {
        if (!isset($this->tools[$method])) {
            throw new \InvalidArgumentException(sprintf('Method not found: %s', $method));
        }
        return ($this->tools[$method])($params);
    }
}
