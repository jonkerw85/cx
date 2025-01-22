<?php

declare(strict_types=1);

namespace Cx\Tasks;

use Cx\Graph\ProjectGraph;

final readonly class TaskRunnerOptions
{
    public function __construct(
        public ProjectGraph $projectGraph,
        /** @var list<string> */
        public array $targets = [],
        /** @var list<string> */
        public array $projects = [],
        public int $parallel = 3,
    ) {}
}
