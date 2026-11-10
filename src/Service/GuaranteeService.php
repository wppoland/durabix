<?php

declare(strict_types=1);

namespace Durabix\Service;

use Durabix\Contract\HasHooks;
use Durabix\Model\GaranLabel;
use Durabix\Model\HarmonisedNotice;

defined('ABSPATH') || exit;

/**
 * Shows the harmonised notice, the GARAN label and the repairability class.
 *
 * Article 6(1)(l) and (la) of Directive 2011/83/EU, as amended by Directive (EU) 2024/825, require
 * the notice and the label from 27 September 2026, in the design of Implementing Regulation (EU)
 * 2025/1960 (Articles 1 and 2, Annexes I and II).
 */
final class GuaranteeService implements HasHooks
{
    public const OPTION = 'durabix_settings';

    public const META_DURABILITY_YEARS    = '_durabix_durability_years';
    public const META_DURABILITY_PRODUCER = '_durabix_durability_producer';
    public const META_MODEL_IDENTIFIER    = '_durabix_model_identifier';
    public const META_REPAIR_SCORE        = '_durabix_repair_score'; // repairability class A to E
    public const META_EXEMPT_GUARANTEE    = '_durabix_exempt_guarantee'; // e.g. digital goods / services

    /** Repairability classes of Delegated Regulation (EU) 2023/1669, Annex II, Table 4. */
    public const REPAIR_CLASSES = ['A', 'B', 'C', 'D', 'E'];

    /** Order emails that confirm the contract to the customer. */
    public const CONFIRMATION_EMAILS = ['customer_processing_order', 'customer_on_hold_order'];

    /**
     * @var array<string, mixed>
     */
    private array $settings;

    public function __construct()
    {
        $this->settings = self::settings();
    }

    /**
     * Defaults apply until the merchant first saves the settings page.
     *
     * @return array<string, string>
     */
    public static function defaults(): array
    {
        return [
            'enabled'         => '1',
            'notice_position' => 'single_summary',
            'notice_checkout' => '1',
            'notice_email'    => '1',
            'notice_footer'   => '0',
            'national_note'   => '',
        ];
    }

    /**
     * Stored settings over the defaults.
     *
     * @return array<string, mixed>
     */
    public static function settings(): array
    {
        $raw = get_option(self::OPTION, []);

        return array_merge(self::defaults(), is_array($raw) ? $raw : []);
    }

    public function isEnabled(): bool
    {
        return ! empty($this->settings['enabled']);
    }

    public function registerHooks(): void
    {
        if (! $this->isEnabled()) {
            return;
        }

        add_action('wp_enqueue_scripts', [$this, 'enqueueStyles']);
        add_shortcode('durabix_notice', [$this, 'renderShortcode']);

        if (! empty($this->settings['notice_footer'])) {
            add_action('wp_footer', [$this, 'renderFooterNotice']);
        }

        if ((string) ($this->settings['notice_position'] ?? 'single_summary') === 'single_summary') {
            add_action('woocommerce_single_product_summary', [$this, 'renderNotice'], 25);
            add_action('woocommerce_single_product_summary', [$this, 'renderGaranLabel'], 26);
            add_action('woocommerce_single_product_summary', [$this, 'renderRepairabilityScore'], 27);
        } else {
            add_filter('woocommerce_product_tabs', [$this, 'registerGuaranteeTab']);
        }

        add_filter('woocommerce_available_variation', [$this, 'variationGaran'], 10, 3);

        if (! empty($this->settings['notice_checkout'])) {
            add_action('woocommerce_review_order_before_submit', [$this, 'renderCheckoutNotice']);
            add_filter('render_block_woocommerce/checkout-actions-block', [$this, 'prependCheckoutNotice']);
        }

        if (! empty($this->settings['notice_email'])) {
            add_action('woocommerce_email_after_order_table', [$this, 'renderEmailNotice'], 20, 4);
            add_filter('woocommerce_email_attachments', [$this, 'attachGaranLabels'], 10, 4);
        }
    }

    public function enqueueStyles(): void
    {
        if (! (is_product() || is_checkout() || ! empty($this->settings['notice_footer']))) {
            return;
        }

        wp_enqueue_style('durabix', plugins_url('assets/css/durabix.css', \Durabix\PLUGIN_FILE), [], \Durabix\VERSION);
    }

    /**
     * The harmonised notice on the product page (Article 6(1)(l)).
     */
    public function renderNotice(): void
    {
        global $product;
        if (! $product instanceof \WC_Product || ! self::showsNotice($product) || ! self::shownIn('product')) {
            return;
        }

        echo $this->noticeHtml(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- built from escaped parts in noticeHtml().
    }

    /**
     * Classic checkout: the notice before the place order button when the cart holds a good.
     */
    public function renderCheckoutNotice(): void
    {
        if ($this->cartHasGoods() && self::shownIn('checkout')) {
            echo $this->noticeHtml(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- built from escaped parts in noticeHtml().
        }
    }

    /**
     * Block checkout: the same notice above the place order button.
     */
    public function prependCheckoutNotice(string $content): string
    {
        return $this->cartHasGoods() && self::shownIn('checkout') ? $this->noticeHtml() . $content : $content;
    }

    /**
     * The notice in the order confirmation email, the durable-medium copy of the pre-contractual information.
     *
     * @param mixed $order
     * @param mixed $sentToAdmin
     * @param mixed $plainText
     * @param mixed $email
     */
    public function renderEmailNotice($order, $sentToAdmin = false, $plainText = false, $email = null): void
    {
        if ($sentToAdmin || ! $order instanceof \WC_Order || ! is_object($email) || ! in_array($email->id ?? '', self::CONFIRMATION_EMAILS, true)) {
            return;
        }

        $hasGoods = false;
        foreach ($order->get_items() as $item) {
            $product = $item instanceof \WC_Order_Item_Product ? $item->get_product() : null;
            if ($product instanceof \WC_Product && self::showsNotice($product)) {
                $hasGoods = true;
                break;
            }
        }
        $labels = self::orderGaranLabels($order);
        $hasGoods = $hasGoods && self::shownIn('email', $order);
        if (! $hasGoods && $labels === []) {
            return;
        }

        $language = HarmonisedNotice::language(determine_locale());

        if ($plainText) {
            if ($hasGoods) {
                echo "\n" . esc_html__('Your legal guarantee rights', 'durabix') . ': ' . esc_url_raw(HarmonisedNotice::infoUrl($language)) . "\n";
                if ($this->nationalNote() !== '') {
                    echo esc_html($this->nationalNote()) . "\n";
                }
            }
            foreach ($labels as $label) {
                echo "\n" . esc_html(self::garanLine($label)) . "\n";
            }
            return;
        }

        if ($hasGoods) {
            printf(
                '<div style="margin:0 0 24px;"><p><strong>%1$s</strong></p><p><img src="%2$s" width="600" style="max-width:100%%;height:auto;display:block;" alt="%3$s" /></p><p><a href="%4$s">%5$s</a></p></div>',
                esc_html__('Your legal guarantee rights', 'durabix'),
                esc_url(plugins_url(HarmonisedNotice::imageFile($language), \Durabix\PLUGIN_FILE)),
                esc_attr__('EU harmonised notice on the legal guarantee of conformity', 'durabix'),
                esc_url(HarmonisedNotice::infoUrl($language)),
                esc_html__('More about your legal guarantee rights on Your Europe', 'durabix')
            );
            if ($this->nationalNote() !== '') {
                echo '<p style="margin:0 0 24px;">' . esc_html($this->nationalNote()) . '</p>';
            }
        }
        foreach ($labels as $label) {
            echo '<p style="margin:0 0 12px;">' . esc_html(self::garanLine($label)) . '</p>';
        }
    }

    /**
     * The confirmation must repeat the Article 6(1) information (Article 8(7)(a) of Directive 2011/83/EU),
     * so it carries each GARAN label as an SVG file: mail clients do not show inline SVG.
     *
     * @param mixed $attachments
     * @param mixed $emailId
     * @param mixed $order
     * @param mixed $email
     * @return mixed
     */
    public function attachGaranLabels($attachments, $emailId = '', $order = null, $email = null)
    {
        if (! is_array($attachments) || ! in_array($emailId, self::CONFIRMATION_EMAILS, true) || ! $order instanceof \WC_Order) {
            return $attachments;
        }

        $labels = self::orderGaranLabels($order);
        if ($labels === []) {
            return $attachments;
        }

        $dir = wp_upload_dir()['basedir'] . '/durabix';
        if (! wp_mkdir_p($dir)) {
            return $attachments;
        }

        foreach ($labels as $key => $label) {
            $file = $dir . '/garan-' . $key . '.svg';
            if (is_file($file) || file_put_contents($file, GaranLabel::full(dirname(\Durabix\PLUGIN_FILE), $label['years'], $label['brand'], $label['model'])) !== false) {
                $attachments[] = $file;
            }
        }

        return $attachments;
    }

    /**
     * GARAN labels of an order, one per distinct duration, brand and model.
     *
     * @return array<string, array{years: int, brand: string, model: string}>
     */
    private static function orderGaranLabels(\WC_Order $order): array
    {
        $labels = [];
        foreach ($order->get_items() as $item) {
            $product = $item instanceof \WC_Order_Item_Product ? $item->get_product() : null;
            $label   = $product instanceof \WC_Product ? self::garan($product) : null;
            if ($label !== null) {
                $labels[self::garanKey($label)] = $label;
            }
        }

        return $labels;
    }

    /**
     * @param array{years: int, brand: string, model: string} $label
     */
    private static function garanLine(array $label): string
    {
        /* translators: 1: brand, 2: model identifier, 3: duration in years of the producer's commercial guarantee of durability */
        return sprintf(__('GARAN label: %1$s %2$s, %3$d-year producer guarantee of durability. The label is attached to this email.', 'durabix'), $label['brand'], $label['model'], $label['years']);
    }

    /**
     * The fields the GARAN label shows, or null when it does not apply. A variation's own
     * duration, brand or model wins; each one it leaves empty comes from the parent product.
     *
     * @return array{years: int, brand: string, model: string}|null
     */
    private static function garan(\WC_Product $product): ?array
    {
        $parent = $product->get_parent_id() > 0 ? wc_get_product($product->get_parent_id()) : null;
        $own    = static fn (string $key): string => trim((string) $product->get_meta($key, true));
        $field  = static fn (string $key): string => $own($key) !== '' || ! $parent instanceof \WC_Product ? $own($key) : trim((string) $parent->get_meta($key, true));

        $years = (int) $field(self::META_DURABILITY_YEARS);
        $brand = $field(self::META_DURABILITY_PRODUCER);
        if ($years <= 2 || $brand === '') {
            return null;
        }

        $model = $field(self::META_MODEL_IDENTIFIER);

        return ['years' => $years, 'brand' => $brand, 'model' => $model !== '' ? $model : (string) $product->get_sku()];
    }

    /**
     * The GARAN label where the producer offers a durability guarantee over two years (Article 6(1)(la)).
     * On a variable product it sits in a slot the variation script refills when the shopper picks one.
     */
    public function renderGaranLabel(): void
    {
        global $product;
        if (! $product instanceof \WC_Product) {
            return;
        }

        $label = self::garan($product);
        $html  = $label === null ? '' : self::garanHtml($label);

        if ($product->is_type('variable')) {
            // Each distinct variation label once, as a template: the full label is ~300 KB of SVG.
            $templates = [];
            foreach ($product->get_children() as $childId) {
                $child = wc_get_product($childId);
                $own   = $child instanceof \WC_Product && self::hasOwnGaran($child) ? self::garan($child) : null;
                if ($own !== null && $own !== $label) {
                    $templates[self::garanKey($own)] = '<template data-durabix-garan="' . esc_attr(self::garanKey($own)) . '">' . self::garanHtml($own) . '</template>';
                }
            }
            echo '<div class="durabix-garan-slot">' . $html . '</div>' . implode('', $templates); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- built from escaped parts in garanHtml().
            // Own handle: a block theme renders this before WooCommerce's variation script is registered, or after it printed.
            wp_register_script('durabix-variation', false, ['jquery'], \Durabix\VERSION, true);
            wp_add_inline_script('durabix-variation', self::VARIATION_SCRIPT);
            wp_enqueue_script('durabix-variation');

            return;
        }

        echo $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- built from escaped parts in garanHtml().
    }

    /**
     * Variation data: the key of the variation's label template, '' for no label, only when it differs from the parent's.
     *
     * @param array<string, mixed> $data
     * @param mixed                $parent
     * @param mixed                $variation
     * @return array<string, mixed>
     */
    public function variationGaran(array $data, $parent, $variation): array
    {
        if (! $variation instanceof \WC_Product || ! $parent instanceof \WC_Product || ! self::hasOwnGaran($variation)) {
            return $data;
        }

        $label = self::garan($variation);
        if ($label !== self::garan($parent)) {
            $data['durabix_garan'] = $label === null ? '' : self::garanKey($label);
        }

        return $data;
    }

    /**
     * Only variations with a GARAN field of their own swap the label; one that differs from
     * the parent by its SKU alone keeps the parent's label on the product page, as before 1.4.0.
     */
    private static function hasOwnGaran(\WC_Product $variation): bool
    {
        foreach ([self::META_DURABILITY_YEARS, self::META_DURABILITY_PRODUCER, self::META_MODEL_IDENTIFIER] as $key) {
            if (trim((string) $variation->get_meta($key, true)) !== '') {
                return true;
            }
        }

        return false;
    }

    /**
     * @param array{years: int, brand: string, model: string} $label
     */
    private static function garanKey(array $label): string
    {
        return md5($label['years'] . '|' . $label['brand'] . '|' . $label['model']);
    }

    /** Swaps the slot on variation pick; WooCommerce 11.1+ may replace the form, so it listens on the document. */
    private const VARIATION_SCRIPT = <<<'JS'
jQuery(function ($) {
    var slot = $('.durabix-garan-slot').first(), base = slot.html();
    $(document).on('found_variation', '.variations_form', function (e, v) {
        var key = v && v.durabix_garan, tpl = key ? document.querySelector('template[data-durabix-garan="' + key + '"]') : null;
        slot.html(typeof key !== 'string' ? base : (tpl ? tpl.innerHTML : ''));
    }).on('reset_data', '.variations_form', function () {
        slot.html(base);
    });
});
JS;

    /**
     * @param array{years: int, brand: string, model: string} $label
     */
    private static function garanHtml(array $label): string
    {
        ['years' => $years, 'brand' => $producer, 'model' => $model] = $label;
        $dir = dirname(\Durabix\PLUGIN_FILE);

        /* translators: %d: duration in years of the producer's commercial guarantee of durability */
        $summary = sprintf(__('GARAN label: %d-year producer guarantee of durability. Show the full label.', 'durabix'), $years);

        // GaranLabel returns static SVG shipped with the plugin and escapes duration, brand and model.
        return '<details class="durabix-garan"><summary aria-label="' . esc_attr($summary) . '">'
            . GaranLabel::nested($dir, $years)
            . '</summary><div class="durabix-garan__full">'
            . GaranLabel::full($dir, $years, $producer, $model)
            . '<p><a href="' . esc_url(GaranLabel::INFO_URL) . '" target="_blank" rel="noopener">' . esc_html__('About the producer guarantee of durability on Your Europe', 'durabix') . '</a></p>'
            . '</div></details>';
    }

    /**
     * Render the repairability class badge if one is assigned.
     */
    public function renderRepairabilityScore(): void
    {
        global $product;
        if (! $product instanceof \WC_Product) {
            return;
        }

        $class = self::repairClass($product);
        if ($class === null) {
            return;
        }

        $badgeColor = match ($class) {
            'A', 'B' => '#16a34a',
            'C' => '#ca8a04',
            'D' => '#ea580c',
            default => '#dc2626',
        };

        echo '<div class="durabix-repair-score" style="margin: 8px 0; font-size: 0.88em;">';
        echo '<span style="display:inline-block; padding: 2px 8px; border-radius: 3px; font-weight: bold; color: #fff; background: ' . esc_attr($badgeColor) . ';">';
        /* translators: %s: repairability class letter, A (most repairable) to E (least repairable) */
        printf(esc_html__('Repairability class: %s (A to E, Regulation (EU) 2023/1669)', 'durabix'), esc_html($class));
        echo '</span>';
        echo '</div>';
    }

    /**
     * @param array<string, mixed> $tabs
     * @return array<string, mixed>
     */
    public function registerGuaranteeTab(array $tabs): array
    {
        global $product;
        if (! $product instanceof \WC_Product || ! $this->hasContent($product)) {
            return $tabs;
        }

        $tabs['durabix_guarantee'] = [
            'title' => __('Guarantee & Durability', 'durabix'),
            'priority' => 35,
            'callback' => [$this, 'renderTabContent'],
        ];

        return $tabs;
    }

    public function renderTabContent(): void
    {
        $this->renderNotice();
        $this->renderGaranLabel();
        $this->renderRepairabilityScore();
    }

    /**
     * A short line that opens the full notice on the first click, as in the Commission's online
     * examples, with a clickable link to the page the QR code leads to.
     */
    private function noticeHtml(): string
    {
        $language = HarmonisedNotice::language(determine_locale());
        $image    = plugins_url(HarmonisedNotice::imageFile($language), \Durabix\PLUGIN_FILE);
        $note     = $this->nationalNote();

        return sprintf(
            '<details class="durabix-notice"><summary>%1$s</summary><div class="durabix-notice__body"><a href="%2$s" target="_blank" rel="noopener"><img src="%2$s" width="1654" height="2339" loading="lazy" alt="%3$s" /></a><p><a href="%4$s" target="_blank" rel="noopener">%5$s</a></p>%6$s</div></details>',
            esc_html__('Your legal guarantee rights', 'durabix'),
            esc_url($image),
            esc_attr__('EU harmonised notice on the legal guarantee of conformity', 'durabix'),
            esc_url(HarmonisedNotice::infoUrl($language)),
            esc_html__('More about your legal guarantee rights on Your Europe', 'durabix'),
            $note === '' ? '' : '<p class="durabix-notice__national">' . esc_html($note) . '</p>'
        );
    }

    /**
     * [durabix_notice]: the notice anywhere the shop puts it, for example its terms or a footer widget.
     */
    public function renderShortcode(): string
    {
        if (! self::shownIn('shortcode')) {
            return '';
        }

        wp_enqueue_style('durabix', plugins_url('assets/css/durabix.css', \Durabix\PLUGIN_FILE), [], \Durabix\VERSION);

        return $this->noticeHtml();
    }

    /**
     * A shop-wide reminder at the foot of every page, outside the product pages.
     */
    public function renderFooterNotice(): void
    {
        if (is_admin() || ! self::shownIn('footer')) {
            return;
        }

        echo '<div class="durabix-footer-notice">' . $this->noticeHtml() . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- built from escaped parts in noticeHtml().
    }

    /**
     * The merchant's own note beside the notice, e.g. a longer national guarantee period the uneditable
     * EU notice cannot state. Plain text; translatable through WPML and Polylang string translation.
     */
    private function nationalNote(): string
    {
        $note = trim((string) ($this->settings['national_note'] ?? ''));

        return (string) apply_filters('wpml_translate_single_string', $note, 'durabix', 'national_note');
    }

    /**
     * The notice is owed to consumers; WordPress cannot tell a consumer from a trade customer, so a B2B shop
     * answers here. $context: product, checkout, email, footer or shortcode.
     *
     * @param mixed $object The product page's product, or the order for the email.
     */
    private static function shownIn(string $context, $object = null): bool
    {
        return (bool) apply_filters('durabix_show_notice', true, $context, $object);
    }

    private function cartHasGoods(): bool
    {
        $cart = function_exists('WC') ? WC()->cart : null;
        if (! $cart instanceof \WC_Cart) {
            return false;
        }

        foreach ($cart->get_cart() as $item) {
            $product = $item['data'] ?? null;
            if ($product instanceof \WC_Product && self::showsNotice($product)) {
                return true;
            }
        }

        return false;
    }

    /**
     * The notice covers goods; the hide switch is for digital content, digital services and services.
     * A variation follows its parent product, where the switch is set.
     */
    private static function showsNotice(\WC_Product $product): bool
    {
        $parentId = $product->get_parent_id();
        $source   = $parentId > 0 ? wc_get_product($parentId) : $product;
        $source   = $source instanceof \WC_Product ? $source : $product;

        return $source->get_meta(self::META_EXEMPT_GUARANTEE, true) !== 'yes';
    }

    /**
     * Durability guarantee years worth showing: more than 2 (art. 6(1)(la)), else 0.
     */
    private static function durabilityYears(\WC_Product $product): int
    {
        $years = (int) $product->get_meta(self::META_DURABILITY_YEARS, true);

        return $years > 2 ? $years : 0;
    }

    /**
     * A number stored by 1.0.x is not a class and is not shown.
     */
    private static function repairClass(\WC_Product $product): ?string
    {
        $raw = (string) $product->get_meta(self::META_REPAIR_SCORE, true);

        return in_array($raw, self::REPAIR_CLASSES, true) ? $raw : null;
    }

    private function hasContent(\WC_Product $product): bool
    {
        return self::showsNotice($product)
            || self::durabilityYears($product) > 0
            || self::repairClass($product) !== null;
    }
}
