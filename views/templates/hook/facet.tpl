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
{foreach from=$facets item=facet}
    {assign var="flag" value=false}
    {assign var="tree_flag" value=false}
    {if $facet->Values}
        <div class="cm_facet">
            <p class="cm_facet_title" onclick="toggleFacet(this)">{$facet->DisplayName|escape:'html':'UTF-8'}</p>
            {if $facet->IsRanged}
                <div class="slider_pad cm_facetbody"><div class="cm_slider" data-fieldname="{$facet->FieldName|escape:'html':'UTF-8'}" data-displayname="{$facet->DisplayName|escape:'html':'UTF-8'}" data-range="{$facet->Values[0]->Term|regex_replace:"/ .*\]/":""|escape:'html':'UTF-8'} {$facet->Values[{$facet->Values|@count}-1]->Term|regex_replace:"/\[.*? /":""|escape:'html':'UTF-8'}"></div></div>
            {elseif $facet->IsTree}
                <div class="cm_tree cm_facetbody">
                    {counter assign=i start=0 print=false}
                    {foreach from=$facet->Values item=val}
                        {if !$val->Selected}
                            {if $i > 19 and $tree_flag == false}
                                <div class="cm_more_results" style="display:none">
                                {assign var="tree_flag" value=true}
                            {/if}
                            <div><a href="#" class="cm_tree_item" data-fieldname="{$facet->FieldName|escape:'html':'UTF-8'}" data-displayname="{$facet->DisplayName|escape:'html':'UTF-8'}" data-value="{$val->Term|escape:'html':'UTF-8'}">{$val->Value|escape:'html':'UTF-8'} ({$val->HitCount|escape:'html':'UTF-8'})</a></div>
                            {counter}
                        {/if}
                    {/foreach}
                    {if $tree_flag == true}
                        </div>
                        <span class="cm_more_link" onclick="toggleList(this)">Show more</span>
                        {assign var="tree_flag" value=false}
                    {/if}
                </div>
            {else}
                    <div class="cm_facetbody">
                    {counter assign=j start=0 print=false}
                    {foreach from=$facet->Values item=value}
                        {if $j > 19 and $flag == false}
                            <div class="cm_more_results" style="display:none">
                            {assign var="flag" value=true}
                        {/if}
                        <div>
                        <label><input type="checkbox" class="checkbox" value="{$value->Term|escape:'html':'UTF-8'}" data-fieldname="{$facet->FieldName|escape:'html':'UTF-8'}" data-displayname="{$facet->DisplayName|escape:'html':'UTF-8'}"{if $value->Selected} checked{/if}>{$value->Value|escape:'html':'UTF-8'} ({$value->HitCount|escape:'html':'UTF-8'})</label>
                        </div>
                        {counter}
                    {/foreach}
                    {if $flag == true}
                        </div>
                        <span class="cm_more_link" onclick="toggleList(this)">Show more</span>
                        {assign var="flag" value=false}
                    {/if}
                    </div>
            {/if}
        </div>
    {/if}
{/foreach}