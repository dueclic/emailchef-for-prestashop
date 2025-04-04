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

    function loadLists(list_id) {

        $(".ecwc-new-list-container button").attr("disabled", "disabled");

        $.post('ajax_lists_add_url', {}, function (response) {

            if (response.success) {

                var options = [];

                $.each(response.data.lists, function (id, text) {
                    options.push({
                        text: text,
                        id: id
                    });
                });

                $("#" + prefixed_setting("list")).empty().select2({
                    data: options
                });

                $("#" + prefixed_setting("list")).val(list_id).trigger("change");

            } else {
                alert(response.data.message);
            }

        });

    }

    function addList(listName, listDesc) {

        $(".ecps-new-list-container button").attr("disabled", "disabled");

        $.post('ajax_add_list_url', {
            'data': {
                'list_name': listName,
                'list_desc': listDesc
            }
        }, function (response) {

            $(".ecps-new-list-container button").removeAttr("disabled");

            if (response.success) {
                $(".ecps-new-list-container").hide();
                loadLists(response.data.list_id);
            } else {
                alert(response.data.message);
            }
        });

    }

    function settings(){
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

    }

}(jQuery);



