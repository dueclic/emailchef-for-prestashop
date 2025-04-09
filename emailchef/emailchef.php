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

if (!defined('_CAN_LOAD_FILES_')) {
    exit;
}

if (!defined('_PS_VERSION_')) {
    exit;
}

define('PS_EMAILCHEF_DIR', dirname(__FILE__));
define('DEBUG', true);

require(PS_EMAILCHEF_DIR . "/lib/vendor/autoload.php");
require(PS_EMAILCHEF_DIR . "/lib/emailchef/class-emailchef.php");

class Emailchef extends Module
{

    protected $_html = '';
    private $namespace = "ps_emailchef";

    /**
     * @var PS_Emailchef $emailchef | null
     */
    private $emailchef;
    private $category_table;
    private $newsletter_before = 0;

    public function __construct()
    {
        $this->name = 'emailchef';
        $this->tab = 'emailing';
        $this->version = '2.0.1';
        $this->author = 'dueclic';
        $this->need_instance = 0;
        $this->bootstrap = true;
        $this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_);
        $this->controllers = array('verification', 'unsubscribe');

        parent::__construct();

        $this->category_table = _DB_PREFIX_ . "emailchef_abcart_synced";
        $this->displayName = $this->l('Emailchef');
        $this->description = 'This PrestaShop module enables seamless integration with Emailchef, allowing your online store to effortlessly run simple, automated, and targeted marketing campaigns.';
        $this->confirmUninstall = $this->l('Do you really want to uninstall this module?');
        $this->emailchef();
    }

    /**
     * Get emailchef connection object
     *
     * @param string|null $consumer_key
     * @param string|null $consumer_secret
     * @param bool $force
     *
     * @return PS_Emailchef
     */

    public function emailchef($consumer_key = null, $consumer_secret = null, $force = false)
    {

        if (empty($this->emailchef) || (!is_null($consumer_key) && !is_null($consumer_secret)) || $force) {

            $consumer_key = $consumer_key ? $consumer_key : $this->_getConf("consumer_key");
            $consumer_secret = $consumer_secret ? $consumer_secret : $this->_getConf("consumer_secret");

            $this->emailchef = new PS_Emailchef(
                $consumer_key,
                $consumer_secret,
                $this->namespace,
                $this->_getConf("enabled")
            );

        }

        return $this->emailchef;

    }

    private function create_emailchef_tables()
    {
        $create_table_sql = 'CREATE TABLE IF NOT EXISTS `' . $this->category_table . '` (
        `id_cart` INT UNSIGNED NOT NULL,
        `date_synced` DATETIME NOT NULL,
        UNIQUE (`id_cart`)
        ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci ;';

        return Db::getInstance()->execute($create_table_sql);
    }

    private function drop_emailchef_tables()
    {
        $drop_table_sql = 'DROP TABLE IF EXISTS `' . $this->category_table . '`';

        return Db::getInstance()->execute($drop_table_sql);
    }

    public function runUpgradeModule()
    {
        if ($this->emailchef()->isLogged()) {
            $list_id = $this->_getConf("list");
            $this->emailchef()->upsert_integration($list_id);
        }
        return parent::runUpgradeModule();
    }

    public function enable($forceAll = false)
    {
        if ($this->emailchef()->isLogged()) {
            $list_id = $this->_getConf("list");
            $this->emailchef()->upsert_integration($list_id);
        }
        return parent::enable($forceAll);
    }

    public function install()
    {
        Configuration::updateValue('EC_SALT', Tools::passwdGen(16));

        return (
            parent::install() &&
            $this->registerHook('backOfficeHeader') &&
            $this->registerHook('actionCustomerAccountAdd') &&
            $this->registerHook('actionObjectCustomerUpdateBefore') &&
            $this->registerHook('actionObjectCustomerUpdateAfter') &&
            $this->registerHook('actionOrderStatusPostUpdate') &&
            $this->registerHook('actionObjectAddressAddAfter') &&
            $this->registerHook('actionObjectAddressUpdateAfter') &&
            $this->registerHook('actionObjectLanguageAddAfter') &&
            $this->registerHook('actionObjectLanguageUpdateAfter') &&
            $this->registerHook('actionObjectLanguageDeleteAfter') &&
            $this->registerHook('backOfficeFooter') &&
            $this->registerHook('footer') &&
            $this->create_emailchef_tables()
        );
    }

    public function deleteConfigurations()
    {
        Configuration::deleteByName($this->prefix_setting('consumer_key'));
        Configuration::deleteByName($this->prefix_setting('consumer_secret'));
        Configuration::deleteByName($this->prefix_setting('list'));
        Configuration::deleteByName($this->prefix_setting('policy_type'));
        Configuration::deleteByName($this->prefix_setting('enabled'));
    }

    public function uninstall()
    {

        $this->deleteConfigurations();

        return (
            parent::uninstall() &&
            $this->unregisterHook('backOfficeHeader') &&
            $this->unregisterHook('actionCustomerAccountAdd') &&
            $this->unregisterHook('actionObjectCustomerUpdateBefore') &&
            $this->unregisterHook('actionObjectCustomerUpdateAfter') &&
            $this->unregisterHook('actionOrderStatusPostUpdate') &&
            $this->unregisterHook('actionObjectAddressAddAfter') &&
            $this->unregisterHook('actionObjectAddressUpdateAfter') &&
            $this->unregisterHook('actionObjectLanguageAddAfter') &&
            $this->unregisterHook('actionObjectLanguageUpdateAfter') &&
            $this->unregisterHook('actionObjectLanguageDeleteAfter') &&
            $this->unregisterHook('backOfficeFooter') &&
            $this->unregisterHook('footer') &&
            $this->drop_emailchef_tables()
        );
    }

    public function getContent()
    {

        $error = null;
        $account = null;
        $manual_sync = false;

        if (
            Tools::isSubmit('submitEmailchefLogin')
        ) {

            $consumer_key = strval(Tools::getValue('consumer_key'));
            $consumer_secret = strval(Tools::getValue('consumer_secret'));

            $emailchef = $this->emailchef($consumer_key, $consumer_secret, true);

            $account = $emailchef->get_account();
            if (isset($account['status']) && $account['status'] === 'error') {
                Configuration::updateValue($this->prefix_setting('consumer_key'), '');
                Configuration::updateValue($this->prefix_setting('consumer_secret'), '');
                Configuration::updateValue($this->prefix_setting('enabled'), false);
                $error = $this->l('API keys are not valid');
            } else {
                Configuration::updateValue($this->prefix_setting('consumer_key'), $consumer_key);
                Configuration::updateValue($this->prefix_setting('consumer_secret'), $consumer_secret);
                Configuration::updateValue($this->prefix_setting('enabled'), true);
            }

        } else if (
            Tools::isSubmit('submitEmailchefSettings')
        ) {

            $list_id = Tools::getValue('list_id');

            if (empty($list_id)) {
                $error = $this->l('You must select a valid list.');
            } else {
                $manual_sync = boolval(Tools::getValue('sync_customers'));
                Configuration::updateValue($this->prefix_setting('list'), $list_id);
                $policy_type = Tools::getValue('policy_type');

                if (!empty($policy_type)) {
                    Configuration::updateValue($this->prefix_setting('policy_type'), $list_id);
                }

            }

        }

        $data = [
            'error' => $error
        ];

        $policy_types = [
            'sopt' => $this->l("Single opt-in"),
            'dopt' => $this->l("Double opt-in")
        ];


        $is_enabled = $this->_getConf('enabled', false);

        if ($is_enabled) {

            $policy = $this->emailchef->get_policy();
            $account = $this->emailchef->get_account();

            if ($policy !== 'premium') {
                unset($policy_types['sopt']);
            }

            $data['account'] = $account;
            $data['policy_types'] = $policy_types;
            $data['list_id'] = $this->_getConf('list', null);
            $data['policy_type'] = $this->_getConf('policy_type', null);
            $data['lists'] = $this->emailchef->get_lists();
            $data['ajax_url'] = $this->_path . "ajax.php";
            $filtered_lists = array_values(
                array_filter($data['lists'], function ($_list) use ($data) {
                    return (int)$_list['id'] === (int)$data['list_id'];
                })
            );
            $data['manualSync'] = $manual_sync;
            $data['list_name'] = count($filtered_lists) > 0 ? $filtered_lists[0]['name'] : '';
            $data['admin_logs_link'] = Context::getContext()->link->getAdminLink('AdminLogs');
            $data['i18n'] = array(
                'create_destination_list' => $this->l('Add a new destination list'),
                'language_set' => $this->l('The language has been loaded. Do you want to refresh the page with the new language?'),
                'create_list' => $this->l('Add a new list'),
                'name_list' => $this->l('List name'),
                'name_list_placeholder' => $this->l('Provide a name for this new list'),
                'desc_list' => $this->l('List description'),
                'desc_list_placeholder' => $this->l('Write a description for this list'),
                'accept_privacy' => $this->l('By creating a new list, you confirm its compliance with the privacy policy and the CAN-SPAM Act.'),
                'undo_btn' => $this->l('Cancel'),
                'check_login_data' => $this->l('Verifying your login data.'),
                'error_login_data' => $this->l('Incorrect login credentials.'),
                'server_failure_login_data' => $this->l('Internal server error. Please try again.'),
                'success_login_data' => $this->l('You have successfully logged into Emailchef.'),
                'no_list_found' => $this->l('No list found.'),
                'are_you_sure_disconnect' => $this->l('Are you sure you want to disconnect your account?'),
                'check_status_list_data' => $this->l('Creating a new list, please wait...'),
                'check_status_list_data_cf' => $this->l('We are creating custom fields for the newly created list...'),
                'check_status_list_data_cf_change' => $this->l('We are adjusting custom fields for the newly selected list...'),
                'error_status_list_data' => $this->l('An error occurred while creating this list.'),
                'error_status_list_data_cf' => $this->l('An error occurred while defining custom fields for this newly created list.'),
                'error_status_list_data_cf_change' => $this->l('An error occurred while modifying custom fields for the chosen list.'),
                'server_error_status_list_data' => $this->l('Internal server error. Please try again.'),
                'success_status_list_data' => $this->l('Your list has been created. We’re now adding the custom fields.'),
                'success_status_list_data_cf' => $this->l('Custom fields for this list have been successfully created.'),
                'success_status_list_data_cf_change' => $this->l('Custom fields for this list have been successfully modified.')
            );
        }

        $this->context->smarty->assign(
            $data
        );

        return $this->display(__FILE__, 'views/templates/admin/logged-' . ($is_enabled ? 'in' : 'out') . '.tpl');

    }

    private function sync_abandoned_cart()
    {

        $output = "";

        if ($this->emailchef()->isLogged()) {

            require_once(dirname(__FILE__) . "/lib/emailchef/class-emailchef-sync.php");
            $sync = new PS_Emailchef_Sync();

            $abandoned_carts = $sync->getAbandonedCarts();

            if (count($abandoned_carts) > 0) {

                $emailchef_abandoned_url = $this->_path . "ajax.php";
                $output .= <<<EOF
				<script>
				    var emailchef_abandoned_url = '$emailchef_abandoned_url';
				</script>
EOF;
                $output .= '<script type="text/javascript" src="' . $this->_path . 'js/plugins/emailchef/jquery.emailchef.abandoned.js"></script>';
            }


        }

        return $output;

    }

    public function hookBackOfficeFooter()
    {
        return $this->sync_abandoned_cart();
    }

    public function hookFooter()
    {
        return $this->sync_abandoned_cart();
    }

    /**
     * @param $config
     * @param bool $default
     *
     * @return string
     */

    public function _getConf($config, $default = false)
    {
        return Configuration::get(
            $this->prefix_setting($config),
            null,
            null,
            null,
            $default
        );
    }

    public function log($message, $severity = 1, $debug = false)
    {
        if ($debug) {
            return PrestaShopLogger::addLog("[Emailchef Plugin] [Debug]" . $message, $severity, null, null, null,
                true);
        }

        return PrestaShopLogger::addLog("[Emailchef Plugin] " . $message, $severity, null, null, null, true);
    }

    private function get_lists()
    {
        if ($this->emailchef->isLogged()) {
            return $this->emailchef->get_lists();
        } else {
            return array(
                array(
                    'id' => -1,
                    'name' => $this->l('Log in to manage your lists.')
                )
            );
        }
    }

    public function prefix_setting($setting)
    {
        return $this->namespace . "_" . $setting;
    }

    public function hookBackOfficeHeader($arr)
    {
        if (strtolower(Tools::getValue('controller')) == 'adminmodules' && Tools::getValue('configure') == "emailchef") {
            $this->context->controller->addCSS($this->_path . "views/css/emailchef.min.css");
            $this->context->controller->addJS($this->_path . "views/bundle/emailchef/emailchef.min.js");
        }
    }

    /**
     * Returns a customer email by token
     *
     * @param string $token
     *
     * @return string email
     */
    protected function getUserEmailByToken($token)
    {
        $sql = 'SELECT `email`
				FROM `' . _DB_PREFIX_ . 'customer`
				WHERE MD5(CONCAT( `email` , `date_add`, \'' . pSQL(Configuration::get('EC_SALT')) . '\')) = \'' . pSQL($token) . '\'
				AND `newsletter` = 0';

        return Db::getInstance()->getValue($sql);
    }


    /**
     * Ends the registration process to the newsletter
     *
     * @param string $token
     *
     * @return string
     */
    public function confirmEmail($token)
    {
        $activated = false;

        if ($email = $this->getUserEmailByToken($token)) {
            $activated = $this->registerUser($email);
        }

        if (!$activated) {
            return $this->l("Provided email address is either already in use or invalid.");
        }

        return $this->l('Thank you for subscribing to our newsletter!');
    }

    /**
     * Ends the registration process to the newsletter
     *
     * @param string $token
     *
     * @return string
     */
    public function unsubEmail($token)
    {

        $deactivated = false;

        if ($email = $this->getUserEmailByToken($token)) {
            $deactivated = $this->unregisterUser($email);
        }

        if (!$deactivated) {
            return $this->l("Provided email address is either already in use or invalid.");
        }

        return $this->l('You have successfully unsubscribed from our newsletter. ');
    }

    /**
     * Unsubscribe a customer to the newsletter
     *
     * @param string $email
     *
     * @return bool
     */
    protected function unregisterUser($email)
    {
        $sql = 'UPDATE ' . _DB_PREFIX_ . 'customer
				SET `newsletter` = 0, newsletter_date_add = NOW(), `ip_registration_newsletter` = \'' . pSQL(Tools::getRemoteAddr()) . '\'
				WHERE `email` = \'' . pSQL($email) . '\'
				AND id_shop = ' . $this->context->shop->id;

        $exec = Db::getInstance()->execute($sql);

        if ($exec) {
            $list_id = $this->_getConf("list");

            $customer_id = CustomerCore::customerExists(
                $email,
                true
            );

            $customer = new CustomerCore($customer_id);

            try {

                $upsert = $this->emailchef()->upsert_customer(
                    $list_id,
                    array(
                        'first_name' => $customer->firstname,
                        'last_name' => $customer->lastname,
                        'user_email' => $email,
                        'newsletter' => 'no',
                        'customer_id' => $customer_id
                    )
                );

            } catch (Exception $e) {
                $upsert = false;
            }

            if ($upsert) {
                $this->log(
                    sprintf(
                        $this->l("Unsubscribe from customer %d list %d (Name: %s Surname: %s Email: %s)"),
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
                        $this->l("Failed unsubscribe from customer %d list %d (Name: %s Surname: %s Email: %s)"),
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
    protected function registerUser($email)
    {
        $sql = 'UPDATE ' . _DB_PREFIX_ . 'customer
				SET `newsletter` = 1, newsletter_date_add = NOW(), `ip_registration_newsletter` = \'' . pSQL(Tools::getRemoteAddr()) . '\'
				WHERE `email` = \'' . pSQL($email) . '\'
				AND id_shop = ' . $this->context->shop->id;

        $exec = Db::getInstance()->execute($sql);

        if ($exec) {
            $list_id = $this->_getConf("list");

            $customer_id = CustomerCore::customerExists(
                $email,
                true
            );

            $customer = new CustomerCore($customer_id);

            try {

                $upsert = $this->emailchef()->upsert_customer(
                    $list_id,
                    array(
                        'first_name' => $customer->firstname,
                        'last_name' => $customer->lastname,
                        'user_email' => $email,
                        'newsletter' => 'yes',
                        'customer_id' => $customer_id
                    )
                );

            } catch (Exception $e) {
                $upsert = false;
            }

            if ($upsert) {
                $this->log(
                    sprintf(
                        $this->l("Double opt-in confirmation from customer %d list %d ((Name: %s Surname: %s Email: %s)"),
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
                        $this->l("Failed double opt-in confirmation from client %d list %d (Name: %s Surname: %s Email: %s)"),
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

    protected function sendDoubleOptIn($firstname, $email, $lang_id, $shop_id)
    {

        $update_sql = 'UPDATE `' . _DB_PREFIX_ . 'customer` SET `newsletter` = 0 WHERE `email` = \'' . pSQL($email) . '\'';

        if (Db::getInstance()->execute($update_sql)) {

            $token_sql = 'SELECT MD5(CONCAT( `email` , `date_add`, \'' . pSQL(Configuration::get('EC_SALT')) . '\' )) as token
						FROM `' . _DB_PREFIX_ . 'customer`
						WHERE `newsletter` = 0
						AND `email` = \'' . pSQL($email) . '\'';

            $token = Db::getInstance()->getValue($token_sql);

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
                '{verif_url}' => $verif_url,
                '{unsub_url}' => $unsub_url,
                '{customer_name}' => $firstname
            );

            return Mail::Send($lang_id, 'newsletter_verif_emailchef',
                Mail::l('Verify email for mailing list subscription', $lang_id), $template_vars, $email, null,
                null, null, null, null, dirname(__FILE__) . '/mails/', false, $shop_id);

        }

    }

    public function hookActionCustomerAccountAdd($params)
    {
        if ($this->emailchef()->isLogged()) {

            $customer = $this->context->customer;
            $newsletter = 'no';
            $list_id = $this->_getConf("list");

            if ($customer->newsletter == 1 && $this->_getConf("policy_type") == "dopt") {
                $this->sendDoubleOptIn(
                    $customer->firstname,
                    $customer->email,
                    $this->context->language->id,
                    $this->context->shop->id
                );
                $newsletter = 'pending';
            }

            if ($customer->newsletter == 1 && $this->_getConf("policy_type") == "sopt") {
                $newsletter = 'yes';
            }

            require_once(dirname(__FILE__) . "/lib/emailchef/class-emailchef-sync.php");

            $sync = new PS_Emailchef_Sync();
            $syncCustomerAccountData = $sync->getSyncCustomerAccountAdd($customer, $newsletter);

            $upsert = $this->emailchef()->upsert_customer(
                $list_id,
                $syncCustomerAccountData
            );

            if ($upsert) {
                $this->log(
                    sprintf(
                        $this->l("Added to customer %d list %d (Name: %s Surname: %s Email: %s Newsletter opt-in: %s)"),
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
                        $this->l("Failed to add to customer %d list %d (Name: %s Surname: %s Newsletter opt-in: %s)"),
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

    public function HookActionObjectCustomerUpdateBefore($params)
    {
        if ($this->emailchef()->isLogged()) {

            /**
             * @var Customer $customer
             */

            $customerob = $params['object'];
            $customer = new Customer($customerob->id);

            $this->newsletter_before = (int)$customer->newsletter;
        }
    }

    public function HookActionObjectCustomerUpdateAfter($params)
    {
        return $this->update_info_customer($params['object']);
    }

    public function hookActionObjectAddressAddAfter($params)
    {
        return $this->update_customer($params['object']);
    }

    public function hookActionObjectAddressUpdateAfter($params)
    {
        return $this->update_customer($params['object']);
    }

    public function hookActionObjectLanguageAddAfter($params)
    {
        $this->update_language_field($params['object'], "add");
    }

    public function hookActionObjectLanguageUpdateAfter($params)
    {
        $this->update_language_field($params['object'], "update");
    }

    public function hookActionObjectLanguageDeleteAfter($params)
    {
        $this->update_language_field($params['object'], "delete");
    }

    /**
     * Update Language Field
     *
     * @param \LanguageCore $language
     * @param $action
     */

    private function update_language_field($language, $action = "update")
    {

        $psec = $this->emailchef();

        if ($psec->isLogged()) {

            $list_id = $this->_getConf("list");

            $custom_fields = require(PS_EMAILCHEF_DIR . "/conf/custom_fields.php");
            $custom_field = $custom_fields['lang'];

            $type = $custom_field['data_type'];
            $name = $custom_field['name'];
            $options = (isset($custom_field['options']) ? $custom_field['options'] : array());
            $default_value = (isset($custom_field['default_value']) ? $custom_field['default_value'] : "");
            $iso_code = $language->iso_code;

            $new_options = array();

            if ($action == "delete") {
                foreach ($options as $option) {
                    if ($option['text'] == $iso_code) {
                        continue;
                    }
                    $new_options[] = $option;
                }
            } else if ($action == "update") {
                $not_found = true;
                foreach ($options as $option) {
                    if ($option['text'] == $iso_code) {
                        $not_found = false;
                    }
                    $new_options[] = $option;
                }

                if ($not_found) {
                    $new_options[] = array(
                        'text' => $iso_code
                    );
                }
            } else {
                $new_options = $options;
                $new_options[] = array(
                    'text' => $iso_code
                );
            }

            $init = $psec->update_custom_field($list_id, $type, $name, 'lang', $new_options, $default_value);

            if ($init) {
                $this->log(
                    sprintf(
                        $this->l("Language custom fields for list %d updated."),
                        $list_id
                    )
                );
            } else {
                $this->log(
                    sprintf(
                        $this->l("update language custom fields for list %d has failed.  (Error: %s)"),
                        $list_id,
                        $psec->lastError
                    ),
                    3
                );
            }

        }
    }

    private function update_customer($object)
    {

        if ($this->emailchef()->isLogged()) {

            /**
             * @var AddressCore $address
             */

            $address = $object;
            $list_id = $this->_getConf("list");

            require_once(dirname(__FILE__) . "/lib/emailchef/class-emailchef-sync.php");

            $sync = new PS_Emailchef_Sync();

            $syncAddressData = $sync->getSyncUpdateCustomerAddress($address);

            $upsert = $this->emailchef()->upsert_customer(
                $list_id,
                $syncAddressData
            );

            if ($upsert) {
                $this->log(
                    sprintf(
                        $this->l("Updated customer %d data in list %d (Name: %s Surname: %s and other %d fields)"),
                        $list_id,
                        $address->id_customer,
                        $address->firstname,
                        $address->lastname,
                        intval(count($syncAddressData) - 2)
                    )
                );
            } else {
                $this->log(
                    sprintf(
                        $this->l("Failed to insert updated customer %d data in list %d (Name: %s Surname: %s and other %d fields)"),
                        $address->id_customer,
                        $address->firstname,
                        $address->lastname,
                        intval(count($syncAddressData) - 2),
                        $list_id
                    ),
                    3
                );
            }

        }

    }

    private function update_info_customer($object)
    {

        if ($this->emailchef()->isLogged()) {

            /**
             * @var Customer $customer
             */

            $customer = $object;
            $list_id = $this->_getConf("list");

            require_once(dirname(__FILE__) . "/lib/emailchef/class-emailchef-sync.php");

            $sync = new PS_Emailchef_Sync();

            $syncCustomerInfo = $sync->getSyncUpdateCustomerInfo($customer);

            if ($customer->newsletter == 1 && $this->_getConf("policy_type") == "dopt" && $this->newsletter_before == 0) {
                $this->sendDoubleOptIn(
                    $customer->firstname,
                    $customer->email,
                    $this->context->language->id,
                    $this->context->shop->id
                );
                $syncCustomerInfo['newsletter'] = 'pending';
            }

            if ($customer->newsletter == 1 && $this->_getConf("policy_type") == "sopt") {
                $syncCustomerInfo['newsletter'] = 'yes';
            }

            $upsert = $this->emailchef()->upsert_customer(
                $list_id,
                $syncCustomerInfo
            );

            if ($upsert) {
                $this->log(
                    sprintf(
                        $this->l("Updated customer %d data in list %d (Name: %s Surname: %s and other %d fields)"),
                        $list_id,
                        $customer->id,
                        $customer->firstname,
                        $customer->lastname,
                        intval(count($syncCustomerInfo) - 2)
                    )
                );
            } else {
                $this->log(
                    sprintf(
                        $this->l("Failed to insert updated customer %d data in list %d (Name: %s Surname: %s and other %d fields)"),
                        $customer->id,
                        $customer->firstname,
                        $customer->lastname,
                        intval(count($syncCustomerInfo) - 2),
                        $list_id
                    ),
                    3
                );
            }

        }

    }

    public function hookActionOrderStatusPostUpdate($params)
    {
        require_once(dirname(__FILE__) . "/lib/emailchef/class-emailchef-sync.php");

        $list_id = $this->_getConf("list");

        $ecps = $this->emailchef();

        if ($ecps->isLogged()) {

            $sync = new PS_Emailchef_Sync();
            $syncOrderData = $sync->getSyncOrderData(
                $params['id_order'],
                $params['newOrderStatus']
            );

            $syncOrderData = array_merge(
                $syncOrderData,
                $sync->flushAbandonedCarts($syncOrderData['customer_id'])
            );

            $upsert = $ecps->upsert_customer(
                $list_id,
                $syncOrderData
            );

            if ($upsert) {
                $this->log(
                    sprintf(
                        $this->l("Updated customer %d data in list %d (Name: %s Surname: %s and other %d fields)"),
                        $list_id,
                        $syncOrderData['customer_id'],
                        $syncOrderData['first_name'],
                        $syncOrderData['last_name'],
                        intval(count($syncOrderData) - 2)
                    )
                );
            } else {
                $this->log(
                    sprintf(
                        $this->l("Failed to insert updated customer %d data in list %d (Name: %s Surname: %s and other %d fields) (Error: %s)"),
                        $list_id,
                        $syncOrderData['customer_id'],
                        $syncOrderData['first_name'],
                        $syncOrderData['last_name'],
                        intval(count($syncOrderData) - 2),
                        $ecps->lastError
                    ),
                    3
                );
            }

        }

    }

}
