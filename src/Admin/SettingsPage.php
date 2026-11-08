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
        $sanitized['notice_position'] = ($input['notice_position'] ?? '') === 'tab' ? 'tab' : 'single_summary';
        $sanitized['notice_checkout'] = ! empty($input['notice_checkout']) ? '1' : '0';
        $sanitized['notice_email'] = ! empty($input['notice_email']) ? '1' : '0';

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
                <?php echo esc_html__('The harmonised notice on the legal guarantee of conformity and the GARAN label for a producer guarantee of durability, as required by Article 6(1)(l) and (la) of Directive 2011/83/EU from 27 September 2026, in the official design of Implementing Regulation (EU) 2025/1960, plus a green claims phrase check (Annex I, points 4a and 4c, of Directive 2005/29/EC). The notice follows the site language.', 'durabix'); ?>
            </p>

            <form method="post" action="options.php">
                <?php settings_fields(self::PAGE_SLUG); ?>
                <table class="form-table">
                    <tr>
                        <th scope="row"><?php echo esc_html__('Enable the notice and label', 'durabix'); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="<?php echo esc_attr(GuaranteeService::OPTION); ?>[enabled]" value="1" <?php checked(! empty($settings['enabled'])); ?> />
                                <?php echo esc_html__('Display the harmonised notice, the GARAN label and the repairability class on product pages', 'durabix'); ?>
                            </label>
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
                        <th scope="row"><?php echo esc_html__('Checkout', 'durabix'); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="<?php echo esc_attr(GuaranteeService::OPTION); ?>[notice_checkout]" value="1" <?php checked(! empty($settings['notice_checkout'])); ?> />
                                <?php echo esc_html__('Show the notice above the place order button, classic and block checkout', 'durabix'); ?>
                            </label>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php echo esc_html__('Order confirmation email', 'durabix'); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="<?php echo esc_attr(GuaranteeService::OPTION); ?>[notice_email]" value="1" <?php checked(! empty($settings['notice_email'])); ?> />
                                <?php echo esc_html__('Add the notice and the GARAN labels (as attached files) to the processing and on-hold order emails sent to the customer', 'durabix'); ?>
                            </label>
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
                <?php echo esc_html__('Shoplex adds other EU consumer law modules, such as the Omnibus lowest price and checkout disclosures.', 'durabix'); ?>
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
