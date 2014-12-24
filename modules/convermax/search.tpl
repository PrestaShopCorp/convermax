{if isset($cm_message) && $cm_message}
    <p class="alert alert-info">
        {$cm_message}
    </p>
{/if}
{include file="$tpl_dir./search.tpl"}
{include file="./related-searches.tpl"}