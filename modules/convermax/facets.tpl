<script type="text/javascript">
    var cm_query = '{$query}';
    var cm_page = '{$pagenumber}';
    var cm_size = '{$pagesize}';
    //alert(cm_params);
    var current_friendly_url = '#/';
</script>
<h4 class="title_block">{l s='Facets' mod='convermax'}</h4>
<div id="cm_selected_facets"></div>
<div id="cm_facets">
    <div id="facets_block">
        {include file="./facet.tpl"}
    </div>
</div>
