<?php

declare(strict_types=1);

namespace Cx\Commands;

use Cx\Graph\Project;
use Cx\Graph\ProjectGraphFactory;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

#[AsCommand(name: 'run-many')]
final class RunManyCommand extends Command
{
    protected function configure(): void
    {
        $this->setName('run-many')
            ->setDefinition([
                new InputOption('target', 't', InputOption::VALUE_REQUIRED),
                new InputOption('project', 'p', mode: InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY),
                new InputOption('bail', mode: InputOption::VALUE_NONE),
            ]);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $projectGraph = ProjectGraphFactory::createProjectGraph();

        $projects = $projectGraph->projects;

        if ($filteredProjects = $input->getOption('project')) {
            $projects = array_values(array_filter($projects, fn(Project $project) => in_array($project->name, $filteredProjects)));
        }

        $failed = false;

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

            if (! $process->isSuccessful()) {
                $failed = true;

                if ($input->getOption('bail')) {
                    return $process->getExitCode();
                }
            }
        }

        return $failed ? self::FAILURE : self::SUCCESS;
    }

    public function isProxyCommand(): bool
    {
        return true;
    }
}
