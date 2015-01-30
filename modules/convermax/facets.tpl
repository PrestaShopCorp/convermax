<script type="text/javascript">
    var cm_query = '{$query}';
    cm_params.page = '{$pagenumber}';
    cm_params.size = '{$pagesize}';
    {if isset($redirect_url) && $redirect_url}
        var redirect_url = '{$redirect_url}';
    {/if}
    {if isset($facets_params) && $facets_params}
        {$facets_params}
    {/if}
    //alert(cm_params);
</script>
<div class="block">
    <h4 class="title_block">{l s='Refine your search' mod='convermax'}</h4>
</div>
<div class="block">
  <div id="cm_selected_facets" class="list-block"></div>
</div>

<div class="block">
  <div id="cm_facets" class="list-block">
    <div id="facets_block">
      {include file="./facet.tpl"}
    </div>
  </div>
</div>  
<div id="cm_ajax_container" style="display: none;">
    <div class="cm_ajax_loader"><img src="{$img_ps_dir}loader.gif" alt="" /><br />{l s='Loading...' mod='convermax'}</div>
</div>
