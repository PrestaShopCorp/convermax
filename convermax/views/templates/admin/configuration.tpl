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
          <img src="{$module_dir|escape:'html'}/img/logo.png" alt="Convermax">
          
      </div>
      <div class="videoblock">
        <iframe width="500" height="315" src="https://www.youtube.com/embed/Sme5uRzhCdM" frameborder="0" allowfullscreen=""></iframe>
        <h1>  {l s='Advanced Site Search' mod='convermax'}</h1>
        <p>{l s='83%% of online shoppers turn to site search to find what they want. Offer an advanced site search experience, so poor results don’t cause shoppers to abandon your online store.' mod='convermax'}</p>
        <p>{l s='Affordable whether you have 100 SKUs or millions.' mod='convermax'}</p>
        <p>{l s='We help walk you through the process to get up and running with our module.' mod='convermax'}</p>
      </div>
      <div class="freeblock">
        <input type="button" value="Get Your 30 Day Free Trial">
{l s='Receive a' mod='convermax'} <span>25% {l s='discount off' mod='convermax'}</span> {l s='setup & integration fees.' mod='convermax'}
        <div class="start">{l s='Start now with a' mod='convermax'} <span>30 {l s='day free' mod='convermax'}</span> {l s='trial' mod='convermax'}!</div>
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
              <li>{l s='Search-based merchandising options' mod='convermax'}</li>
              <li>{l s='Search reporting & dashboard' mod='convermax'}</li>
              <li>{l s='Natively integrates with PrestaShop' mod='convermax'}</li>
              <li>{l s='Fast & easy installation' mod='convermax'}</li>
              <li>{l s='Shopper display control' mod='convermax'}</li>
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
        <h2>{l s='Screenshots' mod='convermax'}</h2>
        <a class="gallery" rel="features" href="{$module_dir|escape:'html'}/img/scr1.jpg">
          <img src="{$module_dir|escape:'html'}/img/feature1.jpg" alt=""/>
        </a>
        <a class="gallery" rel="features" href="{$module_dir|escape:'html'}/img/scr2.jpg">
          <img src="{$module_dir|escape:'html'}/img/feature2.jpg" alt=""/>
        </a>
        <a class="gallery" rel="features" href="{$module_dir|escape:'html'}/img/scr3.jpg">
          <img src="{$module_dir|escape:'html'}/img/feature3.jpg" alt=""/>
        </a>
        <a class="gallery" rel="features" href="{$module_dir|escape:'html'}/img/scr4.jpg">
          <img src="{$module_dir|escape:'html'}/img/feature4.jpg" alt=""/>
        </a>
        <div class="clear"></div>
      </div>
    </div>
</div>