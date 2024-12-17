<?php

declare(strict_types=1);

namespace Cx\Commands;

use Composer\Command\BaseCommand;
use Composer\Console\Input\InputOption;
use Cx\Graph\Project;
use Cx\Graph\ProjectGraphFactory;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

final class RunManyCommand extends BaseCommand
{
    protected function configure(): void
    {
        $this->setName('run-many')
            ->setDefinition([
                new InputOption('target', 't', InputOption::VALUE_REQUIRED),
                new InputOption('project', 'p', mode: InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY),
            ]);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $projectGraph = ProjectGraphFactory::createProjectGraph();

        $projects = $projectGraph->projects;

        if ($filteredProjects = $input->getOption('project')) {
            $projects = array_values(array_filter($projects, fn(Project $project) => in_array($project->name, $filteredProjects)));
        }

        foreach ($projects as $project) {
            if (! in_array($input->getOption('target'), [...$project->scripts, 'install'])) {
                $output->writeln(
                    sprintf('Skipping task "%s" in "%s"', $input->getOption('target'), $project->name),
                    OutputInterface::VERBOSITY_VERBOSE,
                );

                continue;
            }

            $output->writeln(sprintf('Running task "%s" in "%s"', $input->getOption('target'), $project->name));

            $process = (new Process([
                'composer',
                "--working-dir=./{$project->root}",
                ...$input->isInteractive() ? ["--ansi"] : [],
                $input->getOption('target'),
            ]));

            $process->run(fn($type, $buffer) => $output->write($buffer));

            $process->wait();
        }

        return self::SUCCESS;
    }

    public function isProxyCommand(): bool
    {
        return true;
    }
}
