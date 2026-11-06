<?php

declare(strict_types=1);

namespace Durabix\Contract;

defined('ABSPATH') || exit;

interface HasHooks
{
    public function registerHooks(): void;
}
