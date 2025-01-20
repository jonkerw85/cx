<?php

declare(strict_types=1);

namespace Cx\Graph;

use Composer\Json\JsonFile;
use Fhaculty\Graph\Graph;
use RuntimeException;
use Symfony\Component\Finder\Finder;

final class ProjectGraphFactory
{
    private Graph $graph;

    /** @var array<string, Project> */
    private array $projects = [];

    /** @var array<string, list<string>> */
    private array $dependencies = [];

    public function __construct()
    {
        $this->graph = new Graph();
    }

    public static function createProjectGraph(): ProjectGraph
    {
        $projectComposerRoots = Finder::create()
            ->in('.')
            ->exclude('vendor')
            ->name('composer.json');

        $factory = new self();

        foreach ($projectComposerRoots as $projectComposerRoot) {
            /** @var array{
             *     name?: string,
             *     require?: array<string, string>,
             *     scripts?: array<string, mixed>
             *  } $projectComposerConfig
             */
            $projectComposerConfig = json_decode($projectComposerRoot->getContents(), associative: true);

            $projectName = $projectComposerConfig['name'] ?? throw new RuntimeException('Composer file does not contain a name');

            $factory->addProject(new Project(
                name: $projectName,
                root: $projectComposerRoot->getRelativePath(),
                scripts: array_keys($projectComposerConfig['scripts'] ?? []),
            ));

            foreach ($projectComposerConfig['require'] ?? [] as $dependencyName => $version) {
                $factory->addDependency($projectName, $dependencyName);
            }
        }

        return $factory->toGraph();
    }

    public function addProject(Project $project): self
    {
        $this->graph->createVertex($project->name); /** @phpstan-ignore argument.type */

        $this->projects[$project->name] = $project;

        return $this;
    }

    public function addDependency(string $projectName, string $dependencyName): self
    {
        $this->dependencies[$projectName][] = $dependencyName;

        return $this;
    }

    public function toGraph(): ProjectGraph
    {
        $graph = new Graph();

        foreach ($this->projects as $project) {
            $graph->createVertex($project->name); /** @phpstan-ignore argument.type */
        }

        foreach ($this->dependencies as $projectName => $dependencies) {
            foreach ($dependencies as $dependency) {
                if (! $graph->hasVertex($dependency)) {
                    continue;
                }

                $graph->getVertex($projectName)->createEdgeTo($graph->getVertex($dependency));
            }
        }

        return new ProjectGraph(
            projects: $this->projects,
            graph: $graph,
        );
    }
}
