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

var PS_Emailchef = function ($) {

    var namespace = 'ps_emailchef';
    var i18n = {};

    function prefixed_setting(suffix) {
        return namespace + "_" + suffix;
    }

    return {
        loginPage: loginPage,
        settings: settings
    };

    function loginPage(){
        var showPasswordButton = document.getElementById('showPassword');
        var hidePasswordButton = document.getElementById('hidePassword');
        var consumerSecretInput = document.getElementById("consumer_secret");

        if (showPasswordButton) {
            showPasswordButton.addEventListener('click', () => {
                consumerSecretInput.setAttribute('type', 'text');
                showPasswordButton.style.display = 'none';
                hidePasswordButton.style.display = 'flex';
            });
        }

        if (hidePasswordButton) {
            hidePasswordButton.addEventListener('click', () => {
                consumerSecretInput.setAttribute('type', 'password');
                showPasswordButton.style.display = 'flex';
                hidePasswordButton.style.display = 'none';
            });
        }
    }
    function addList(listName, listDesc) {

        $(".ecps-new-list-container button").prop("disabled", true);

        var ajax_data = {
            action: 'emailchefaddlist',
            list_name: listName,
            list_desc: listDesc
        };

        var ajax_url = $(".ecps-new-list-container").data("ajax-url");

        var $selList =  $("#"+prefixed_setting('list'));

        $(".status-list").hide();
        $(".check-list").show();

        $.ajax({
            type: 'POST',
            url: ajax_url,
            data: ajax_data,
            dataType: 'json',
            success: function (response) {

                if (response.type == 'error') {
                    $(".status-list").hide();
                    $("#error_status_list_data").find(".reason").text(response.msg);
                    $("#error_status_list_data").show();
                    return;
                }

                $(".ecps-new-list-container #"+prefixed_setting('new_save')).text(i18n.create_list);

                $("#success_status_list_data").show().delay(3000).fadeOut();

                if (response.list_id !== undefined) {
                    $selList.append($('<option>').text(listName).attr('value', response.list_id))
                    $selList.val(response.list_id).attr("selected", "selected");
                }

                createCustomFields(response.list_id);

            },
            error: function (jxqr, textStatus, thrown) {
                $("#error_status_list_data").find(".reason").text(jxqr.error + " " + textStatus + " " + thrown);
                $("#server_error_status_list_data").show();
            },
            complete: function () {
                $(".check-list").hide();
                $(".ecps-new-list-container button").prop("disabled", false);
            }
        });

    }

    function createCustomFields(listId) {

        $(".ecps-new-list-container button").prop("disabled", true);

        var ajax_data = {
            action: 'emailchefaddcustomfields',
            list_id: listId
        };

        var ajax_url = $(".ecps-new-list-container").data("ajax-url");

        $(".status-list-cf").hide();
        $(".check-list-cf").show();

        $.ajax({
            type: 'POST',
            url: ajax_url,
            data: ajax_data,
            dataType: 'json',
            success: function (response) {

                if (response.type == 'error') {
                    $(".status-list-cf").hide();
                    $("#error_status_list_data_cf").find(".reason").text(response.msg);
                    $("#error_status_list_data_cf").show();
                    return;
                }

                $("#success_status_list_data_cf").show().delay(3000).fadeOut();
                $('.ecps-new-list-container').slideUp();

            },
            error: function (jxqr, textStatus, thrown) {
                $("#error_status_list_data_cf").find(".reason").text(jxqr.error + " " + textStatus + " " + thrown);
                $("#server_error_status_list_data_cf").show();
                $(".ecps-new-list-container button").prop("disabled", false);
            },
            complete: function () {
                $(".check-list-cf").hide();
                $(".ecps-new-list-container button").prop("disabled", false);

            }
        });

    }

    function manualSync(
        suppressAlerts = false
    ) {

        var $syncButton = $("#"+prefixed_setting('sync_now'));

        $syncButton.prop("disabled", true);

        var ajax_data = {
            action: 'emailchefsync'
        };

        var ajax_url = $syncButton.data("ajax-url");


        $.ajax({
            type: 'POST',
            url: ajax_url,
            data: ajax_data,
            dataType: 'json',
            success: function (response) {

                $syncButton.prop("disabled", false);

                if (response.status === 'success' && !suppressAlerts) {
                    alert(response.msg);
                }

            },
            error: function (jxqr, textStatus, thrown) {
                $syncButton.prop("disabled", false);

            },
            complete: function () {
                $syncButton.prop("disabled", false);
            }
        });

    }

    function disconnectAccount(
    ) {

        var $disconnectButton = $("#emailchef-disconnect");

        $disconnectButton.prop("disabled", true);

        var ajax_data = {
            action: 'emailchefdisconnect'
        };

        var ajax_url = $disconnectButton.data("ajax-url");


        $.ajax({
            type: 'POST',
            url: ajax_url,
            data: ajax_data,
            dataType: 'json',
            success: function (response) {
               window.location.reload();
            },
            error: function (jxqr, textStatus, thrown) {
                $disconnectButton.prop("disabled", false);

            },
            complete: function () {
                $disconnectButton.prop("disabled", false);
            }
        });

    }


    function settings(
        _i18n,
        doManualSync = false
    ){

        i18n = _i18n;

        $(document).on("click", "#"+prefixed_setting('create_list'), function (evt) {
            evt.preventDefault();
            $(".ecps-new-list-container").toggle();
        });

        $(document).on("click", ".ecps-new-list-container #"+prefixed_setting('undo_save'), function (evt) {
            evt.preventDefault();
            $(".ecps-new-list-container").hide();
        });
        $(document).on("click", ".ecps-new-list-container #"+prefixed_setting('new_save'), function (evt) {
            evt.preventDefault();
            $(this).attr("disabled", "disabled");
            addList(
                $("#" + prefixed_setting("new_name")).val(),
                $("#" + prefixed_setting("new_description")).val()
            );
        });

        $(document).on("click",  "#"+prefixed_setting('sync_now'), function(evt){
            evt.preventDefault();
            manualSync();
        });

        $(document).on("click", "#emailchef-disconnect", function(evt){
           evt.preventDefault();
           if (confirm(i18n.are_you_sure_disconnect)){
                disconnectAccount();
           }
        });

        doManualSync && manualSync(
            true
        );


    }

}(jQuery);



