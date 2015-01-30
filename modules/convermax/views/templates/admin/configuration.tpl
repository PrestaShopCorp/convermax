<style>
  .nobootstrap { min-width:720px !important;}
</style>  
  <div class="cm-config-wrapper">
    <div class="cm-config-content">
      <div class="header">
          <img src="{$module_dir}/img/logo.png" alt="Convermax">
          
      </div>
      <div class="videoblock">
        <iframe width="500" height="315" src="https://www.youtube.com/embed/Sme5uRzhCdM" frameborder="0" allowfullscreen=""></iframe>
        <h1>  {l s='Advanced Site Search' mod='convermax'}</h1>
        <p>83% of online shoppers turn to site search to find what they want. Offer an advanced site search experience, so poor results don’t cause shoppers to abandon your online store.</p>
        <p>{l s='Affordable whether you have 100 SKUs or millions.' mod='convermax'}</p>
        <p>{l s='We help walk you through the process to get up and running with our module.' mod='convermax'}</p>
      </div>
      <div class="freeblock">
        <input type="button" value="Get Your 30 Day Free Trial">
        Receive a <span>25% discount off</span> setup & integration fees.  
        <div class="start">Start now with a <span>30 day free</span> trial!</div>
      </div>
      <div class="featuresblock">
        <div class="lcol">
          <h2>Full featured advanced site search</h2>
            <ul>
              <li>Rich auto-complete</li>
              <li>Automatic spelling corrections</li>
              <li>Refinement panel</li>
              <li>Configurable search relevance</li>
              <li>Advanced faceted panel</li>
              <li>Search-based merchandising options</li>
              <li>Search reporting & dashboard</li>
              <li>Natively integrates with PrestaShop</li>
              <li>Fast & easy installation</li>
              <li>Shopper display control</li>
          </ul>
        </div>
        <div class="rcol">
          <div class="connectionform">
            <h3> Have your connection info?  <br>Configure it now</h3>
              <form action="{$smarty.server.REQUEST_URI|escape:'html':'UTF-8'}" method="post" enctype="multipart/form-data">
              <label for="hash">{l s='URL' mod='convermax'}:</label>
              <div>
                <input type="text" size="20" id="hash" name="hash" value="{$url}">
              </div>
              <label for="fileUpload">{l s='Certificate'}:</label>
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

    </div>






</div>