<?php

declare(strict_types=1);

namespace Cx\Tasks\Renderer;

use Cx\Tasks\Task;
use Cx\Tasks\TaskCollection;
use Cx\Tasks\TaskRunnerOptions;
use Cx\Utils\Spinner;
use Illuminate\Support\Str;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\ConsoleSectionOutput;
use Termwind\Components\Hr;
use Twig\Environment;

use function Termwind\parse;

final readonly class InteractiveRenderer implements Renderer
{
    private Spinner $spinner;

    private ConsoleSectionOutput $section;

    public function __construct(
        private ConsoleOutputInterface $output,
        private Environment $twig,
    ) {
        $this->spinner = new Spinner();
        $this->section = $this->output->section();
    }

    public function tick(TaskRunnerOptions $options, TaskCollection $tasks): void
    {
        $this->spinner->next();

        $html = $this->twig->render('run-many-output.twig', [
            'tasks' => $tasks,
            'projects' => $tasks->projects(),
            'spinner' => $this->spinner,
            'targets' => collect($options->targets),
        ]);

        $this->section->overwrite(parse($html));
    }

    public function started(TaskRunnerOptions $options, TaskCollection $tasks): void
    {
    }

    public function taskStarted(TaskRunnerOptions $options, Task $task): void
    {
    }

    public function taskFinished(TaskRunnerOptions $options, Task $task): void
    {
    }

    public function finished(TaskRunnerOptions $options, TaskCollection $tasks): void
    {
        $this->section->overwrite("");

        if ($tasks->successful()->isNotEmpty()) {
            foreach ($tasks->successful() as $task) {
                $this->section->writeln(parse("<div><span class='text-green mx-2'>✔</span>  <span class='text-gray-400'>cx run</span> {$task->project->name} {$task->target}</div>"));
            }
        }

        foreach ($tasks->failed() as $task) {
            $this->section->writeln(parse("<div><span class='text-red mx-2'>✗</span>  <span class='text-gray-400'>cx run</span> {$task->project->name} {$task->target}</div>"));

            $this->section->writeln($this->indent($task->getCombinedOutput(), spaces: 5));
        }

        $targets = implode(', ', $options->targets);
        $targetsLabel = Str::plural('target', $options->targets) . " {$targets}";
        $projectsLabel = $tasks->projects()->containsOneItem() ? "project {$tasks->projects()->first()->name}" : "{$tasks->projects()->count()} projects";

        if ($tasks->failed()->isEmpty()) {
            $this->section->writeln(parse(<<<HTML
<div>
    <hr class="text-green"/>

    <div class="my-1"><span class='px-1 text-brightwhite bg-green'>Cx</span><span class='ml-2 text-green'>Successfully ran {$targetsLabel} for {$projectsLabel}</span></div>
</div>
HTML
));
        } else {
            $this->section->writeln(parse(<<<HTML
<div>
    <hr class="text-red"/>

    <div class="mt-1"><span class='px-1 text-brightwhite bg-red'>Cx</span><span class='ml-2 text-red'>Ran {$targetsLabel} for {$projectsLabel}</span></div>

    <div class="my-1 ml-1">
        <div class='text-gray-400'><span class="mx-2">✔</span><span>{$tasks->successful()->count()}/{$tasks->count()} succeeded</span</div>
        <div class="mt-1 text-black"><span class='mx-2 text-red'>✗</span>{$tasks->failed()->count()}/{$tasks->count()} targets failed, including the following:</span></div>
        <ul class="mt-1 ml-5 list-none">
            {$tasks->failed()->map(fn (Task $task) => "<li>- cx run <span class='text-black'>{$task->project->name} {$task->target}</span></li>")->join("\n")}
        </ul>
    </div>
</div>
HTML
));
        }
    }

    public function nothingToRun(TaskRunnerOptions $options, TaskCollection $tasks): void
    {
        $this->section->writeln(<<<TXT

<bg=gray> Cx </>  <fg=gray>No tasks were run</>

TXT);
    }

    private static function indent(string $text, int $spaces): string
    {
        return implode(PHP_EOL, array_map(fn (string $line) => str_repeat(' ', $spaces) . $line, explode(PHP_EOL, $text)));
    }
}
