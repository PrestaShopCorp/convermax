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
<style>
  .nobootstrap { min-width:720px !important;}
</style>  
  <div class="cm-config-wrapper">
    <div class="cm-config-content">
      <div class="header">
          <img src="{$module_dir|escape:'html'}/views/img/logo.png" alt="Convermax">
          
      </div>
      <div class="videoblock">
        {*<iframe width="500" height="315" src="https://www.youtube.com/embed/Sme5uRzhCdM" frameborder="0" allowfullscreen=""></iframe>*}
        <div id="player"></div>
        <h1>  {l s='Advanced Site Search' mod='convermax'}</h1>
        <p>{l s='83%% of online shoppers turn to site search to find what they want. Offer an advanced site search experience, so poor results don’t cause shoppers to abandon your online store.' mod='convermax'}</p>
        <p>{l s='Affordable whether you have 100 SKUs or millions.' mod='convermax'}</p>
        <p>{l s='We help walk you through the process to get up and running with our module.' mod='convermax'}</p>
      </div>
      <div class="freeblock">
		<div class="start"><span class="starleft"></span>Limited Time Offer<span class="starright"></span></div>
		We'll waive the setup fees for new Convermax users on Prestashop — that's a $500+ value!<br>
		And to make the offer even sweeter, you'll get the first two month of service at no charge.
        <input type="button" id="startbutton" value="I want this offer. Let's get started!">
      </div>
      <div class="featuresblock">
        <div class="lcol">
          <h2>{l s='Full featured advanced site search' mod='convermax'}</h2>
            <ul>
              <li>{l s='Rich auto-complete' mod='convermax'}</li>
              <li>{l s='Automatic spelling corrections' mod='convermax'}</li>
              <li>{l s='Refinement panel' mod='convermax'}</li>
              <li>{l s='Configurable search relevance' mod='convermax'}</li>
              <li>{l s='Advanced faceted panel' mod='convermax'}</li>
			  <li>{l s='Shopper display control' mod='convermax'}</li>
			  <li>{l s='Search-based merchandising options' mod='convermax'}</li>
              <li>{l s='Search reporting & dashboard' mod='convermax'}</li>
              <li>{l s='Native PrestaShop integration' mod='convermax'}</li>
              <li>{l s='Fast & easy installation' mod='convermax'}</li>
          </ul>
        </div>
        <div class="rcol">
          <div class="connectionform">
            <h3> {l s='Have your connection info?' mod='convermax'}  <br>{l s='Configure it now' mod='convermax'}</h3>
              <form action="{$smarty.server.REQUEST_URI|escape:'html':'UTF-8'}" method="post" enctype="multipart/form-data">
              <label for="url">{l s='Service URL' mod='convermax'}:</label>
              <div>
                <input type="text" size="20" id="url" name="url" value="{$url|escape:'html'}">
              </div>
              <label for="fileUpload">{l s='Certificate' mod='convermax'}:</label>
              <div>
                <input type="file" name="cert" id="fileUpload" />
              </div>
              <div class="btn">
                <input type="submit" name="submitModule" id="fileUpload" value="Connect" />
              </div>
            </form>
          </div>
        </div>
        <div class="clear"></div>
      </div>
      
      <div class="gallery_block">
        <h2>{l s='Convermax Works with Prestashop!' mod='convermax'}</h2>
        <a class="gallery" rel="features" href="{$module_dir|escape:'html'}/views/img/scr1.jpg">
          <img src="{$module_dir|escape:'html'}/views/img/feature1.jpg" alt="Automatic spelling corrections" title="Automatic spelling corrections"/>
        </a>
        <a class="gallery" rel="features" href="{$module_dir|escape:'html'}/views/img/scr2.jpg">
          <img src="{$module_dir|escape:'html'}/views/img/feature2.jpg" alt="Rich auto-complete" title="Rich auto-complete"/>
        </a>
        <a class="gallery" rel="features" href="{$module_dir|escape:'html'}/views/img/scr3.jpg">
          <img src="{$module_dir|escape:'html'}/views/img/feature3.jpg" alt="Refinement panel" title="Refinement panel"/>
        </a>
        <a class="gallery" rel="features" href="{$module_dir|escape:'html'}/views/img/scr4.jpg">
          <img src="{$module_dir|escape:'html'}/views/img/feature4.jpg" alt="Dashboard & reporting" title="Dashboard & reporting"/>
        </a>
        <div class="clear"></div>
      </div>
    </div>
</div>