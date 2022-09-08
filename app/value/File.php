<?php

namespace app\value;

class File
{
    public function __construct(
        private readonly null|\CURLStringFile $content,
        private readonly null|string $filename
    ) {
    }

    public function isSuccess(): bool
    {
        return $this->content !== null;
    }

    public function getContent(): \CURLStringFile
    {
        return $this->content;
    }

    public function getFilename(): string
    {
        return $this->filename;
    }
}
