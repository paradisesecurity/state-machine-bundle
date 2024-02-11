<?php

declare(strict_types=1);

namespace ParadiseSecurity\Bundle\StateMachineBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class ParadiseSecurityStateMachineBundle extends Bundle
{
    public function getPath(): string
    {
        return dirname(__DIR__);
    }
}
