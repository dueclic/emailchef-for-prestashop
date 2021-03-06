{extends file="helpers/form/form.tpl"}

{block name="legend" append}
    <div class="emailchef-image row">
        <div class="col-lg-9 col-lg-offset-3">
            <img src="{$logo_url}" alt="eMailChef Logo">
        </div>
    </div>
{/block}

{block name="input"}

    {if $input.type == "select_and_create"}

        {if isset($input.options.query) && !$input.options.query && isset($input.empty_message)}
            {$input.empty_message}
            {$input.required = false}
            {$input.desc = null}
        {else}
            <select class="select_and_create fixed-width-xl" name="{$input.name|escape:'html':'utf-8'}"
                    class="{if isset($input.class)}{$input.class|escape:'html':'utf-8'}{/if} fixed-width-xl"
                    id="{if isset($input.id)}{$input.id|escape:'html':'utf-8'}{else}{$input.name|escape:'html':'utf-8'}{/if}"
                    {if isset($input.multiple) && $input.multiple} multiple="multiple"{/if}
                    {if isset($input.size)} size="{$input.size|escape:'html':'utf-8'}"{/if}
                    {if isset($input.onchange)} onchange="{$input.onchange|escape:'html':'utf-8'}"{/if}
                    {if isset($input.disabled) && $input.disabled} disabled="disabled"{/if}>
                {if isset($input.options.default)}
                    <option value="{$input.options.default.value|escape:'html':'utf-8'}">{$input.options.default.label|escape:'html':'utf-8'}</option>
                {/if}
                {if isset($input.options.optiongroup)}
                    {foreach $input.options.optiongroup.query AS $optiongroup}
                        <optgroup label="{$optiongroup[$input.options.optiongroup.label]}">
                            {foreach $optiongroup[$input.options.options.query] as $option}
                                <option value="{$option[$input.options.options.id]}"
                                        {if isset($input.multiple)}
                                            {foreach $fields_value[$input.name] as $field_value}
                                                {if $field_value == $option[$input.options.options.id]}selected="selected"{/if}
                                            {/foreach}
                                        {else}
                                            {if $fields_value[$input.name] == $option[$input.options.options.id]}selected="selected"{/if}
                                        {/if}
                                >{$option[$input.options.options.name]}</option>
                            {/foreach}
                        </optgroup>
                    {/foreach}
                {else}
                    {foreach $input.options.query AS $option}
                        {if is_object($option)}
                            <option value="{$option->$input.options.id}"
                                    {if isset($input.multiple)}
                                        {foreach $fields_value[$input.name] as $field_value}
                                            {if $field_value == $option->$input.options.id}
                                                selected="selected"
                                            {/if}
                                        {/foreach}
                                    {else}
                                        {if $fields_value[$input.name] == $option->$input.options.id}
                                            selected="selected"
                                        {/if}
                                    {/if}
                            >{$option->$input.options.name}</option>
                        {elseif $option == "-"}
                            <option value="">-</option>
                        {else}
                            <option value="{$option[$input.options.id]}"
                                    {if isset($input.multiple)}
                                        {foreach $fields_value[$input.name] as $field_value}
                                            {if $field_value == $option[$input.options.id]}
                                                selected="selected"
                                            {/if}
                                        {/foreach}
                                    {else}
                                        {if $fields_value[$input.name] == $option[$input.options.id]}
                                            selected="selected"
                                        {/if}
                                    {/if}
                            >{$option[$input.options.name]}</option>
                        {/if}
                    {/foreach}
                {/if}
            </select>
            <a class="select_and_create_btn btn btn-default" id="{$create_list_id}">{$i18n['create_list']}</a>
            <div class="select_and_create_clear"></div>
        {/if}

        {block name='label' append}
        {/block}

    {elseif $input.type == "password"}
        <div class="input-group fixed-width-lg">
                                            <span class="input-group-addon">
                                                <i class="icon-key"></i>
                                            </span>
            <input type="password"
                   id="{if isset($input.id)}{$input.id}{else}{$input.name}{/if}"
                   name="{$input.name}"
                   class="{if isset($input.class)}{$input.class}{/if}"
                   value="{$fields_value[$input.name]}"
                   {if isset($input.autocomplete) && !$input.autocomplete}autocomplete="off"{/if}
                    {if isset($input.required) && $input.required } required="required" {/if} />
        </div>
    {else}
        {$smarty.block.parent}
    {/if}

{/block}

{block name="label"}
    {if isset($input.label)}
        <label class="control-label col-lg-3{if isset($input.required) && $input.required && $input.type != 'radio'} required{/if}">
            {$input.label}
            {if isset($input.hint)}
                <span class="label-tooltip" data-toggle="tooltip" data-html="true" title="{if is_array($input.hint)}
                            {foreach $input.hint as $hint}
                                {if is_array($hint)}
                                    {$hint.text|escape:'quotes'}
                                {else}
                                    {$hint|escape:'quotes'}
                                {/if}
                            {/foreach}
                        {else}
                            {$input.hint|escape:'quotes'}
                        {/if}"><i class="icon-info-sign"></i></span>
            {/if}

        </label>
    {/if}
{/block}

{block name="input_row" append}

    {if $input.type == "select_and_create"}
        <div class="list_creation" data-ajax-action="{$ajax_url}">
            <div class="row">
                <div class="col-lg-offset-3 col-lg-9">

                    <div class="list_creation_form panel panel-emailchef">

                        <div class="panel-heading">
                            <h1 class="panel-title">{$i18n['create_destination_list']}</h1>
                        </div>

                        <div class="panel-body">

                            <div class="form-group">
                                <label class="col-lg-3 control-label required"
                                       for="{$new_name_id}">{$i18n['name_list']}</label>
                                <div class="col-lg-6">
                                    <input type="text" class="form-control" id="{$new_name_id}" name="{$new_name_id}"
                                           placeholder="{$i18n['name_list_placeholder']}">
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-lg-3 control-label"
                                       for="{$new_desc_id}">{$i18n['desc_list']}</label>
                                <div class="col-lg-6">
                                    <input type="text" class="form-control" id="{$new_desc_id}" name="{$new_desc_id}"
                                           placeholder="{$i18n['desc_list_placeholder']}">
                                </div>
                            </div>

                            <div class="form-group">
                                <div class="col-lg-9 col-lg-offset-3">
                                    {$i18n['accept_privacy']}
                                </div>
                            </div>

                            <div class="form-group">

                                <div class="col-lg-9 col-lg-offset-3">
                                    &nbsp;
                                    <a value="1" id="{$save_id}" name="{$save_id}"
                                            class="btn btn-default">
                                        <i class="icon-plus"></i> {$i18n['create_list']}
                                    </a>

                                    <a value="1" id="{$undo_id}" name="{$undo_id}"
                                            class="btn btn-default">
                                        <i class="icon-undo"></i> {$i18n['undo_btn']}
                                    </a>

                                </div>

                            </div>
                        </div>

                    </div>

                </div>
            </div>
        </div>
        <!--
        creazione lista
        -->
        <div id="success_status_list_data" class="status-list response-list">
            <div class="row">
                <div class="col-lg-offset-3 col-lg-9">
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
                <div class="col-lg-offset-3 col-lg-9">
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
                <div class="col-lg-offset-3 col-lg-9">
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
                <div class="col-lg-offset-3 col-lg-9">
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
                <div class="col-lg-offset-3 col-lg-9">
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
                <div class="col-lg-offset-3 col-lg-9">
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
                <div class="col-lg-offset-3 col-lg-9">
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
                <div class="col-lg-offset-3 col-lg-9">
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
        <div id="success_status_list_data_cf_change" class="status-list-cf-change response-list-cf-change">
            <div class="row">
                <div class="col-lg-offset-3 col-lg-9">
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
                <div class="col-lg-offset-3 col-lg-9">
                    <div class="alert alert-info alert-check">

                        <h4>
                            <span class="loading-spinner-emailchef"></span> {$i18n['check_status_list_data_cf_change']}
                        </h4>
                    </div>
                </div>
            </div>
        </div>
        <div id="error_status_list_data_cf_change" class="status-list-cf-change response-list-cf-change">
            <div class="row">
                <div class="col-lg-offset-3 col-lg-9">
                    <div class="alert alert-danger alert-check">

                        <h4>
                            {$i18n['error_status_list_data_cf_change']}
                        </h4>
                        <p class="reason"></p>
                    </div>
                </div>
            </div>
        </div>
        <div id="server_error_status_list_data_cf_change" class="status-list-cf-change response-list-cf-change">
            <div class="row">
                <div class="col-lg-offset-3 col-lg-9">
                    <div class="alert alert-danger alert-check">

                        <h4>
                            {$i18n['server_error_status_list_data']}
                        </h4>
                        <p class="reason"></p>
                    </div>
                </div>
            </div>
        </div>
    {/if}

    {if $input.type == "password" && $input.name == $password_field}
        <div id="check_login_data" class="check-login">
            <div class="row">
                <div class="col-lg-offset-3 col-lg-9">
                    <div class="alert alert-info alert-check">

                        <h4>
                            <span class="loading-spinner-emailchef"></span> {$i18n['check_login_data']}
                        </h4>
                    </div>
                </div>
            </div>
        </div>
        <div id="error_login_data" class="status-login response-login">
            <div class="row">
                <div class="col-lg-offset-3 col-lg-9">
                    <div class="alert alert-danger alert-check">

                        <h4>
                            {$i18n['error_login_data']}
                        </h4>
                    </div>
                </div>
            </div>
        </div>
        <div id="server_failure_login_data" class="status-login response-login">
            <div class="row">
                <div class="col-lg-offset-3 col-lg-9">
                    <div class="alert alert-danger alert-check">

                        <h4>
                            {$i18n['server_failure_login_data']}
                        </h4>
                    </div>
                </div>
            </div>
        </div>
        <div id="success_login_data" class="status-login response-login">
            <div class="row">
                <div class="col-lg-offset-3 col-lg-9">
                    <div class="alert alert-success alert-check">

                        <h4>
                            {$i18n['success_login_data']}
                        </h4>
                    </div>
                </div>
            </div>
        </div>
    {/if}

{/block}
{block name="footer" append}
    <script>
        var i18n = {
            'no_list_found': '{$i18n['no_list_found']}',
            'create_list': '{$i18n['create_list']}',
            'language_set': '{$i18n['language_set']}'
        };
    </script>
{/block}