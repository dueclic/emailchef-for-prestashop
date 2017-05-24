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

if ( ! defined( '_CAN_LOAD_FILES_' ) ) {
	exit;
}

if ( ! defined( '_PS_VERSION_' ) ) {
	exit;
}

define( 'PS_EMAILCHEF_DIR', dirname( __FILE__ ) );
define( 'DEBUG', true );

require( PS_EMAILCHEF_DIR . "/lib/vendor/autoload.php" );
require( PS_EMAILCHEF_DIR . "/lib/emailchef/class-emailchef.php" );

class Emailchef extends Module {

	protected $_html = '';
	private $namespace = "ps_emailchef";
	private $emailchef;

	public function __construct() {
		$this->name          = 'emailchef';
		$this->tab           = 'administration';
		$this->version       = '1.0.0.H';
		$this->author        = 'dueclic';
		$this->need_instance = 0;
		$this->bootstrap     = true;
		$this->controllers   = array( 'verification', 'unsubscribe' );

		parent::__construct();

		$this->displayName      = $this->l( 'eMailChef' );
		$this->description      = $this->l( 'Integrazione di eMailChef' );
		$this->confirmUninstall = $this->l( 'Sei sicuro di voler disinstallare questo modulo?' );
		$this->emailchef();
	}

	/**
	 * Get emailchef connection object
	 * @param string|null $api_user
	 * @param string|null $api_pass
	 * @return PS_Emailchef
	 */

	public function emailchef( $api_user = null, $api_pass = null ) {

		if ( empty( $this->emailchef ) || ! is_null( $api_user ) || ! is_null( $api_pass ) ) {

			$api_user        = $api_user ? $api_user : $this->_getConf( "username" );
			$api_pass        = $api_pass ? $api_pass : $this->_getConf( "password" );
			$this->emailchef = new PS_Emailchef( $api_user, $api_pass );

		}

		return $this->emailchef;

	}

	public function install() {
		Configuration::updateValue( 'EC_SALT', Tools::passwdGen( 16 ) );

		return (
			parent::install() &&
			$this->registerHook( 'backOfficeHeader' ) &&
			$this->registerHook( 'actionCustomerAccountAdd' ) &&
			$this->registerHook( 'actionOrderStatusPostUpdate' ) &&
			$this->registerHook( 'actionObjectAddressAddAfter' ) &&
			$this->registerHook( 'actionObjectAddressUpdateAfter' )
		);
	}

	public function uninstall() {

		Configuration::deleteByName( $this->prefix_setting( 'username' ) );
		Configuration::deleteByName( $this->prefix_setting( 'password' ) );
		Configuration::deleteByName( $this->prefix_setting( 'list' ) );
		Configuration::deleteByName( $this->prefix_setting( 'policy_type' ) );

		return (
			parent::uninstall() &&
			$this->unregisterHook( 'backOfficeHeader' ) &&
			$this->unregisterHook( 'actionCustomerAccountAdd' ) &&
			$this->unregisterHook( 'actionOrderStatusPostUpdate' ) &&
			$this->unregisterHook( 'actionObjectAddressAddAfter' ) &&
			$this->unregisterHook( 'actionObjectAddressUpdateAfter' )
		);
	}

	public function getContent() {

		$output = null;

		if ( Tools::isSubmit( 'submit' . $this->name ) ) {
			$ec_username    = strval( Tools::getValue( $this->prefix_setting( 'username' ) ) );
			$ec_password    = strval( Tools::getValue( $this->prefix_setting( 'password' ) ) );
			$ec_list        = intval( Tools::getValue( $this->prefix_setting( 'list' ) ) );
			$ec_policy_type = strval( Tools::getValue( $this->prefix_setting( 'policy_type' ) ) );

			$ec_list_old = $this->_getConf( "list" );

			if ( ! $ec_username || empty( $ec_username ) || ! Validate::isGenericName( $ec_username ) ) {
				$output .= $this->displayError( $this->l( 'Inserisci uno username valido.' ) );
			} else {
				if ( ! $ec_password || empty( $ec_password ) || ! Validate::isGenericName( $ec_password ) ) {
					$output .= $this->displayError( $this->l( 'Inserisci una password valida.' ) );
				} else {
					if ( ! $ec_list || empty( $ec_list ) ) {
						$output .= $this->displayError( $this->l( 'Devi scegliere una lista.' ) );
					} else {
						Configuration::updateValue( $this->prefix_setting( 'username' ), $ec_username );
						Configuration::updateValue( $this->prefix_setting( 'password' ), $ec_password );
						Configuration::updateValue( $this->prefix_setting( 'list' ), $ec_list );
						Configuration::updateValue( $this->prefix_setting( 'policy_type' ), $ec_policy_type );

						$output             .= $this->displayConfirmation( $this->l( 'Impostazioni salvate con successo.' ) );
						$emailchef_cron_url = $this->_path . "/ajax.php";
						$output             .= <<<EOF
<script>
    var emailchef_cron_url = '$emailchef_cron_url';
</script>
EOF;
						if ( $ec_list_old != $ec_list ) {
							$output .= $this->adminDisplayInformation( $this->l( "E' in esecuzione un processo automatico di esportazione dei dati relativi ai tuoi clienti verso eMailChef" ) );

							$this->context->controller->addJs( $this->_path . "js/plugins/emailchef/jquery.emailchef.cron.js" );
						}


					}
				}
			}
		}

		return $output . $this->displayForm();
	}

	/**
	 * @param $config
	 *
	 * @return string
	 */

	public function _getConf( $config ) {
		return Configuration::get( $this->prefix_setting( $config ) );
	}

	public function log( $message, $severity = 1, $debug = false ) {
		if ( $debug ) {
			return PrestaShopLogger::addLog( "[eMailChef Plugin] [Debug]" . $message, $severity, null, null, null, true );
		}

		return PrestaShopLogger::addLog( "[eMailChef Plugin] " . $message, $severity, null, null, null, true );
	}

	private function get_lists() {
		if ( $this->emailchef->isLogged() ) {
			return $this->emailchef->get_lists();
		} else {
			return array(
				array(
					'id'   => - 1,
					'name' => $this->l( 'Accedi per visualizzare le tue liste.' )
				)
			);
		}
	}

	private function get_pages() {

		$pages = CMSCore::getCMSPages();

		return array_map( function ( $page ) {

			return array(
				'id'   => $page['id_cms'],
				'name' => Meta::getCmsMetas( $page['id_cms'], (int) Configuration::get( 'PS_LANG_DEFAULT' ),
					true )['meta_title']
			);

		}, $pages );

	}

	private function prefix_setting( $setting ) {
		return $this->namespace . "_" . $setting;
	}

	public function displayForm() {
		$default_lang           = (int) Configuration::get( 'PS_LANG_DEFAULT' );
		$fields_form[0]['form'] = array(

			'legend' => array(
				'title' => $this->l( 'Impostazioni plugin' ),
			),
			'input'  => array(
				array(
					'type'     => 'text',
					'label'    => $this->l( 'eMailChef username' ),
					'name'     => $this->prefix_setting( 'username' ),
					'required' => true
				),
				array(
					'type'     => 'password',
					'label'    => $this->l( 'eMailChef password' ),
					'name'     => $this->prefix_setting( 'password' ),
					'required' => true
				),
				array(
					'type'     => 'select_and_create',
					'label'    => $this->l( 'Scegli la lista' ),
					'desc'     => $this->l( 'Lista di destinazione' ),
					'name'     => $this->prefix_setting( 'list' ),
					'required' => true,
					'options'  => array(
						'query' => $this->get_lists(),
						'id'    => 'id',
						'name'  => 'name'
					)
				),
				array(
					'type'     => 'select',
					'label'    => $this->l( 'Policy attiva' ),
					'desc'     => $this->l( 'Scegli che tipo di policy vuoi adottare' ),
					'name'     => $this->prefix_setting( 'policy_type' ),
					'hint'     => 'Puoi scegliere tra Double Opt-in e Single Opt-in',
					'required' => false,
					'options'  => array(
						'query' => array(
							array(
								'id'   => 'dopt',
								'name' => $this->l( 'Double opt-in' )
							),
							array(
								'id'   => 'sopt',
								'name' => $this->l( 'Single opt-in' )
							),
						),
						'id'    => 'id',
						'name'  => 'name'
					)
				)
			),
			'submit' => array(
				'title' => $this->l( 'Salva ed esporta i contatti' ),
				'class' => 'btn btn-default pull-right'
			)
		);

		$helper = new HelperForm();

		$helper->module          = $this;
		$helper->name_controller = $this->name;
		$helper->token           = Tools::getAdminTokenLite( 'AdminModules' );

		$helper->currentIndex = AdminController::$currentIndex . '&configure=' . $this->name;

		$this->context->smarty->assign(
			array(
				'create_list_id' => $this->prefix_setting( 'create_list' ),
				'new_name_id'    => $this->prefix_setting( 'new_name' ),
				'new_desc_id'    => $this->prefix_setting( 'new_description' ),
				'save_id'        => $this->prefix_setting( 'save' ),
				'undo_id'        => $this->prefix_setting( 'undo' ),
				'ajax_url'       => $this->_path . "ajax.php",
				'password_field' => $this->prefix_setting( 'password' ),
				'logo_url'       => $this->_path . "js/plugins/emailchef/img/emailchef.png"
			)
		);

		$this->context->smarty->assign( 'i18n', array(
			'create_destination_list'            => $this->l( 'Crea una nuova lista di destinazione' ),
			'create_list'                        => $this->l( 'Crea lista' ),
			'name_list'                          => $this->l( 'Nome lista' ),
			'name_list_placeholder'              => $this->l( 'Inserisci il nome della nuova lista' ),
			'desc_list'                          => $this->l( 'Descrizione lista' ),
			'desc_list_placeholder'              => $this->l( 'Inserisci la descrizione della nuova lista' ),
			'accept_privacy'                     => $this->l( 'Creando una nuova lista certifichi che è conforme alla politica Anti-SPAM e all\' informativa sulla privacy.' ),
			'undo_btn'                           => $this->l( 'Annulla' ),
			'check_login_data'                   => $this->l( 'Controllo dei dati di accesso in corso...' ),
			'error_login_data'                   => $this->l( 'I dati di accesso inseriti sono errati.' ),
			'server_failure_login_data'          => $this->l( 'Errore interno del server, riprova.' ),
			'success_login_data'                 => $this->l( 'Login con eMailChef effettuato con successo.' ),
			'no_list_found'                      => $this->l( 'Nessuna lista trovata.' ),
			'check_status_list_data'             => $this->l( 'Creazione della lista in corso...' ),
			'check_status_list_data_cf'          => $this->l( 'Stiamo creando i custom fields per la lista appena creata...' ),
			'check_status_list_data_cf_change'   => $this->l( 'Stiamo sistemando i custom fields per la lista appena scelta...' ),
			'error_status_list_data'             => $this->l( 'Errore nella creazione della lista indicata.' ),
			'error_status_list_data_cf'          => $this->l( 'Errore nella creazione dei custom fields per la lista creata.' ),
			'error_status_list_data_cf_change'   => $this->l( 'Errore nella sistemazione dei custom fields per la lista scelta.' ),
			'server_error_status_list_data'      => $this->l( 'Errore interno del server, riprova.' ),
			'success_status_list_data'           => $this->l( 'La lista è stata creata con successo, ora verranno creati i custom fields.' ),
			'success_status_list_data_cf'        => $this->l( 'Creazione dei custom fields per la lista avvenuta con successo.' ),
			'success_status_list_data_cf_change' => $this->l( 'Sistemazione dei custom fields per la lista avvenuta con successo.' )
		) );

		$helper->default_form_language    = $default_lang;
		$helper->allow_employee_form_lang = $default_lang;

		$helper->title          = $this->displayName;
		$helper->show_toolbar   = true;
		$helper->toolbar_scroll = true;
		$helper->submit_action  = 'submit' . $this->name;
		$helper->toolbar_btn    = array(
			'save' =>
				array(
					'desc' => $this->l( 'Salva' ),
					'href' => AdminController::$currentIndex . '&configure=' . $this->name . '&save' . $this->name .
					          '&token=' . Tools::getAdminTokenLite( 'AdminModules' ),
				),
			'back' => array(
				'href' => AdminController::$currentIndex . '&token=' . Tools::getAdminTokenLite( 'AdminModules' ),
				'desc' => $this->l( 'Vai indietro' )
			)
		);

		// Load current value
		$helper->fields_value[ $this->prefix_setting( 'username' ) ]    = Configuration::get( $this->prefix_setting( 'username' ) );
		$helper->fields_value[ $this->prefix_setting( 'password' ) ]    = Configuration::get( $this->prefix_setting( 'password' ) );
		$helper->fields_value[ $this->prefix_setting( 'list' ) ]        = Configuration::get( $this->prefix_setting( 'list' ) );
		$helper->fields_value[ $this->prefix_setting( 'policy_type' ) ] = Configuration::get( $this->prefix_setting( 'policy_type' ) );

		return $helper->generateForm( $fields_form );

	}

	public function hookBackOfficeHeader( $arr ) {
		if ( strtolower( Tools::getValue( 'controller' ) ) == 'adminmodules' && Tools::getValue( 'configure' ) == "emailchef" ) {
			$this->context->controller->addJS( $this->_path . 'js/plugins/emailchef/jquery.emailchef.js' );
			$this->context->controller->addCSS( $this->_path . "js/plugins/emailchef/jquery.emailchef.css" );
		}
	}

	/**
	 * Returns a customer email by token
	 *
	 * @param string $token
	 *
	 * @return string email
	 */
	protected function getUserEmailByToken( $token ) {
		$sql = 'SELECT `email`
				FROM `' . _DB_PREFIX_ . 'customer`
				WHERE MD5(CONCAT( `email` , `date_add`, \'' . pSQL( Configuration::get( 'EC_SALT' ) ) . '\')) = \'' . pSQL( $token ) . '\'
				AND `newsletter` = 0';

		return Db::getInstance()->getValue( $sql );
	}


	/**
	 * Ends the registration process to the newsletter
	 *
	 * @param string $token
	 *
	 * @return string
	 */
	public function confirmEmail( $token ) {
		$activated = false;

		if ( $email = $this->getUserEmailByToken( $token ) ) {
			$activated = $this->registerUser( $email );
		}

		if ( ! $activated ) {
			return $this->l( "L' email fornita è già registrata o non valida." );
		}

		return $this->l( 'Grazie per esserti registrato alla nostra newsletter.' );
	}

	/**
	 * Ends the registration process to the newsletter
	 *
	 * @param string $token
	 *
	 * @return string
	 */
	public function unsubEmail( $token ) {

		$deactivated = false;

		if ( $email = $this->getUserEmailByToken( $token ) ) {
			$deactivated = $this->unregisterUser( $email );
		}

		if ( ! $deactivated ) {
			return $this->l( "L' email fornita è già registrata o non valida." );
		}

		return $this->l( 'Ti sei disiscritto con successo dalla nostra newsletter.' );
	}

	/**
	 * Unsubscribe a customer to the newsletter
	 *
	 * @param string $email
	 *
	 * @return bool
	 */
	protected function unregisterUser( $email ) {
		$sql = 'UPDATE ' . _DB_PREFIX_ . 'customer
				SET `newsletter` = 0, newsletter_date_add = NOW(), `ip_registration_newsletter` = \'' . pSQL( Tools::getRemoteAddr() ) . '\'
				WHERE `email` = \'' . pSQL( $email ) . '\'
				AND id_shop = ' . $this->context->shop->id;

		$exec = Db::getInstance()->execute( $sql );

		if ( $exec ) {
			$list_id = $this->_getConf( "list" );

			$customer_id = CustomerCore::customerExists(
				$email,
				true
			);

			$customer = new CustomerCore( $customer_id );

			try {

				$upsert = $this->emailchef()->upsert_customer(
					$list_id,
					array(
						'first_name'  => $customer->firstname,
						'last_name'   => $customer->lastname,
						'user_email'  => $email,
						'newsletter'  => 'no',
						'customer_id' => $customer_id
					)
				);

			} catch ( Exception $e ) {
				$upsert = false;
			}

			if ( $upsert ) {
				$this->log(
					sprintf(
						$this->l( "Disiscrizione nella lista %d del cliente %d (Nome: %s Cognome: %s Email: %s)" ),
						$list_id,
						$customer_id,
						$customer->firstname,
						$customer->lastname,
						$email
					)
				);
			} else {
				$this->log(
					sprintf(
						$this->l( "Disiscrizione non avvenuta nella lista %d del cliente %d (Nome: %s Cognome: %s Email: %s)" ),
						$list_id,
						$customer_id,
						$customer->firstname,
						$customer->lastname,
						$email
					),
					3
				);
			}

		}

		return $exec;

	}

	/**
	 * Subscribe a customer to the newsletter
	 *
	 * @param string $email
	 *
	 * @return bool
	 */
	protected function registerUser( $email ) {
		$sql = 'UPDATE ' . _DB_PREFIX_ . 'customer
				SET `newsletter` = 1, newsletter_date_add = NOW(), `ip_registration_newsletter` = \'' . pSQL( Tools::getRemoteAddr() ) . '\'
				WHERE `email` = \'' . pSQL( $email ) . '\'
				AND id_shop = ' . $this->context->shop->id;

		$exec = Db::getInstance()->execute( $sql );

		if ( $exec ) {
			$list_id = $this->_getConf( "list" );

			$customer_id = CustomerCore::customerExists(
				$email,
				true
			);

			$customer = new CustomerCore( $customer_id );

			try {

				$upsert = $this->emailchef()->upsert_customer(
					$list_id,
					array(
						'first_name'  => $customer->firstname,
						'last_name'   => $customer->lastname,
						'user_email'  => $email,
						'newsletter'  => 'yes',
						'customer_id' => $customer_id
					)
				);

			} catch ( Exception $e ) {
				$upsert = false;
			}

			if ( $upsert ) {
				$this->log(
					sprintf(
						$this->l( "Conferma double opt-in nella lista %d del cliente %d (Nome: %s Cognome: %s Email: %s)" ),
						$list_id,
						$customer_id,
						$customer->firstname,
						$customer->lastname,
						$email
					)
				);
			} else {
				$this->log(
					sprintf(
						$this->l( "Conferma double opt-in non avvenuta nella lista %d del cliente %d (Nome: %s Cognome: %s Email: %s)" ),
						$list_id,
						$customer_id,
						$customer->firstname,
						$customer->lastname,
						$email
					),
					3
				);
			}

		}

		return $exec;

	}

	/**
	 * Send double opt-in
	 *
	 * @param $email
	 * @param $lang_id
	 * @param $shop_id
	 *
	 * @return bool
	 */

	protected function sendDoubleOptIn( $firstname, $email, $lang_id, $shop_id ) {

		$update_sql = 'UPDATE `' . _DB_PREFIX_ . 'customer` SET `newsletter` = 0 WHERE `email` = \'' . pSQL( $email ) . '\'';

		if ( Db::getInstance()->execute( $update_sql ) ) {

			$token_sql = 'SELECT MD5(CONCAT( `email` , `date_add`, \'' . pSQL( Configuration::get( 'EC_SALT' ) ) . '\' )) as token
						FROM `' . _DB_PREFIX_ . 'customer`
						WHERE `newsletter` = 0
						AND `email` = \'' . pSQL( $email ) . '\'';

			$token = Db::getInstance()->getValue( $token_sql );

			$verif_url = Context::getContext()->link->getModuleLink(
				'emailchef', 'verification', array(
					'token' => $token,
				)
			);

			$unsub_url = Context::getContext()->link->getModuleLink(
				'emailchef', 'unsubscribe', array(
					'token' => $token,
				)
			);

			$template_vars = array(
				'{verif_url}'     => $verif_url,
				'{unsub_url}'     => $unsub_url,
				'{customer_name}' => $firstname
			);

			return Mail::Send( $lang_id, 'newsletter_verif_emailchef',
				Mail::l( 'Verifica email per inserimento nella mailing list', $lang_id ), $template_vars, $email, null,
				null, null, null, null, dirname( __FILE__ ) . '/mails/', false, $shop_id );

		}

	}

	/*
	 * Deprecated since 1.0.0.E

	public function hookActionCartSave() {

		$customer = $this->context->customer;

		if ( $this->emailchef()->isLogged() && $customer->isLogged() && $this->context->cart !== null ) {

			$list_id = $this->_getConf( "list" );

			require_once( dirname( __FILE__ ) . "/lib/emailchef/class-emailchef-sync.php" );
			$sync = new PS_Emailchef_Sync();


			$higher_product = $sync->getHigherProductCart(
				$this->context->cart
			);

			$syncCartSave = array(
				'first_name' => $customer->firstname,
				'last_name'  => $customer->lastname,
				'user_email' => $customer->email
			);

			$syncCartSave = array_merge( $syncCartSave, $higher_product );

			$upsert = $this->emailchef()->upsert_customer(
				$list_id,
				$syncCartSave
			);

			if ( $upsert ) {
				$this->log(
					sprintf(
						$this->l( "Inserito nella lista %d il prodotto con prezzo più alto nel carrello di %d (Nome: %s Cognome: %s Email: %s)" ),
						$list_id,
						$customer->id,
						$customer->firstname,
						$customer->lastname,
						$customer->email
					)
				);
			} else {
				$this->log(
					sprintf(
						$this->l( "Inserimento non riuscito del prodotto con prezzo più alto nel carrello nella lista %d da parte di %d (Nome: %s Cognome: %s Email: %s)" ),
						$list_id,
						$customer->id,
						$customer->firstname,
						$customer->lastname,
						$customer->email
					),
					3
				);
			}

		}

	}

	*/

	public function hookActionCustomerAccountAdd( $params ) {
		if ( $this->emailchef()->isLogged() ) {

			$customer   = $this->context->customer;
			$newsletter = 'no';
			$list_id    = $this->_getConf( "list" );

			if ( $customer->newsletter == 1 && $this->_getConf( "policy_type" ) == "dopt" ) {
				$this->sendDoubleOptIn(
					$customer->firstname,
					$customer->email,
					$this->context->language->id,
					$this->context->shop->id
				);
				$newsletter = 'pending';
			}

			if ( $customer->newsletter == 1 && $this->_getConf( "policy_type" ) == "sopt" ) {
				$newsletter = 'yes';
			}

			require_once( dirname( __FILE__ ) . "/lib/emailchef/class-emailchef-sync.php" );

			$sync                    = new PS_Emailchef_Sync();
			$syncCustomerAccountData = $sync->getSyncCustomerAccountAdd( $customer, $newsletter );

			$upsert = $this->emailchef()->upsert_customer(
				$list_id,
				$syncCustomerAccountData
			);

			if ( $upsert ) {
				$this->log(
					sprintf(
						$this->l( "Inserito nella lista %d il cliente %d (Nome: %s Cognome: %s Email: %s Consenso Newsletter: %s)" ),
						$list_id,
						$customer->id,
						$customer->firstname,
						$customer->lastname,
						$customer->email,
						$newsletter
					)
				);
			} else {
				$this->log(
					sprintf(
						$this->l( "Inserimento nella lista %d del cliente %d (Nome: %s Cognome: %s Email: %s Consenso Newsletter: %s) non avvenuto" ),
						$list_id,
						$customer->id,
						$customer->firstname,
						$customer->lastname,
						$customer->email,
						$newsletter
					),
					3
				);
			}

		}
	}

	public function hookActionObjectAddressAddAfter( $params ) {
		return $this->update_customer( $params['object'] );
	}

	public function hookActionObjectAddressUpdateAfter( $params ) {
		return $this->update_customer( $params['object'], "edit" );
	}

	private function update_customer( $object, $action = 'add' ) {

		if ( $this->emailchef()->isLogged() ) {

			/**
			 * @var AddressCore $address
			 */

			$address = $object;
			$list_id = $this->_getConf( "list" );

			require_once( dirname( __FILE__ ) . "/lib/emailchef/class-emailchef-sync.php" );

			$sync = new PS_Emailchef_Sync();

			$syncAddressData = $sync->getSyncUpdateCustomerAddress( $address );

			/**
			 * Dinamica carrello abbandonato
			 */

			$higher_product_data = $sync->getHigherProductCart(
				$this->context->cart
			);

			$syncAddressData = array_merge( $syncAddressData, $higher_product_data );

			/**
			 * Fine dinamica carrello abbandonato
			 */

			$upsert = $this->emailchef()->upsert_customer(
				$list_id,
				$syncAddressData
			);

			if ( $upsert ) {
				$this->log(
					sprintf(
						$this->l( "Aggiornati nella lista %d i campi del cliente %d (Nome: %s Cognome %s e altri %d campi)" ),
						$list_id,
						$address->id_customer,
						$address->firstname,
						$address->lastname,
						intval( count( $syncAddressData ) - 2 )
					)
				);
			} else {
				$this->log(
					sprintf(
						$this->l( "I campi del cliente %d (Nome: %s Cognome %s e altri %d campi) nella lista %d non sono stati aggiornati" ),
						$address->id_customer,
						$address->firstname,
						$address->lastname,
						intval( count( $syncAddressData ) - 2 ),
						$list_id
					),
					3
				);
			}

		}

	}

	public function hookActionOrderStatusPostUpdate( $params ) {
		require_once( dirname( __FILE__ ) . "/lib/emailchef/class-emailchef-sync.php" );

		$list_id = $this->_getConf( "list" );

		$ecps = $this->emailchef();

		if ( $ecps->isLogged() ) {

			$sync          = new PS_Emailchef_Sync();
			$syncOrderData = $sync->getSyncOrderData(
				$params['id_order'],
				$params['newOrderStatus']
			);

			$syncOrderData = array_merge(
				$syncOrderData,
				$sync->getHigherProductAbandonedCartOrEmpty( $syncOrderData['customer_id'] )
			);

			$upsert = $ecps->upsert_customer(
				$list_id,
				$syncOrderData
			);

			if ( $upsert ) {
				$this->log(
					sprintf(
						$this->l( "Inserito nella lista %d i dati aggiornati del cliente %d (Nome: %s Cognome: %s e altri %d campi)" ),
						$list_id,
						$syncOrderData['customer_id'],
						$syncOrderData['first_name'],
						$syncOrderData['last_name'],
						intval( count( $syncOrderData ) - 2 )
					)
				);
			} else {
				$this->log(
					sprintf(
						$this->l( "Inserimento nella lista %d dei dati aggiornati del cliente %d (Nome: %s Cognome: %s e altri %d campi) non avvenuto (Errore: %s)" ),
						$list_id,
						$syncOrderData['customer_id'],
						$syncOrderData['first_name'],
						$syncOrderData['last_name'],
						intval( count( $syncOrderData ) - 2 ),
						$ecps->lastError
					),
					3
				);
			}

		}

	}

}