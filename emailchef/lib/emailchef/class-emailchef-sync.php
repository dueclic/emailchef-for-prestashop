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

require_once( PS_EMAILCHEF_DIR . '/lib/emailchef/class-emailchef.php' );

class PS_Emailchef_Sync {

	private $custom_field;

	public function __construct() {
		//$this->custom_fields = $this->get_custom_fields();
	}


	/**
	 * Get total ordered by customer ID
	 *
	 * @param $customer_id
	 *
	 * @return float
	 */

	private function getTotalOrdered( $customer_id ) {
		$fetch = Db::getInstance()->executeS( 'SELECT SUM(`total_paid_real`) as tot FROM ' . _DB_PREFIX_ . 'orders WHERE `id_customer`=' . (int) $customer_id . ' group by id_customer ORDER BY `date_add` DESC limit 1' );

		if ( array_key_exists( 0, $fetch ) ) {
			return (float) Tools::ps_round( (float) $fetch[0]['tot'], _PS_PRICE_COMPUTE_PRECISION_ );
		}

		return (float) 0;
	}

	/**
	 * Get total ordered by customer ID in last 30 days
	 *
	 * @param $customer_id
	 *
	 * @return float
	 */

	private function getTotalOrdered30d( $customer_id ) {
		$fetch = Db::getInstance()->executeS( 'SELECT ifnull(SUM(`total_paid_real`),0) as tot FROM ' . _DB_PREFIX_ . 'orders WHERE `id_customer`=' . (int) $customer_id . ' AND date_add >= DATE_SUB(CURRENT_DATE, INTERVAL 1 MONTH) group by id_customer DESC limit 1' );

		if ( array_key_exists( 0, $fetch ) ) {
			return (float) Tools::ps_round( (float) $fetch[0]['tot'], _PS_PRICE_COMPUTE_PRECISION_ );
		}

		return (float) 0;
	}

	/**
	 * Get total ordered by customer ID in last year
	 *
	 * @param $customer_id
	 *
	 * @return float
	 */

	private function getTotalOrdered12m( $customer_id ) {
		$fetch = Db::getInstance()->executeS( 'SELECT ifnull(SUM(`total_paid_real`),0) as tot FROM ' . _DB_PREFIX_ . 'orders WHERE `id_customer`=' . (int) $customer_id . ' AND date_add >= DATE_SUB(CURRENT_DATE, INTERVAL 1 YEAR) group by id_customer DESC limit 1' );

		if ( array_key_exists( 0, $fetch ) ) {
			return (float) Tools::ps_round( (float) $fetch[0]['tot'], _PS_PRICE_COMPUTE_PRECISION_ );
		}

		return (float) 0;
	}

	/**
	 * Get last order info
	 *
	 * @param $customer_id
	 * @param string $param
	 *
	 * @return null
	 */

	private function getLastOrder( $customer_id, $param = "id_order" ) {
		$fetch = Db::getInstance()->executeS( 'SELECT `' . $param . '` FROM ' . _DB_PREFIX_ . 'orders WHERE `id_customer`=' . (int) $customer_id . ' ORDER BY `id_order`  DESC limit 1' );

		if ( array_key_exists( 0, $fetch ) ) {

			if ( $param == "date_add" ) {
				$date = new DateTime( $fetch[0]['date_add'] );

				return $date->format( 'Y-m-d' );
			}

			return $fetch[0][ $param ];
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

	private function getAllOrderedProductIDS( $customer_id ) {

		$orders = Db::getInstance()->executeS( 'SELECT `id_order` FROM ' . _DB_PREFIX_ . 'orders WHERE `id_customer`=' . (int) $customer_id . ' ORDER BY `id_order`' );

		if ( array_key_exists( 0, $orders ) ) {

			$all_ordered = array();

			foreach ( $orders as $key => $order ) {

				$order = new OrderCore( $order['id_order'] );

				$list_products = $order->getProducts();
				foreach ( $list_products as $product ) {
					if ( ! in_array( $product['product_id'], $all_ordered ) ) {
						$all_ordered[] = $product['product_id'];
					}
				}

			}

			return implode( ",", $all_ordered );

		}

		return "";
	}

	private function getLastOrderProductIDS( OrderCore $latest_order ) {
		$products    = $latest_order->getProducts();
		$all_ordered = array();
		foreach ( $products as $product ) {
			$all_ordered[] = $product['product_id'];
		}

		return implode( ",", $all_ordered );
	}

	/**
	 * Get customer data
	 *
	 * @param array $customer
	 *
	 * @return array
	 */
	private function getCustomerData( array $customer ) {

		$address = new AddressCore(
			AddressCore::getFirstCustomerAddressId( $customer['id_customer'] )
		);

		$data = array(
			'first_name'               => $customer['firstname'],
			'last_name'                => $customer['lastname'],
			'user_email'               => $customer['email'],
			'customer_id'              => $customer['id_customer'],
			'billing_company'          => $address->company,
			'billing_address_1'        => $address->address1,
			'billing_postcode'         => $address->postcode,
			'billing_city'             => $address->city,
			'billing_phone'            => $address->phone,
			'billing_state'            => StateCore::getNameById( $address->id_state ),
			'billing_country'          => $address->country,
			'currency'                 => CurrencyCore::getDefaultCurrency()->name,
			'newsletter'               => 'no'

		);

		$latest_order_id     = $this->getLastOrder( $customer['id_customer'], 'id_order' );

		if ($latest_order_id !== null) {

			$latest_order        = new OrderCore( $latest_order_id );
			$latest_order_date   = new DateTime( $latest_order->date_add );
			$latest_order_status = $latest_order->getCurrentStateFull( (int) Configuration::get( 'PS_LANG_DEFAULT' ) )['name'];

			array_merge($data, array(
				'total_ordered'            => $this->getTotalOrdered( $customer['id_customer'] ),
				'total_ordered_30d'        => $this->getTotalOrdered30d( $customer['id_customer'] ),
				'total_ordered_12m'        => $this->getTotalOrdered12m( $customer['id_customer'] ),
				'total_orders'             => OrderCore::getCustomerNbOrders( $customer['id_customer'] ),
				'latest_order_id'          => $latest_order_id,
				'latest_order_date'        => $latest_order_date->format( 'Y-m-d' ),
				'latest_order_status'      => $latest_order_status,
				'latest_order_amount'      => CartCore::getCartByOrderId( $latest_order_id )->getOrderTotal(),
				'all_ordered_product_ids'  => $this->getAllOrderedProductIDS( $customer['id_customer'] ),
				'latest_order_product_ids' => $this->getLastOrderProductIDS( $latest_order ),
			));

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
	public function getSyncOrderData( $order_id, OrderStateCore $status ) {
		$order                 = new OrderCore( $order_id );
		$customer              = $order->getCustomer();
		$id_customer           = $customer->id;
		$latest_order_date     = new DateTime( $order->date_add );
		$latest_order_date_upd = new DateTime( $order->date_upd );
		$latest_order_status   = $status->name;
		$status_id = $status->id;

		$data = array(
			'first_name'               => $customer->firstname,
			'last_name'                => $customer->lastname,
			'user_email'               => $customer->email,
			'customer_id'              => $id_customer,
			'total_ordered'            => $this->getTotalOrdered( $id_customer ),
			'total_ordered_30d'        => $this->getTotalOrdered30d( $id_customer ),
			'total_ordered_12m'        => $this->getTotalOrdered12m( $id_customer ),
			'total_orders'             => OrderCore::getCustomerNbOrders( $id_customer ),
			'latest_order_id'          => $order_id,
			'latest_order_date'        => $latest_order_date->format( 'Y-m-d' ),
			'latest_order_status'      => $latest_order_status,
			'latest_order_amount'      => CartCore::getCartByOrderId( $order_id )->getOrderTotal(),
			'all_ordered_product_ids'  => $this->getAllOrderedProductIDS( $id_customer ),
			'latest_order_product_ids' => $this->getLastOrderProductIDS( $order ),
		);

		if ( $customer->isGuest() ) {

			$address = new AddressCore(
				AddressCore::getFirstCustomerAddressId( $id_customer )
			);

			$data = array_merge( $data, array(
				'billing_company'   => $address->company,
				'billing_address_1' => $address->address1,
				'billing_postcode'  => $address->postcode,
				'billing_city'      => $address->city,
				'billing_phone'     => $address->phone,
				'billing_state'     => StateCore::getNameById( $address->id_state ),
				'billing_country'   => $address->country,
				'currency'          => CurrencyCore::getDefaultCurrency()->name,
				'status_id'         => $status_id
			) );
		}

		if ( in_array( $status_id, array(
			Configuration::get( "PS_OS_SHIPPING" ),
			Configuration::get( "PS_OS_DELIVERED" )
		) ) ) {
			$data = array_merge( $data, array(
				'latest_shipped_order_id'     => $order_id,
				'latest_shipped_order_date'   => $latest_order_date_upd->format( 'Y-m-d' ),
				'latest_shipped_order_status' => $latest_order_status
			) );
		}

		return $data;
	}

	/**
	 * @param CustomerCore $customer
	 * @param $newsletter
	 * @return array
	 */

	public function getSyncCustomerAccountAdd(CustomerCore $customer, $newsletter) {
		return array(
			'first_name'  => $customer->firstname,
			'last_name'   => $customer->lastname,
			'user_email'  => $customer->email,
			'newsletter'  => $newsletter,
			'customer_id' => $customer->id
		);
	}

	/**
	 * Get Sync Update Customer Address
	 * @param AddressCore $address
	 * @return array
	 */

	public function getSyncUpdateCustomerAddress(AddressCore $address){

		$customer = new Customer($address->id_customer);
		$customer_email = $customer->email;

		return array(
			'customer_id'       => $address->id_customer,
			'first_name'        => $address->firstname,
			'last_name'         => $address->lastname,
			'user_email'        => $customer_email,
			'billing_company'   => $address->company,
			'billing_address_1' => $address->address1,
			'billing_postcode'  => $address->postcode,
			'billing_city'      => $address->city,
			'billing_phone'     => $address->phone,
			'billing_state'     => StateCore::getNameById( $address->id_state ),
			'billing_country'   => CountryCore::getNameById(
				(int) Configuration::get( 'PS_LANG_DEFAULT' ),
				$address->id_country
			)
		);

	}

	public function getCustomersData() {

		$data = array();

		foreach ( CustomerCore::getCustomers() as $customer ) {
			$data[] = $this->getCustomerData( $customer );
		}

		return $data;

	}

}
