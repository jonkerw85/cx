<?php

declare(strict_types=1);

namespace Cx\Utils;

final class Spinner
{
    /** @var list<string> */
    protected array $frames = ['⠂', '⠒', '⠐', '⠰', '⠠', '⠤', '⠄', '⠆'];

    protected int $count = 0;

    private bool $stopped = false;

    private function next(): void
    {
        ++$this->count;
    }

    public function stop(): void
    {
        $this->stopped = true;
    }

    public function __toString(): string
    {
        return $this->frames[$this->count % count($this->frames)];
    }

    public static function with(callable $callback, int $interval = 200): void
    {
        $spinner = new self();

        do {
            $callback($spinner);

            $spinner->next();

            usleep($interval * 1000);
        } while (! $spinner->stopped);
    }
}
