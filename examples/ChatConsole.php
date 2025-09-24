<?php

namespace Pyz\Zed\SymfonyAI\Communication\Console;

use InvalidArgumentException;
use Spryker\Zed\Kernel\Communication\Console\Console;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\AI\Platform\Bridge\Ollama\Ollama;
use Symfony\AI\Platform\Bridge\Ollama\PlatformFactory;
use Symfony\AI\Platform\Message\Message;
use Symfony\AI\Platform\Message\MessageBag;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\HttpClient\HttpClient;

class ChatConsole extends Console
{
    /**
     * @var string
     */
    public const COMMAND_NAME = 'ai:chat';

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
        $platform = PlatformFactory::create( 'http://host.docker.internal:11434', HttpClient::create());
        $model = new Ollama('llama3.2');


        $helper = new QuestionHelper();

        while (true) {
            $question = new Question('Do you have a question for me?' . PHP_EOL);
            $question->setTrimmable(false);
            // if the users inputs 'elsa ' it will not be trimmed and you will get 'elsa ' as value
            $userInput = $helper->ask($input, $output, $question);

            if ($userInput === 'exit' . PHP_EOL) {
                break;
            }


            $messages = new MessageBag(
                Message::ofUser($userInput),
            );

            try {
                $result = $platform->invoke($model, $messages);
                echo $result->getResult()->getContent().\PHP_EOL;
            } catch (InvalidArgumentException $e) {
                echo $e->getMessage()."\nMaybe use a different model?\n";
            }
        }


        return static::CODE_SUCCESS;
    }
}
