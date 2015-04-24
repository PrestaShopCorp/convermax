{*
* 2015 CONVERMAX CORP
*
* NOTICE OF LICENSE
*
* This source file is subject to the Open Software License (OSL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/osl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to info@convermax.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author CONVERMAX CORP <info@convermax.com>
*  @copyright  2015 CONVERMAX CORP
*  @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*  International Registered Trademark & Property of CONVERMAX CORP
*}
<script type="text/javascript">
    var cm_query = '{$query|escape:'html':'UTF-8'}';
    cm_params.page = '{$pagenumber|escape:'html':'UTF-8'}';
    cm_params.size = '{$pagesize|escape:'html':'UTF-8'}';
    {if isset($facets_params) && $facets_params}
        {$facets_params|escape:'quotes':'UTF-8'}
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
    <div class="cm_ajax_loader"><img src="{$img_ps_dir|escape:'html':'UTF-8'}loader.gif" alt="" /><br />{l s='Loading...' mod='convermax'}</div>
</div>
