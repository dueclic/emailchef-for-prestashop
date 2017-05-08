/*
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

var PS_Emailchef = function($) {

    var namespace = 'ps_emailchef';

    var $createList;
    var $selList;
    var $apiUser;
    var $apiPass;
    var $saveNewList;
    var $newListName;
    var $newListDesc;
    var $policyList;
    var $landingList;
    var $fpageList;
    var $listCreation;

    return {
        go : go
    };

    function getElements() {
        $createList = $("#" + prefixed_setting("create_list"));
        $selList = $("#" + prefixed_setting("list"));
        $apiUser = $("#" + prefixed_setting("username"));
        $apiPass = $("#" + prefixed_setting("password"));
        $newListName = $("#" + prefixed_setting("new_name"));
        $newListDesc = $("#" + prefixed_setting("new_description"));
        $saveNewList = $("#" + prefixed_setting("new_save"));
        $policyList = $("#" + prefixed_setting("policy_type"));
        $landingList = $("#" + prefixed_setting("landing_page"));
        $fpageList = $("#" + prefixed_setting("fuck_page"));
        $listCreation = $(".list_creation");
    }

    function triggerElements() {

        $createList.on("click", function (evt) {
            evt.preventDefault();
            $(".list_creation").slideToggle();
            $(".list_creation").find("input").val("");

        });

        $(document).on("click", "#" + prefixed_setting("new_save"), function (evt) {
            evt.preventDefault();
            addList($apiUser.val(), $apiPass.val(), $("#" + prefixed_setting("new_name")).val(), $("#" + prefixed_setting("new_description")).val());
        });

        $(document).on("click", "#" + prefixed_setting("undo_save"), function (evt) {
            evt.preventDefault();
            $listCreation.slideUp();
        });

        $policyList.on("change", function(evt){

            evt.preventDefault();

            if ($(this).val() === 'sopt'){
                $landingList.closest("tr").fadeOut();
                $fpageList.closest("tr").fadeOut();
            }
            else {
                $landingList.closest("tr").fadeIn();
                $fpageList.closest("tr").fadeIn();
            }

        });

    }

    function accessIsValid(apiUser, apiPass, apiLoad) {

        var ajax_data = {
            action: 'emailcheflogin',
            api_user: '',
            api_pass: ''
        };

        if (apiUser !== "islogin" && apiPass !== "islogin") {
            ajax_data.api_user = apiUser;
            ajax_data.api_pass = apiPass;
        }

        var query = $.ajax({
            type: 'POST',
            url: $(".list_creation").data("ajax-action"),
            data: ajax_data,
            dataType: 'json',
            success: function(json) {
                console.log(json);
            },
            error: function(jxqr, textStatus, thrown){
                alert(jxqr.status+" "+thrown+" "+textStatus);
            }
        });

        /*formContent('hide');

        $(".submit input[name='save']").attr("disabled", "disabled");

        $("#check_login_data").remove();

        if (apiUser === '' || apiPass === '')
            return;

        $('<span id="check_login_data">Controllo dati di accesso in corso...</span>').insertAfter($apiUser);

        $selList.attr("disabled", "disabled");

        $.post(ajaxurl, {
                'action': '' + prefixed_setting('account'),
                'data': {
                    'api_user': apiUser,
                    'api_pass': apiPass
                }
            },
            function (response) {

                $(".submit input[name='save']").removeAttr("disabled");

                var result = $.parseJSON(response);

                if (result.type == 'error') {
                    $("#check_login_data").removeClass().addClass("error").html('<i class="dashicons dashicons-warning"></i> I dati inseriti sono errati.');
                    return;
                }

                $("#check_login_data").removeClass().addClass("success").html('<i class="dashicons dashicons-yes"></i>');

                formContent('show');

                console.log("Policy = "+result.policy);

                if (result.policy !== 'premium'){
                    console.log("Policy != premium, remove other policy options");
                    formPolicy('hide');
                }
                else {
                    formPolicy('show');
                }

                $selList.removeAttr("disabled");

                if (apiLoad) {
                    console.log("Loading lists...");
                    loadLists(apiUser, apiPass, -1);
                }

            }
        );*/

    }

    function mainListChanges() {

        $(".form-wrapper > div").each(function(key, div){

            if (key > 3)
                $(div).hide();

        });

        $("#" + prefixed_setting("username") + ", " + "#" + prefixed_setting("password")).change(function () {
            accessIsValid($apiUser.val(), $apiPass.val(), false);
        });

        accessIsValid("islogin", "islogin", false);

    }

    function prefixed_setting(suffix) {
        return namespace + "_" + suffix;
    }

    function go() {
        getElements();
        triggerElements();
        mainListChanges();
    }

}(jQuery);

(function($){

    $(document).ready(function() {
        PS_Emailchef.go();
    });

})(jQuery);


