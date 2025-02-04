<?php

declare(strict_types=1);

namespace Cx\Tests\Integration;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Process;

final class InstallTest extends TestCase
{
    private Filesystem $filesystem;
    private string $workdir;

    public function setUp(): void
    {
        $this->filesystem = new Filesystem();
        $this->filesystem->mkdir($this->workdir = __DIR__ . '/storage/' . uniqid());

        $this->composer(['init', '--name=example/my-project'], cwd: $this->workdir);
        $this->composer(['config', 'allow-plugins.wikimedia/composer-merge-plugin', 'true'], cwd: $this->workdir);
        $this->composer(['config', 'extra.merge-plugin.include', '--json', '["libs/*/composer.json"]'], cwd: $this->workdir);
        $this->composer(['require', 'wikimedia/composer-merge-plugin'], cwd: $this->workdir);
    }

    protected function tearDown(): void
    {
        $this->filesystem->remove($this->workdir);
    }

    #[Test]
    public function it_installs_packages_in_projects(): void
    {
        // Create a dependency
        $this->filesystem->mkdir($dependency = $this->workdir . '/libs/my-dependency');
        $this->composer(['init', '--name=example/my-dependency'], cwd: $dependency);
        $this->composer(['require', 'illuminate/support:^11.20', '--no-install'], cwd: $dependency);

        $this->composer(['update', 'illuminate/support:11.20.0'], cwd: $this->workdir);
        $this->assertEquals(['v11.20.0'], json_decode($this->composer(['show', 'illuminate/support', '--format=json'], cwd: $this->workdir), associative: true)['versions']);

        $this->cx(['install'], cwd: $this->workdir);
        $this->assertEquals(['v11.20.0'], json_decode($this->composer(['show', 'illuminate/support', '--format=json'], cwd: $dependency), associative: true)['versions']);
    }

    private function composer(array $command, string $cwd = null): string
    {
        return $this->exec(['composer', ...$command], cwd: $cwd);
    }

    private function cx(array $command, string $cwd = null): string
    {
        return $this->exec([__DIR__ . '/../../cx', ...$command], cwd: $cwd);
    }

    private function exec(array $command, string $cwd = null): string
    {
        $process = (new Process([...$command], cwd: $cwd));
        $process->mustRun();

        return $process->getOutput();
    }

}
