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

require(PS_EMAILCHEF_DIR . "/lib/vendor/autoload.php");
require(PS_EMAILCHEF_DIR . "/lib/emailchef/class-emailchef.php");

class Emailchef extends Module
{

    protected $_html = '';
    private $namespace = "ps_emailchef";
    private $emailchef;

    public function __construct()
    {
        $this->name = 'emailchef';
        $this->tab = 'administration';
        $this->version = '1.0.0';
        $this->author = 'dueclic';
        $this->need_instance = 0;
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('eMailChef');
        $this->description = $this->l('Integrazione di eMailChef');
        $this->confirmUninstall = $this->l('Sei sicuro di voler disinstallare questo modulo?');
        $this->emailchef = new PS_Emailchef($this->_getConf("username"), $this->_getConf("password"));

    }

    public function install()
    {
        return parent::install() && $this->registerHook('backOfficeHeader');
    }

    public function uninstall()
    {
        return parent::uninstall() && $this->unregisterHook('backOfficeHeader');
    }

    public function getContent()
    {

        $output = null;

        if (Tools::isSubmit('submit' . $this->name)) {
            $ec_username = strval(Tools::getValue($this->prefix_setting('username')));
            $ec_password = strval(Tools::getValue($this->prefix_setting('password')));
            $ec_list = strval(Tools::getValue($this->prefix_setting('list')));
            $ec_policy_type = strval(Tools::getValue($this->prefix_setting('policy_type')));

            if (!$ec_username || empty($ec_username) || !Validate::isGenericName($ec_username)) {
                $output .= $this->displayError($this->l('Inserisci uno username valido.'));
            } else {
                if (!$ec_password || empty($ec_password) || !Validate::isGenericName($ec_password)) {
                    $output .= $this->displayError($this->l('Inserisci una password valida.'));
                } else {
                    if (!$ec_list || empty($ec_list)) {
                        $output .= $this->displayError($this->l('Devi scegliere una lista.'));
                    } else {
                        Configuration::updateValue($this->prefix_setting('username'), $ec_username);
                        Configuration::updateValue($this->prefix_setting('password'), $ec_password);
                        Configuration::updateValue($this->prefix_setting('list'), $ec_list);
                        Configuration::updateValue($this->prefix_setting('policy_type'), $ec_policy_type);

                        $output .= $this->displayConfirmation($this->l('Impostazioni salvate con successo.'));
                    }
                }
            }
        }
        return $output . $this->displayForm();
    }

    /**
     * @param $config
     * @return string
     */

    private function _getConf($config)
    {
        return Configuration::get($this->prefix_setting($config));
    }

    private
    function get_lists()
    {

    }

    private function prefix_setting($setting)
    {
        return $this->namespace . "_" . $setting;
    }

    public
    function displayForm()
    {
        $default_lang = (int)Configuration::get('PS_LANG_DEFAULT');
        $fields_form[0]['form'] = array(

            'legend' => array(
                'title' => $this->l('Impostazioni plugin')
            ),
            'input'  => array(
                array(
                    'type'     => 'text',
                    'label'    => $this->l('eMailChef username'),
                    'name'     => $this->prefix_setting('username'),
                    'required' => true
                ),
                array(
                    'type'     => 'password',
                    'label'    => $this->l('eMailChef password'),
                    'name'     => $this->prefix_setting('password'),
                    'required' => true
                ),
                array(
                    'type'     => 'select',
                    'label'    => $this->l('Scegli la lista'),
                    'desc'     => $this->l('Lista di destinazione'),
                    'name'     => $this->prefix_setting('list'),
                    'required' => true,
                    /* @TODO get_lists() */
                    'options'  => $this->get_lists()
                ),
                array(
                    'type'     => 'select',
                    'label'    => $this->l('Policy attiva'),
                    'desc'     => $this->l('Scegli che tipo di policy vuoi adottare'),
                    'name'     => $this->prefix_setting('policy_type'),
                    'required' => false,
                    'options'  => array(
                        'query' => array(
                            array(
                                'id'   => 'dopt',
                                'name' => $this->l('Double opt-in')
                            ),
                            array(
                                'id'   => 'sopt',
                                'name' => $this->l('Single opt-in')
                            ),
                        ),
                        'id'    => 'id',
                        'name'  => 'name'
                    )
                )
                /**
                 * @TODO Choose Thank You Page for Double Opt-in
                 * @TODO Choose Unsubscribe Page for Single Opt-in
                 */
            ),
            'submit' => array(
                'title' => $this->l('Salva'),
                'class' => 'btn btn-default pull-right'
            )
        );

        $helper = new HelperForm();

        $helper->module = $this;
        $helper->name_controller = $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->currentIndex = AdminController::$currentIndex . '&configure=' . $this->name;

        $helper->default_form_language = $default_lang;
        $helper->allow_employee_form_lang = $default_lang;

        $helper->title = $this->displayName;
        $helper->show_toolbar = true;
        $helper->toolbar_scroll = true;
        $helper->submit_action = 'submit' . $this->name;
        $helper->toolbar_btn = array(
            'save' =>
                array(
                    'desc' => $this->l('Salva'),
                    'href' => AdminController::$currentIndex . '&configure=' . $this->name . '&save' . $this->name .
                        '&token=' . Tools::getAdminTokenLite('AdminModules'),
                ),
            'back' => array(
                'href' => AdminController::$currentIndex . '&token=' . Tools::getAdminTokenLite('AdminModules'),
                'desc' => $this->l('Vai indietro')
            )
        );

        // Load current value
        $helper->fields_value[$this->prefix_setting('username')] = Configuration::get($this->prefix_setting('username'));
        $helper->fields_value[$this->prefix_setting('password')] = Configuration::get($this->prefix_setting('password'));
        $helper->fields_value[$this->prefix_setting('list')] = Configuration::get($this->prefix_setting('list'));
        $helper->fields_value[$this->prefix_setting('policy_type')] = Configuration::get($this->prefix_setting('policy_type'));

        return $helper->generateForm($fields_form);

    }

    public function hookBackOfficeHeader($arr)
    {
        if (strtolower(Tools::getValue('controller')) == 'adminmodules' && Tools::getValue('configure') == "emailchef") {
            $this->context->controller->addJS($this->_path . 'js/plugins/emailchef/jquery.emailchef.js');
        }
    }

}