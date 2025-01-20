<?php

declare(strict_types=1);

namespace Cx\Utils;

use Illuminate\Support\Collection;

/**
 * @extends Collection<int, Task>
 */
final class TaskCollection extends Collection
{
    public function remaining(): self
    {
        return $this->reject(fn (Task $task) => $task->process->isTerminated());
    }

    public function finished(): self
    {
        return $this->filter(fn (Task $task) => $task->process->isTerminated());
    }

    public function successful(): self
    {
        return $this->filter(fn (Task $task) => $task->process->isSuccessful());
    }

    public function failed(): self
    {
        return $this->finished()->reject(fn (Task $task) => $task->process->isSuccessful());
    }

    public function projects(): Collection
    {
        return $this->pluck('project')->unique();
    }
}
