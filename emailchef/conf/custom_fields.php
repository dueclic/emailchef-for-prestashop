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

function get_all_languages()
{
    $langs = array();
    foreach (LanguageCore::getLanguages() as $lang) {
        $langs[] = array(
            "text" => LanguageCore::getIsoById($lang['id_lang']),
        );
    }

    return $langs;
}

function get_group_names()
{

    $groups = array(
        array(
            'text' => 'Visitor',
        ),
        array(
            'text' => 'Guest',
        ),
        array(
            'text' => 'Customer',
        ),
    );

    return $groups;

}

function get_currencies()
{
    $currencies = array();
    foreach (CurrencyCore::getCurrencies() as $currency) {
        $currencies[] = array(
            "text" => $currency['iso_code'],
        );
    }

    return $currencies;
}

function get_order_statuses()
{
    return array(
        array("text" => "Awaiting bank wire payment"),
        array("text" => "Awaiting Cash On Delivery validation"),
        array("text" => "Awaiting check payment"),
        array("text" => "Awaiting PayPal payment"),
        array("text" => "Canceled"),
        array("text" => "Delivered"),
        array("text" => "On backorder (not paid)"),
        array("text" => "On backorder (paid)"),
        array("text" => "Payment accepted"),
        array("text" => "Payment error"),
        array("text" => "Processing in progress"),
        array("text" => "Refunded"),
        array("text" => "Remote payment accepted"),
        array("text" => "Shipped"),
    );

}

function get_order_statuses_delivered()
{
    return array(
        array("text" => "Delivered"),
        array("text" => "Shipped"),
    );
}

$mod = Module::getInstanceByName('emailchef');

return array(

    'first_name'                  => array(
        'name'      => $mod->l('Name'),
        'data_type' => 'predefined',
        'ord'       => 0,
    ),
    'last_name'                   => array(
        'name'      => $mod->l('Surname'),
        'data_type' => 'predefined',
        'ord'       => 1,
    ),
    'user_email'                  => array(
        'name'      => $mod->l('Email'),
        'data_type' => 'predefined',
        'ord'       => 2,
    ),
    'source'                      => array(
        'name'      => $mod->l('Source'),
        'data_type' => 'text',
        'ord'       => 3,
    ),
    'gender'                      => array(
        'name'          => $mod->l('Gender'),
        'data_type'     => 'select',
        'options'       => array(
            array(
                'text' => 'na',
            ),
            array(
                'text' => 'm',
            ),
            array(
                'text' => 'f',
            ),
        ),
        'default_value' => 'na',
        'ord'           => 4,
    ),
    'lang'                        => array(
        'name'          => $mod->l('Language'),
        'data_type'     => 'select',
        'options'       => get_all_languages(),
        'default_value' => LanguageCore::getIsoById(Configuration::get("PS_LANG_DEFAULT")),
        'ord'           => 5,
    ),
    'birthday'                    => array(
        'name'      => $mod->l('Date of birth'),
        'data_type' => 'date',
        'ord'       => 6,
    ),
    'billing_company'             => array(
        'name'      => $mod->l('Company'),
        'data_type' => 'text',
        'ord'       => 7,
    ),
    'billing_address_1'           => array(
        'name'      => $mod->l('Address'),
        'data_type' => 'text',
        'ord'       => 8,
    ),
    'billing_postcode'            => array(
        'name'      => $mod->l('ZIP code'),
        'data_type' => 'text',
        'ord'       => 9,
    ),
    'billing_city'                => array(
        'name'      => $mod->l('City'),
        'data_type' => 'text',
        'ord'       => 10,
    ),
    'billing_state'               => array(
        'name'      => $mod->l('State'),
        'data_type' => 'text',
        'ord'       => 11,
    ),
    'billing_country'             => array(
        'name'      => $mod->l('Country'),
        'data_type' => 'text',
        'ord'       => 12,
    ),
    'billing_phone'               => array(
        'name'      => $mod->l('Land phone'),
        'data_type' => 'text',
        'ord'       => 13,
    ),
    'billing_phone_2'             => array(
        'name'      => $mod->l('Mobile phone'),
        'data_type' => 'text',
        'ord'       => 14,
    ),
    'newsletter'                  => array(
        'name'          => $mod->l('Agreed to newsletter'),
        'data_type'     => 'select',
        'options'       => array(
            array(
                'text' => 'yes',
            ),
            array(
                'text' => 'no',
            ),
            array(
                'text' => 'pending',
            ),
        ),
        'default_value' => 'no',
        'ord'           => 15,
    ),
    'currency'                    => array(
        'name'          => $mod->l('Currency'),
        'data_type'     => 'select',
        'options'       => get_currencies(),
        'default_value' => CurrencyCore::getDefaultCurrency()->iso_code,
        'ord'           => 16,
    ),
    'customer_id'                 => array(
        'name'      => $mod->l('Customer ID'),
        'data_type' => 'number',
        'ord'       => 17,
    ),
    'customer_type'               => array(
        'name'      => $mod->l('Customer type'),
        'data_type' => 'select',
        'options'   => get_group_names(),
        'ord'       => 18,
    ),
    'total_ordered'               => array(
        'name'      => $mod->l('Subtotal'),
        'data_type' => 'number',
        'ord'       => 19,
    ),
    'total_ordered_30d'           => array(
        'name'      => $mod->l('Subtotal in the last 30 days'),
        'data_type' => 'number',
        'ord'       => 20,
    ),
    'total_ordered_12m'           => array(
        'name'      => $mod->l('Subtotal in the last 12 months'),
        'data_type' => 'number',
        'ord'       => 21,
    ),
    'total_orders'                => array(
        'name'      => $mod->l('Orders'),
        'data_type' => 'number',
        'ord'       => 22,
    ),
    'all_ordered_product_ids'     => array(
        'name'      => $mod->l('Ordered Product ID'),
        'data_type' => 'text',
        'ord'       => 23,
    ),
    'latest_order_id'             => array(
        'name'      => $mod->l('Last order - ID'),
        'data_type' => 'number',
        'ord'       => 24,
    ),
    'latest_order_date'           => array(
        'name'      => $mod->l('Last order - Date'),
        'data_type' => 'date',
        'ord'       => 25,
    ),
    'latest_order_amount'         => array(
        'name'      => $mod->l('Last order - Total'),
        'data_type' => 'number',
        'ord'       => 26,
    ),
    'latest_order_status'         => array(
        'name'      => $mod->l('Last order - Status'),
        'data_type' => 'select',
        'options'   => get_order_statuses(),
        'ord'       => 27,
    ),
    'latest_order_product_ids'    => array(
        'name'      => $mod->l('Last order - Products IDs'),
        'data_type' => 'text',
        'ord'       => 28,
    ),
    'latest_shipped_order_id'     => array(
        'name'      => $mod->l('Last shipped order - ID'),
        'data_type' => 'number',
        'ord'       => 29,
    ),
    'latest_shipped_order_date'   => array(
        'name'      => $mod->l('Last shipped order - Data'),
        'data_type' => 'date',
        'ord'       => 30,
    ),
    'latest_shipped_order_status' => array(
        'name'      => $mod->l('Last shipped order - Status'),
        'data_type' => 'select',
        'options'   => get_order_statuses_delivered(),
        'ord'       => 31,
    ),
    'ab_cart_is_abandoned_cart'   => array(
        'name'      => $mod->l('Abandoned cart - Yes/No'),
        'data_type' => 'boolean',
        'ord'       => 32,
    ),
    'ab_cart_prod_name_pr_hr'     => array(
        'name'      => $mod->l('Abandoned cart - Most expensive product name'),
        'data_type' => 'text',
        'ord'       => 33,
    ),
    'ab_cart_prod_desc_pr_hr'     => array(
        'name'      => $mod->l('Abandoned cart - Most expensive product description'),
        'data_type' => 'text',
        'ord'       => 34,
    ),
    'ab_cart_prod_pr_pr_hr'       => array(
        'name'      => $mod->l('Abandoned cart - Most expensive product price'),
        'data_type' => 'number',
        'ord'       => 35,
    ),
    'ab_cart_prod_url_pr_hr'      => array(
        'name'      => $mod->l('Abandoned cart - Most expensive product URL'),
        'data_type' => 'text',
        'ord'       => 36,
    ),
    'ab_cart_prod_url_img_pr_hr'  => array(
        'name'      => $mod->l('Abandoned cart - Most expensive product image URL'),
        'data_type' => 'text',
        'ord'       => 37,
    ),
    'ab_cart_prod_id_pr_hr'       => array(
        'name'      => $mod->l('Abandoned cart - Most expensive product ID'),
        'data_type' => 'number',
        'ord'       => 38,
    ),
    'ab_cart_date'                => array(
        'name'      => $mod->l('Abandoned cart - Date'),
        'data_type' => 'date',
        'ord'       => 39,
    ),

);
