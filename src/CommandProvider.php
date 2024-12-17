<?php

declare(strict_types=1);

namespace Cx;

use Composer\Plugin\Capability\CommandProvider as CommandProviderCapability;
use Cx\Commands\AffectedCommand;
use Cx\Commands\GraphCommand;
use Cx\Commands\RunManyCommand;

final readonly class CommandProvider implements CommandProviderCapability
{
    public function getCommands(): array
    {
        return [
            new AffectedCommand(),
            new GraphCommand(),
            new RunManyCommand(),
        ];
    }
}
