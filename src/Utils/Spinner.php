<?php

declare(strict_types=1);

namespace Cx\Utils;

final class Spinner
{
    /** @var list<string> */
    protected array $frames = ['⠂', '⠒', '⠐', '⠰', '⠠', '⠤', '⠄', '⠆'];

    protected int $count = 0;

    public function next(): void
    {
        ++$this->count;
    }

    public function __toString(): string
    {
        return $this->frames[$this->count % count($this->frames)];
    }
}
