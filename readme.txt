=== Durabix - EU Legal Guarantee Notice for WooCommerce ===
Contributors: motylanogha
Tags: woocommerce, legal guarantee, garan label, harmonised notice, green claims
Requires at least: 6.5
Tested up to: 7.1
Requires PHP: 8.1
Stable tag: 1.3.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

The EU harmonised legal guarantee notice and the GARAN label in the official design, plus a repairability class badge and a green claims phrase check.

== Description ==

Directive (EU) 2024/825 (Empowering Consumers for the Green Transition) amends Directive 2011/83/EU. Before a distance contract, the trader must remind the consumer of the legal guarantee of conformity "in a prominent manner, using the harmonised notice" (Article 6(1)(l)), and, where the producer offers a commercial guarantee of durability at no additional cost covering the entire good for more than two years and tells the trader, say so "using the harmonised label" (Article 6(1)(la)). Article 22a requires both, and Commission Implementing Regulation (EU) 2025/1960 fixes their design: the notice in Annex I, the GARAN label in Annex II, both applicable from 27 September 2026 (Article 3). Member States apply these rules from 27 September 2026 (Article 4(1) of Directive (EU) 2024/825); in Poland the transposing act (Dz.U. 2026 poz. 1353) applies from 27 September 2027.

Directive (EU) 2024/825 also adds to Annex I of Directive 2005/29/EC a ban on generic environmental claims the trader cannot back with recognised excellent environmental performance (point 4a; recital 9 gives "eco-friendly", "green" and "biodegradable" as examples) and on claims that a product has a neutral, reduced or positive greenhouse gas impact based on offsetting (point 4c).

Durabix adds the following to WooCommerce:

* **Harmonised notice**: The Commission's official colour notice on the legal guarantee of conformity, in the site language (all 24 official EU languages ship with the plugin; Catalan, Basque and Galician get the Spanish notice, any other language the English one). A short "Your legal guarantee rights" line opens the full, unedited notice on the first click, as in the Commission's online examples, with a link to the same Your Europe page the QR code leads to.
* **Notice at checkout and in the confirmation email**: The same line above the place order button on the classic and the block checkout, and the notice in the processing and on-hold order emails to the customer. Each can be switched off.
* **GARAN label**: When you enter a producer guarantee of durability over two years and the producer's name on a product, the product page shows the nested GARAN label, which opens the full label with the years, the brand and the model identifier (or the SKU) filled in. Only those three fields are editable under Annex II; the rest is the Commission's file. The label is set in Inter, which ships with the plugin. The brand and the model identifier take up to 40 characters each; when the two would run into each other, both are narrowed to fit the line.
* **Repairability class**: Shows the repairability class (A to E) you enter for a smartphone or slate tablet as a colour-coded badge (see the FAQ).
* **Green claims check**: Scans product titles, descriptions, short descriptions, attribute values and the descriptions of up to 100 variations in the admin and flags phrases such as "climate neutral" or "eco-friendly" (English, Polish, German, Spanish) for you to review. It works in the product editor whether or not the notice is shown.
* **Display options**: The notice and label sit under the product summary (near the price and add-to-cart button) or in a "Guarantee & Durability" product tab. The notice can also sit at the foot of every page as a general reminder (off by default), and the `[durabix_notice]` shortcode places it anywhere, for example on your terms page.
* **National note**: The EU notice cannot be edited and states the two-year EU minimum. If your country gives consumers more, write it in the national note field and it shows under the notice and in the order email. It is empty until you fill it in.
* **Trade customers**: The notice is owed to consumers. The `durabix_show_notice` filter receives the place (product, checkout, email, footer, shortcode) so a shop that also sells to businesses can hide it for them.
* **Per-product hide switch**: A checkbox on each product hides the notice, for example on a digital download or a service, since the notice covers goods. It is not set automatically. Article 6(1)(lb) of Directive 2011/83/EU requires a separate reminder for digital content and digital services, which Durabix does not show. Checkout and the email show the notice when at least one item is a good.
* **No external calls**: The notice images, the label and the font load from your own site. Durabix sends nothing to other services; the Your Europe links are plain links. It declares compatibility with HPOS and the Cart and Checkout Blocks.

* **GARAN label in the confirmation email**: Article 8(7)(a) of Directive 2011/83/EU requires the confirmation of the contract to repeat the Article 6(1) information, so the processing and on-hold order emails carry each GARAN label of the order as an attached SVG file, with one line naming it in the email. It follows the order email switch.

What Durabix does not do: it does not show the GARAN label at checkout, has no per-variation label fields, and does not check that the producer's guarantee meets Article 17 of Directive (EU) 2019/771. Showing the notice and label does not by itself make a shop compliant.

The notice and label files are © European Union, reused under the Commission's reuse policy (Commission Decision 2011/833/EU, CC BY 4.0) and shown unchanged, as Implementing Regulation (EU) 2025/1960 requires; Durabix only fills in the three editable label fields. Durabix is not affiliated with or endorsed by the European Commission. Inter is licensed under the SIL Open Font License 1.1 (assets/fonts/OFL.txt).

= Documentation and links =

* **Documentation** - [plogins.com/durabix/docs/](https://plogins.com/durabix/docs/)
* **Plugin page** - [plogins.com/durabix/](https://plogins.com/durabix/)
* **Source code** - [github.com/wppoland/durabix](https://github.com/wppoland/durabix)
* **Bug reports and feature requests** - [GitHub issues](https://github.com/wppoland/durabix/issues)

Reporting a security issue: email hello@wppoland.com under our coordinated disclosure policy. We confirm within two business days, assess within five, and patch a critical issue within seven days of confirming it.

== Translations ==

Durabix is fully translatable and ships the `durabix.pot` template. Translations are delivered by WordPress.org language packs from translate.wordpress.org.

== Installation ==

1. Ensure WooCommerce 8.0 or higher is installed and active.
2. Install Durabix and activate it.
3. Open WooCommerce > EU Legal Guarantee to choose where the notice appears: product page, checkout and order email.
4. On products with a producer guarantee of durability, enter the years (3 or more), the producer name and the model identifier for the GARAN label. Add a repairability class where it applies and review the green claims check.

== Frequently Asked Questions ==

= When does Directive (EU) 2024/825 take effect? =
Member States had to adopt and publish the transposing measures by 27 March 2026 and apply them from 27 September 2026 (Article 4(1)). The rules bind shops through each country's transposing law: in Poland that act (Dz.U. 2026 poz. 1353) applies from 27 September 2027.

= What is the harmonised notice? =
Article 6(1)(l) of Directive 2011/83/EU (as amended by Directive (EU) 2024/825) requires a reminder of the legal guarantee of conformity, including its minimum duration of two years, "in a prominent manner, using the harmonised notice referred to in Article 22a". Implementing Regulation (EU) 2025/1960 sets its design: Article 1 says it "shall comply with the design and content set out in Annex I", Annex I note (1) says "None of the elements of the harmonised notice can be edited" and note (5) says it is in colour for online contracts. Durabix shows the Commission's colour file for the site language, unchanged.

= What is the GARAN label? =
Article 6(1)(la) of Directive 2011/83/EU requires the harmonised label where the producer offers a commercial guarantee of durability at no additional cost, covering the entire good and lasting more than two years, and makes that known to the trader. Annex II to Implementing Regulation (EU) 2025/1960 sets the design and lets only three fields change: the years, the brand or trademark, and the model identifier (note 1). For online sale the label is in colour (note 5) and may use the nested display, which "shall appear in its entirety on the first mouse click, mouse roll-over or tactile screen expansion". Durabix shows the nested label and opens the full one on click.

= Which language does the notice use? =
The site language, through the WordPress locale. A language outside the 24 official EU languages gets the English notice. The checkout line and email text come from the translation files.

= Does the notice fit second-hand goods? =
Yes. The notice itself says that for second-hand goods a shorter period may apply, but not less than one year (Article 10(6) of Directive (EU) 2019/771 lets Member States allow that when seller and consumer agree).

= Does Durabix protect against greenwashing penalties? =
No. The green claims check matches product text against a short list of phrases linked to points 4a and 4c of Annex I to Directive 2005/29/EC (as amended by Directive (EU) 2024/825) and lists them in the product editor for review. A match is not a finding of a breach: point 4c bans climate claims only when based on offsetting, and recital 9 of Directive (EU) 2024/825 says a claim whose specification is provided in clear and prominent terms on the same medium, such as the online selling interface, is not a generic environmental claim. No match does not mean the page is compliant either, because only the listed phrases are checked.

= Which repairability value can I enter? =
The repairability class from the energy label of a smartphone or slate tablet. Commission Delegated Regulation (EU) 2023/1669 sets it from A (most repairable) to E (least repairable), applicable from 20 June 2025 (Article 8 and Annex II, Table 4). Article 1 excludes mobile phones and tablets with a flexible main display the user can roll up or unroll, and smartphones for high security communication. Leave the field empty for those and for every other product. Durabix shows the letter as a badge, not in the official label format. A number entered in Durabix 1.0.x is not shown.

== Screenshots ==

1. Product page on Twenty Twenty-Five: the "Your legal guarantee rights" line, the nested GARAN label and the repairability badge under the price.
2. Product editor: hide switch for products that are not goods, producer guarantee years, producer name, model identifier and repairability class.
3. Green claims phrase check in the product editor, listing two phrases to review with the Annex I point each relates to.
4. Settings page under WooCommerce > EU Legal Guarantee: product page placement, checkout and order email.
5. The full harmonised notice, opened from the product page.
6. The full GARAN label with the years, brand and model identifier filled in.
7. The notice line above the place order button on the block checkout.

== Changelog ==

= 1.3.0 =
* Added: the notice at the foot of every page as a general reminder, off by default, and the `[durabix_notice]` shortcode.
* Added: a national note field shown under the notice and in the order email, for a longer national legal guarantee the EU notice cannot state. Empty by default; translatable with WPML and Polylang.
* Added: the `durabix_show_notice` filter to hide the notice per place, for example from trade customers.
* Added: a warning on the Plugins and Durabix screens when Durabix is switched off and the notice is shown nowhere.
* Changed: Catalan, Basque and Galician sites get the Spanish notice instead of the English one.

= 1.2.1 =
* Added: the processing and on-hold order emails carry each GARAN label of the order as an attached SVG file, with one line naming it. Article 8(7)(a) of Directive 2011/83/EU requires the confirmation to repeat the Article 6(1) information, which includes the label. Variations use the label of their parent product.
* Fixed: a long brand and a long model identifier ran into each other on the GARAN label. Both are now narrowed to fit the line, and each field takes up to 40 characters.

= 1.2.0 =
* Added: the harmonised notice on the legal guarantee of conformity in the official design of Implementing Regulation (EU) 2025/1960, Annex I, in all 24 official EU languages, on the product page, at checkout (classic and block) and in the processing and on-hold order emails. Article 6(1)(l) of Directive 2011/83/EU requires it from 27 September 2026.
* Added: the GARAN label (Annex II) with the nested display, filled with the years, the producer and a new model identifier field (the SKU when empty).
* Removed: the plain text reminder, the durability note, and the duration and custom text settings. They do not meet Article 6(1)(l) and (la), which require the notice and the label.

= 1.1.2 =
* Changed: renamed from Garantio to Durabix at the request of the WordPress.org plugin review (the old name is used by warranty services). The text domain, main file and settings prefix are now durabix; nothing else changed.

= 1.1.1 =
* Fixed: a shop manager could open the settings page but saving it failed with "Sorry, you are not allowed to manage options for this site."

= 1.1.0 =
* Fixed: on a fresh install the reminder, product fields and green claims check stayed off until the settings were saved once.
* Fixed: switching the reminder off no longer removes the product fields and the green claims check.
* Fixed: the reminder and durability note are now one English text each, translated through WordPress.org language packs; the built-in Polish, German, Spanish and Romanian texts had no diacritics.
* Changed: the reminder now says national law may provide a longer period (Article 22a(3) of Directive 2011/83/EU).
* Fixed: the green claims check now matches Polish, German and Spanish forms with diacritics and inflected endings, and also reads attribute values and the descriptions of up to 100 variations (not the products a grouped product lists); the box says when a product has more.
* Changed: the green claims box lists phrases to review instead of calling them prohibited, and no longer shows an all-clear tick.
* Fixed: the product tab is not added when it would be empty; a durability of 2 years or less is not stored.
* Changed: the repairability field takes the repairability class A to E of Delegated Regulation (EU) 2023/1669 (smartphones and slate tablets) instead of a number from 1 to 10, and the badge shows the class; a number saved by 1.0.x is no longer shown.
* Fixed: uninstall also removes the plugin's product meta.
* Changed: readme names Implementing Regulation (EU) 2025/1960, which sets the harmonised notice and label and applies from 27 September 2026.

= 1.0.3 =
* Fixed: deleting the plugin on a multisite network now removes its settings from every site, not only the main one.

= 1.0.2 =
* Changed: display name is now "Garantio - EU Legal Guarantee Notice for WooCommerce". The old name suggested the plugin shows the harmonised notice of Article 22a of Directive 2011/83/EU; it shows its own text reminder.

= 1.0.1 =
* Fixed: legal references. The guarantee reminder is Article 6(1)(l) of Directive 2011/83/EU and the durability note Article 6(1)(la), not Article 6(1)(ea) or 22a(2); offsetting claims are point 4c of Annex I to Directive 2005/29/EC, not 4b. The readme now says the 2024/825 rules apply from 27 September 2026 in Member States and from 27 September 2027 in Poland, and that the plugin shows text, not the harmonised notice or label.

= 1.0.0 =
* Initial release: legal guarantee reminder text, commercial durability note, repairability score display and green claims phrase check.
