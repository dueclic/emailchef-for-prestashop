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

require_once(PS_EMAILCHEF_DIR . '/lib/emailchef/class-emailchef-api.php');

class PS_Emailchef extends PS_Emailchef_Api
{

    public $lastError;
    private $new_custom_id;

    public function __construct($username, $password)
    {
        parent::__construct($username, $password);
        $this->api_url = "https://app.emailchef.com/apps/api/v1";
    }

    /**
     * Get policy of account
     * @return string
     */

    public function get_policy()
    {
        $account = $this->get("/accounts/current", array(), "GET");
        return $account['mode'];
    }

    /**
     * Get lists from eMailChef
     * @param array $args
     * @param bool $asArray
     * @return mixed
     */

    private function lists($args = array(), $asArray = true)
    {
        return $this->get("/lists", $args, "GET");
    }

    /**
     * Get lists in a format valid for PrestaShop
     * @return array|bool
     */

    public function get_lists()
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
            $results[] = array(
                'id'   => $list['id'],
                'name' => $list['name']
            );
        }
        return $results;
    }

    /**
     * Get collection of custom fields from eMailChef List
     * @param $list_id
     * @return mixed
     */

    public function get_collection($list_id)
    {
        $route = sprintf("/lists/%d/customfields", $list_id);
        return $this->get($route, array(), "GET");
    }

    /**
     * Get custom fields from Config
     * @return mixed
     */

    protected function get_custom_fields()
    {
        $custom_fields = require(PS_EMAILCHEF_DIR . "/conf/custom_fields.php");
        return $custom_fields;
    }

    /**
     * Initialize custom fields for eMailChef List ID
     * @param $list_id
     * @return bool
     */
    public function initialize_custom_fields($list_id)
    {

        $collection = $this->get_collection($list_id);

        $new_custom_fields = array();

        foreach ($this->get_custom_fields() as $place_holder => $custom_field) {

            $type = $custom_field['type'];
            $name = $custom_field['name'];

            /**
             *
             * Check if is predefined
             * if it is continue
             *
             */

            if ($type == "predefined") {
                continue;
            }

            /**
             *
             * Check if a custom field exists by placeholder
             *
             */

            $cID = array_search($place_holder, array_column($collection, "place_holder"));

            if ($cID !== false) {

                /**
                 *
                 * Check if the type of custom fields is valid
                 *
                 */

                $data_type = $collection[$cID]['data_type'];
                $data_id = $collection[$cID]['id'];

                if ($type != $data_type) {
                    $this->delete_custom_field($data_id);
                } else {
                    $new_custom_fields[] = $data_id;
                    continue;
                }

            }

            $this->create_custom_field($list_id, $type, $name, $place_holder);
            $new_custom_fields[] = $this->new_custom_id;

        }

        /**
         *
         * Check if there are fields in emailChef
         * not present in @private $custom_fiels
         *
         * If fields are present delete
         *
         */

        $ec_id_custom_fields = array_column($collection, "id");
        $diff = array_diff($ec_id_custom_fields, $new_custom_fields);

        foreach ($diff as $custom_id) {
            $this->delete_custom_field($custom_id);
        }

        return true;

    }

    /**
     * Create eMailChef List
     * @param $name
     * @param $description
     * @return bool
     */

    public function create_list($name, $description)
    {

        $args = array(

            "instance_in" => array(
                "list_name"        => $name,
                "list_description" => $description
            )

        );

        $response = $this->get("/lists", $args, "POST");

        if ($response['status'] != "OK") {
            $this->lastError = $response['status']['message'];

            return false;
        }

        return $response['list_id'];

    }

    /**
     * Delete Custom Field
     * @param $field_id
     * @return bool
     */

    public function delete_custom_field($field_id)
    {

        $route = sprintf("/customfields/%d", $field_id);

        $status = $this->get($route, array(), "DELETE", true);

        if ($status !== "OK") {
            $this->lastError = $status['message'];
        }

        return ($status == "OK");

    }

    /**
     * Create a Custom Field in List ID
     * @param $list_id
     * @param $type
     * @param string $name
     * @param $placeholder
     * @param string $default_value
     * @return bool
     */
    public function create_custom_field($list_id, $type, $name = "", $placeholder, $default_value = "")
    {

        $route = sprintf("/lists/%d/customfields", $list_id);

        $args = array(

            "instance_in" => array(
                "data_type"     => $type,
                "name"          => ($name == "" ? $placeholder : $name),
                "place_holder"  => $placeholder,
                "default_value" => $default_value
            )

        );

        $response = $this->get($route, $args, "POST", true);

        if (isset($response['status']) && $response['status'] == "OK") {

            $this->new_custom_id = $response['custom_field_id'];

            return true;
        }

        $this->lastError = $response['message'];

        return false;


    }

}