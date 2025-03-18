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

class PS_Emailchef_Api {

	protected $api_url = "https://app.emailchef.com/api";
	public $lastError;
	private $consumerKey = null;
	private $consumerSecret = null;

	public function __construct( $consumer_key, $consumer_secret ) {
        $this->consumerKey = $consumer_key;
        $this->consumerSecret = $consumer_secret;
    }
	private function getRequest(
        $url,
        $payload,
        $type,
        $headers = []
    ) {

		try {

			Httpful::register(
				Mime::JSON,
				new JsonHandler(
					array( 'decode_as_array' => true )
				)
			);

            /**
             * @var $response \Httpful\Response
             */

            if ($type == 'POST') {
                $response = Request::post($url)
                    ->strictSSL(1)
                    ->body($payload, 'application/json')
                    ->addHeaders($headers)
                    ->send();
            } elseif ($type == 'DELETE') {
                $response = Request::init(Http::DELETE)
                    ->strictSSL(1)
                    ->uri($url)
                    ->body($payload, 'application/json')
                    ->addHeaders($headers)
                    ->send();
            } elseif ($type == 'PUT') {
                $response = Request::put($url)
                    ->strictSSL(1)
                    ->body($payload, 'application/json')
                    ->addHeaders($headers)
                    ->send();
            } else {
                $response = Request::get($url)
                    ->strictSSL(1)
                    ->body($payload, 'application/json')
                    ->addHeaders($headers)
                    ->send();
            }

            if ($response->hasErrors()){
                throw new \Exception(
                    'request_error',
                    $response->code
                );
            }

		} catch ( \Exception $e ) {
			$response_data = array(
				'status' => 'error',
                'code' => $e->getCode(),
				'error'  => $e->getMessage()
			);
            return json_encode($response_data);
		}

		return $response;
	}

	protected function call( $route, $payload = array(), $type = "POST", $encoded = false ) {

		$url  = $this->api_url . $route;
		$headers = array(
            'User-Agent' => 'Emailchef for PrestaShop'
        );

		if (
            !is_null($this->consumerKey) &&
            !is_null($this->consumerSecret)
        ) {
            $headers = array(
				'consumerKey' => $this->consumerKey,
                'consumerSecret' => $this->consumerSecret
			);
		}

		if ( $encoded ) {
			$payload = json_encode( $payload );
		}

		return json_decode( $this->getRequest(
            $url,
            $payload,
            $type,
            $headers
        ), true );
	}

    protected function json($route, $args = array(), $type = "POST"){
        return $this->call( $route, $args, $type, true );
    }

}
