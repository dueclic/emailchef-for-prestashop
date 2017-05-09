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

require_once(PS_EMAILCHEF_DIR . '/lib/emailchef/class-emailchef.php');

class PS_Emailchef_Sync extends PS_Emailchef
{

    private $custom_field;

    public function __construct()
    {
        $this->custom_fields = $this->get_custom_fields();
    }


    /**
     * Get total ordered by customer ID
     * @param $customer_id
     * @return float
     */

    private function getTotalOrdered($customer_id) {
        $fetch = Db::getInstance()->executeS('SELECT SUM(`total_paid_real`) as tot FROM '._DB_PREFIX_.'orders WHERE `id_customer`='.(int) $customer_id.' group by id_customer ORDER BY `date_add` DESC limit 1');

        if (array_key_exists(0, $fetch))
            return (float)Tools::ps_round((float)$fetch[0]['tot'], _PS_PRICE_COMPUTE_PRECISION_);
        return (float)0;
    }

    /**
     * Get total ordered by customer ID in last 30 days
     * @param $customer_id
     * @return float
     */

    private function getTotalOrdered30d($customer_id){
        $fetch = Db::getInstance()->executeS('SELECT ifnull(SUM(`total_paid_real`),0) as tot FROM '._DB_PREFIX_.'orders WHERE `id_customer`='.(int) $customer_id.' AND date_add >= DATE_SUB(CURRENT_DATE, INTERVAL 1 MONTH) group by id_customer DESC limit 1');

        if (array_key_exists(0, $fetch))
            return (float)Tools::ps_round((float)$fetch[0]['tot'], _PS_PRICE_COMPUTE_PRECISION_);

        return (float)0;
    }

    /**
     * Get total ordered by customer ID in last year
     * @param $customer_id
     * @return float
     */

    private function getTotalOrdered12m($customer_id){
        $fetch = Db::getInstance()->executeS('SELECT ifnull(SUM(`total_paid_real`),0) as tot FROM '._DB_PREFIX_.'orders WHERE `id_customer`='.(int) $customer_id.' AND date_add >= DATE_SUB(CURRENT_DATE, INTERVAL 1 YEAR) group by id_customer DESC limit 1');

        if (array_key_exists(0, $fetch))
            return (float)Tools::ps_round((float)$fetch[0]['tot'], _PS_PRICE_COMPUTE_PRECISION_);

        return (float)0;
    }

    /**
     * Get last order info
     * @param $customer_id
     * @param string $param
     * @return null
     */

    private function getLastOrder($customer_id, $param = "id_order"){
        $fetch = Db::getInstance()->executeS('SELECT `'.$param.'` FROM '._DB_PREFIX_.'orders WHERE `id_customer`='.(int) $customer_id.' ORDER BY `id_order`  DESC limit 1');

        if (array_key_exists(0, $fetch)) {

            if ($param == "date_add"){
                $date = new DateTime($fetch[0]['date_add']);
                return $date->format('Y-m-d');
            }

            return $fetch[0][$param];
        }
        return null;
    }

    /**
     * @param array $customer
     */
    private function getCustomerData(array $customer)
    {

        $address = new AddressCore(
            AddressCore::getFirstCustomerAddressId($customer['id_customer'])
        );

        $latest_order_id = $this->getLastOrder($customer['id_customer'], 'id_order');
        $latest_order = new Order($latest_order_id);
        $latest_order_date = new DateTime($latest_order->date_add);
        $latest_order_status = $latest_order->getCurrentStateFull((int)Configuration::get('PS_LANG_DEFAULT'));

        $data = array(
            'first_name' => $customer['firstname'],
            'last_name' => $customer['lastname'],
            'user_email' => $customer['email'],
            'customer_id' => $customer['id_customer'],
            'billing_company' => $address->company,
            'billing_address_1' => $address->address1,
            'billing_postcode' => $address->postcode,
            'billing_city' => $address->city,
            'billing_phone' => $address->phone,
            'billing_state' => StateCore::getNameById($address->id_state),
            'billing_country' => CountryCore::getNameById($address->country),
            'currency' => CurrencyCore::getDefaultCurrency()->name,
            'total_ordered' => $this->getTotalOrdered($customer['id_customer']),
            'total_ordered_30d' => $this->getTotalOrdered30d($customer['id_customer']),
            'total_ordered_12m' => $this->getTotalOrdered12m($customer['id_customer']),
            'total_orders' => OrderCore::getCustomerNbOrders($customer['id_customer']),
            'latest_order_id' => $latest_order_id,
            'latest_order_date' => $latest_order_date->format('Y-m-d'),
            'latest_order_status' => $latest_order_status,
            'latest_order_amount' => CartCore::getCartByOrderId($latest_order_id)->getOrderTotal()

        );

    }

    public function getCustomersData()
    {

        $data = array();

        foreach (CustomerCore::getCustomers as $customer) {
            $data[] = $this->getCustomerData($customer);
        }

        return $data;

    }

}

