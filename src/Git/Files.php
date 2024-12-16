<?php

declare(strict_types=1);

namespace Cx\Git;

use Symfony\Component\Process\Process;

final class Files
{
    /**
     * @return list<string>
     */
    public static function diff(
        string $base,
        bool $uncommitted = false,
        bool $untracked = false,
        ?string $head = null,
    ): array  {
        return match (true) {
            $uncommitted => self::getUncommitedFiles(),
            $untracked => self::getUntrackedFiles(),
            $base && $head => self::getFilesUsingBaseAndHead($base, $head),
            default => [
                ...self::getFilesUsingBaseAndHead($base, 'HEAD'),
                ...self::getUncommitedFiles(),
                ...self::getUntrackedFiles(),
            ],
        };
    }

    /**
     * @return list<string>
     */
    public static function getUncommitedFiles(): array
    {
        return self::getFilesUsingBaseAndHead('HEAD', '.');
    }

    /**
     * @return list<string>
     */
    public static function getUntrackedFiles(): array
    {
        return self::parseGitOutput('git ls-files --others --exclude-standard');
    }

    /**
     * @return list<string>
     */
    public static function getFilesUsingBaseAndHead(string $base, string $head): array
    {
        return self::parseGitOutput("git diff --name-only --no-renames --relative $base $head");
    }

    /**
     * @return list<string>
     */
    private static function parseGitOutput(string $command): array
    {
        return array_values(array_filter(array_map(
            fn ($line) => trim($line),
            explode(PHP_EOL, Process::fromShellCommandline($command)->mustRun()->getOutput())
        )));
    }
}
