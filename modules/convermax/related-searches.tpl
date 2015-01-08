{if isset($related_searches) && $related_searches}
    <div id="cm_related">
        <strong>{l s='Related searches:' mod='convermax'}</strong>
        {foreach from=$related_searches item=query}
            <a href="#">{$query}</a>,
        {/foreach}
    </div>
{/if}