<?php

declare(strict_types=1);

namespace Durabix;

use Durabix\Admin\ProductMetaBox;
use Durabix\Admin\SettingsPage;
use Durabix\Service\GuaranteeService;

defined('ABSPATH') || exit;

/**
 * Main plugin coordinator for Durabix.
 */
final class Plugin
{
    private static ?self $instance = null;

    private GuaranteeService $guaranteeService;
    private ProductMetaBox $productMetaBox;
    private SettingsPage $settingsPage;

    public static function instance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    private function __construct()
    {
        $this->guaranteeService = new GuaranteeService();
        $this->productMetaBox   = new ProductMetaBox();
        $this->settingsPage     = new SettingsPage();
    }

    public function boot(): void
    {
        $this->guaranteeService->registerHooks();
        $this->productMetaBox->registerHooks();
        $this->settingsPage->registerHooks();
    }
}
