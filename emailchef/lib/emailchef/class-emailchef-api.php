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

use \Httpful\Request as Request;
use \Httpful\Http as Http;
use \Httpful\Httpful as Httpful;
use \Httpful\Mime as Mime;
use \Httpful\Handlers\JsonHandler as JsonHandler;

class PS_Emailchef_Api
{

    protected $api_url = "https://app.emailchef.com/api";
    public $lastError;
    private $isLogged = false;
    private $authkey = false;

    public function __construct($username, $password)
    {
        $this->process_login($username, $password);
    }

    public function isLogged()
    {
        return $this->isLogged;
    }

    private function process_login($username, $password)
    {

        $response = $this->get("/login", array(

            'username' => $username,
            'password' => $password

        ), "POST", true);

        if (!isset($response['authkey'])) {
            $this->lastError = $response['message'];
        } else {
            $this->authkey = $response['authkey'];
            $this->isLogged = true;
        }

    }

    private function getRequest($url, $payload, $type)
    {

        Httpful::register(
            Mime::JSON,
            new JsonHandler(
                array('decode_as_array' => true)
            )
        );

        $response = null;
        switch ($type) {
            case 'POST':
                $response = Request::post($url)
                    ->body($payload, 'application/json')
                    ->send();
                break;
            case 'DELETE':
                $response = Request::init(Http::DELETE)
                    ->uri($url)
                    ->body($payload, 'application/json')
                    ->send();
                break;
            case 'PUT':
                $response = Request::put($url)
	                ->body($payload, 'application/json')
                    ->send();
                break;
            case 'GET':
            default:
                $response = Request::get($url)
                    ->body($payload, 'application/json')
                    ->send();
                break;
        }

        return $response;
    }

    protected function get($route, $args = array(), $type = "POST", $encoded = false)
    {

        $url = $this->api_url . $route;
        $auth = array();

        if ($this->authkey !== false) {
            $auth = array(
                'authkey' => $this->authkey
            );
        }

        $payload = array_merge($auth, $args);

        if ($encoded) {
            $payload = json_encode($payload);
        }

        return json_decode($this->getRequest($url, $payload, $type), true);
    }

}
