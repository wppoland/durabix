<?php

declare(strict_types=1);

namespace Durabix\Admin;

use Durabix\Contract\HasHooks;
use Durabix\Service\GreenClaimsAuditor;
use Durabix\Service\GuaranteeService;

defined('ABSPATH') || exit;

/**
 * Product edit screen fields and Green Claims Auditor notifications.
 */
final class ProductMetaBox implements HasHooks
{
    private GreenClaimsAuditor $auditor;

    public function __construct()
    {
        $this->auditor = new GreenClaimsAuditor();
    }

    public function registerHooks(): void
    {
        add_action('woocommerce_product_options_general_product_data', [$this, 'renderProductFields']);
        add_action('woocommerce_process_product_meta', [$this, 'saveProductFields']);
        add_action('add_meta_boxes', [$this, 'registerGreenClaimsBox']);
    }

    public function registerGreenClaimsBox(): void
    {
        add_meta_box(
            'durabix_green_claims',
            __('Green Claims Phrase Check', 'durabix'),
            [$this, 'renderGreenClaimsBox'],
            'product',
            'side',
            'high'
        );
    }

    public function renderGreenClaimsBox(\WP_Post $post): void
    {
        $product = wc_get_product($post->ID);
        if (! $product instanceof \WC_Product) {
            return;
        }

        $violations = $this->auditor->auditProduct($product);

        $variations = count(GreenClaimsAuditor::variationIds($product));
        if ($variations > GreenClaimsAuditor::MAX_VARIATIONS) {
            echo '<p style="margin:0 0 8px;">';
            /* translators: 1: number of variations checked, 2: number of variations the product has */
            printf(esc_html__('Only the first %1$d of %2$d variations were checked.', 'durabix'), (int) GreenClaimsAuditor::MAX_VARIATIONS, (int) $variations);
            echo '</p>';
        }

        if (empty($violations)) {
            echo '<p style="margin:0;">';
            echo esc_html__('None of the checked phrases found. This check looks for a short list of phrases only and does not assess other environmental claims.', 'durabix');
            echo '</p>';
            return;
        }

        echo '<div style="background:#fef2f2; border-left:4px solid #dc2626; padding:8px 10px; margin-bottom:8px;">';
        echo '<strong style="color:#991b1b;">' . esc_html__('Phrases to review:', 'durabix') . '</strong>';
        echo '<ul style="margin:6px 0 0 16px; padding:0; list-style:disc; font-size:12px; color:#7f1d1d;">';
        foreach ($violations as $v) {
            echo '<li>';
            echo '<strong>"' . esc_html($v['matched']) . '"</strong>: ' . esc_html($v['reason']);
            echo ' <em>(' . esc_html($v['legal_basis']) . ')</em>';
            echo '</li>';
        }
        echo '</ul>';
        echo '<p style="margin:6px 0 0; font-size:12px;">' . esc_html__('A match is not a breach in itself. Point 4a does not apply when you can show recognised excellent environmental performance relevant to the claim, or when the claim is specified in clear and prominent terms on the same medium, such as the online selling interface (recital 9 of Directive (EU) 2024/825). Point 4c covers climate claims based on offsetting.', 'durabix') . '</p>';
        echo '</div>';
    }

    public function renderProductFields(): void
    {
        wp_nonce_field('durabix_save_product_fields', 'durabix_product_nonce');

        echo '<div class="options_group durabix-options-group">';
        echo '<h4 style="padding-left:12px;">' . esc_html__('EU harmonised notice and GARAN label (Article 6(1)(l) and (la) of Directive 2011/83/EU)', 'durabix') . '</h4>';

        woocommerce_wp_checkbox([
            'id' => GuaranteeService::META_EXEMPT_GUARANTEE,
            'label' => __('Hide the notice (not a good)', 'durabix'),
            'description' => __('Hide the harmonised notice for this product, for example a digital download or a service: the notice covers goods. Article 6(1)(lb) of Directive 2011/83/EU requires a separate reminder for digital content and digital services, which Durabix does not show.', 'durabix'),
        ]);

        woocommerce_wp_text_input([
            'id' => GuaranteeService::META_DURABILITY_YEARS,
            'label' => __('Commercial Durability (Years)', 'durabix'),
            'description' => __('Only if the producer offers a commercial guarantee of durability at no extra cost for more than 2 years (e.g. 3, 5, 10). Values of 2 or less are not saved.', 'durabix'),
            'desc_tip' => true,
            'type' => 'number',
            'custom_attributes' => ['min' => '3', 'step' => '1'],
        ]);

        woocommerce_wp_text_input([
            'id' => GuaranteeService::META_DURABILITY_PRODUCER,
            'label' => __('Producer / Brand Name', 'durabix'),
            'description' => __('Name of the producer offering the commercial guarantee of durability, as printed on the GARAN label. The label shows only when this and the years are filled in. Up to 40 characters.', 'durabix'),
            'desc_tip' => true,
            'custom_attributes' => ['maxlength' => (string) \Durabix\Model\GaranLabel::MAX_CHARS],
        ]);

        woocommerce_wp_text_input([
            'id' => GuaranteeService::META_MODEL_IDENTIFIER,
            'label' => __('Model identifier', 'durabix'),
            'description' => __('Model identifier for which the producer offers the guarantee, as printed on the GARAN label. Empty uses the SKU. Up to 40 characters.', 'durabix'),
            'desc_tip' => true,
            'custom_attributes' => ['maxlength' => (string) \Durabix\Model\GaranLabel::MAX_CHARS],
        ]);

        woocommerce_wp_select([
            'id' => GuaranteeService::META_REPAIR_SCORE,
            'label' => __('Repairability Class (A - E)', 'durabix'),
            'description' => __('Repairability class from the energy label of a smartphone or slate tablet under Delegated Regulation (EU) 2023/1669, from 20 June 2025. Article 1 excludes phones and tablets with a main display the user can roll up or unroll, and smartphones for high security communication. Leave it empty for every other product. Durabix shows the class as a badge, not in the official label format.', 'durabix'),
            'desc_tip' => true,
            'options' => [
                '' => __('None', 'durabix'),
                'A' => 'A',
                'B' => 'B',
                'C' => 'C',
                'D' => 'D',
                'E' => 'E',
            ],
        ]);

        echo '</div>';
    }

    public function saveProductFields(int $productId): void
    {
        if (
            ! isset($_POST['durabix_product_nonce']) ||
            ! wp_verify_nonce(sanitize_key(wp_unslash($_POST['durabix_product_nonce'])), 'durabix_save_product_fields')
        ) {
            return;
        }

        if (! current_user_can('edit_post', $productId)) {
            return;
        }

        $product = wc_get_product($productId);
        if (! $product instanceof \WC_Product) {
            return;
        }

        $exempt   = isset($_POST[GuaranteeService::META_EXEMPT_GUARANTEE]) ? 'yes' : 'no';
        $years    = isset($_POST[GuaranteeService::META_DURABILITY_YEARS]) ? absint(wp_unslash($_POST[GuaranteeService::META_DURABILITY_YEARS])) : 0;
        $producer = isset($_POST[GuaranteeService::META_DURABILITY_PRODUCER]) ? mb_substr(sanitize_text_field(wp_unslash($_POST[GuaranteeService::META_DURABILITY_PRODUCER])), 0, \Durabix\Model\GaranLabel::MAX_CHARS) : '';
        $model    = isset($_POST[GuaranteeService::META_MODEL_IDENTIFIER]) ? mb_substr(sanitize_text_field(wp_unslash($_POST[GuaranteeService::META_MODEL_IDENTIFIER])), 0, \Durabix\Model\GaranLabel::MAX_CHARS) : '';
        $repair   = isset($_POST[GuaranteeService::META_REPAIR_SCORE]) ? self::parseClass(sanitize_text_field(wp_unslash($_POST[GuaranteeService::META_REPAIR_SCORE]))) : '';

        $product->update_meta_data(GuaranteeService::META_EXEMPT_GUARANTEE, $exempt);
        $product->update_meta_data(GuaranteeService::META_DURABILITY_YEARS, $years > 2 ? $years : '');
        $product->update_meta_data(GuaranteeService::META_DURABILITY_PRODUCER, $producer);
        $product->update_meta_data(GuaranteeService::META_MODEL_IDENTIFIER, $model);
        $product->update_meta_data(GuaranteeService::META_REPAIR_SCORE, $repair);
        $product->save_meta_data();
    }

    /**
     * Repairability class A to E (Annex II, Table 4, of Delegated Regulation (EU) 2023/1669); anything else stores nothing.
     */
    public static function parseClass(string $raw): string
    {
        $class = strtoupper(trim($raw));

        return in_array($class, GuaranteeService::REPAIR_CLASSES, true) ? $class : '';
    }
}
