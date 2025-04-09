<div class="ecps-main-container">
    <div class="ecps-main-account">
        <div class="ecps-forms-logo">
            <img src="{$module_dir}views/img/logo-compact.svg"
                 alt="{l s='Emailchef' mod='emailchef'}"/>
            <div class="ecps-account-status">
                <div>{l s='Account connected' mod='emailchef'}</div>
                <div class="ecps-account-connected"></div>
            </div>
        </div>
        <div class="ecps-account-info">
            <span class="flex-grow-1 truncate"
                  title="{$account['email']}"><strong>{$account['email']}</strong>
            </span>
            <span>
                <a id="emailchef-disconnect" class="ecps-account-disconnect" data-ajax-url="{$ajax_url}"
                   title="{l s='Disconnect account' mod='emailchef'}">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path
                                d="M280 24c0-13.3-10.7-24-24-24s-24 10.7-24 24l0 240c0 13.3 10.7 24 24 24s24-10.7 24-24l0-240zM134.2 107.3c10.7-7.9 12.9-22.9 5.1-33.6s-22.9-12.9-33.6-5.1C46.5 112.3 8 182.7 8 262C8 394.6 115.5 502 248 502s240-107.5 240-240c0-79.3-38.5-149.7-97.8-193.3c-10.7-7.9-25.7-5.6-33.6 5.1s-5.6 25.7 5.1 33.6c47.5 35 78.2 91.2 78.2 154.7c0 106-86 192-192 192S56 368 56 262c0-63.4 30.7-119.7 78.2-154.7z"></path></svg>
                </a>
            </span>

        </div>
        <hr class="ecps-hr-separator">
        <div>
            <div><strong>{l s='Emailchef connected list' mod='emailchef'}</strong></div>
            <div class="ecps-list-container {if $list_id}ecps-has-list{/if}" id="listName">
            <span>
                    <svg xmlns="http://www.w3.org/2000/svg" style="height: 16px; margin-top: 2px; display: block"
                         viewBox="0 0 640 512"><path fill="#CCCCCC"
                                                     d="M96 128a128 128 0 1 1 256 0A128 128 0 1 1 96 128zM0 482.3C0 383.8 79.8 304 178.3 304l91.4 0C368.2 304 448 383.8 448 482.3c0 16.4-13.3 29.7-29.7 29.7L29.7 512C13.3 512 0 498.7 0 482.3zM609.3 512l-137.8 0c5.4-9.4 8.6-20.3 8.6-32l0-8c0-60.7-27.1-115.2-69.8-151.8c2.4-.1 4.7-.2 7.1-.2l61.4 0C567.8 320 640 392.2 640 481.3c0 17-13.8 30.7-30.7 30.7zM432 256c-31 0-59-12.6-79.3-32.9C372.4 196.5 384 163.6 384 128c0-26.8-6.6-52.1-18.3-74.3C384.3 40.1 407.2 32 432 32c61.9 0 112 50.1 112 112s-50.1 112-112 112z"></path></svg>

                </span>

                {if $list_id}
                    <span class="truncate ecps-list-selected" title="{$list_name}" id="ecps-list-selected">
                         {$list_name}
                    </span>
                {else}
                    <span class="ecps-list-none ecps-list-none" id="ecps-no-list-selected">
                   {l s='No list connected...' mod='emailchef'}
                </span>
                {/if}
            </div>
        </div>
        <hr class="ecps-hr-separator">
        <div>
            <p>{l s="Prestashop users usually sync automatically with Emailchef. If an issue arises or you need an immediate update, use the button below for manual sync." mod='emailchef'}</p>
            <p class="ecps-text-center ecps-submit">
                <button {if !$list_id}disabled{/if} type="button"  data-ajax-url="{$ajax_url}"
                        id="ps_emailchef_sync_now"
                        class="btn btn-default btn-sm"
                        title="{if !$list_id}{l s="Please select a list and save settings first" mod="emailchef"}{/if}">
                    {l s="Manual Sync Now" mod="emailchef"}
                </button>
            </p>
        </div>
        <hr class="ecps-hr-separator">
        <div>
            <p>
                <a href="{$admin_logs_link}" target="_blank">{l s="Show Logs" mod="emailchef"}</a>
            </p>
        </div>
    </div>
    <div class="ecps-main-forms">
        <h2>{l s='Emailchef for PrestaShop settings' mod='emailchef'}</h2>
        <p>{l s='Welcome to the Emailchef Integration section for PrestaShop. This module allows you to effortlessly synchronize your PrestaShop customers with your preferred Emailchef list, ensuring your email marketing efforts are always up-to-date and targeting the right audience.' mod='emailchef'}</p>
        <form method="post" action="">
            <div class="panel" style="margin-top: 1.5rem">
                <header class="panel-heading">
                    {l s='Emailchef List Settings' mod='emailchef'}
                </header>
                <p>{l s='Simply select the Emailchef list that aligns with your campaign objectives, and the module will handle the rest. Our seamless synchronization process updates your chosen list with new users, modifications to existing user information, and any other relevant changes.' mod='emailchef'}</p>
                <table class="form-table">
                    <tbody>
                    <tr>
                        <th scope="row" class="titledesc">
                            <label for="ps_emailchef_list"><strong>{l s='Emailchef List' mod='emailchef'}</strong></label>
                        </th>
                        <td class="forminp forminp-select">

                            <select name="list_id"
                                    id="ps_emailchef_list"
                                    style="min-width: 350px;" tabindex="-1"
                                    aria-hidden="true">
                                <option value="">{l s='Select a list...' mod='emailchef'}</option>
                                {foreach from=$lists item=$list}
                                    <option value="{$list['id']}" {if $list_id == $list['id']}selected{/if}>
                                        {$list['name']|escape:'html':'UTF-8'}
                                    </option>
                                {/foreach}
                            </select>
                            <p class="description" style="margin-top: 1rem">
                                <a href="#"
                                   id="ps_emailchef_create_list">{l s='Add a new Emailchef destination list' mod='emailchef'}</a>
                            </p>
                            <div class="ecps-new-list-container" data-ajax-url="{$ajax_url}">
                                <label><strong>{l s='List name' mod='emailchef'}</strong></label>
                                <input name="ps_emailchef_new_name" id="ps_emailchef_new_name" type="text" dir="ltr"
                                       style="min-width:350px;" value=""
                                       placeholder="{l s='Provide a name for this new list.' mod='emailchef'}">
                                <label><strong>{l s='List description' mod='emailchef'}</strong></label>
                                <input name="ps_emailchef_new_description" id="ps_emailchef_new_description" type="text"
                                       dir="ltr" style="min-width:350px;" value=""
                                       placeholder="{l s='Provide a description for this new list.' mod='emailchef'}">
                                <p>{html_entity_decode({l s='By setting up a new list within Emailchef, you acknowledge and affirm adherence to %s and %s, as well as compliance with the CAN-SPAM Act.' mod='emailchef' sprintf=['<a href="https://emailchef.com/privacy-policy/" target="_blank">privacy policy</a>','<a href="https://emailchef.com/terms-of-use/" target="_blank">terms of use</a>']})}</p>
                                <p class="ecps-buttons-container">
                                    <button type="button" name="ps_emailchef_save"
                                            class="btn btn-primary btn-sm" id="ps_emailchef_new_save">
                                        {l s='Create List' mod='emailchef'}
                                    </button>
                                    <button type="button" name="ps_emailchef_undo" class="btn btn-default btn-sm"
                                            id="ps_emailchef_undo_save">
                                        {l s='Undo' mod='emailchef'}
                                    </button>
                                </p>

                                <div id="success_status_list_data" class="status-list response-list">
                                    <div class="row">
                                        <div class="col-lg-12">
                                            <div class="alert alert-success alert-check">

                                                <h4>
                                                    {$i18n['success_status_list_data']}
                                                </h4>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div id="check_status_list_data" class="check-list">
                                    <div class="row">
                                        <div class="col-lg-12">
                                            <div class="alert alert-info alert-check">

                                                <h4>
                                                    <span class="loading-spinner-emailchef"></span> {$i18n['check_status_list_data']}
                                                </h4>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div id="error_status_list_data" class="status-list response-list">
                                    <div class="row">
                                        <div class="col-lg-12">
                                            <div class="alert alert-danger alert-check">

                                                <h4>
                                                    {$i18n['error_status_list_data']}
                                                </h4>
                                                <p class="reason"></p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div id="server_error_status_list_data" class="status-list response-list">
                                    <div class="row">
                                        <div class="col-lg-12">
                                            <div class="alert alert-danger alert-check">

                                                <h4>
                                                    {$i18n['server_error_status_list_data']}
                                                </h4>
                                                <p class="reason"></p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!--
                                creazione custom fields
                                -->
                                <div id="success_status_list_data_cf" class="status-list-cf response-list-cf">
                                    <div class="row">
                                        <div class="col-lg-12">
                                            <div class="alert alert-success alert-check">

                                                <h4>
                                                    {$i18n['success_status_list_data_cf']}
                                                </h4>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div id="check_status_list_data_cf" class="check-list-cf">
                                    <div class="row">
                                        <div class="col-lg-12">
                                            <div class="alert alert-info alert-check">

                                                <h4>
                                                    <span class="loading-spinner-emailchef"></span> {$i18n['check_status_list_data_cf']}
                                                </h4>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div id="error_status_list_data_cf" class="status-list-cf response-list-cf">
                                    <div class="row">
                                        <div class="col-lg-12">
                                            <div class="alert alert-danger alert-check">

                                                <h4>
                                                    {$i18n['error_status_list_data_cf']}
                                                </h4>
                                                <p class="reason"></p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div id="server_error_status_list_data_cf" class="status-list-cf response-list-cf">
                                    <div class="row">
                                        <div class="col-lg-12">
                                            <div class="alert alert-danger alert-check">

                                                <h4>
                                                    {$i18n['server_error_status_list_data']}
                                                </h4>
                                                <p class="reason"></p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!--
                                    Sistemazione custom fields
                                -->
                                <div id="success_status_list_data_cf_change"
                                     class="status-list-cf-change response-list-cf-change">
                                    <div class="row">
                                        <div class="col-lg-12">
                                            <div class="alert alert-success alert-check">

                                                <h4>
                                                    {$i18n['success_status_list_data_cf_change']}
                                                </h4>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div id="check_status_list_data_cf_change" class="check-list-cf-change">
                                    <div class="row">
                                        <div class="col-lg-12">
                                            <div class="alert alert-info alert-check">

                                                <h4>
                                                    <span class="loading-spinner-emailchef"></span> {$i18n['check_status_list_data_cf_change']}
                                                </h4>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div id="error_status_list_data_cf_change"
                                     class="status-list-cf-change response-list-cf-change">
                                    <div class="row">
                                        <div class="col-lg-12">
                                            <div class="alert alert-danger alert-check">

                                                <h4>
                                                    {$i18n['error_status_list_data_cf_change']}
                                                </h4>
                                                <p class="reason"></p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div id="server_error_status_list_data_cf_change"
                                     class="status-list-cf-change response-list-cf-change">
                                    <div class="row">
                                        <div class="col-lg-12">
                                            <div class="alert alert-danger alert-check">

                                                <h4>
                                                    {$i18n['server_error_status_list_data']}
                                                </h4>
                                                <p class="reason"></p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row" class="titledesc">
                            <label><strong>{l s="Sync existing customers" mod="emailchef"}</strong></label>
                        </th>
                        <td class="forminp forminp-checkbox ">
                            <fieldset>
                                <label for="ps_emailchef_sync_customers">
                                    <input name="sync_customers" id="ps_emailchef_sync_customers"
                                           style="margin-right: 5px;"
                                           type="checkbox" value="1"/>
                                    {l s="Sync existing PrestaShop customers on save" mod="emailchef"}
                                </label>
                            </fieldset>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>

            <div class="panel">
                <header class="panel-heading">
                    {l s='Emailchef Subscription Settings' mod='emailchef'}
                </header>
                <p>{l s="Manage subscriber integration with your newsletter through Emailchef's plan-based options. With Single Opt-in, users join immediately. Double Opt-in, where users confirm via email, enhances audience engagement. Note that option availability varies by user plan." mod='emailchef'}</p>
                <table class="form-table">
                    <tbody>
                    <tr>
                        <th scope="row" class="titledesc">
                            <label for="ps_emailchef_policy_type">
                                <strong>{l s='Subscription policy' mod='emailchef'}</strong>
                            </label>
                        </th>
                        <td class="forminp forminp-select">
                            <select name="policy_type" id="ps_emailchef_policy_type" style="max-width: 250px"
                                    aria-hidden="true">
                                {foreach from=$policy_types key=value item=$policy}
                                    <option value="{$policy}" {if $policy_type == $policy}selected{/if}>
                                        {$policy}
                                    </option>
                                {/foreach}
                            </select>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>

            <div class="ecps-submit mt-3">
                <button name="save" class="prestashop-save-button components-button is-primary btn btn-primary"
                        type="submit"
                        value="{l s='Save changes' mod='emailchef' }">{l s='Save changes' mod='emailchef' }</button>
            </div>
            <input type="hidden" name="submitEmailchefSettings" value=""/>
        </form>
    </div>

</div>
<script type="text/javascript">
    PS_Emailchef.settings({
        'no_list_found': '{$i18n['no_list_found']}',
        'create_list': '{$i18n['create_list']}',
        'language_set': '{$i18n['language_set']}',
        'are_you_sure_disconnect': '{$i18n['are_you_sure_disconnect']}'
    }, {$manualSync});
</script>
