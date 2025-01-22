<?php

declare(strict_types=1);

namespace Cx\Tasks;

use Cx\Graph\Project;
use Symfony\Component\Process\Process;

final readonly class Task
{
    public function __construct(
        public Project $project,
        public string $target,
        public Process $process,
    ) {}

    public function start(): void
    {
        $this->process->start();
    }
}
