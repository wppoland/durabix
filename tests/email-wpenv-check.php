<?php
// Real WooCommerce email run (needs `wp language core install pl_PL`): wp-env, then `wp eval-file wp-content/plugins/<dir>/tests/email-wpenv-check.php`.
$fail = 0;
$ok = static function (string $name, bool $cond) use (&$fail): void { echo ($cond ? 'PASS ' : 'FAIL ') . $name . "\n"; $fail += $cond ? 0 : 1; };
$sent = [];
add_filter('pre_wp_mail', static function ($r, array $atts) use (&$sent) { $sent[] = $atts; return true; }, 10, 2);

$good = new WC_Product_Simple(); $good->set_name('Kettle'); $good->set_regular_price('30'); $good->save();
$svc = new WC_Product_Simple(); $svc->set_name('Repair'); $svc->set_regular_price('80'); $svc->set_virtual(true); $svc->save();

$send = static function (WC_Product $p, string $type) use (&$sent): array {
    $sent = [];
    $order = wc_create_order(); $order->add_product($p, 1); $order->set_billing_email('jan@example.com'); $order->calculate_totals(); $order->save();
    $mail = WC()->mailer()->emails['WC_Email_Customer_Processing_Order'];
    $mail->settings['email_type'] = $type; $mail->email_type = $type;
    $mail->trigger($order->get_id(), $order);
    return $sent[0] ?? [];
};
$names = static fn (array $m): array => array_map('basename', (array) ($m['attachments'] ?? []));

$m = $send($good, 'html');
$ok('html: one email sent', $m !== []);
$ok('html: notice PNG attached (' . implode(',', $names($m)) . ')', in_array('notice-en.png', $names($m), true));
$ok('html: notice image in body', str_contains((string) ($m['message'] ?? ''), 'notice-en.png'));
$m = $send($good, 'plain');
$ok('plain: PNG attached', in_array('notice-en.png', $names($m), true));
$ok('plain: body names the attachment', str_contains((string) ($m['message'] ?? ''), 'attached to this email'));
$m = $send($svc, 'html');
$ok('service only: no notice attached', ! in_array('notice-en.png', $names($m), true));
$ok('service only: no notice in body', ! str_contains((string) ($m['message'] ?? ''), 'notice-en.png'));
update_option('WPLANG', 'pl_PL'); unset($GLOBALS['locale']); // get_locale() caches the first answer of the request.
$m = $send($good, 'html');
$ok('pl_PL site: Polish notice attached (' . implode(',', $names($m)) . ')', in_array('notice-pl.png', $names($m), true));
update_option('WPLANG', '');
echo $fail === 0 ? "ALL OK\n" : "$fail failed\n";
