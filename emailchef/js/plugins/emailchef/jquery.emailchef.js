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
    var $btnSave;

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
        $btnSave = $("button[name='submitemailchef']");
        $listCreation = $(".list_creation");
    }

    function triggerElements() {

        $createList.on("click", function (evt) {
            evt.preventDefault();
            $listCreation.slideToggle('slow', function(){

                if ($(this).is(":hidden")){
                    $createList.text(i18n.create_list);
                }
                else {
                    $createList.html('<span class="icon icon-times icon-lg"></span>');
                }

            });
            $listCreation.find("input").val("");
        });

        $(document).on("click", "#" + prefixed_setting("save"), function (evt) {
            evt.preventDefault();
            addList($apiUser.val(), $apiPass.val(), $("#" + prefixed_setting("new_name")).val(), $("#" + prefixed_setting("new_description")).val());
        });

        $(document).on("click", "#" + prefixed_setting("undo"), function (evt) {
            evt.preventDefault();
            $createList.text(i18n.create_list);
            $listCreation.slideUp();
        });

        $policyList.on("change", function(evt){

            evt.preventDefault();

            if ($(this).val() === 'sopt'){
                $landingList.closest(".form-group").fadeOut();
                $fpageList.closest(".form-group").fadeOut();
            }
            else {
                $landingList.closest(".form-group").fadeIn();
                $fpageList.closest(".form-group").fadeIn();
            }

        });

    }

    function policyContent(status) {
        if (status == 'hide') {
            $policyList.closest(".form-group").hide();
        }
        else {
            $policyList.closest(".form-group").hide();
        }
    }

    function accessIsValid(apiUser, apiPass) {

        var ajax_url = $listCreation.data("ajax-action");
        var ajax_data = {
            action: 'emailcheflogin',
            api_user: '',
            api_pass: ''
        };

        if (apiUser !== "islogin" && apiPass !== "islogin") {
            ajax_data.api_user = apiUser;
            ajax_data.api_pass = apiPass;
        }
        else {
            ajax_data.fetch = true
        }

        formContent('hide');
        $btnSave.attr("disabled", "disabled");

        if (apiUser === '' || apiPass === '')
            return;

        $(".status-login").hide();
        $(".check-login").show();

        $.ajax({
            type: 'POST',
            url: ajax_url,
            data: ajax_data,
            dataType: 'json',
            success: function(response) {

                if (response.type == 'error') {
                    $(".status-login").hide();
                    $("#error_login_data").show();
                    return;
                }

                $("#success_login_data").show().delay(3000).fadeOut();

                $selList.empty();
                if (response.lists.length > 0) {

                    $.each(response.lists, function (key, list) {
                        $selList.append($('<option>').text(list.name).attr('value', list.id));
                    });

                }

                else {
                    $selList.append($('<option>').text(i18n.no_list_found).attr('value', -1))
                }

                formContent('show');

                console.log("Policy = "+response.policy);

                if (response.policy !== 'premium'){
                    policyContent('hide');
                }
                else {
                    policyContent('show');
                }

                if (response.list !== undefined)
                    $selList.val(response.list).attr("selected", "selected");

                $btnSave.removeAttr("disabled");

            },
            error: function(jxqr, textStatus, thrown){
                $("#server_failure_login_data").show();
            },
            complete: function() {
                $(".check-login").hide();
            }
        });
    }

    function formContent(status) {

        $(".form-wrapper > div").each(function(key, div) {
            if (key > 5) {
                if (status == 'hide')
                    $(div).hide();
                else {
                    $(div).show();
                }
            }
        });

        $listCreation.hide();
        $(".check-list, .response-list").hide();
        $(".check-list-cf, .response-list-cf").hide();

    }

    function mainListChanges() {

        $("#" + prefixed_setting("username") + ", " + "#" + prefixed_setting("password")).change(function () {
            accessIsValid($apiUser.val(), $apiPass.val());
        });
        accessIsValid("islogin", "islogin");

    }

    function createCustomFields(apiUser, apiPass, listId) {

        var ajax_data = {
            action: 'emailchefaddcustomfields',
            api_user: apiUser,
            api_pass: apiPass,
            list_id: listId
        };

        var ajax_url = $listCreation.data("ajax-action");

        $(".status-list-cf").hide();
        $(".check-list-cf").show();

        $.ajax({
            type: 'POST',
            url: ajax_url,
            data: ajax_data,
            dataType: 'json',
            success: function(response) {

                if (response.type == 'error') {
                    $(".status-list-cf").hide();
                    $("#error_status_list_data_cf").find(".reason").text(response.msg);
                    $("#error_status_list_data_cf").show();
                    return;
                }

                $("#success_status_list_data_cf").show().delay(3000).fadeOut();

            },
            error: function(jxqr, textStatus, thrown){
                $("#error_status_list_data_cf").find(".reason").text(jxqr.error +" "+textStatus+" "+thrown);
                $("#server_error_status_list_data_cf").show();
            },
            complete: function() {
                $(".check-list-cf").hide();
                $btnSave.removeAttr("disabled");
                $selList.removeAttr("disabled");
            }
        });

    }

    function addList(apiUser, apiPass, listName, listDesc) {

        var ajax_data = {
            action: 'emailchefaddlist',
            api_user: apiUser,
            api_pass: apiPass,
            list_name: listName,
            list_desc: listDesc
        };

        var ajax_url = $listCreation.data("ajax-action");

        $btnSave.attr("disabled", "disabled");
        $selList.attr("disabled", "disabled");

        $(".status-list").hide();
        $(".check-list").show();

        $.ajax({
            type: 'POST',
            url: ajax_url,
            data: ajax_data,
            dataType: 'json',
            success: function(response) {

                if (response.type == 'error') {
                    $(".status-list").hide();
                    $("#error_status_list_data").find(".reason").text(response.msg);
                    $("#error_status_list_data").show();
                    return;
                }

                $createList.text(i18n.create_list);
                $listCreation.slideUp();

                $("#success_status_list_data").show().delay(3000).fadeOut();

                if (response.list_id !== undefined) {
                    $selList.append($('<option>').text(listName).attr('value', response.list_id))
                    $selList.val(response.list_id).attr("selected", "selected");
                }

                createCustomFields(apiUser, apiPass, response.list_id);

                /*
                $btnSave.removeAttr("disabled");
                $selList.removeAttr("disabled");
                */

            },
            error: function(jxqr, textStatus, thrown){
                $("#error_status_list_data").find(".reason").text(jxqr.error +" "+textStatus+" "+thrown);
                $("#server_error_status_list_data").show();
            },
            complete: function() {
                $(".check-list").hide();
            }
        });

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


