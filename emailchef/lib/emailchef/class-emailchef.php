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

require_once( PS_EMAILCHEF_DIR . '/lib/emailchef/class-emailchef-api.php' );

class PS_Emailchef extends PS_Emailchef_Api {

	public function __construct( $username, $password ) {
		parent::__construct( $username, $password );
		$this->api_url = "https://app.emailchef.com/apps/api/v1";
	}

	public function get_policy() {
		$account = $this->get( "/accounts/current", array(), "GET" );
		return $account['mode'];
	}

}