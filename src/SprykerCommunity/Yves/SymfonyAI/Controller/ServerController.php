<?php

declare(strict_types=1);

namespace SprykerCommunity\Yves\SymfonyAI\Controller;

use Spryker\Yves\Kernel\Controller\AbstractController;
use SprykerCommunity\Yves\SymfonyAI\Model\ToolRegistry;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class ServerController extends AbstractController
{
    public function indexAction(Request $request): JsonResponse
    {
        // JSON only
        if ($request->getMethod() !== Request::METHOD_POST) {
            return $this->jsonError(null, -32600, 'Invalid request method; use POST');
        }

        $raw = $request->getContent();
        $data = json_decode($raw, true);

        if (json_last_error() !== JSON_ERROR_NONE || !is_array($data)) {
            return $this->jsonError(null, -32700, 'Parse error');
        }

        $id     = $data['id']     ?? null;
        $method = $data['method'] ?? null;
        $params = $data['params'] ?? [];

        if (!is_string($method)) {
            return $this->jsonError($id, -32600, 'Invalid Request (missing method)');
        }
        if (!is_array($params)) {
            return $this->jsonError($id, -32602, 'Invalid params (expected object)');
        }

        $registry = new ToolRegistry();

        // Basic discovery method that MCP clients expect
        if ($method === 'listTools') {
            return new JsonResponse([
                'jsonrpc' => '2.0',
                'id' => $id,
                'result' => $registry->listTools(),
            ]);
        }

        try {
            $result = $registry->call($method, $params);
            return new JsonResponse([
                'jsonrpc' => '2.0',
                'id' => $id,
                'result' => $result,
            ]);
        } catch (\InvalidArgumentException $e) {
            return $this->jsonError($id, -32601, $e->getMessage()); // Method not found
        } catch (\Throwable $e) {
            return $this->jsonError($id, -32000, $e->getMessage()); // Server error
        }
    }

    private function jsonError($id, int $code, string $message): JsonResponse
    {
        return new JsonResponse([
            'jsonrpc' => '2.0',
            'id' => $id,
            'error' => [
                'code' => $code,
                'message' => $message,
            ],
        ], 200, ['Content-Type' => 'application/json']);
    }
}
