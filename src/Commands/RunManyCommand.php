<?php

declare(strict_types=1);

namespace Cx\Commands;

use Cx\Graph\Project;
use Cx\Graph\ProjectGraphFactory;
use Cx\Utils\Spinner;
use Cx\Utils\Task;
use Cx\Utils\TaskCollection;
use Illuminate\Support\Str;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

use Twig\Environment;

use Twig\Extra\Html\HtmlExtension;

use Twig\Loader\FilesystemLoader;

use function Termwind\parse;

#[AsCommand(name: 'run-many')]
final class RunManyCommand extends Command
{
    protected function configure(): void
    {
        $this->setDefinition([
            new InputArgument('target', mode:  InputArgument::REQUIRED | InputArgument::IS_ARRAY),
            new InputOption('project', 'p', mode: InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY),
            new InputOption('bail', mode: InputOption::VALUE_NONE),
        ]);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if (!$output instanceof ConsoleOutputInterface) {
            throw new \LogicException('This command accepts only an instance of "ConsoleOutputInterface".');
        }

        $projectGraph = ProjectGraphFactory::createProjectGraph();

        $projects = $projectGraph->projects;

        if ($filteredProjects = $input->getOption('project')) {
            $projects = array_values(
                array_filter($projects, fn(Project $project) => in_array($project->name, $filteredProjects)),
            );
        }

        $section = $output->section();

        $tasksToRun = collect($projects)
            ->flatMap(fn(Project $project)
                => collect([...$project->scripts, 'install', 'validate'])
                ->filter(fn(string $script) => Str::is($input->getArgument('target'), $script))
                ->map(fn(string $script) => [$project, $script]));

        if ($tasksToRun->isEmpty()) {
            $section->writeln(parse('<p><strong class="bg-gray">&nbsp;Cx&nbsp;</strong> No tasks were run</p>'));

            return self::SUCCESS;
        }

        $tasks = new TaskCollection($tasksToRun->map(function (array $task) {
            [$project, $task] = $task;

            return new Task(
                project: $project,
                target: $task,
                process: new Process([
                    'composer',
                    $task,
                ], realpath($project->root) ?: null),
            );
        }));

        $tasks->each(fn(Task $task) => $task->start());

        Spinner::with(function (Spinner $spinner) use ($input, $section, $tasks) {
            $twig = new Environment(new FilesystemLoader(__DIR__ . '/../../resources/views'));
            $twig->addExtension(new HtmlExtension());
            $html = $twig->render('run-many-output.twig', [
                'tasks' => $tasks,
                'projects' => $tasks->projects(),
                'spinner' => $spinner,
                'targets' => collect($input->getArgument('target')),
            ]);

            $section->overwrite(parse($html));

            if ($input->getOption('bail') && $tasks->failed()->isNotEmpty()) {
                $spinner->stop();
            }

            if ($tasks->remaining()->isEmpty()) {
                $spinner->stop();
            }
        });

        return $tasks->failed()->isEmpty() ? self::SUCCESS : self::FAILURE;
    }
}
