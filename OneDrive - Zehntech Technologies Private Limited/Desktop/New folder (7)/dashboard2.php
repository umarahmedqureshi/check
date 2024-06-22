<!-- Ensure the UI is properly scaled in the Shopify Mobile app -->
<meta name="viewport" content="width=device-width, initial-scale=1">
<?php 
  include('./constant.php');
  include('./inc/functions.php');
  if ($_REQUEST['return'] == 1){
    include ('./inc/DB.class.php');
  }
  $db = new DB();
  $table = 'stores_gyata';
  $conditions['return_type'] = 'single';
  $conditions['where'] = array('shop_url' => $_REQUEST['shop']);
  $get_single_result = $db->getRows($table, $conditions);
  $access_token = $get_single_result['access_token'];
  $sfat = $get_single_result['storefront_access_token'];
  $application_id = $get_single_result['application_id'];
  $widget_script = $get_single_result['widget_script'];
  $accountConnected = ($application_id && $widget_script) ? "connected" : "not_connected";
  $shop_url = $_REQUEST['shop'];

  $pos = strpos($shop_url, ".myshopify.com");
  $storeName = substr($shop_url, 0, $pos);

  $shop_details = shopify_call($access_token, $shop_url, "/admin/api/".API_VERSION."/shop.json", null, 'GET');
  $shop_details = json_decode($shop_details['response'], true);

  $shop_name = $shop_details['shop']['name'];
?>
<head>
  <meta name="shopify-api-key" content="<?php echo SHOPIFY_API_KEY; ?>" />
  <script src="https://cdn.shopify.com/shopifycloud/app-bridge.js"></script>
  <link href="./assets/css/polaris.css" rel="stylesheet">
  <!-- <link rel="stylesheet" href="./assets/css/admin-cb.css"> -->
</head>

<ui-modal id="disconnect-modal">
  <p style="margin: 20px auto;">Are you sure you want to disconnect your account? Press Yes to proceed.</p>
  <ui-title-bar title="Disconnect Account">
    <button variant="primary" tone="critical" onclick="disconnectAccount();">Yes</button>
    <button onclick="document.getElementById('disconnect-modal').hide()">No</button>
  </ui-title-bar>
</ui-modal>

<ui-modal id="connect-modal">
  <p style="margin: 20px;">You will be redirected to GyataGPT WebApp for Acoount connection when pressing the 'Connect' button.</p>
  <!-- <p style="margin: 20px;">If you already have a secret key, then press the 'Have Key?' button. Otherwise, if you want to connect to a new account, press the 'Connect' button.</p> -->
  <ui-title-bar title="Connect Account">
    <button onclick="ConnectAccount();" variant="primary">Connect</button>
    <!-- <button onclick="redirectToConfiguration();">Have key?</button> -->
  </ui-title-bar>
</ui-modal>
<div class="loader" id="loader"></div>
<div class="Polaris-Page">
  <div class="Polaris-Box" style="--pc-box-padding-block-start-xs:var(--p-space-400);--pc-box-padding-block-start-md:var(--p-space-600);--pc-box-padding-block-end-xs:var(--p-space-400);--pc-box-padding-block-end-md:var(--p-space-600);--pc-box-padding-inline-start-xs:var(--p-space-400);--pc-box-padding-inline-start-sm:var(--p-space-0);--pc-box-padding-inline-end-xs:var(--p-space-400);--pc-box-padding-inline-end-sm:var(--p-space-0);position:relative">
    <!-- tabs -->
    <div class="Polaris-Tabs__Outer">
      <div class="Polaris-Box" style="--pc-box-padding-block-start-md:var(--p-space-200);--pc-box-padding-block-end-md:var(--p-space-200);--pc-box-padding-inline-start-md:var(--p-space-200);--pc-box-padding-inline-end-md:var(--p-space-200)">
        <div class="Polaris-Tabs__Wrapper">
          <div class="Polaris-Tabs__ButtonWrapper">
            <ul class="Polaris-Tabs" data-tabs-focus-catchment="true" role="tablist">
              <li class="Polaris-Tabs__TabContainer" role="presentation">
                <button id="all-customers-1" class="Polaris-Tabs__Tab Polaris-Tabs__Tab--active" aria-label="All customers" role="tab" type="button" aria-controls="all-customers-content-1" tabindex="0" aria-selected="true">
                  <div class="Polaris-InlineStack" style="--pc-inline-stack-align: center; --pc-inline-stack-block-align: center; --pc-inline-stack-wrap: nowrap; --pc-inline-stack-gap-xs: var(--p-space-200);">
                    <span class="Polaris-Text--root Polaris-Text--bodySm Polaris-Text--medium">Authentication</span>
                  </div>
                </button>
              </li>
              <li class="Polaris-Tabs__TabContainer" role="presentation">
                <button id="accepts-marketing-1" onclick="redirectToConfiguration()" class="Polaris-Tabs__Tab" role="tab" type="button" aria-controls="accepts-marketing-content-1" tabindex="-1" aria-selected="false">
                  <div class="Polaris-InlineStack" style="--pc-inline-stack-align: center; --pc-inline-stack-block-align: center; --pc-inline-stack-wrap: nowrap; --pc-inline-stack-gap-xs: var(--p-space-200);">
                    <span class="Polaris-Text--root Polaris-Text--bodySm Polaris-Text--medium">Configuration</span>
                  </div>
                </button>
              </li>
            </ul>
          </div>
        </div>
      </div>
    </div>
    <div data-portal-id="popover-:r1:">
    </div>
    <!-- endtabs -->
    <!-- Card  -->
    <div class="Polaris-ShadowBevel Polaris-ShadowBevel-xs " style="--pc-shadow-bevel-z-index: 32; --pc-shadow-bevel-content-xs: &quot;&quot;; --pc-shadow-bevel-box-shadow-xs: var(--p-shadow-100); --pc-shadow-bevel-border-radius-xs: var(--p-border-radius-300); height: 130px; overflow-y: auto;">
      <div class="Polaris-Box" style="--pc-box-background:var(--p-color-bg-surface);--pc-box-min-height:100%;--pc-box-overflow-x:clip;--pc-box-overflow-y:clip;--pc-box-padding-block-start-xs:var(--p-space-400);--pc-box-padding-block-end-xs:var(--p-space-400);--pc-box-padding-inline-start-xs:var(--p-space-400);--pc-box-padding-inline-end-xs:var(--p-space-400)">
        <div class="Polaris-SettingAction">
          <div class="Polaris-SettingAction__Setting">
            <div class="Polaris-InlineStack" style="--pc-inline-stack-wrap:wrap;--pc-inline-stack-gap-xs:var(--p-space-400)">
              <div class="Polaris-BlockStack" style="--pc-block-stack-order:column;--pc-block-stack-gap-xs:var(--p-space-100)">
                <h2 class="Polaris-Text--root Polaris-Text--headingSm">GyataGPT App</h2>
                <span class="Polaris-Text--root Polaris-Text--subdued" id="accountStatus">No account connected</span>
              </div>
            </div>
          </div>
          <div class="Polaris-SettingAction__Action">
            <button class="Polaris-Button Polaris-Button--pressable Polaris-Button--variantPrimary Polaris-Button--sizeMedium Polaris-Button--textAlignCenter" type="submit" name="disconnect_account" id="connectButton">
              <span class="">Connect</span>
            </button>
          </div>
        </div>
        <div class="Polaris-Box" style="--pc-box-padding-block-start-xs: var(--p-space-400);" id="termsAndConditions">
          <p>By clicking <strong>Connect</strong>, you agree to accept GyataGPT App’s<!-- --> <a class="Polaris-Link" href="https://www.gyata.ai/terms-of-service/" data-polaris-unstyled="true" target="_blank">terms and conditions</a>. You’ll be conected to GyataGPT WebApp.</p>
        </div>
      </div>
    </div>
    <!-- endCards  -->
    <!-- TnC -->
    <!-- <div class="Polaris-Page__Content">
    <div class="Polaris-Layout">
      <div class="Polaris-Layout__AnnotatedSection">
        <div class="Polaris-Layout__AnnotationWrapper">
          <div class="Polaris-Layout__Annotation">
            <div class="Polaris-TextContainer Polaris-TextContainer--spacingTight">
              <h2 class="Polaris-Text--root Polaris-Text--headingMd" id="tnc">Terms and Conditions</h2>
            </div>
          </div>
          <div class="Polaris-Layout__AnnotationContent">
            <div class="Polaris-LegacyCard">
              <div class="Polaris-LegacyCard__Section Polaris-LegacyCard__FirstSectionPadding Polaris-LegacyCard__LastSectionPadding">
                <a class="Polaris-Link" href="https://www.gyata.ai/terms-of-service/" data-polaris-unstyled="true" target="_blank">GyataGPT Terms and Conditions page</a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div> -->
<!-- endTnC -->
<!--  -->
  </div>
</div>

<!-- footer -->
<div class="Polaris-FooterHelp">
  <div class="Polaris-FooterHelp__Text">Need help? Visit our <!-- -->
    <a class="Polaris-Link" href="https://gyatagpt.ai/contact-us/" data-polaris-unstyled="true" target="_blank">support page.</a>
  </div>
</div>
<!--  endfooter -->
<script src="./assets/js/jquery-3.6.0.min.js"></script>
<script>
  function setConnectedState() {
    accountStatus.textContent = 'Account connected';
    connectButton.textContent = 'Disconnect';
    connectButton.classList.add('Polaris-Button--toneCritical');
    // termsAndConditions.style.display = 'none';
    termsAndConditions.innerHTML = '<p>Explore more features and functionalities on <!-- --><a class="Polaris-Link" href="<?php echo WEB_APP_URL_DEV ?>/dashboard" data-polaris-unstyled="true" target="_blank">GyataGPT</a>.</p>';
  }

  function setDisconnectedState() {
    accountStatus.textContent = 'No account connected';
    connectButton.textContent = 'Connect';
    connectButton.classList.remove('Polaris-Button--toneCritical');
    // termsAndConditions.style.display = 'block';
    termsAndConditions.innerHTML = `<p>By clicking <strong>Connect</strong>, you agree to accept GyataGPT App’s<!-- --> <a class="Polaris-Link" href="https://www.gyata.ai/terms-of-service/" data-polaris-unstyled="true" target="_blank">terms and conditions</a>. You’ll be conected to GyataGPT WebApp.</p>`;
  }

  function disconnectAccount() {
    const formData = new FormData();
    formData.append('disconnect_account', 'true');
    formData.append('shop', "<?php echo $_REQUEST['shop']; ?>");

    fetch('events.php', {
      method: 'POST',
      body: formData,
    })
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        setDisconnectedState();
        location.reload();
      } else {
        console.error('Disconnect failed:', data.message);
      }
    })
    .catch(error => {
      console.error('Disconnect error:', error);
    });
  }

  function ConnectAccount() {
    window.open("<?php echo WEB_APP_URL_DEV ?>?callbackURL=<?php echo GYATAAI_APP_URL ?>/configuration2.php&storeType=shopify&storeUrl=https://<?php echo $shop_url ?>&accessToken=<?php echo $access_token ?>&storefront_access_token=<?php echo $sfat ?>&storeName=<?php echo $shop_name ?>&api_version=<?php echo API_VERSION ?>", '_blank');
    location.reload();
  }
  
  // <!-- JavaScript to handle tab changes -->
  function redirectToConfiguration() {
    window.location.href = './configuration2.php?shop=<?php echo $shop_url;?>';
    document.getElementById('connect-modal').hide();
  }
  
  document.addEventListener('DOMContentLoaded', function () {
    const accountStatus = document.getElementById('accountStatus');
    const termsAndConditions = document.getElementById('termsAndConditions');
    const connectButton = document.getElementById('connectButton');
    var accountConnected = "<?php echo $accountConnected; ?>";

    if (accountConnected === "connected") {
      setConnectedState();
    } else {
      setDisconnectedState();
    }

    connectButton.addEventListener('click', function (event) {
      event.preventDefault();

      if (accountConnected === "connected") {
        document.getElementById('disconnect-modal').show();
      } else {
        document.getElementById('connect-modal').show();
      }
    });
  });

</script>


