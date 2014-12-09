{foreach from=$facets item=facet}
    <div id="{$facet->FieldName}">
        <p>{$facet->DisplayName}</p>
        {*<input type="hidden" value="{$facet->FieldName}" class="fieldstore">*}
        {*<ul>*}
        {foreach from=$facet->Values item=value}
            {*<li>*}
            <label><input type="checkbox" value="{$value->Value}" data-fieldname="{$facet->FieldName}"{* onclick="convermaxSearch('{$facet->FieldName}', '{$value->Value}', '{$query}')"*}{if $value->Selected} checked{/if}>{$value->Value} - ({$value->HitCount})</label>
            {*<input type="hidden" value="{$facet->FieldName}" class="fieldstore">
            <input type="hidden" value="{$value->Value}" class="valuestore">*}
            {*</li>*}
        {/foreach}
        {*</ul>*}
    </div>
{/foreach}