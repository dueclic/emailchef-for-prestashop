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
 *     @author    dueclic <info@dueclic.com>
 *     @copyright 2017 dueclic
 *     @license   https://www.gnu.org/licenses/gpl-3.0.html  GNU General Public License (GPL 3.0)
 * /
 */

require_once(PS_EMAILCHEF_DIR . '/lib/emailchef/class-emailchef-api.php');

class PS_Emailchef extends PS_Emailchef_Api
{
    public function get_collection($list_id)
    {
        $route = sprintf("/lists/%d/customfields", $list_id);

        return $this->best_get($route, array(), true, "GET");
    }

    public function get_policy()
    {

        $account = $this->best_get("/accounts/current", array(), true, "GET");

        return $account['mode'];

    }

    public function __construct($username, $password)
    {

        parent::__construct($username, $password);
        $this->api_url = "https://app.emailchef.com/apps/api/v1";
    }

    private function best_get($route, $args, $asArray, $type = "POST")
    {

        if ($asArray) {
            return $this->getDecodedJson($route, $args, $type);
        }

        return $this->get($route, $args, $type);

    }

    public function lists($args = array(), $asArray = true)
    {
        return $this->best_get("/lists", $args, $asArray, "GET");
    }

    public function wrap_list($args = array())
    {


        $args['offset'] = 0;
        $args['orderby'] = 'cd';
        $args['ordertype'] = 'd';

        if (!array_key_exists('limit', $args)) {
            $args['limit'] = 100;
        }

        $lists = $this->lists($args);

        if (!$lists) {
            return false;
        }

        $results = array();

        foreach ($lists as $list) {

            $results[$list['id']] = $list['name'];

        }


        return $results;

    }

    public function account($asArray = true)
    {
        return $this->best_get("/accounts/current", array(), $asArray, "GET");
    }

    public function create_list($name, $description, $asArray = true)
    {

        $args = array(

            "instance_in" => array(
                "list_name"        => $name,
                "list_description" => $description
            )

        );

        $response = $this->best_get("/lists", $args, $asArray, "POST");

        if ($response['status'] != "OK") {
            $this->lastError = $response['status']['message'];

            return false;
        }

        return $response['list_id'];

    }

}