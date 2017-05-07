{extends file="helpers/form/form.tpl"}
{block name="field"}
    {if $input.name == 'ps_emailchef_list'}
        prova
    {else}
        {$smarty.block.parent}
    {/if}
{/block}