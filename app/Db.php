<?php

declare(strict_types=1);

namespace app;

use app\value\Article;

class Db implements DbInterface
{
    public readonly string $filename;
    private array $structure = [];

    public function __construct(string $filename)
    {
        $this->filename = $filename;
        if (is_readable($this->filename)) {
            $this->structure = @unserialize(
                file_get_contents($this->filename),
                ['allow_classes' => [Article::class]]
            ) ?: [];
        }
    }

    public function __destruct()
    {
        file_put_contents($this->filename, serialize($this->structure));
    }

    public function toArray(): array
    {
        return $this->structure;
    }

    public function append($item): void
    {
        $this->structure[] = $item;
    }
}
