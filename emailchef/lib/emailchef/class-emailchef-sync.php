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

require_once(PS_EMAILCHEF_DIR.'/lib/emailchef/class-emailchef.php');

class PS_Emailchef_Sync
{

    private $custom_field;

    public function __construct()
    {
        //$this->custom_fields = $this->get_custom_fields();
    }


    /**
     * Get total ordered by customer ID
     *
     * @param $customer_id
     *
     * @return float
     */

    private function getTotalOrdered($customer_id)
    {
        $fetch = Db::getInstance()->executeS(
            'SELECT SUM(`total_paid_real`) as tot FROM '._DB_PREFIX_.'orders WHERE `id_customer`='.(int)$customer_id.' group by id_customer ORDER BY `date_add` DESC limit 1'
        );

        if (array_key_exists(0, $fetch)) {
            return (float)Tools::ps_round((float)$fetch[0]['tot'], _PS_PRICE_COMPUTE_PRECISION_);
        }

        return (float)0;
    }

    /**
     * Get total ordered by customer ID in last 30 days
     *
     * @param $customer_id
     *
     * @return float
     */

    private function getTotalOrdered30d($customer_id)
    {
        $fetch = Db::getInstance()->executeS(
            'SELECT ifnull(SUM(`total_paid_real`),0) as tot FROM '._DB_PREFIX_.'orders WHERE `id_customer`='.(int)$customer_id.' AND date_add >= DATE_SUB(CURRENT_DATE, INTERVAL 1 MONTH) group by id_customer DESC limit 1'
        );

        if (array_key_exists(0, $fetch)) {
            return (float)Tools::ps_round((float)$fetch[0]['tot'], _PS_PRICE_COMPUTE_PRECISION_);
        }

        return (float)0;
    }

    /**
     * Get total ordered by customer ID in last year
     *
     * @param $customer_id
     *
     * @return float
     */

    private function getTotalOrdered12m($customer_id)
    {
        $fetch = Db::getInstance()->executeS(
            'SELECT ifnull(SUM(`total_paid_real`),0) as tot FROM '._DB_PREFIX_.'orders WHERE `id_customer`='.(int)$customer_id.' AND date_add >= DATE_SUB(CURRENT_DATE, INTERVAL 1 YEAR) group by id_customer DESC limit 1'
        );

        if (array_key_exists(0, $fetch)) {
            return (float)Tools::ps_round((float)$fetch[0]['tot'], _PS_PRICE_COMPUTE_PRECISION_);
        }

        return (float)0;
    }

    /**
     * Get last order info
     *
     * @param $customer_id
     * @param string $param
     *
     * @return null
     */

    private function getLastOrder($customer_id, $param = "id_order")
    {
        $fetch = Db::getInstance()->executeS(
            'SELECT `'.$param.'` FROM '._DB_PREFIX_.'orders WHERE `id_customer`='.(int)$customer_id.' ORDER BY `id_order`  DESC limit 1'
        );

        if (array_key_exists(0, $fetch)) {

            if ($param == "date_add") {
                return $this->get_date($fetch[0]['date_add']);
            }

            return $fetch[0][$param];
        }

        return null;
    }

    /**
     * Get last shipped and completed info
     *
     * @param $customer_id
     * @param string $param
     *
     * @return null
     */

    private function getLastShippedCompletedOrder($customer_id, $param = "id_order")
    {
        $fetch = Db::getInstance()->executeS(
            'SELECT `'.$param.'` FROM '._DB_PREFIX_.'orders WHERE `id_customer`='.(int)$customer_id.' AND `current_state` IN ('.(int)Configuration::get(
                "PS_OS_SHIPPING"
            ).', '.(int)Configuration::get("PS_OS_DELIVERED").') ORDER BY `id_order`  DESC limit 1'
        );

        if (array_key_exists(0, $fetch)) {

            if ($param == "date_add") {
                return $this->get_date($fetch[0]['date_add']);
            }

            return $fetch[0][$param];
        }

        return null;
    }

    /**
     * Get all ordered product ids
     *
     * @param $customer_id
     *
     * @return string
     */

    private function getAllOrderedProductIDS($customer_id)
    {

        $orders = Db::getInstance()->executeS(
            'SELECT `id_order` FROM '._DB_PREFIX_.'orders WHERE `id_customer`='.(int)$customer_id.' ORDER BY `id_order`'
        );

        if (array_key_exists(0, $orders)) {

            $all_ordered = array();

            foreach ($orders as $key => $order) {

                $order = new OrderCore($order['id_order']);

                $list_products = $order->getProducts();
                foreach ($list_products as $product) {
                    if ( ! in_array($product['product_id'], $all_ordered)) {
                        $all_ordered[] = $product['product_id'];
                    }
                }

            }

            return implode(",", $all_ordered);

        }

        return "";
    }

    /**
     * Get last ordered product IDS
     *
     * @param OrderCore $latest_order
     *
     * @return string
     */

    private function getLastOrderProductIDS(OrderCore $latest_order)
    {
        $products    = $latest_order->getProducts();
        $all_ordered = array();
        foreach ($products as $product) {
            $all_ordered[] = $product['product_id'];
        }

        return implode(",", $all_ordered);
    }

    /**
     * Get abandoned cart for Customer ID
     *
     * @param $customer_id
     *
     * @return int|null
     */

    public function getLastAbandonedCart($customer_id)
    {

        $sql = "SELECT c.`id_cart`, IF (IFNULL(o.id_order, 'Non ordered') = 'Non ordered', IF(TIME_TO_SEC(TIMEDIFF('".date(
                'Y-m-d H:i:s'
            )."', c.`date_add`)) > 86000, 'Abandoned cart', 'Non ordered'), o.id_order) id_order FROM "._DB_PREFIX_."cart c LEFT JOIN ps_orders o ON ( c.`id_cart` = o.`id_cart` ) WHERE o.`id_order` IS NULL AND c.`id_customer` = ".(int)$customer_id." ORDER BY c.id_cart DESC LIMIT 1";

        $fetch = Db::getInstance()->executeS($sql);
        if (array_key_exists(0, $fetch) && $fetch[0]['id_order'] == "Abandoned cart") {
            return $fetch[0]['id_cart'];
        }

        return null;
    }

    /**
     * Get abandoned carts
     * @return array|false|mysqli_result|null|PDOStatement|resource
     */

    public function getAbandonedCarts()
    {
        $sql = "SELECT * FROM (
		SELECT
		c.firstname, c.lastname, a.id_cart total, ca.name carrier, c.id_customer, c.email, a.date_upd,a.date_add,
				IF (IFNULL(o.id_order, 'Non ordered') = 'Non ordered', IF(TIME_TO_SEC(TIMEDIFF('".date('Y-m-d H:i:s')."', a.`date_add`)) > 86000, 'Abandoned cart', 'Non ordered'), o.id_order) id_order, IF(o.id_order, 1, 0) badge_success, IF(o.id_order, 0, 1) badge_danger, IF(co.id_guest, 1, 0) id_guest
		FROM `"._DB_PREFIX_."cart` a  
				JOIN `"._DB_PREFIX_."customer` c ON (c.id_customer = a.id_customer)
				LEFT JOIN `"._DB_PREFIX_."currency` cu ON (cu.id_currency = a.id_currency)
				LEFT JOIN `"._DB_PREFIX_."carrier` ca ON (ca.id_carrier = a.id_carrier)
				LEFT JOIN `"._DB_PREFIX_."orders` o ON (o.id_cart = a.id_cart)
				LEFT JOIN `"._DB_PREFIX_."connections` co ON (a.id_guest = co.id_guest AND TIME_TO_SEC(TIMEDIFF('".date(
                'Y-m-d H:i:s'
            )."', co.`date_add`)) < 1800)
				WHERE a.date_add > (NOW() - INTERVAL 7 DAY) ORDER BY a.id_cart DESC 
		) AS toto LEFT JOIN `"._DB_PREFIX_."emailchef_abcart_synced` ec ON (toto.total = ec.id_cart) WHERE id_order='Abandoned cart' AND ec.id_cart IS NULL ORDER BY date_add ASC";

        return Db::getInstance()->ExecuteS($sql);
    }

    /**
     * Get higher product in Cart
     *
     * @param Cart $cart
     *
     * @return array
     */

    public function getHigherProductCart($cart)
    {

        $products = $cart->getProducts();

        usort(
            $products,
            function ($p1, $p2) {
                if ($p1['price'] == $p2['price']) {
                    return 0;
                }

                return $p1['price'] > $p2['price'] ? -1 : 1;
            }
        );

        $product = new ProductCore($products[0]['id_product'], false, (int)Configuration::get('PS_LANG_DEFAULT'));

        $image      = new ImageCore($product->getCoverWs());
        $image_path = _PS_BASE_URL_._THEME_PROD_DIR_.$image->getExistingImgPath().".jpg";

        return array(
            'ab_cart_prod_name_pr_hr'    => $product->name,
            'ab_cart_prod_desc_pr_hr'    => strip_tags($product->description_short),
            'ab_cart_prod_pr_pr_hr'      => $product->getPrice(true, null, 2),
            'ab_cart_date'               => $this->get_date($cart->date_upd),
            'ab_cart_prod_id_pr_hr'      => $product->id,
            'ab_cart_prod_url_pr_hr'     => $product->getLink(),
            'ab_cart_prod_url_img_pr_hr' => $image_path,
            'ab_cart_is_abandoned_cart'  => true,
        );

    }

    /**
     * Get higher product abandoned cart for Customer ID
     *
     * @param $customer_id
     *
     * @return array|bool
     */

    private function getHigherProductAbandonedCart($customer_id)
    {
        $cart_id = $this->getLastAbandonedCart($customer_id);

        if ($cart_id === null) {
            return false;
        }

        $cart = new CartCore($cart_id);

        return $this->getHigherProductCart($cart);

    }

    /**
     * Flush abandoned carts for customer ID
     *
     * @param $customer_id
     *
     * @return array
     */

    public function flushAbandonedCarts($customer_id)
    {

        $abandoned_carts = $this->getAbandonedCarts();

        foreach ($abandoned_carts as $cart) {

            $id_customer = $cart['id_customer'];

            if ($id_customer == $customer_id) {

                Db::getInstance()->insert(
                    "emailchef_abcart_synced",
                    array(
                        'id_cart'     => $cart['total'],
                        'date_synced' => date("Y-m-d H:i:s"),
                    )
                );

            }

        }

        return array(
            'ab_cart_prod_name_pr_hr'    => '',
            'ab_cart_prod_desc_pr_hr'    => '',
            'ab_cart_prod_pr_pr_hr'      => '',
            'ab_cart_date'               => '',
            'ab_cart_prod_id_pr_hr'      => '',
            'ab_cart_prod_url_pr_hr'     => '',
            'ab_cart_prod_url_img_pr_hr' => '',
            'ab_cart_is_abandoned_cart'  => false,
        );
    }

    /**
     * Get higher product abandoned cart or empty
     *
     * @param $customer_id
     *
     * @return array
     */

    public function getHigherProductAbandonedCartOrEmpty($customer_id)
    {

        /**
         * @var $abandoned_cart array
         */
        $abandoned_cart = $this->getHigherProductAbandonedCart($customer_id);

        if ($abandoned_cart === false) {
            return array(
                'ab_cart_prod_name_pr_hr'    => '',
                'ab_cart_prod_desc_pr_hr'    => '',
                'ab_cart_prod_pr_pr_hr'      => '',
                'ab_cart_date'               => '',
                'ab_cart_prod_id_pr_hr'      => '',
                'ab_cart_prod_url_pr_hr'     => '',
                'ab_cart_prod_url_img_pr_hr' => '',
                'ab_cart_is_abandoned_cart'  => false,
            );
        }

        return $abandoned_cart;
    }

    /**
     * Get date helper
     *
     * @param $date
     * @param string $format
     *
     * @return string
     */

    private function get_date($date, $format = "Y-m-d")
    {
        $dt = new DateTime($date);

        return $dt->format($format);
    }

    /**
     * Get order status
     * @param $id_order_state
     *
     * @return mixed
     */

    private function get_order_status($id_order_state)
    {
        $status = array(
            10 => "Awaiting bank wire payment",
            14 => "Awaiting Cash On Delivery validation",
            1  => "Awaiting check payment",
            11 => "Awaiting PayPal payment",
            6  => "Canceled",
            5  => "Delivered",
            13 => "On backorder (not paid)",
            9  => "On backorder (paid)",
            2  => "Payment accepted",
            8  => "Payment error",
            3  => "Processing in progress",
            7  => "Refunded",
            12 => "Remote payment accepted",
            4  => "Shipped",
        );

        return $status[$id_order_state];

    }

    /**
     * Get gender helper
     *
     * @param $id_gender
     *
     * @return string
     */

    private function get_gender($id_gender)
    {

        if ($id_gender == 1) {
            return "m";
        } else if ($id_gender == 2) {
            return "f";
        } else {
            return "na";
        }

    }

    /**
     * Get language helper
     *
     * @param $id_lang
     *
     * @return string
     */

    private function get_lang($id_lang)
    {
        return LanguageCore::getIsoById($id_lang);
    }

    /**
     * Get birthday helper
     *
     * @param $birthday
     *
     * @return string
     */

    private function get_birthday($birthday)
    {
        if (empty($birthday)) {
            return "";
        }

        return $this->get_date($birthday);
    }

    /**
     * Group name by Customer Group ID
     *
     * @param $group_id
     *
     * @return string
     */

    private function get_group($group_id)
    {

        if ($group_id == 1) {
            return "Visitor";
        }

        if ($group_id == 2) {
            return "Guest";
        }

        return "Customer";

    }

    /**
     * Get platform used
     * @return string
     */

    private function get_platform()
    {
        return 'eMailChef for PrestaShop';
    }

    /**
     * Get customer data
     *
     * @param array $customer
     *
     * @return array
     */
    private function getCustomerData(array $customer)
    {

        $address = new AddressCore(
            AddressCore::getFirstCustomerAddressId($customer['id_customer'])
        );

        $customerob = new CustomerCore($customer['id_customer']);

        $data = array(
            'first_name'        => $customerob->firstname,
            'last_name'         => $customerob->lastname,
            'user_email'        => $customerob->email,
            'customer_id'       => $customer['id_customer'],
            'customer_type'     => $this->get_group(CustomerCore::getDefaultGroupId($customer['id_customer'])),
            'gender'            => $this->get_gender($customerob->id_gender),
            'birthday'          => $this->get_birthday($customerob->birthday),
            'lang'              => $this->get_lang($customerob->id_lang),
            'billing_company'   => $address->company,
            'billing_address_1' => $address->address1,
            'billing_postcode'  => $address->postcode,
            'billing_city'      => $address->city,
            'billing_phone'     => $address->phone,
            'billing_phone_2'   => $address->phone_mobile,
            'billing_state'     => StateCore::getNameById($address->id_state),
            'billing_country'   => $address->country,
            'currency'          => CurrencyCore::getDefaultCurrency()->iso_code,
            'source'            => $this->get_platform(),
            'newsletter'        => $customerob->newsletter ? 'yes' : 'no',

        );

        $latest_order_id = $this->getLastOrder($customer['id_customer'], 'id_order');

        if ($latest_order_id !== null) {

            $latest_order        = new OrderCore($latest_order_id);
            $latest_order_date   = $this->get_date($latest_order->date_add);
            $latest_order_status = $latest_order->getCurrentStateFull(
                (int)Configuration::get('PS_LANG_DEFAULT')
            )['id_order_state'];

            $data = array_merge(
                $data,
                array(
                    'total_ordered'            => $this->getTotalOrdered($customer['id_customer']),
                    'total_ordered_30d'        => $this->getTotalOrdered30d($customer['id_customer']),
                    'total_ordered_12m'        => $this->getTotalOrdered12m($customer['id_customer']),
                    'total_orders'             => OrderCore::getCustomerNbOrders($customer['id_customer']),
                    'latest_order_id'          => $latest_order_id,
                    'latest_order_date'        => $latest_order_date,
                    'latest_order_status'      => $this->get_order_status($latest_order_status),
                    'latest_order_amount'      => CartCore::getCartByOrderId($latest_order_id)->getOrderTotal(),
                    'all_ordered_product_ids'  => $this->getAllOrderedProductIDS($customer['id_customer']),
                    'latest_order_product_ids' => $this->getLastOrderProductIDS($latest_order),
                )
            );

        }

        $latest_shipped_completed_order_id = $this->getLastShippedCompletedOrder($customer['id_customer'], 'id_order');

        if ($latest_shipped_completed_order_id !== null) {

            $latest_order          = new OrderCore($latest_shipped_completed_order_id);
            $latest_order_date_upd = $this->get_date($latest_order->date_upd);
            $latest_order_status   = $latest_order->getCurrentStateFull(
                (int)Configuration::get('PS_LANG_DEFAULT')
            )['id_order_state'];

            $data = array_merge(
                $data,
                array(
                    'latest_shipped_order_id'     => $latest_shipped_completed_order_id,
                    'latest_shipped_order_date'   => $latest_order_date_upd,
                    'latest_shipped_order_status' => $this->get_order_status($latest_order_status),
                )
            );

        }

        return $data;

    }

    /**
     * Get sync order data
     *
     * @param $order_id
     * @param OrderStateCore $status
     *
     * @return array
     */
    public function getSyncOrderData($order_id, OrderStateCore $status)
    {
        $order                 = new OrderCore($order_id);
        $customer              = $order->getCustomer();
        $id_customer           = $customer->id;
        $latest_order_date     = $this->get_date($order->date_add);
        $latest_order_date_upd = $this->get_date($order->date_upd);
        $latest_order_status   = $status->name;
        $status_id             = $status->id;

        $data = array(
            'first_name'               => $customer->firstname,
            'last_name'                => $customer->lastname,
            'user_email'               => $customer->email,
            'customer_id'              => $id_customer,
            'customer_type'            => $this->get_group(CustomerCore::getDefaultGroupId($id_customer)),
            'gender'                   => $this->get_gender($customer->id_gender),
            'total_ordered'            => $this->getTotalOrdered($id_customer),
            'total_ordered_30d'        => $this->getTotalOrdered30d($id_customer),
            'total_ordered_12m'        => $this->getTotalOrdered12m($id_customer),
            'total_orders'             => OrderCore::getCustomerNbOrders($id_customer),
            'latest_order_id'          => $order_id,
            'latest_order_date'        => $latest_order_date,
            'latest_order_status'      => $this->get_order_status($status_id),
            'latest_order_amount'      => CartCore::getCartByOrderId($order_id)->getOrderTotal(),
            'all_ordered_product_ids'  => $this->getAllOrderedProductIDS($id_customer),
            'latest_order_product_ids' => $this->getLastOrderProductIDS($order),
            'source'                   => $this->get_platform(),
            'currency'                 => CurrencyCore::getDefaultCurrency()->iso_code,
        );

        if ($customer->isGuest()) {

            $address = new AddressCore(
                AddressCore::getFirstCustomerAddressId($id_customer)
            );

            $data = array_merge(
                $data,
                array(
                    'billing_company'   => $address->company,
                    'billing_address_1' => $address->address1,
                    'billing_postcode'  => $address->postcode,
                    'billing_city'      => $address->city,
                    'billing_phone'     => $address->phone,
                    'billing_phone_2'   => $address->phone_mobile,
                    'billing_state'     => StateCore::getNameById($address->id_state),
                    'billing_country'   => $address->country,
                    'currency'          => CurrencyCore::getDefaultCurrency()->iso_code,
                    'status_id'         => $status_id,
                )
            );
        }

        if (in_array(
            $status_id,
            array(
                Configuration::get("PS_OS_SHIPPING"),
                Configuration::get("PS_OS_DELIVERED"),
            )
        )) {
            $data = array_merge(
                $data,
                array(
                    'latest_shipped_order_id'     => $order_id,
                    'latest_shipped_order_date'   => $latest_order_date_upd,
                    'latest_shipped_order_status' => $this->get_order_status($status_id),
                )
            );
        }

        return $data;
    }

    /**
     * @param CustomerCore $customer
     * @param $newsletter
     *
     * @return array
     */

    public function getSyncCustomerAccountAdd(CustomerCore $customer, $newsletter)
    {

        $data = array(
            'first_name'    => $customer->firstname,
            'last_name'     => $customer->lastname,
            'user_email'    => $customer->email,
            'customer_type' => $this->get_group(CustomerCore::getDefaultGroupId($customer->id)),
            'newsletter'    => $newsletter,
            'customer_id'   => $customer->id,
            'gender'        => $this->get_gender($customer->id_gender),
            'birthday'      => $this->get_birthday($customer->birthday),
            'lang'          => $this->get_lang($customer->id_lang),
            'source'        => $this->get_platform(),
        );

        return $data;

    }

    /**
     * Get Sync Update Customer Address
     *
     * @param AddressCore $address
     *
     * @return array
     */

    public function getSyncUpdateCustomerAddress(AddressCore $address)
    {

        $customer       = new Customer($address->id_customer);
        $customer_email = $customer->email;

        $data = array(
            'customer_id'       => $address->id_customer,
            'first_name'        => $address->firstname,
            'last_name'         => $address->lastname,
            'user_email'        => $customer_email,
            'gender'            => $this->get_gender($customer->id_gender),
            'birthday'          => $this->get_birthday($customer->birthday),
            'language'          => $this->get_lang($customer->id_lang),
            'currency'          => CurrencyCore::getDefaultCurrency()->iso_code,
            'customer_type'     => $this->get_group(CustomerCore::getDefaultGroupId($address->id_customer)),
            'billing_company'   => $address->company,
            'billing_address_1' => $address->address1,
            'billing_postcode'  => $address->postcode,
            'billing_city'      => $address->city,
            'billing_phone'     => $address->phone,
            'billing_phone_2'   => $address->phone_mobile,
            'billing_state'     => StateCore::getNameById($address->id_state),
            'billing_country'   => CountryCore::getNameById(
                (int)Configuration::get('PS_LANG_DEFAULT'),
                $address->id_country
            ),
            'source'            => $this->get_platform(),
        );

        return $data;

    }

    /**
     * Get Sync Update Customer Info
     *
     * @param Customer $customer
     *
     * @return array
     */

    public function getSyncUpdateCustomerInfo(Customer $customer)
    {

        $data = array(
            'customer_id'   => $customer->id,
            'first_name'    => $customer->firstname,
            'last_name'     => $customer->lastname,
            'user_email'    => $customer->email,
            'birthday'      => $this->get_birthday($customer->birthday),
            'language'      => $this->get_lang($customer->id_lang),
            'currency'      => CurrencyCore::getDefaultCurrency()->iso_code,
            'customer_type' => $this->get_group(CustomerCore::getDefaultGroupId($customer->id)),
            'source'        => $this->get_platform(),
        );

        return $data;

    }

    public function getCustomersData()
    {

        $data = array();

        foreach (CustomerCore::getCustomers() as $customer) {
            $data[] = $this->getCustomerData($customer);
        }

        return $data;

    }

}