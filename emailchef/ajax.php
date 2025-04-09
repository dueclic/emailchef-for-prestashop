<?php

define("MAX_CONTACTS", 2000);

require_once( dirname( __FILE__ ) . '../../../config/config.inc.php' );
require_once( dirname( __FILE__ ) . '../../../init.php' );

final class EmailchefAjaxRequest {

	private static $instance;

	public static function get_instance() {

		if ( empty( self::$instance ) && ! ( self::$instance instanceof EmailchefAjaxRequest ) ) {
			self::$instance = new EmailchefAjaxRequest;
		}

		return self::$instance;

	}

	/**
	 * @var Emailchef $module
	 */

	private $module;

	/**
	 * EmailchefAjaxRequest constructor.
	 */

	private function __construct() {
		$this->module = Module::getInstanceByName( 'emailchef' );
	}

	public function route( $args ) {
		$action = $args['action'];
		$method = 'ajax_' . $action;

		$response = $this->_errorRequest();

		if ( method_exists( $this, $method ) ) {
			try {
				$response = call_user_func( array( $this, $method ), $args );
			} catch ( \Exception $e ) {

				$response['msg'] = $this->module->l( 'Error exception: ' ) . $e->getMessage();

				die( json_encode( $response ));
			}
		}

		die(
        json_encode( $response )
		);

	}

	private function _errorRequest() {
		return array(
			'type' => 'error',
			'msg'  => $this->module->l( 'Route invalid' )
		);
	}

	public function ajax_emailchefaddcustomfields( $args ) {

        $psec = $this->module->emailchef();

		$response = array(
			'type' => 'error',
			'msg'  => $this->module->l( 'API credentials are wrong.' )
		);

		if ( $psec->isLogged() ) {

			if ( ! $args['list_id'] || empty( $args['list_id'] ) ) {
				$response['msg'] = $this->module->l( 'List provided is not valid.' );

				return $response;
			}

            $psec->upsert_integration($args['list_id']);

            $init = $psec->initialize_custom_fields( $args['list_id'] );

			if ( $init ) {

				$response['type'] = "success";
				$response['msg']  = $this->module->l( "Custom fields created successfully." );

				$this->module->log(
					sprintf(
						$this->module->l( 'Custom fields created for list %d' ),
						$args['list_id']
					)
				);

				return $response;

			}

			$response['msg'] = $psec->lastError;

			$this->module->log(
				sprintf(
					$this->module->l( 'Failed attempt to create custom fields for list %d' ),
					$args['list_id']
				),
				3
			);


		}

		return $response;

	}

	public function ajax_changelanguage($args) {

		error_reporting(0);

		$response = array(
			'type' => 'success'
		);

		Configuration::updateValue( $this->module->prefix_setting( 'lang' ), $args['lang'] );

		return $response;

	}

	public function ajax_emailchefabandonedcart($args) {

		$psec    = $this->module->emailchef();
		$list_id = $this->module->_getConf( "list" );

		require_once( dirname( __FILE__ ) . "/lib/emailchef/class-emailchef-sync.php" );

		if ($psec->isLogged()){

			$this->module->log(
				sprintf(
					$this->module->l( 'Started exporting abandoned carts for list %d' ),
					$list_id
				)
			);

			$sync = new PS_Emailchef_Sync();
			$abandoned = $sync->getAbandonedCarts();

			$ids = array();

			foreach ($abandoned as $cart){

				$cartob = new Cart($cart['total']);

				if (!in_array($cart['total'], $ids) &&
				    $cartob->getOrderTotal() !== 0.00 &&
					$cartob->date_add
				) {

					$higher_product_data = $sync->getHigherProductCart($cartob);

					$customer = array(
						'first_name' => $cart['firstname'],
						'last_name' => $cart['last_name'],
						'user_email' => $cart['email'],
						'customer_id' => $cart['id_customer'],
					);

					$customer = array_merge( $customer, $higher_product_data );

					$ids[] = $cart['total'];

                    $psec->upsert_customer($list_id, $customer );

                    Db::getInstance()->insert("emailchef_abcart_synced", array(
                        'id_cart' => $cart['total'],
                        'date_synced' => date("Y-m-d H:i:s")
                    ));

				}



			}

			$response = array(
				'status' => 'success',
				'msg'    => $this->module->l( 'Abandoned cart export completed successfully.' ),
				'abandoned' => $abandoned
			);


		}

		else {
			$response = array(
				'type' => 'error',
				'msg'  => $this->module->l( 'API credentials are wrong.' )
			);

			$this->module->log(
				sprintf(
					$this->module->l( 'Abandoned cart export for list %d failed. Error reason: %s' ),
					$list_id,
					$response['msg']
				)
			);
		}

		return $response;

	}

	public function ajax_emailchefaddlist( $args ) {

		error_reporting( 0 );

        $psec = $this->module->emailchef();


        $response = array(
			'type' => 'error',
			'msg'  => $this->module->l( 'API credentials are wrong.' )
		);

		if ( $psec->isLogged() ) {

			if ( ! $args['list_name'] || empty( $args['list_name'] ) ) {
				$response['msg'] = $this->module->l( 'Enter a name for the new list.' );

				return $response;
			}

			if ( ! $args['list_desc'] || empty( $args['list_desc'] ) ) {
				$args['list_desc'] = "";
			}

			$list_id = $psec->create_list( $args['list_name'], $args['list_desc'] );

			$response['full_response'] = $response;

			if ( $list_id !== false ) {

				$response['type']    = "success";
				$response['msg']     = $this->module->l( "List created successfully." );
				$response['list_id'] = $list_id;

				$this->module->log(
					sprintf(
						$this->module->l( 'List %d created (Name: %s, Description: %s)' ),
						$list_id,
						$args['list_name'],
						$args['list_desc']
					)
				);

				return $response;

			}

			$response['msg'] = $psec->lastError;

			$this->module->log(
				sprintf(
					$this->module->l( 'Failed attempt to create list %d (Name: %s, Description: %s)' ),
					$list_id,
					$args['list_name'],
					$args['list_desc']
				),
				3
			);

		}

		return $response;

	}

    public function ajax_emailchefdisconnect( ) {

        $this->module->deleteConfigurations();

        return array(
            'type' => 'success'
        );

    }

	public function ajax_emailcheflogin( $args ) {

		if ( isset( $args['api_user'] ) && isset( $args['api_pass'] ) ) {
			$psec = $this->module->emailchef( $args['api_user'], $args['api_pass'] );
		} else {
			$psec = $this->module->emailchef();
		}

		if ( $psec->isLogged() ) {

			$response = array(
				'status' => 'success',
				'msg'    => $this->module->l( 'User logged in successfully.' ),
				'policy' => $psec->get_policy(),
				'lists'  => $psec->get_lists()
			);

			if ( isset( $_POST['fetch'] ) && $_POST['fetch'] ) {
				$response['list'] = $this->module->_getConf( 'list' );
			}


		} else {

			$response = array(
				'type' => 'error',
				'msg'  => $this->module->l( 'API credentials are wrong.' )
			);

		}

		return $response;

	}

	public function ajax_emailchefsync( $args ) {

		set_time_limit(0);

		require_once( dirname( __FILE__ ) . "/lib/emailchef/class-emailchef-sync.php" );

		$psec    = $this->module->emailchef();
		$list_id = $this->module->_getConf( "list" );

		$this->module->log(
			sprintf(
				$this->module->l( 'Initial synchronization started for list %d.' ),
				$list_id
			)
		);

		if ( $psec->isLogged() ) {

			$sync = new PS_Emailchef_Sync();

			$data = array();

			$start = isset( $_POST['offset'] ) ? $_POST['offset'] : 0;
            $limit = isset( $_POST['limit'] ) ? $_POST['limit'] : 0;

            if ($limit == 0){
                $customers = CustomerCore::getCustomers();
            } else {
                $customers = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('
            SELECT `id_customer`, `email`, `firstname`, `lastname`
            FROM `'._DB_PREFIX_.'customer` ORDER BY `id_customer` ASC LIMIT '.$limit.' OFFSET '.$start
                );
            }


			foreach ($customers as $customer) {

				$curCustomer = array();

				foreach ($sync->getCustomerData($customer) as $placeholder => $value){

					if ($placeholder == "user_email")
						$placeholder = "email";

					$curCustomer[] = array(
						"placeholder" => $placeholder,
						"value" => $value
					);
				}

				if (count($data) > MAX_CONTACTS) {
					$this->module->emailchef()->import($list_id, $data);
					$data = array();
					$data[] = $curCustomer;
				}
				else {
					$data[] = $curCustomer;
				}

			}

			if (count($data) > 0)
				$this->module->emailchef()->import($list_id, $data);

			$response = array(
				'status' => 'success',
				'msg'    => $this->module->l( 'Initial export completed successfully.' ),
			);

			$this->module->log(
				sprintf(
					$this->module->l( 'Export for list %d completed successfully.' ),
					$list_id
				)
			);


		} else {

			$response = array(
				'type' => 'error',
				'msg'  => $this->module->l( 'API credentials are wrong.' )
			);

			$this->module->log(
				sprintf(
					$this->module->l( 'Export for list %d failed. Error reason: %s' ),
					$list_id,
					$response['msg']
				)
			);

		}

		return $response;
	}

}

$emailchef = EmailchefAjaxRequest::get_instance();
$emailchef->route( $_POST + $_GET );
