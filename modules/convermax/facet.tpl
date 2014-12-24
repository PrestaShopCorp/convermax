{foreach from=$facets item=facet}
    {if $facet->Values}
        <div id="{$facet->FieldName}">
            <p>{$facet->DisplayName}</p>
            {*<input type="hidden" value="{$facet->FieldName}" class="fieldstore">*}
            {*<ul>*}
            {foreach from=$facet->Values item=value}
                {*<li>*}
                <label><input type="checkbox" class="checkbox" value="{$value->Term}" data-fieldname="{$facet->FieldName}"{* onclick="convermaxSearch('{$facet->FieldName}', '{$value->Value}', '{$query}')"*}{if $value->Selected} checked{/if}>{$value->Value} - ({$value->HitCount})</label>
                {*<input type="hidden" value="{$facet->FieldName}" class="fieldstore">
                <input type="hidden" value="{$value->Value}" class="valuestore">*}
                {*</li>*}
            {/foreach}
            {*</ul>*}
        </div>
    {/if}
{/foreach}