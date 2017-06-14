<?php
/**
 * *
 *  2017 dueclic
 *
 *  NOTICE OF LICENSE
 *
 *  This source file is subject to the Academic Free License (AFL 3.0)
 *  that is bundled with this package in the file LICENSE.txt.
 *  It is also available through the world-wide-web at this URL:
 *  http://opensource.org/licenses/afl-3.0.php
 *  If you did not receive a copy of the license and are unable to
 *  obtain it through the world-wide-web, please send an email
 *  to license@prestashop.com so we can send you a copy immediately.
 *
 *  DISCLAIMER
 *
 *  Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 *  versions in the future. If you wish to customize PrestaShop for your
 *  needs please refer to http://www.prestashop.com for more information.
 *
 * @author    dueclic <info@dueclic.com>
 * @copyright 2017 dueclic
 * @license   https://www.gnu.org/licenses/gpl-3.0.html  GNU General Public License (GPL 3.0)
 * /
 */

/**
 * Helper function for get all languages
 */

function get_all_languages() {
	$langs = array();
	foreach ( LanguageCore::getLanguages() as $lang ) {
		$langs[] = array(
			"text" => LanguageCore::getIsoById( $lang['id_lang'] )
		);
	}

	return $langs;
}

function get_group_names() {

	$groups = array(
		array(
			'text' => 'Visitor'
		),
		array(
			'text' => 'Group'
		),
		array(
			'text' => 'Customer'
		)
	);

	return $groups;

}

function get_currencies() {
	$currencies = array();
	foreach ( CurrencyCore::getCurrencies() as $currency ) {
		$currencies[] = array(
			"text" => $currency['iso_code']
		);
	}

	return $currencies;
}

return array(

	'first_name'                  => array(
		'name'      => Translate::getAdminTranslation( 'Nome' ),
		'data_type' => 'predefined',
		'ord'       => 0,
	),
	'last_name'                   => array(
		'name'      => Translate::getAdminTranslation( 'Cognome' ),
		'data_type' => 'predefined',
		'ord'       => 1,
	),
	'user_email'                  => array(
		'name'      => Translate::getAdminTranslation( 'Email' ),
		'data_type' => 'predefined',
		'ord'       => 2,
	),
	'source'                      => array(
		'name'      => Translate::getAdminTranslation( 'Sorgente' ),
		'data_type' => 'text',
		'ord'       => 3
	),
	'gender'                      => array(
		'name'          => Translate::getAdminTranslation( 'Sesso' ),
		'data_type'     => 'select',
		'options'       => array(
			array(
				'text' => 'na'
			),
			array(
				'text' => 'm'
			),
			array(
				'text' => 'f'
			)
		),
		'default_value' => 'na',
		'ord'           => 4
	),
	'lang'                        => array(
		'name'          => Translate::getAdminTranslation( 'Lingua' ),
		'data_type'     => 'select',
		'options'       => get_all_languages(),
		'default_value' => LanguageCore::getIsoById( Configuration::get( "PS_LANG_DEFAULT" ) ),
		'ord'           => 5
	),
	'birthday'                    => array(
		'name'      => Translate::getAdminTranslation( 'Data di nascita' ),
		'data_type' => 'date',
		'ord'       => 6
	),
	'billing_company'             => array(
		'name'      => Translate::getAdminTranslation( 'Società' ),
		'data_type' => 'text',
		'ord'       => 7
	),
	'billing_address_1'           => array(
		'name'      => Translate::getAdminTranslation( 'Indirizzo' ),
		'data_type' => 'text',
		'ord'       => 8
	),
	'billing_postcode'            => array(
		'name'      => Translate::getAdminTranslation( 'CAP' ),
		'data_type' => 'text',
		'ord'       => 9
	),
	'billing_city'                => array(
		'name'      => Translate::getAdminTranslation( 'Città' ),
		'data_type' => 'text',
		'ord'       => 10
	),
	'billing_state'               => array(
		'name'      => Translate::getAdminTranslation( 'Provincia' ),
		'data_type' => 'text',
		'ord'       => 11
	),
	'billing_country'             => array(
		'name'      => Translate::getAdminTranslation( 'Paese' ),
		'data_type' => 'text',
		'ord'       => 12
	),
	'billing_phone'               => array(
		'name'      => Translate::getAdminTranslation( 'Telefono fisso' ),
		'data_type' => 'text',
		'ord'       => 13
	),
	'billing_phone_2'             => array(
		'name'      => Translate::getAdminTranslation( 'Telefono cellulare' ),
		'data_type' => 'text',
		'ord'       => 14
	),
	'newsletter'                  => array(
		'name'          => Translate::getAdminTranslation( 'Consenso newsletter' ),
		'data_type'     => 'select',
		'options'       => array(
			array(
				'text' => 'yes'
			),
			array(
				'text' => 'no'
			),
			array(
				'text' => 'pending'
			)
		),
		'default_value' => 'no',
		'ord'           => 15
	),
	'currency'                    => array(
		'name'          => Translate::getAdminTranslation( 'Valuta' ),
		'data_type'     => 'select',
		'options'       => get_currencies(),
		'default_value' => CurrencyCore::getDefaultCurrency()->iso_code,
		'ord'           => 16
	),
	'customer_id'                 => array(
		'name'      => Translate::getAdminTranslation( 'ID Cliente' ),
		'data_type' => 'number',
		'ord'       => 17
	),
	'customer_type'               => array(
		'name'      => Translate::getAdminTranslation( 'Tipo cliente' ),
		'data_type' => 'select',
		'options'   => get_group_names(),
		'ord'       => 18
	),
	'total_ordered'               => array(
		'name'      => Translate::getAdminTranslation( 'Totale ordinato' ),
		'data_type' => 'number',
		'ord'       => 19
	),
	'total_ordered_30d'           => array(
		'name'      => Translate::getAdminTranslation( 'Totale ordinato negli ultimi 30 giorni' ),
		'data_type' => 'number',
		'ord'       => 20
	),
	'total_ordered_12m'           => array(
		'name'      => Translate::getAdminTranslation( 'Totale ordinato negli ultimi 12 mesi' ),
		'data_type' => 'number',
		'ord'       => 21
	),
	'total_orders'                => array(
		'name'      => Translate::getAdminTranslation( 'Ordini totali' ),
		'data_type' => 'number',
		'ord'       => 22
	),
	'all_ordered_product_ids'     => array(
		'name'      => Translate::getAdminTranslation( 'ID prodotti ordinati' ),
		'data_type' => 'text',
		'ord'       => 23
	),
	'latest_order_id'             => array(
		'name'      => Translate::getAdminTranslation( 'Ultimo ordine - ID' ),
		'data_type' => 'number',
		'ord'       => 24
	),
	'latest_order_date'           => array(
		'name'      => Translate::getAdminTranslation( 'Ultimo ordine - Data' ),
		'data_type' => 'date',
		'ord'       => 25
	),
	'latest_order_amount'         => array(
		'name'      => Translate::getAdminTranslation( 'Ultimo ordine - Totale' ),
		'data_type' => 'number',
		'ord'       => 26
	),
	'latest_order_status'         => array(
		'name'      => Translate::getAdminTranslation( 'Ultimo ordine - Stato lavorazione' ),
		'data_type' => 'text',
		'ord'       => 27
	),
	'latest_order_product_ids'    => array(
		'name'      => Translate::getAdminTranslation( 'Ultimo ordine - ID prodotti' ),
		'data_type' => 'text',
		'ord'       => 28
	),
	'latest_shipped_order_id'     => array(
		'name'      => Translate::getAdminTranslation( 'Ultimo ordine inviato - ID' ),
		'data_type' => 'number',
		'ord'       => 29
	),
	'latest_shipped_order_date'   => array(
		'name'      => Translate::getAdminTranslation( 'Ultimo ordine inviato - Data' ),
		'data_type' => 'date',
		'ord'       => 30
	),
	'latest_shipped_order_status' => array(
		'name'      => Translate::getAdminTranslation( 'Ultimo ordine inviato - Stato lavorazione' ),
		'data_type' => 'text',
		'ord'       => 31
	),
	'ab_cart_is_abandoned_cart'   => array(
		'name'      => Translate::getAdminTranslation( 'Carrello abbandonato - Sì/No' ),
		'data_type' => 'boolean',
		'ord'       => 32
	),
	'ab_cart_prod_name_pr_hr'     => array(
		'name'      => Translate::getAdminTranslation( 'Carrello abbandonato - Nome prodotto più caro' ),
		'data_type' => 'text',
		'ord'       => 33
	),
	'ab_cart_prod_desc_pr_hr'     => array(
		'name'      => Translate::getAdminTranslation( 'Carrello abbandonato - Desc. prodotto più caro' ),
		'data_type' => 'text',
		'ord'       => 34
	),
	'ab_cart_prod_pr_pr_hr'       => array(
		'name'      => Translate::getAdminTranslation( 'Carrello abbandonato - Prezzo prodotto più caro' ),
		'data_type' => 'number',
		'ord'       => 35
	),
	'ab_cart_prod_url_pr_hr'      => array(
		'name'      => Translate::getAdminTranslation( 'Carrello abbandonato - URL prodotto più caro' ),
		'data_type' => 'text',
		'ord'       => 36
	),
	'ab_cart_prod_url_img_pr_hr'  => array(
		'name'      => Translate::getAdminTranslation( 'Carrello abbandonato - URL immagine prodotto più caro' ),
		'data_type' => 'text',
		'ord'       => 37
	),
	'ab_cart_prod_id_pr_hr'       => array(
		'name'      => Translate::getAdminTranslation( 'Carrello abbandonato - ID prodotto più caro' ),
		'data_type' => 'number',
		'ord'       => 38
	),
	'ab_cart_date'                => array(
		'name'      => Translate::getAdminTranslation( 'Carrello abbandonato - Data' ),
		'data_type' => 'date',
		'ord'       => 39
	)

);