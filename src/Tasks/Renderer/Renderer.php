<?php

declare(strict_types=1);

namespace Cx\Tasks\Renderer;

use Cx\Tasks\Task;
use Cx\Tasks\TaskCollection;
use Cx\Tasks\TaskRunnerOptions;

interface Renderer
{
    public function nothingToRun(TaskRunnerOptions $options, TaskCollection $tasks): void;

    public function started(TaskRunnerOptions $options, TaskCollection $tasks): void;

    public function taskStarted(TaskRunnerOptions $options, Task $task): void;

    public function taskFinished(TaskRunnerOptions $options, Task $task): void;

    public function finished(TaskRunnerOptions $options, TaskCollection $tasks): void;

    public function tick(TaskRunnerOptions $options, TaskCollection $tasks): void;
}
