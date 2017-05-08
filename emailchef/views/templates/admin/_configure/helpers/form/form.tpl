{extends file="helpers/form/form.tpl"}
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
            <button class="select_and_create_btn btn btn-default" id="{$create_list_id}">Crea Lista</button>
            <div class="select_and_create_clear"></div>
        {/if}

        {block name='label'}
            {$smarty.block.parent}
        {/block}

    {else}
        {$smarty.block.parent}
    {/if}

{/block}

{block name="input_row"}
    {$smarty.block.parent}

    {if $input.type == "select_and_create"}
        <div class="list_creation alert alert-onboarding" data-ajax-action="{$ajax_url}">


            <div class="form-group">
                <label class="col-lg-3 control-label required" for="{$new_name_id}">Nome lista</label>
                <div class="col-lg-6">
                    <input type="text" class="form-control" name="{$new_name_id}"
                           placeholder="Inserisci il nome della nuova lista">
                </div>
            </div>

            <div class="form-group">
                <label class="col-lg-3 control-label required" for="{$new_desc_id}">Descrizione lista</label>
                <div class="col-lg-6">
                    <input type="text" class="form-control" name="{$new_desc_id}"
                           placeholder="Inserisci la descrizione della nuova lista">
                </div>
            </div>

            <div class="form-group">
                <div class="col-lg-12">
                    * Creando una nuova lista certifichi che è conforme alla politica Anti-SPAM e all' informativa sulla privacy.
                </div>
            </div>

            <div class="form-group">

                <button type="submit" value="1" id="{$undo_id}" name="{$undo_id}" class="btn btn-default pull-right">
                    <i class="icon-undo"></i> Annulla
                </button>
                &nbsp;
                <button type="submit" value="1" id="{$save_id}" name="{$save_id}" class="btn btn-default pull-right">
                    <i class="icon-plus"></i> Crea lista
                </button>

            </div>

        </div>
    {/if}

    {if $input.type == "text" && $input.name == $password_field}
        <div>
            <i class="process-icon-loading"></i> Controllo dei dati in corso...
        </div>
    {/if}

{/block}