<?php

require_once( dirname( __FILE__ ) . '../../../config/config.inc.php' );
require_once( dirname( __FILE__ ) . '../../../init.php' );

final class EmailchefAjaxRequest{

	private static $instance;

	public static function get_instance() {

		if ( empty( self::$instance ) && ! ( self::$instance instanceof EmailchefAjaxRequest ) )
			self::$instance = new EmailchefAjaxRequest;

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
		$this->module = Module::getInstanceByName('emailchef');
	}

	public function route($args){
		$action = $args['action'];
		$method = 'ajax_'.$action;

		$response = $this->_errorRequest();

		if (method_exists($this, $method))
			$response = call_user_func(array($this, $method), $args);

		die(
			Tools::jsonEncode($response)
		);

	}

	private function _errorRequest() {
		array(
			'type' => 'error',
			'msg' => $this->module->l('Route non valida')
		);
	}

	public function ajax_emailcheflogin($args){

		if (isset($args['api_user']) && isset($args['api_pass'])) {
			$psec = $this->module->emailchef( $args['api_user'], $args['api_pass'] );
		}
		else {
			$psec = $this->module->emailchef();
		}

		if ( $psec->isLogged() ) {

			$response = array(
				'status' => 'success',
				'msg'    => $this->module->l( 'Utente loggato con successo.' ),
				'policy' => $psec->get_policy()
			);

		} else {

			$response = array(
				'type' => 'error',
				'msg'  => $this->module->l( 'Username o password non corretti.' )
			);

		}

		return $response;

	}

}

$emailchef = EmailchefAjaxRequest::get_instance();
$emailchef->route(Tools::getAllValues());