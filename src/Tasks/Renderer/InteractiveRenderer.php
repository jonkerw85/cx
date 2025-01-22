<?php

declare(strict_types=1);

namespace Cx\Tasks\Renderer;

use Cx\Tasks\Task;
use Cx\Tasks\TaskCollection;
use Cx\Tasks\TaskRunnerOptions;
use Cx\Utils\Spinner;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\ConsoleSectionOutput;
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
    }

    public function nothingToRun(TaskRunnerOptions $options, TaskCollection $tasks): void
    {
        $this->section->writeln(<<<TXT

<bg=gray> Cx </>  <fg=gray>No tasks were run</>

TXT);
    }
}
