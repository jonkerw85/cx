<?php

declare(strict_types=1);

namespace Cx;

use Composer\Plugin\Capability\CommandProvider as CommandProviderCapability;
use Cx\Commands\GraphCommand;

final readonly class CommandProvider implements CommandProviderCapability
{
    public function getCommands(): array
    {
        return [
            new GraphCommand(),
        ];
    }
}
