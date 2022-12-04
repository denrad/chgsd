<?php

declare(strict_types=1);

use app\services\DbService;
use app\value\Article;
use GuzzleHttp\HandlerStack;
use GuzzleLogMiddleware\LogMiddleware;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

require_once 'vendor/autoload.php';

Dotenv\Dotenv::createImmutable(__DIR__)->load();

$logger = new Logger('guzzle');  //A new PSR-3 Logger like Monolog
$logger->pushHandler(new StreamHandler('php://stdout', Logger::WARNING));
$stack = HandlerStack::create(); // will create a stack stack with middlewares of guzzle already pushed inside of it.
$stack->push(new LogMiddleware($logger));
$client = new GuzzleHttp\Client([
    'handler' => $stack,
]);

$db = new DbService(
  $_ENV['RESDB_URL'],
    $_ENV['RESTDB_API'],
    Article::class,
    $client,
);

$db->append(
    new Article(
        'https://cheb.ru',
        '23.08.2022',
        'Hello World',
    )
);
