<?php

namespace app\commands;

use app\services\DbService;
use app\value\Article;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class HelloCommand extends Command
{
    protected static $defaultName = 'hello';
    private DbService $db;

    public function __construct(string $name = null)
    {
        parent::__construct($name);
        $this->db = new DbService();
    }

    protected function configure(): void
    {
        $this->setDescription('Get the WhoAmI information.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $article = new Article(
            'https://example.com',
            '22.06.2022',
            'Hello World'
        );

        $this->db->append($article);

        return self::SUCCESS;
    }

}
