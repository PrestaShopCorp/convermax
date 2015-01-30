{foreach from=$facets item=facet}
    {if $facet->Values}
        <div class="cm_facet">
            <p class="cm_facet_title">{$facet->DisplayName}</p>
            {if $facet->IsRanged}
                <div class="cm_slider" data-fieldname="{$facet->FieldName}" data-range="{$facet->Values[0]->Term}"></div>
            {elseif $facet->IsTree}
                <ul class="cm_tree">
                    {foreach from=$facet->Values item=val}
                        {if !$val->Selected}
                            <li><a href="#" class="cm_tree_item" data-fieldname="{$facet->FieldName}" data-displayname="{$facet->DisplayName}" data-value="{$val->Term}">{$val->Value} - ({$val->HitCount})</a></li>
                        {/if}
                    {/foreach}
                </ul>
            {else}
                    <ul>
                    {foreach from=$facet->Values item=value}
                        <li>
                        <label><input type="checkbox" class="checkbox" value="{$value->Term}" data-fieldname="{$facet->FieldName}" data-displayname="{$facet->DisplayName}"{if $value->Selected} checked{/if}>{$value->Value} ({$value->HitCount})</label>
                        </li>
                    {/foreach}
                    </ul>
            {/if}
        </div>
    {/if}
{/foreach}