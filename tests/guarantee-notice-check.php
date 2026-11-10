<?php
/**
 * Self-check for Durabix: settings defaults, harmonised notice, GARAN label, green claims
 * check, product field saving and the product tab.
 * Run: php tests/guarantee-notice-check.php
 *
 * Each check prints PASS or FAIL; the script exits 1 if any check fails.
 *
 * @package Durabix
 */

declare(strict_types=1);

define('ABSPATH', __DIR__);
define('Durabix\\PLUGIN_FILE', dirname(__DIR__) . '/durabix.php');
define('Durabix\\VERSION', 'test');

require_once __DIR__ . '/../autoload.php';

// ------------------- WordPress / WooCommerce stubs -------------------
$GLOBALS['options'] = [];
$GLOBALS['actions'] = [];
$GLOBALS['locale']  = 'en_US';
$GLOBALS['fields']  = [];
$GLOBALS['products'] = [];

function __(string $text, string $domain = 'default'): string
{
    return $text;
}
function _n(string $single, string $plural, int $number, string $domain = 'default'): string
{
    return $number === 1 ? $single : $plural;
}
function esc_html__(string $text, string $domain = 'default'): string
{
    return $text;
}
function esc_html(string $text): string
{
    return htmlspecialchars($text, ENT_QUOTES);
}
function esc_attr(string $text): string
{
    return htmlspecialchars($text, ENT_QUOTES);
}
function esc_attr__(string $text, string $domain = 'default'): string
{
    return htmlspecialchars($text, ENT_QUOTES);
}
function esc_url(string $url): string
{
    return htmlspecialchars($url, ENT_QUOTES);
}
function esc_url_raw(string $url): string
{
    return $url;
}
function plugins_url(string $path, string $file): string
{
    return 'https://shop.test/wp-content/plugins/durabix/' . $path;
}
function apply_filters(string $tag, $value, ...$args)
{
    return isset($GLOBALS['filters'][$tag]) ? ($GLOBALS['filters'][$tag])($value, ...$args) : $value;
}

function do_action(string $tag, ...$args): void
{
    $GLOBALS['did'][$tag][] = $args;
}

function sanitize_textarea_field(string $text): string
{
    return trim(strip_tags($text));
}

function wp_enqueue_style(...$args): void
{
}

function add_shortcode(string $tag, $callback): void
{
    $GLOBALS['shortcodes'][$tag] = $callback;
}

function is_admin(): bool
{
    return false;
}

function determine_locale(): string
{
    return $GLOBALS['locale'];
}
function add_action(string $tag, $callback, int $priority = 10, int $accepted_args = 1): void
{
    $GLOBALS['actions'][] = $tag;
}
function add_filter(string $tag, $callback, int $priority = 10, int $accepted_args = 1): void
{
    $GLOBALS['actions'][] = $tag;
}
function get_option(string $option, $default = false)
{
    return $GLOBALS['options'][$option] ?? $default;
}
function sanitize_text_field(string $text): string
{
    return trim(strip_tags($text));
}
function sanitize_key(string $key): string
{
    return $key;
}
function wp_unslash($value)
{
    return $value;
}
function absint($value): int
{
    return abs((int) $value);
}
function wp_verify_nonce(string $nonce, string $action): bool
{
    return true;
}
function current_user_can(string $cap, ...$args): bool
{
    return true;
}
function wp_strip_all_tags(string $text): string
{
    return strip_tags($text);
}
function wp_add_inline_script(string $handle, string $data): bool
{
    $GLOBALS['inline'][$handle] = $data;
    return true;
}
function wp_register_script(...$args): bool
{
    return true;
}
function wp_enqueue_script(...$args): void
{
}
function wc_get_product($id)
{
    return $GLOBALS['products'][$id] ?? null;
}
function wc_get_product_terms(int $id, string $taxonomy, array $args = []): array
{
    return [];
}
function wp_nonce_field(string $action, string $name): void
{
}
function woocommerce_wp_checkbox(array $args): void
{
    $GLOBALS['fields'][$args['id']] = $args;
}
function woocommerce_wp_text_input(array $args): void
{
    $GLOBALS['fields'][$args['id']] = $args;
}
function woocommerce_wp_select(array $args): void
{
    $GLOBALS['fields'][$args['id']] = $args;
}

function wp_upload_dir(): array
{
    return ['basedir' => $GLOBALS['uploads']];
}
function wp_mkdir_p(string $dir): bool
{
    return is_dir($dir) || mkdir($dir, 0777, true);
}
class WC_Order_Item_Product
{
    public function __construct(private ?WC_Product $product)
    {
    }
    public function get_product(): ?WC_Product
    {
        return $this->product;
    }
}

class WC_Order
{
    public function __construct(public array $items = [])
    {
    }
    public function get_items(): array
    {
        return $this->items;
    }
}

class WP_Post
{
    public int $ID = 0;
}

class WC_Product_Attribute
{
    public function __construct(private array $options = [])
    {
    }
    public function is_taxonomy(): bool
    {
        return false;
    }
    public function get_name(): string
    {
        return 'material';
    }
    public function get_options(): array
    {
        return $this->options;
    }
}

class WC_Product
{
    public array $meta = [];
    public array $children = [];
    public array $attributes = [];
    public string $description = '';
    public string $type = 'simple';
    public function __construct(public int $id = 1)
    {
        $GLOBALS['products'][$id] = $this;
    }
    public string $sku = '';
    public int $parent = 0;
    public function get_id(): int
    {
        return $this->id;
    }
    public function get_parent_id(): int
    {
        return $this->parent;
    }
    public function get_sku(): string
    {
        return $this->sku;
    }
    public function get_meta(string $key, bool $single = true)
    {
        return $this->meta[$key] ?? '';
    }
    public function update_meta_data(string $key, $value): void
    {
        $this->meta[$key] = $value;
    }
    public function save_meta_data(): void
    {
    }
    public function get_name(): string
    {
        return 'Steel bottle';
    }
    public function get_description(): string
    {
        return $this->description;
    }
    public function get_short_description(): string
    {
        return '';
    }
    public function get_children(): array
    {
        return $this->children;
    }
    public function get_attributes(): array
    {
        return $this->attributes;
    }
    public function is_type($type): bool
    {
        return in_array($this->type, (array) $type, true);
    }
}

use Durabix\Admin\ProductMetaBox;
use Durabix\Admin\SettingsPage;
use Durabix\Service\GreenClaimsAuditor;
use Durabix\Service\GuaranteeService;

// ------------------- harness -------------------
$failed = 0;
function check(string $name, callable $test): void
{
    global $failed;
    try {
        $ok = (bool) $test();
    } catch (\Throwable $e) {
        $ok = false;
        $name .= ' [' . get_class($e) . ': ' . $e->getMessage() . ']';
    }
    echo ($ok ? 'PASS' : 'FAIL') . "  {$name}\n";
    if (! $ok) {
        $failed++;
    }
}

function render(callable $fn): string
{
    ob_start();
    $fn();
    return (string) ob_get_clean();
}

function reset_state(): void
{
    $GLOBALS['options']  = [];
    $GLOBALS['actions']  = [];
    $GLOBALS['locale']   = 'en_US';
    $GLOBALS['fields']   = [];
    $GLOBALS['products'] = [];
    $GLOBALS['product']  = null;
    $GLOBALS['filters']  = [];
    $GLOBALS['did']      = [];
    $_POST               = [];
}

// 3. Harmonised notice (Article 6(1)(l) of Directive 2011/83/EU, Annex I to Regulation (EU) 2025/1960).
function notice_html(string $locale): string
{
    reset_state();
    $GLOBALS['locale']  = $locale;
    $GLOBALS['product'] = new WC_Product();
    return render(static fn () => (new GuaranteeService())->renderNotice());
}
check('de_DE: the German notice file and the German Your Europe page', static function (): bool {
    $out = notice_html('de_DE');
    return str_contains($out, 'assets/notice/notice-de.png') && str_contains($out, 'guarantees/index_de.htm');
});
check('pl_PL: the Polish notice file', static fn (): bool => str_contains(notice_html('pl_PL'), 'notice-pl.png'));
check('ja: a language outside the 24 gets the English notice', static fn (): bool => str_contains(notice_html('ja'), 'notice-en.png'));
check('notice: one English summary line, opens on the first click', static function (): bool {
    $out = notice_html('en_US');
    return str_contains($out, '<details class="durabix-notice"><summary>Your legal guarantee rights</summary>');
});
check('ca_ES, eu, gl_ES: the Spanish notice, not the English one', static function (): bool {
    return str_contains(notice_html('ca'), 'notice-es.png') && str_contains(notice_html('eu'), 'notice-es.png') && str_contains(notice_html('gl_ES'), 'notice-es.png');
});
check('national note: shown under the notice, escaped', static function (): bool {
    reset_state();
    $GLOBALS['options'][GuaranteeService::OPTION] = ['national_note' => 'In Spain the legal guarantee lasts 3 years <b>'];
    $GLOBALS['product'] = new WC_Product();
    $out = render(static fn () => (new GuaranteeService())->renderNotice());
    return str_contains($out, 'class="durabix-notice__national">In Spain the legal guarantee lasts 3 years &lt;b&gt;</p>');
});
check('national note: empty by default, nothing printed', static fn (): bool => ! str_contains(notice_html('en_US'), 'durabix-notice__national'));
check('durabix_show_notice: a B2B shop can hide the product page notice', static function (): bool {
    reset_state();
    $GLOBALS['product'] = new WC_Product();
    $GLOBALS['filters']['durabix_show_notice'] = static fn (bool $show, string $context): bool => $context !== 'product';
    return render(static fn () => (new GuaranteeService())->renderNotice()) === '';
});
check('footer: off by default, on when enabled, shortcode registered', static function (): bool {
    reset_state();
    (new GuaranteeService())->registerHooks();
    $offByDefault = ! in_array('wp_footer', $GLOBALS['actions'], true);
    $shortcode    = isset($GLOBALS['shortcodes']['durabix_notice']);
    reset_state();
    $GLOBALS['options'][GuaranteeService::OPTION] = ['notice_footer' => '1'];
    $GLOBALS['actions'] = [];
    (new GuaranteeService())->registerHooks();
    $onWhenEnabled = in_array('wp_footer', $GLOBALS['actions'], true);
    $footer = render(static fn () => (new GuaranteeService())->renderFooterNotice());
    return $offByDefault && $onWhenEnabled && $shortcode && str_contains($footer, 'class="durabix-footer-notice"><details class="durabix-notice">');
});
check('settings: footer switch and national note are saved and registered for translation', static function (): bool {
    reset_state();
    $out = (new SettingsPage())->sanitizeSettings(['notice_footer' => '1', 'national_note' => "  Three years in Spain.  "]);
    return $out['notice_footer'] === '1' && $out['national_note'] === 'Three years in Spain.'
        && ($GLOBALS['did']['wpml_register_single_string'][0] ?? []) === ['durabix', 'national_note', 'Three years in Spain.'];
});
check('notice: all 24 official language files ship with the plugin', static function (): bool {
    foreach (\Durabix\Model\HarmonisedNotice::LANGUAGES as $lang) {
        if (! is_readable(__DIR__ . '/../' . \Durabix\Model\HarmonisedNotice::imageFile($lang))) {
            return false;
        }
    }
    return count(\Durabix\Model\HarmonisedNotice::LANGUAGES) === 24;
});
check('notice: hidden on a product marked as not a good', static function (): bool {
    reset_state();
    $GLOBALS['product'] = new WC_Product();
    $GLOBALS['product']->meta['_durabix_exempt_guarantee'] = 'yes';
    return render(static fn () => (new GuaranteeService())->renderNotice()) === '';
});

// 4. GARAN label (Article 6(1)(la), Annex II to Regulation (EU) 2025/1960).
function garan_html(array $meta, string $sku = ''): string
{
    reset_state();
    $GLOBALS['product'] = new WC_Product();
    $GLOBALS['product']->meta = $meta;
    $GLOBALS['product']->sku  = $sku;
    return render(static fn () => (new GuaranteeService())->renderGaranLabel());
}
check('GARAN: no label without a producer name', static fn (): bool => garan_html(['_durabix_durability_years' => '5']) === '');
check('GARAN: no label for 2 years', static fn (): bool => garan_html(['_durabix_durability_years' => '2', '_durabix_durability_producer' => 'Fairline']) === '');
check('GARAN: years, brand and model replace the placeholders', static function (): bool {
    $out = garan_html(['_durabix_durability_years' => '5', '_durabix_durability_producer' => 'Fairline', '_durabix_model_identifier' => 'FL-6']);
    return str_contains($out, '>5</tspan>') && str_contains($out, '>Fairline</tspan>') && str_contains($out, '>FL-6</tspan>') && ! str_contains($out, '{{');
});
check('GARAN: model falls back to the SKU', static fn (): bool => str_contains(garan_html(['_durabix_durability_years' => '5', '_durabix_durability_producer' => 'Fairline'], 'SKU-9'), '>SKU-9</tspan>'));
check('GARAN: brand is escaped inside the SVG', static function (): bool {
    $out = garan_html(['_durabix_durability_years' => '5', '_durabix_durability_producer' => '<script>x</script>&Co']);
    return ! str_contains($out, '<script>') && str_contains($out, '&lt;script&gt;');
});
check('GARAN: nested display opens the full label and links to Your Europe', static function (): bool {
    $out = garan_html(['_durabix_durability_years' => '7', '_durabix_durability_producer' => 'Fairline']);
    return str_starts_with($out, '<details class="durabix-garan"><summary') && str_contains($out, 'commercial-guarantee-durability/index.htm') && substr_count($out, '<svg') === 2;
});
check('GARAN: the two SVG templates share no element id', static function (): bool {
    preg_match_all('/id="([^"]+)"/', (string) file_get_contents(__DIR__ . '/../assets/garan/label.svg'), $a);
    preg_match_all('/id="([^"]+)"/', (string) file_get_contents(__DIR__ . '/../assets/garan/nested.svg'), $b);
    return array_intersect($a[1], $b[1]) === [];
});

// 4b. GARAN in the confirmation email (Article 8(7)(a) of Directive 2011/83/EU) and text fit.
$GLOBALS['uploads'] = sys_get_temp_dir() . '/durabix-test-' . getmypid();
function garan_order(): WC_Order
{
    reset_state();
    $parent = new WC_Product(10);
    $parent->meta = ['_durabix_durability_years' => '5', '_durabix_durability_producer' => 'Fairline', '_durabix_model_identifier' => 'FL-6'];
    $a = new WC_Product(11);
    $a->parent = 10;
    $b = new WC_Product(12);
    $b->parent = 10;
    return new WC_Order([new WC_Order_Item_Product($a), new WC_Order_Item_Product($b)]);
}
check('GARAN email: one SVG attached for two variations whose meta sits on the parent', static function (): bool {
    $files = (new GuaranteeService())->attachGaranLabels([], 'customer_processing_order', garan_order(), null);
    return count($files) === 1 && str_ends_with($files[0], '.svg') && str_contains((string) file_get_contents($files[0]), '>Fairline</tspan>');
});
check('GARAN email: nothing attached to an admin email', static fn (): bool => (new GuaranteeService())->attachGaranLabels([], 'new_order', garan_order(), null) === []);
check('GARAN email: the body names the label and the attachment', static function (): bool {
    $order = garan_order();
    $email = (object) ['id' => 'customer_processing_order'];
    $out = render(static fn () => (new GuaranteeService())->renderEmailNotice($order, false, true, $email));
    return substr_count($out, 'GARAN label: Fairline FL-6, 5-year') === 1;
});
check('GARAN variation: own duration and model, brand from the parent; email gets two labels', static function (): bool {
    $order = garan_order();
    $GLOBALS['products'][12]->meta = ['_durabix_durability_years' => '10', '_durabix_model_identifier' => 'FL-9'];
    $files = (new GuaranteeService())->attachGaranLabels([], 'customer_processing_order', $order, null);
    $all = implode('', array_map(static fn ($f) => (string) file_get_contents($f), $files));
    return count($files) === 2 && str_contains($all, '>10</tspan>') && str_contains($all, '>FL-9</tspan>') && substr_count($all, '>Fairline</tspan>') === 2;
});
check('GARAN variation: data carries the label only when it differs from the parent', static function (): bool {
    garan_order();
    $svc = new GuaranteeService();
    [$parent, $same, $own] = [$GLOBALS['products'][10], $GLOBALS['products'][11], $GLOBALS['products'][12]];
    $own->meta = ['_durabix_durability_years' => '10'];
    $a = $svc->variationGaran([], $parent, $same);
    $b = $svc->variationGaran([], $parent, $own);
    return ! isset($a['durabix_garan']) && preg_match('/^[0-9a-f]{32}$/', (string) ($b['durabix_garan'] ?? '')) === 1;
});
check('GARAN variation: a variation that differs only by SKU swaps nothing', static function (): bool {
    garan_order();
    $GLOBALS['products'][10]->meta['_durabix_model_identifier'] = '';
    $GLOBALS['products'][11]->sku = 'SKU-11';
    return ! isset((new GuaranteeService())->variationGaran([], $GLOBALS['products'][10], $GLOBALS['products'][11])['durabix_garan']);
});
check('GARAN variation: a label only on a variation, none on the parent', static function (): bool {
    reset_state();
    $parent = new WC_Product(20);
    $v = new WC_Product(21);
    $v->parent = 20;
    $v->meta = ['_durabix_durability_years' => '4', '_durabix_durability_producer' => 'Kettle Co'];
    $parent->type = 'variable';
    $parent->children = [21];
    $GLOBALS['product'] = $parent;
    $data = (new GuaranteeService())->variationGaran([], $parent, $v);
    $out  = render(static fn () => (new GuaranteeService())->renderGaranLabel());
    return str_contains($out, '<div class="durabix-garan-slot"></div><template data-durabix-garan="' . $data['durabix_garan'] . '">') && substr_count($out, '>Kettle Co</tspan>') === 1;
});
check('GARAN variation: the variable product page renders a slot', static function (): bool {
    reset_state();
    $GLOBALS['product'] = new WC_Product(30);
    $GLOBALS['product']->type = 'variable';
    return str_starts_with(render(static fn () => (new GuaranteeService())->renderGaranLabel()), '<div class="durabix-garan-slot">')
        && str_contains($GLOBALS['inline']['durabix-variation'] ?? '', 'found_variation');
});
check('GARAN fit: short brand and model get no textLength', static fn (): bool => \Durabix\Model\GaranLabel::fit('Fairline', 'FL-6') === ['', '']);
check('GARAN fit: long brand and model are squeezed into the row', static function (): bool {
    [$b, $m] = \Durabix\Model\GaranLabel::fit(str_repeat('W', 40), str_repeat('M', 40));
    preg_match('/textLength="([\d.]+)"/', $b, $x);
    preg_match('/textLength="([\d.]+)"/', $m, $y);
    return isset($x[1], $y[1]) && (float) $x[1] + (float) $y[1] <= 248.66;
});
check('GARAN fit: brand and model are cut at 40 characters', static function (): bool {
    $svg = \Durabix\Model\GaranLabel::full(dirname(__DIR__), 5, str_repeat('a', 50), 'X');
    return str_contains($svg, '>' . str_repeat('a', 40) . '</tspan>') && ! str_contains($svg, str_repeat('a', 41)) && ! str_contains($svg, '_FIT}}');
});

// 5. Green claims: Unicode, diacritics, inflected forms.
$auditor = new GreenClaimsAuditor();
foreach (
    [
        'przyjazny dla środowiska' => 'Opakowanie przyjazny dla środowiska.',
        'umweltfreundliche'        => 'Umweltfreundliche Verpackung.',
        'klimaneutrale'            => 'Eine klimaneutrale Flasche.',
        'ecológico'                => 'Un envase ecológico.',
    ] as $label => $text
) {
    check("green claims: flags \"{$label}\"", static fn (): bool => count($auditor->auditText($text)) === 1);
}
check('green claims: clean text has no matches', static fn (): bool => $auditor->auditText('Stainless steel bottle with silicone seal.') === []);
check('green claims: "climate-neutral" still matched', static fn (): bool => ($auditor->auditText('A climate-neutral bottle.')[0]['matched'] ?? '') === 'climate-neutral');

// 6. Auditor reads variation descriptions and attributes; no green tick.
check('green claims: variation description is checked', static function () use ($auditor): bool {
    reset_state();
    $parent = new WC_Product(10);
    $parent->type = 'variable';
    $child  = new WC_Product(11);
    $child->description = 'An eco-friendly variant.';
    $parent->children   = [11];
    return count($auditor->auditProduct($parent)) === 1;
});
check('green claims: a grouped product does not scan the products it groups', static function () use ($auditor): bool {
    reset_state();
    $parent = new WC_Product(13);
    $parent->type = 'grouped';
    $child = new WC_Product(14);
    $child->description = 'An eco-friendly bottle sold separately.';
    $parent->children = [14];
    return $auditor->auditProduct($parent) === [];
});
$variable = static function (int $flagged, int $count = 150): WC_Product {
    reset_state();
    $parent = new WC_Product(1000);
    $parent->type = 'variable';
    for ($i = 1; $i <= $count; $i++) {
        $child = new WC_Product(1000 + $i);
        if ($i === $flagged) {
            $child->description = 'An eco-friendly variant.';
        }
        $parent->children[] = 1000 + $i;
    }
    return $parent;
};
check('green claims: variation 50 of 150 is checked', static fn (): bool => count($auditor->auditProduct($variable(50))) === 1);
check('green claims: variations past the first 100 are not loaded', static fn (): bool => $auditor->auditProduct($variable(150)) === []);
check('green claims box: says when only the first 100 variations were checked', static function () use ($variable): bool {
    $variable(0);
    $post     = new WP_Post();
    $post->ID = 1000;
    $out      = render(static fn () => (new ProductMetaBox(new GuaranteeService()))->renderGreenClaimsBox($post));
    return str_contains($out, 'Only the first 100 of 150 variations were checked.');
});
check('green claims box: no cap note under 100 variations', static function () use ($variable): bool {
    $variable(0, 20);
    $post     = new WP_Post();
    $post->ID = 1000;
    $out      = render(static fn () => (new ProductMetaBox(new GuaranteeService()))->renderGreenClaimsBox($post));
    return ! str_contains($out, 'variations were checked');
});
check('recital 9: every paraphrase says "clear and prominent terms on the same medium"', static function (): bool {
    foreach (['src/Admin/ProductMetaBox.php', 'src/Model/GreenClaimsRules.php', 'readme.txt'] as $f) {
        $src = (string) file_get_contents(__DIR__ . '/../' . $f);
        if (! str_contains($src, 'clear and prominent terms on the same medium') || preg_match('/clear terms|same page/', $src)) {
            return false;
        }
    }
    return true;
});
check('green claims: custom attribute values are checked', static function () use ($auditor): bool {
    reset_state();
    $p = new WC_Product(12);
    $p->attributes = [new WC_Product_Attribute(['biodegradable film'])];
    return count($auditor->auditProduct($p)) === 1;
});
check('green claims box: no "No prohibited" all-clear claim when nothing matches', static function (): bool {
    reset_state();
    new WC_Product(20);
    $post     = new WP_Post();
    $post->ID = 20;
    $out      = render(static fn () => (new ProductMetaBox(new GuaranteeService()))->renderGreenClaimsBox($post));
    return $out !== '' && ! str_contains($out, 'No prohibited') && ! str_contains($out, '&#10004;');
});
check('green claims auditor: no strip_tags() (Plugin Check)', static function (): bool {
    return ! str_contains((string) file_get_contents(__DIR__ . '/../src/Service/GreenClaimsAuditor.php'), 'strip_tags(');
});

// 7. Edge input.
check('tab mode: exempt product with nothing to show gets no tab', static function (): bool {
    reset_state();
    $p = new WC_Product();
    $p->meta['_durabix_exempt_guarantee'] = 'yes';
    $GLOBALS['product'] = $p;
    return (new GuaranteeService())->registerGuaranteeTab([]) === [];
});
check('tab mode: product with a reminder still gets the tab', static function (): bool {
    reset_state();
    $GLOBALS['product'] = new WC_Product();
    return isset((new GuaranteeService())->registerGuaranteeTab([])['durabix_guarantee']);
});

$save = static function (array $post): WC_Product {
    reset_state();
    $p = new WC_Product(30);
    $_POST = $post + ['durabix_product_nonce' => 'x'];
    (new ProductMetaBox(new GuaranteeService()))->saveProductFields(30);
    return $p;
};
check('save: durability of 2 years is not stored', static fn (): bool => $save(['_durabix_durability_years' => '2'])->meta['_durabix_durability_years'] === '');
check('save: durability of 5 years is stored', static fn (): bool => $save(['_durabix_durability_years' => '5'])->meta['_durabix_durability_years'] === 5);
check('save: repairability class "b" is stored as "B"', static fn (): bool => $save(['_durabix_repair_score' => 'b'])->meta['_durabix_repair_score'] === 'B');
check('save: a number such as "8.2" is not stored as a repairability class', static fn (): bool => $save(['_durabix_repair_score' => '8.2'])->meta['_durabix_repair_score'] === '');
check('save: "F" is not a repairability class', static fn (): bool => $save(['_durabix_repair_score' => 'F'])->meta['_durabix_repair_score'] === '');
check('fields: repairability is a select of classes A to E; durability minimum is 3', static function (): bool {
    reset_state();
    render(static fn () => (new ProductMetaBox(new GuaranteeService()))->renderProductFields());
    $options = $GLOBALS['fields']['_durabix_repair_score']['options'] ?? [];
    return array_keys($options) === ['', 'A', 'B', 'C', 'D', 'E']
        && ! isset($GLOBALS['fields']['_durabix_repair_score']['type'])
        && ($GLOBALS['fields']['_durabix_durability_years']['custom_attributes']['min'] ?? '') === '3';
});
check('storefront: class B renders as a repairability class, not a score out of 10', static function (): bool {
    reset_state();
    $p = new WC_Product();
    $p->meta['_durabix_repair_score'] = 'B';
    $GLOBALS['product'] = $p;
    $out = render(static fn () => (new GuaranteeService())->renderRepairabilityScore());
    return str_contains($out, 'Repairability class: B') && str_contains($out, '2023/1669') && ! str_contains($out, '/ 10');
});
check('storefront: a number stored by 1.0.x is not shown', static function (): bool {
    reset_state();
    $p = new WC_Product();
    $p->meta['_durabix_repair_score'] = '8.2';
    $GLOBALS['product'] = $p;
    return render(static fn () => (new GuaranteeService())->renderRepairabilityScore()) === '';
});
check('changelog: no decimal comma claim for a type=number field', static function (): bool {
    return ! str_contains((string) file_get_contents(__DIR__ . '/../readme.txt'), 'decimal comma');
});
check('repairability: field, heading and FAQ do not tie the 2023/1669 class to Article 6(1)(u); they name the Art. 1 exclusions', static function (): bool {
    reset_state();
    $html = render(static fn () => (new ProductMetaBox(new GuaranteeService()))->renderProductFields());
    $desc = (string) ($GLOBALS['fields']['_durabix_repair_score']['description'] ?? '');
    $readme = (string) file_get_contents(__DIR__ . '/../readme.txt');
    $faq = substr($readme, (int) strpos($readme, '= Which repairability value'), 900);
    foreach ([$desc, $faq] as $text) {
        if (str_contains($text, '6(1)(u)') || ! str_contains($text, 'high security') || ! str_contains($text, 'roll')) {
            return false;
        }
    }
    return ! str_contains($html, '(u)');
});
check('description: plugin header and composer.json say "repairability class", not "score"', static function (): bool {
    $header = (string) file_get_contents(__DIR__ . '/../durabix.php');
    $composer = (string) file_get_contents(__DIR__ . '/../composer.json');
    return str_contains($header, 'repairability class badge') && str_contains($composer, 'repairability class badge')
        && ! str_contains($header . $composer, 'repairability score');
});
check('blueprint: the demo product with a repairability class is a smartphone or slate tablet', static function (): bool {
    $bp = (string) file_get_contents(__DIR__ . '/../.wordpress-org/blueprints/blueprint.json');
    return ! str_contains($bp, '_durabix_repair_score') || (bool) preg_match('/set_name\(\'[^\']*(Smartphone|Tablet)/i', $bp);
});

check('settings: a non-array option value does not fatal', static function (): bool {
    reset_state();
    $out = (new SettingsPage())->sanitizeSettings('garbage');
    return ($out['enabled'] ?? null) === '0';
});
check('settings: the old reminder text and duration settings are gone', static function (): bool {
    reset_state();
    $out = (new SettingsPage())->sanitizeSettings(['statutory_years' => '5', 'custom_notice' => 'x', 'notice_email' => '1']);
    return ! isset($out['statutory_years']) && ! isset($out['custom_notice']) && $out['notice_email'] === '1' && $out['notice_checkout'] === '0';
});
check('no unused DURABIX_DIR / DURABIX_URL constants', static function (): bool {
    return ! str_contains((string) file_get_contents(__DIR__ . '/../durabix.php'), 'DURABIX_');
});

echo $failed === 0 ? "\nALL DURABIX CHECKS PASSED\n" : "\n{$failed} CHECK(S) FAILED\n";
exit($failed === 0 ? 0 : 1);
