<?php

declare(strict_types=1);

namespace Cx\Tasks\Renderer;

use Cx\Tasks\Task;
use Cx\Tasks\TaskCollection;
use Cx\Tasks\TaskRunnerOptions;
use Illuminate\Support\Str;
use Symfony\Component\Console\Output\OutputInterface;

use function Termwind\parse;

final readonly class StaticRenderer implements Renderer
{
    public function __construct(
        private OutputInterface $output,
    ) {}

    public function started(TaskRunnerOptions $options, TaskCollection $tasks): void
    {
        $targets = implode(', ', $options->targets);
        $targetsLabel = Str::plural('target', $options->targets) . " {$targets}";
        $projectsLabel = $tasks->projects()->containsOneItem(
        ) ? "project {$tasks->projects()->first()->name}" : "{$tasks->projects()->count()} projects";

        $this->output->writeln(
            parse(
                <<<HTML
<div>
    <div class="py-1 space-y-1">
        <div>
            <div class="bg-blue px-1">Cx</div>
            <span class="text-blue ml-2">Running {$targetsLabel} for {$projectsLabel}:</span>
        </div>

        <ul class="list-none">
            {$tasks->projects()->map(
                    fn($project) => sprintf('<li><span class="text-gray-400">-</span> %s</li>', $project->name)
                )->join(PHP_EOL)}
        </ul>
    </div>
    <hr class="text-blue"/>
</div>
HTML
));
    }

    public function taskFinished(TaskRunnerOptions $options, Task $task): void
    {
        if ($task->process->isSuccessful()) {
            $this->output->writeln(
                <<<TXT

<fg=green>✔</> <fg=gray>cx run</> {$task->project->name} {$task->target}
TXT);
        } else {
            $this->output->writeln(
                <<<TXT

<fg=red>✗</> <fg=gray>cx run</> {$task->project->name} {$task->target}

TXT);

            $this->output->writeln($task->process->getErrorOutput());
        }
    }

    public function finished(TaskRunnerOptions $options, TaskCollection $tasks): void
    {
        $targets = implode(', ', $options->targets);
        $targetsLabel = Str::plural('target', $options->targets) . " {$targets}";
        $projectsLabel = $tasks->projects()->containsOneItem(
        ) ? "project {$tasks->projects()->first()->name}" : "{$tasks->projects()->count()} projects";

        if ($tasks->failed()->isEmpty()) {
            $this->output->writeln(
                parse(
                    <<<HTML
<div>
    <hr class="mt-1 text-green"/>
    <div class="py-1">
        <div class="bg-green px-1 mr-2">Cx</div>
        <span class="text-green">Successfully ran {$targetsLabel} for {$projectsLabel}</span>
    </div>
</div>
HTML));
        } else {
            $this->output->writeln(
                parse(
                    <<<HTML
<div class="space-y-1">
    <hr class="text-red"/>

    <div>
        <div class="bg-red px-1 mr-2">Cx</div>
        <span class="text-red">Running targets {$targetsLabel} for {$projectsLabel} failed</span>
    </div>

    <div class="text-gray-400">Failed tasks:</div>

    <ul class="list-none">
        {$tasks->failed()->map(fn (Task $task) => sprintf('<li><span class="text-gray-400">-</span> %s %s</li>', $task->project->name, $task->target))->join(PHP_EOL)}
    </ul>
</div>
HTML));

            $this->output->writeln("");
        }
    }

    public function taskStarted(TaskRunnerOptions $options, Task $task): void
    {
    }

    public function tick(TaskRunnerOptions $options, TaskCollection $tasks): void
    {
    }

    public function nothingToRun(TaskRunnerOptions $options, TaskCollection $tasks): void
    {
        $this->output->writeln(
            <<<TXT

<bg=gray> Cx </>  <fg=gray>No tasks were run</>

TXT);
    }
}
