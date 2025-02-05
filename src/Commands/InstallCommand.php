<?php

declare(strict_types=1);

namespace Cx\Commands;

use Cx\Graph\ProjectGraphFactory;
use Illuminate\Support\Collection;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

#[AsCommand(name: 'install')]
final class InstallCommand extends Command
{
    protected function configure()
    {
        $this->setDefinition([
            new InputOption('project', 'p', mode: InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY),
        ]);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $projectGraph = ProjectGraphFactory::createProjectGraph();

        $rootProject = $projectGraph->getRoot();

        $filesystem = new Filesystem();

        $projects = collect($projectGraph->projects)
            ->when(
                $input->getOption('project'),
                fn(Collection $projects) => $projects->whereIn('name', $input->getOption('project')),
            );

        foreach ($projects as $project) {
            if ($project === $rootProject) {
                continue;
            }

            $output->writeln("Installing dependencies from lock file for {$project->name}");

            $filesystem->copy(
                'composer.lock',
                $projectComposerLock = $project->root . '/composer.lock',
            );

            try {
                (new Process(
                    ['composer', 'update', '--no-install', ...$projects->pluck('name')],
                    cwd: $project->root,
                ))->mustRun(fn ($_, $buf) => $output->write($buf));

                $process = (new Process(
                    ['composer', 'remove', '--unused'],
                    cwd: $project->root,
                ));

                if (! in_array($process->run(fn ($_, $buf) => $output->write($buf)), [0, 2])) {
                    throw new ProcessFailedException($process);
                }
            } finally {
                $filesystem->remove($projectComposerLock);
            }
        }

        return self::SUCCESS;
    }
}
