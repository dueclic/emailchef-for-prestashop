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

return array(

	'first_name'                                      => array(
		'name' => Translate::getAdminTranslation( 'Nome' ),
		'data_type' => 'predefined'
	),
	'last_name'                                       => array(
		'name' => Translate::getAdminTranslation( 'Cognome' ),
		'data_type' => 'predefined'
	),
	'user_email'                                      => array(
		'name' => Translate::getAdminTranslation( 'Email' ),
		'data_type' => 'predefined'
	),
	'source'                                          => array(
		'name' => Translate::getAdminTranslation( 'Sorgente' ),
		'data_type' => 'text'
	),
	'gender'                                          => array(
		'name' => Translate::getAdminTranslation( 'Sesso' ),
		'data_type' => 'select',
		'options' => array(
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
		'default_value' => 'na'
	),
	'language'                                        => array(
		'name' => Translate::getAdminTranslation( 'Lingua' ),
		'data_type' => 'text'
	),
	'birthday'                                        => array(
		'name' => Translate::getAdminTranslation( 'Data di nascita' ),
		'data_type' => 'date'
	),
	'billing_company'                                 => array(
		'name' => Translate::getAdminTranslation( 'Società' ),
		'data_type' => 'text'
	),
	'billing_address_1'                               => array(
		'name' => Translate::getAdminTranslation( 'Indirizzo' ),
		'data_type' => 'text'
	),
	'billing_postcode'                                => array(
		'name' => Translate::getAdminTranslation( 'CAP' ),
		'data_type' => 'text'
	),
	'billing_city'                                    => array(
		'name' => Translate::getAdminTranslation( 'Città' ),
		'data_type' => 'text'
	),
	'billing_phone'                                   => array(
		'name' => Translate::getAdminTranslation( 'Telefono fisso' ),
		'data_type' => 'text'
	),
	'billing_phone_2'                                 => array(
		'name' => Translate::getAdminTranslation( 'Telefono cellulare' ),
		'data_type' => 'text'
	),
	'billing_state'                                   => array(
		'name' => Translate::getAdminTranslation( 'Provincia' ),
		'data_type' => 'text'
	),
	'billing_country'                                 => array(
		'name' => Translate::getAdminTranslation( 'Paese' ),
		'data_type' => 'text'
	),
	'currency'                                        => array(
		'name' => Translate::getAdminTranslation( 'Valuta' ),
		'data_type' => 'text'
	),
	'customer_id'                                     => array(
		'name' => Translate::getAdminTranslation( 'ID Cliente' ),
		'data_type' => 'number'
	),
	'customer_data_type'                                   => array(
		'name' => Translate::getAdminTranslation( 'Tipo cliente' ),
		'data_type' => 'text'
	),
	'total_ordered'                                   => array(
		'name' => Translate::getAdminTranslation( 'Totale ordinato' ),
		'data_type' => 'number'
	),
	'total_ordered_30d'                               => array(
		'name' => Translate::getAdminTranslation( 'Totale ordinato negli ultimi 30 giorni' ),
		'data_type' => 'number'
	),
	'total_ordered_12m'                               => array(
		'name' => Translate::getAdminTranslation( 'Totale ordinato negli ultimi 12 mesi' ),
		'data_type' => 'number'
	),
	'total_orders'                                    => array(
		'name' => Translate::getAdminTranslation( 'Ordini totali' ),
		'data_type' => 'number'
	),
	'latest_order_id'                                 => array(
		'name' => Translate::getAdminTranslation( 'ID ultimo ordine' ),
		'data_type' => 'number'
	),
	'latest_order_date'                               => array(
		'name' => Translate::getAdminTranslation( 'Data ultimo ordine' ),
		'data_type' => 'date'
	),
	'latest_order_amount'                             => array(
		'name' => Translate::getAdminTranslation( 'Totale ultimo ordine' ),
		'data_type' => 'number'
	),
	'latest_order_status'                             => array(
		'name' => Translate::getAdminTranslation( 'Stato lavorazione ultimo ordine' ),
		'data_type' => 'text'
	),
	'all_ordered_product_ids'                         => array(
		'name' => Translate::getAdminTranslation( 'ID prodotti ordinati' ),
		'data_type' => 'text'
	),
	'latest_order_product_ids'                        => array(
		'name' => Translate::getAdminTranslation( 'ID prodotti ultimo ordine' ),
		'data_type' => 'text'
	),
	'latest_shipped_order_id'                         => array(
		'name' => Translate::getAdminTranslation( 'ID ultimo ordine inviato' ),
		'data_type' => 'number'
	),
	'latest_shipped_order_date'                       => array(
		'name' => Translate::getAdminTranslation( 'Data ultimo ordine inviato' ),
		'data_type' => 'date'
	),
	'latest_shipped_order_status'                     => array(
		'name' => Translate::getAdminTranslation( 'Stato lavorazione ultimo ordine inviato' ),
		'data_type' => 'text'
	),
	'newsletter'                                      => array(
		'name' => Translate::getAdminTranslation( 'Consenso newsletter' ),
		'data_type' => 'select',
		'options' => array(
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
		'default_value' => 'no'
	),
	'is_abandoned_cart' => array(
		'name' => Translate::getAdminTranslation( 'Carrello abbandonato' ),
		'data_type' => 'boolean'
	),
	'abandoned_cart_product_name_price_higher'        => array(
		'name' => Translate::getAdminTranslation( 'Nome prodotto con prezzo più alto in carrello abbandonato' ),
		'data_type' => 'text'
	),
	'abandoned_cart_product_description_price_higher' => array(
		'name' => Translate::getAdminTranslation( 'Descrizione prodotto con prezzo più alto in carrello abbandonato' ),
		'data_type' => 'text'
	),
	'abandoned_cart_product_price_price_higher'       => array(
		'name' => Translate::getAdminTranslation( 'Prezzo prodotto con prezzo più alto in carrello abbandonato' ),
		'data_type' => 'number'
	),
	'abandoned_cart_product_url_price_higher'         => array(
		'name' => Translate::getAdminTranslation( 'URL prodotto con prezzo più alto in carrello abbandonato' ),
		'data_type' => 'text'
	),
	'abandoned_cart_product_url_image_price_higher'   => array(
		'name' => Translate::getAdminTranslation( 'URL immagine prodotto con prezzo più alto in carrello abbandonato' ),
		'data_type' => 'text'
	),
	'abandoned_cart_product_id_price_higher'          => array(
		'name' => Translate::getAdminTranslation( 'ID prodotto con prezzo più alto in carrello abbandonato' ),
		'data_type' => 'number'
	),
	'abandoned_cart_purchase_date_price_higher'       => array(
		'name' => Translate::getAdminTranslation( 'Data acquisto prodotto con prezzo più alto in carrello abbandonato' ),
		'data_type' => 'date'
	)

);