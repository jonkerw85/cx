<?php

declare(strict_types=1);

namespace Cx\Tests\Graph;

use Cx\Graph\Project;
use Cx\Graph\ProjectGraph;
use Cx\Graph\ProjectGraphFactory;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class ProjectGraphTest extends TestCase
{
    #[DataProvider('affectedTestCases')]
    public function testAffected(ProjectGraph $initial, array $projects, array $expected): void
    {
        $this->assertEquals($expected, $initial->affected(...$projects));
    }

    public static function affectedTestCases(): iterable
    {
        yield [
            (new ProjectGraphFactory())->addProject(new Project(name: 'a', root: '/', scripts: []))->toGraph(),
            ['a'],
            ['a'],
        ];

        yield [
            (new ProjectGraphFactory())->addProject(new Project(name: 'a', root: '/', scripts: []))->toGraph(),
            ['b'],
            [],
        ];

        yield [
            (new ProjectGraphFactory())
                ->addProject(new Project(name: 'a', root: '/', scripts: []))
                ->addProject(new Project(name: 'b', root: '/', scripts: []))
                ->addDependency('a', 'b')
                ->toGraph(),
            ['a'],
            ['a'],
        ];

        yield [
            (new ProjectGraphFactory())
                ->addProject(new Project(name: 'a', root: '/', scripts: []))
                ->addProject(new Project(name: 'b', root: '/', scripts: []))
                ->addDependency('a', 'b')
                ->toGraph(),
            ['b'],
            ['a', 'b'],
        ];

        yield [
            (new ProjectGraphFactory())
                ->addProject(new Project(name: 'a', root: '/', scripts: []))
                ->addProject(new Project(name: 'b', root: '/', scripts: []))
                ->addProject(new Project(name: 'c', root: '/', scripts: []))
                ->addDependency('a', 'b')
                ->addDependency('b', 'c')
                ->toGraph(),
            ['b'],
            ['a', 'b'],
        ];

        yield [
            (new ProjectGraphFactory())
                ->addProject(new Project(name: 'a', root: '/', scripts: []))
                ->addProject(new Project(name: 'b', root: '/', scripts: []))
                ->addProject(new Project(name: 'c', root: '/', scripts: []))
                ->addDependency('a', 'b')
                ->addDependency('b', 'c')
                ->toGraph(),
            ['c'],
            ['a', 'b', 'c'],
        ];
    }
}
