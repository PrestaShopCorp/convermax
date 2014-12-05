<script type="text/javascript">
    //var query = '{$query}';
</script>
<div id="facets_block">
    <h4 class="title_block">Facets</h4>
    {*<h4>1{$myvar1}1</h4>*}
    {foreach from=$facets item=facet}
        <div id="{$facet->FieldName}">
            <p>{$facet->DisplayName}</p>
            {*<input type="hidden" value="{$facet->FieldName}" class="fieldstore">*}
            {*<ul>*}
            {foreach from=$facet->Values item=value}
                {*<li>*}
                    <label><input type="checkbox" value="{$value->Value}" data-fieldname="{$facet->FieldName}"{* onclick="convermaxSearch('{$facet->FieldName}', '{$value->Value}', '{$query}')"*}>{$value->Value} - ({$value->HitCount})</label>
                    {*<input type="hidden" value="{$facet->FieldName}" class="fieldstore">
                    <input type="hidden" value="{$value->Value}" class="valuestore">*}
                {*</li>*}
            {/foreach}
            {*</ul>*}
        </div>
    {/foreach}
</div>