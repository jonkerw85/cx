<?php

declare(strict_types=1);

namespace Cx\Commands;

use Cx\Graph\Project;
use Cx\Graph\ProjectGraphFactory;
use Cx\Tasks\Renderer\InteractiveRenderer;
use Cx\Tasks\Renderer\StaticRenderer;
use Cx\Tasks\TaskRunner;
use Cx\Tasks\TaskRunnerOptions;
use Illuminate\Support\Str;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

#[AsCommand(name: 'run-many')]
final class RunManyCommand extends Command
{
    protected function configure(): void
    {
        $this->setDefinition([
            new InputArgument('target', mode:  InputArgument::REQUIRED | InputArgument::IS_ARRAY),
            new InputOption('project', 'p', mode: InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY),
            new InputOption('parallel', mode: InputOption::VALUE_REQUIRED, default: 3),
            new InputOption('outputStyle', mode: InputOption::VALUE_REQUIRED, default: 'dynamic', suggestedValues: ['dynamic', 'static']),
        ]);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $projectGraph = ProjectGraphFactory::createProjectGraph();

        $twig = new Environment(new FilesystemLoader(__DIR__ . '/../../resources/views'));

        $renderer = match (true) {
            ! $input->isInteractive() || ! $output instanceof ConsoleOutputInterface => new StaticRenderer($output),
            $input->getOption('outputStyle') === 'dynamic' => new InteractiveRenderer($output, $twig),
            default => new StaticRenderer($output),
        };

        $taskRunner = new TaskRunner(
            renderer: $renderer,
        );

        $result = $taskRunner->run(new TaskRunnerOptions(
            projectGraph: $projectGraph,
            targets: $input->getArgument('target'),
            projects: $input->getOption('project'),
            parallel: (int) $input->getOption('parallel'),
        ));

        return $result;
    }
}
