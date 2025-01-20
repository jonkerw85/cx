<?php

declare(strict_types=1);

namespace Cx\Commands;

use Cx\Git\Files;
use Cx\Graph\ProjectGraphFactory;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'graph')]
final class GraphCommand extends Command
{
    protected function configure(): void
    {
        $this->setName('graph')
            ->setDefinition([
                new InputOption('affected', mode: InputOption::VALUE_NONE),
                new InputOption('base', mode: InputOption::VALUE_REQUIRED, default: 'main'),
                new InputOption('head', mode: InputOption::VALUE_REQUIRED),
                new InputOption('uncommitted', mode: InputOption::VALUE_NONE),
                new InputOption('untracked', mode: InputOption::VALUE_NONE),
            ]);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $projectGraph = ProjectGraphFactory::createProjectGraph();

        if ($input->getOption('affected')) {
            $files = Files::diff(
                base: $input->getOption('base'),
                uncommitted: $input->getOption('uncommitted'),
                untracked: $input->getOption('untracked'),
                head: $input->getOption('head'),
            );

            $changedProjects = [];

            foreach ($files as $file) {
                foreach ($projectGraph->projects as $project) {
                    if (str_starts_with($file, $project->root)) {
                        $changedProjects[] = $project->name;
                    }
                }
            }

            $affectedProjects = $projectGraph->affected(...$changedProjects);
        }

        $output->write($projectGraph->toDot($affectedProjects ?? []));

        return self::SUCCESS;
    }
}
