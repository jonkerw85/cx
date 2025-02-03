<?php

declare(strict_types=1);

namespace Cx\Tasks;

use Cx\Graph\Project;
use Symfony\Component\Process\Process;

final class Task
{
    private string $output = '';

    public function __construct(
        public readonly Project $project,
        public readonly string $target,
        public readonly Process $process,
    ) {}

    public function start(): void
    {
        $this->process->start(
            fn ($_, $buf) => $this->output .= $buf,
        );
    }

    public function getCombinedOutput(): string
    {
        return $this->output;
    }
}
