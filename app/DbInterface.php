<?php

namespace app;

interface DbInterface
{
    public function toArray(): array;
    public function append($item): void;
}
