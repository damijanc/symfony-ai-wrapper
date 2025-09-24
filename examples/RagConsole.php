<?php

namespace Pyz\Zed\SymfonyAI\Communication\Console;

use Spryker\Zed\Kernel\Communication\Console\Console;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\AI\Platform\Bridge\Ollama\Ollama;
use Symfony\AI\Platform\Bridge\Ollama\PlatformFactory;
use Symfony\AI\Platform\Message\Message;
use Symfony\AI\Platform\Message\MessageBag;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\AI\Agent\Agent;
use Symfony\AI\Agent\Toolbox\AgentProcessor;
use Symfony\AI\Agent\Toolbox\Tool\SimilaritySearch;
use Symfony\AI\Agent\Toolbox\Toolbox;
use Pyz\Zed\SymfonyAI\Fixtures\Movies;
use Symfony\AI\Store\Bridge\Local\InMemoryStore;
use Symfony\AI\Store\Document\Loader\InMemoryLoader;
use Symfony\AI\Store\Document\Metadata;
use Symfony\AI\Store\Document\TextDocument;
use Symfony\AI\Store\Document\Vectorizer;
use Symfony\AI\Store\Indexer;
use Symfony\Component\Uid\Uuid;

class RagConsole extends Console
{
    /**
     * @var string
     */
    public const COMMAND_NAME = 'ai:rag';

    /**
     * @var string
     */
    public const DESCRIPTION = 'Example conversation';

    /**
     * @return void
     */
    protected function configure(): void
    {
        $this->setName(static::COMMAND_NAME);
        $this->setDescription(static::DESCRIPTION);

        parent::configure();
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     *
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {

        // initialize the store
        $store = new InMemoryStore();
        $documents = [];

        // create embeddings and documents
        foreach (Movies::all() as $i => $movie) {
            $documents[] = new TextDocument(
                id: Uuid::v4(),
                content: 'Title: '.$movie['title'].\PHP_EOL.'Director: '.$movie['director'].\PHP_EOL.'Description: '.$movie['description'],
                metadata: new Metadata($movie),
            );
        }

        // create embeddings for documents
        $platform = PlatformFactory::create('http://host.docker.internal:11434', HttpClient::create());
        $vectorizer = new Vectorizer($platform, new Ollama('nomic-embed-text'), new ConsoleLogger(new ConsoleOutput(ConsoleOutput::VERBOSITY_NORMAL)));
        $indexer = new Indexer(new InMemoryLoader($documents), $vectorizer, $store, logger: new ConsoleLogger(new ConsoleOutput(ConsoleOutput::VERBOSITY_NORMAL)));
        $indexer->index($documents);


        $model = new Ollama('llama3.2');


        $similaritySearch = new SimilaritySearch($vectorizer, $store);
        $toolbox = new Toolbox([$similaritySearch], logger: new ConsoleLogger(new ConsoleOutput(ConsoleOutput::VERBOSITY_NORMAL)));
        $processor = new AgentProcessor($toolbox);
        $agent = new Agent($platform, $model, [$processor], [$processor], logger: new ConsoleLogger(new ConsoleOutput(ConsoleOutput::VERBOSITY_NORMAL)));

        $messages = new MessageBag(
            Message::forSystem('Please answer all user questions only using SimilaritySearch function.'),
            Message::ofUser('Which movie fits the theme of the mafia?')
        );
        $result = $agent->call($messages);

        echo $result->getContent().\PHP_EOL;

        return static::CODE_SUCCESS;
    }
}
