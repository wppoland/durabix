<?php

declare(strict_types=1);

namespace Durabix\Admin;

use Durabix\Contract\HasHooks;
use Durabix\Service\GuaranteeService;

defined('ABSPATH') || exit;

/**
 * Settings admin interface for Durabix.
 */
final class SettingsPage implements HasHooks
{
    public const PAGE_SLUG = 'durabix-settings';

    public function registerHooks(): void
    {
        add_action('admin_menu', [$this, 'registerMenu']);
        add_action('admin_init', [$this, 'registerSettings']);
        // options.php checks manage_options unless the group says otherwise; the menu allows shop managers.
        add_filter('option_page_capability_' . self::PAGE_SLUG, static fn (): string => 'manage_woocommerce');
    }

    public function registerMenu(): void
    {
        add_submenu_page(
            'woocommerce',
            __('Durabix EU Guarantees', 'durabix'),
            __('EU Legal Guarantee', 'durabix'),
            'manage_woocommerce',
            self::PAGE_SLUG,
            [$this, 'renderPage']
        );
    }

    public function registerSettings(): void
    {
        register_setting(self::PAGE_SLUG, GuaranteeService::OPTION, [
            'type' => 'array',
            'sanitize_callback' => [$this, 'sanitizeSettings'],
            'default' => GuaranteeService::defaults(),
        ]);
    }

    /**
     * @param mixed $input
     * @return array<string, mixed>
     */
    public function sanitizeSettings($input): array
    {
        $input     = is_array($input) ? $input : [];
        $sanitized = [];

        $sanitized['enabled'] = ! empty($input['enabled']) ? '1' : '0';
        $sanitized['statutory_years'] = min(10, max(2, (int) ($input['statutory_years'] ?? 2)));
        $sanitized['notice_position'] = ($input['notice_position'] ?? '') === 'tab' ? 'tab' : 'single_summary';
        $sanitized['custom_notice'] = sanitize_text_field((string) ($input['custom_notice'] ?? ''));

        return $sanitized;
    }

    public function renderPage(): void
    {
        if (! current_user_can('manage_woocommerce')) {
            return;
        }

        $settings = GuaranteeService::settings();
        ?>
        <div class="wrap durabix-settings-wrap">
            <h1><?php echo esc_html__('Durabix - EU Legal Guarantee & Durability Notices', 'durabix'); ?></h1>
            <p class="description">
                <?php echo esc_html__('Legal guarantee reminder (Article 6(1)(l) of Directive 2011/83/EU), durability guarantee note (Article 6(1)(la)) and a green claims phrase check (Annex I, points 4a and 4c, of Directive 2005/29/EC). The reminder is plain text, not the harmonised notice of Implementing Regulation (EU) 2025/1960, which applies from 27 September 2026 and which this plugin does not display.', 'durabix'); ?>
            </p>

            <form method="post" action="options.php">
                <?php settings_fields(self::PAGE_SLUG); ?>
                <table class="form-table">
                    <tr>
                        <th scope="row"><?php echo esc_html__('Enable legal guarantee reminder', 'durabix'); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="<?php echo esc_attr(GuaranteeService::OPTION); ?>[enabled]" value="1" <?php checked(! empty($settings['enabled'])); ?> />
                                <?php echo esc_html__('Display the legal guarantee reminder, durability note and repairability class on product pages', 'durabix'); ?>
                            </label>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php echo esc_html__('Statutory Guarantee Duration', 'durabix'); ?></th>
                        <td>
                            <input type="number" min="2" max="10" step="1" name="<?php echo esc_attr(GuaranteeService::OPTION); ?>[statutory_years]" value="<?php echo esc_attr((string) ($settings['statutory_years'] ?? 2)); ?>" />
                            <span><?php echo esc_html__('years (at least 2 under Article 10(1) of Directive (EU) 2019/771)', 'durabix'); ?></span>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php echo esc_html__('Display Location', 'durabix'); ?></th>
                        <td>
                            <label>
                                <input type="radio" name="<?php echo esc_attr(GuaranteeService::OPTION); ?>[notice_position]" value="single_summary" <?php checked(($settings['notice_position'] ?? 'single_summary') === 'single_summary'); ?> />
                                <?php echo esc_html__('Under single product summary (near add to cart)', 'durabix'); ?>
                            </label><br/>
                            <label>
                                <input type="radio" name="<?php echo esc_attr(GuaranteeService::OPTION); ?>[notice_position]" value="tab" <?php checked(($settings['notice_position'] ?? '') === 'tab'); ?> />
                                <?php echo esc_html__('In a dedicated product tab ("Guarantee & Durability")', 'durabix'); ?>
                            </label>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php echo esc_html__('Custom Notice Override', 'durabix'); ?></th>
                        <td>
                            <input type="text" class="large-text" name="<?php echo esc_attr(GuaranteeService::OPTION); ?>[custom_notice]" value="<?php echo esc_attr($settings['custom_notice'] ?? ''); ?>" />
                            <p class="description">
                                <?php echo esc_html__('Leave empty to use the built-in reminder text. A custom text replaces the reminder in every language as typed and ignores the duration setting above.', 'durabix'); ?>
                            </p>
                        </td>
                    </tr>
                </table>
                <?php submit_button(); ?>
            </form>
            <?php $this->renderCompanionCard(); ?>
        </div>
        <?php
    }

    /**
     * Cross-recommendation card for sister plugins (Shoplex EU Compliance).
     * Strictly scoped to the Durabix settings screen per WordPress.org Guideline 11.
     */
    private function renderCompanionCard(): void
    {
        $isShoplexActive = defined('\\Shoplex\\VERSION') || (function_exists('is_plugin_active') && (is_plugin_active('shoplex/shoplex.php') || is_plugin_active('plogins-shoplex/shoplex.php')));

        ?>
        <div class="card" style="max-width: 800px; margin-top: 24px; padding: 18px 22px;">
            <h2 class="title" style="margin-top: 0; font-size: 15px;"><?php echo esc_html__('Complementary integration: Shoplex - EU Compliance for WooCommerce', 'durabix'); ?></h2>
            <p style="color: #50575e; line-height: 1.5;">
                <?php echo esc_html__('Pair Durabix legal guarantee notices with Shoplex to ensure end-to-end compliance with EU Consumer Rights, Omnibus price reduction rules, and statutory checkout disclosures.', 'durabix'); ?>
            </p>
            <?php if ($isShoplexActive) : ?>
                <p style="color: #007017; font-weight: 600; margin-bottom: 0;">
                    <span class="dashicons dashicons-yes-alt" style="vertical-align: text-bottom;"></span>
                    <?php echo esc_html__('Shoplex is active.', 'durabix'); ?>
                </p>
            <?php else : ?>
                <p style="margin-bottom: 0;">
                    <a href="<?php echo esc_url(admin_url('plugin-install.php?s=' . rawurlencode('Shoplex - EU Compliance for WooCommerce') . '&tab=search&type=term')); ?>" class="button button-secondary">
                        <?php echo esc_html__('Explore Shoplex EU Compliance', 'durabix'); ?> &rarr;
                    </a>
                </p>
            <?php endif; ?>
        </div>
        <?php
    }
}
