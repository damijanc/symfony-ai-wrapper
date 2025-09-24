# Spryker Symfony AI Wrapper

[![MIT Licensed](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE)

A Spryker  module that provides an integration with Symfony AI.

What you can use this for: quickly add AI-powered features to your Spryker  apps, such as chatbots, assistants, content generation, or Retrieval-Augmented Generation (RAG) over your own data. It leverages the Symfony AI framework, which provides a unified API for AI models and tools, so you can call models, embed documents, and wire up tool-based agents consistently. In this repo, we show how to use Symfony AI with a local Ollama server to run fully local chat and RAG workflows.

This repository  contains example console commands that demonstrate:
- A simple chat using a local Ollama model.
- A minimal RAG flow using an embedding model + a chat model.

Both examples expect a locally running Ollama server that is reachable from within your Docker containers.

---

## Overview

- Chat example: `examples/ChatConsole.php`
- RAG example: `examples/RagConsole.php`

Both examples target an local Ollama endpoint at `http://localhost:11434` and use the following models by default:
- Chat model: `llama3.2`
- Embedding model (for RAG): `nomic-embed-text`

If you use different models, adjust the model names in the example files accordingly.

---

## Prerequisites: Local Ollama service

1) Install Ollama on your host machine:
- macOS: `brew install ollama`
- Linux: follow https://ollama.com/download
- Windows: install the Ollama app from https://ollama.com/download

2) Make Ollama listen on all interfaces so Docker containers can reach it:
- Set the environment variable and start the server:
  - macOS/Linux: `OLLAMA_HOST=0.0.0.0:11434 ollama serve`
  - Windows (PowerShell): `$env:OLLAMA_HOST = '0.0.0.0:11434'; ollama serve`

3) Pull the required models:
```
ollama pull llama3.2
ollama pull nomic-embed-text
```

You can verify Ollama is serving by opening http://localhost:11434 or running:
```
curl http://localhost:11434/api/tags
```

---

## Make Ollama reachable from Docker

The examples use `http://host.docker.internal:11434` from inside your containers.

- macOS and Windows: `host.docker.internal` is available by default.
- Linux: you typically need to add an extra host entry so containers can resolve `host.docker.internal` to the host gateway.

Example docker-compose service snippet (Linux):
```
services:
  zed:
    image: your-spryker-zed-image
    extra_hosts:
      - "host.docker.internal:host-gateway"
    # ... the rest of your service definition
```

If your Docker engine does not support `host-gateway`, you can alternatively run Ollama as a container in the same Docker network and reference it by service name. For example:
```
services:
  ollama:
    image: ollama/ollama:latest
    pull_policy: always
    ports:
      - "11434:11434"
    environment:
      - OLLAMA_HOST=0.0.0.0:11434
    volumes:
      - ollama:/root/.ollama

  zed:
    image: your-spryker-zed-image
    depends_on:
      - ollama
    # now use http://ollama:11434 as the endpoint

volumes:
  ollama:
```
In this case, update the example files to use `http://ollama:11434` instead of `host.docker.internal`.

---

## Installation (module)

Install the module in your Spryker project:

```
composer require damijanc/symfony-ai-wrapper
```

Register the `SprykerCommunity` namespace so the Spryker kernel can locate the module's classes.

File: `config/Shared/config_default.php`
```
<?php

use Spryker\Shared\Kernel\KernelConstants;

// ...
$config[KernelConstants::CORE_NAMESPACES] = [
    'Spryker',
    'SprykerCommunity',
];
```

---

## Using the examples

The example console commands live under the `Pyz\Zed\SymfonyAI\Communication\Console` namespace:
- `ai:chat` (simple interactive chat)
- `ai:rag` (RAG example with similarity search)

To try them in your Spryker project:
1) Copy the example classes from this repository into your project (or require this package and wire the commands as part of your Zed console application bootstrapping).
2) Ensure your Zed container can reach Ollama as described above.
3) From inside your Zed container, run:
```
docker/sdk cli vendor/bin/console ai:chat
```
or
```
docker/sdk cli vendor/bin/console ai:rag
```

Notes:
- The chat example uses model `llama3.2`.
- The RAG example uses `nomic-embed-text` for embeddings and `llama3.2` for generation.
- If you see an "InvalidArgumentException" mentioning the model, make sure the model is pulled in Ollama, or change the model name in the example to one you have available locally.

---

## Troubleshooting

- Connection refused from container:
  - Verify Ollama is running and listening on `0.0.0.0:11434` on the host.
  - On Linux, add `extra_hosts: ["host.docker.internal:host-gateway"]` to your service in docker-compose.
  - Run the command `docker/sdk up` to apply the changes.
  - Test from inside the container: `curl http://host.docker.internal:11434/api/tags`.

- Model not found / invalid model:
  - Pull the model: `ollama pull llama3.2` or `ollama pull nomic-embed-text`.
  - Adjust the model names in `examples/ChatConsole.php` and `examples/RagConsole.php`.

- Slow first response:
  - Ollama may need to download model weights or warm up on first run.

---

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.
