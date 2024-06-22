<?php
include('./inc/DB.class.php');
include('./inc/functions.php');
include('./constant.php');
$db = new DB();
$table = 'stores_gyata';

if (isset($_REQUEST["application_id"]) && isset($_REQUEST["widget_script"])) {
  $data = array(
    'application_id' => $_REQUEST["application_id"],
    'widget_script' => $_REQUEST["widget_script"]
  );
  $conditions = array(
    'shop_url' => $_REQUEST['shop'],
    'status' => 1
  );
  $row = $db->update($table, $data, $conditions);
}

$conditions['return_type'] = 'single';
$conditions['where'] = array('shop_url' => $_REQUEST['shop']);
$get_single_result = $db->getRows($table, $conditions);
$application_id = $get_single_result['application_id'];
$widget_script = $get_single_result['widget_script'];
$store_url = $_REQUEST['shop'];
?>

<head>
  <meta name="shopify-api-key" content="<?php echo SHOPIFY_API_KEY; ?>" />
  <script src="https://cdn.shopify.com/shopifycloud/app-bridge.js"></script>
  <!-- <script src="./assets/css/polaris.js"></script> -->
  <link href="./assets/css/polaris.css" rel="stylesheet">
</head>

<div class="Polaris-Page">
    <div class="Polaris-Box" style="--pc-box-padding-block-start-xs:var(--p-space-400);--pc-box-padding-block-start-md:var(--p-space-600);--pc-box-padding-block-end-xs:var(--p-space-400);--pc-box-padding-block-end-md:var(--p-space-600);--pc-box-padding-inline-start-xs:var(--p-space-400);--pc-box-padding-inline-start-sm:var(--p-space-0);--pc-box-padding-inline-end-xs:var(--p-space-400);--pc-box-padding-inline-end-sm:var(--p-space-0);position:relative">
        <!-- tabs -->
        <div class="Polaris-Tabs__Outer">
            <div class="Polaris-Box" style="--pc-box-padding-block-start-md:var(--p-space-200);--pc-box-padding-block-end-md:var(--p-space-200);--pc-box-padding-inline-start-md:var(--p-space-200);--pc-box-padding-inline-end-md:var(--p-space-200)">
                <div class="Polaris-Tabs__Wrapper">
                    <div class="Polaris-Tabs__ButtonWrapper">
                        <ul class="Polaris-Tabs" data-tabs-focus-catchment="true" role="tablist">
                        <li class="Polaris-Tabs__TabContainer" role="presentation">
                            <button id="all-customers-1" onclick="redirectToDashboard()" class="Polaris-Tabs__Tab" aria-label="All customers" role="tab" type="button" aria-controls="all-customers-content-1" tabindex="0" aria-selected="true">
                            <div class="Polaris-InlineStack" style="--pc-inline-stack-align: center; --pc-inline-stack-block-align: center; --pc-inline-stack-wrap: nowrap; --pc-inline-stack-gap-xs: var(--p-space-200);">
                                <span class="Polaris-Text--root Polaris-Text--bodySm Polaris-Text--medium">Authentication</span>
                            </div>
                            </button>
                        </li>
                        <li class="Polaris-Tabs__TabContainer" role="presentation">
                            <button id="accepts-marketing-1" class="Polaris-Tabs__Tab Polaris-Tabs__Tab--active" role="tab" type="button" aria-controls="accepts-marketing-content-1" tabindex="-1" aria-selected="false">
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
        <div class="Polaris-ShadowBevel" style="--pc-shadow-bevel-z-index: 32; --pc-shadow-bevel-content-xs: &quot;&quot;; --pc-shadow-bevel-box-shadow-xs: var(--p-shadow-100); --pc-shadow-bevel-border-radius-xs: var(--p-border-radius-300); height: 250px">
            <div class="Polaris-Box" style="--pc-box-background:var(--p-color-bg-surface);--pc-box-min-height:100%;--pc-box-overflow-x:clip;--pc-box-overflow-y:clip;--pc-box-padding-block-start-xs:var(--p-space-400);--pc-box-padding-block-end-xs:var(--p-space-400);--pc-box-padding-inline-start-xs:var(--p-space-400);--pc-box-padding-inline-end-xs:var(--p-space-400)">
                <div class="">
                    <div class="Polaris-Labelled__LabelWrapper">
                        <div class="Polaris-Label">
                        <label id="application_idLabel" for="application_id" class="Polaris-Label__Text">Application ID:</label>
                        </div>
                    </div>
                    <div class="Polaris-Connected">
                        <div class="Polaris-Connected__Item Polaris-Connected__Item--primary">
                            <div class="Polaris-TextField Polaris-TextField--hasValue">
                                <input id="application_id" autocomplete="off" class="Polaris-TextField__Input" type="password" aria-labelledby="application_idLabel" aria-invalid="false" data-1p-ignore="true" data-lpignore="true" data-form-type="other" name="application_id" value="<?php echo $application_id;?>" readonly>
                                <!-- placeholder="Please enter Application ID"  -->
                                <div class="Polaris-TextField__Backdrop">
                                </div>
                            </div>
                        </div>
                        <button onclick="visiblityFunction()" class="Polaris-Button Polaris-Button--pressable Polaris-Button--variantPrimary Polaris-Button--sizeMedium Polaris-Button--textAlignCenter" type="button">
                            <span class=""><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" style="height: 20px; fill: white;"><path fill-rule="evenodd" d="M13 10a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm-1.5 0a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0Z"></path><path fill-rule="evenodd" d="M10 4c-2.476 0-4.348 1.23-5.577 2.532a9.266 9.266 0 0 0-1.4 1.922 5.98 5.98 0 0 0-.37.818c-.082.227-.153.488-.153.728s.071.501.152.728c.088.246.213.524.371.818.317.587.784 1.27 1.4 1.922 1.229 1.302 3.1 2.532 5.577 2.532 2.476 0 4.348-1.23 5.577-2.532a9.265 9.265 0 0 0 1.4-1.922 5.98 5.98 0 0 0 .37-.818c.082-.227.153-.488.153-.728s-.071-.501-.152-.728a5.984 5.984 0 0 0-.371-.818 9.269 9.269 0 0 0-1.4-1.922c-1.229-1.302-3.1-2.532-5.577-2.532Zm-5.999 6.002v-.004c.004-.02.017-.09.064-.223a4.5 4.5 0 0 1 .278-.608 7.768 7.768 0 0 1 1.17-1.605c1.042-1.104 2.545-2.062 4.487-2.062 1.942 0 3.445.958 4.486 2.062a7.77 7.77 0 0 1 1.17 1.605c.13.24.221.447.279.608.047.132.06.203.064.223v.004c-.004.02-.017.09-.064.223a4.503 4.503 0 0 1-.278.608 7.768 7.768 0 0 1-1.17 1.605c-1.042 1.104-2.545 2.062-4.487 2.062-1.942 0-3.445-.958-4.486-2.062a7.766 7.766 0 0 1-1.17-1.605 4.5 4.5 0 0 1-.279-.608c-.047-.132-.06-.203-.064-.223Z"></path></svg></span>
                        </button>
                    </div>
                </div><br>
                <!--  -->
                <div class="">
                    <div class="Polaris-Labelled__LabelWrapper">
                        <div class="Polaris-Label">
                        <label id="embed_scriptLabel" for="embed_script" class="Polaris-Label__Text">
                            <span class="Polaris-Text--root Polaris-Text--bodyMd">Embed Script:</span>
                        </label>
                        </div>
                    </div>
                    <div class="Polaris-Connected">
                        <div class="Polaris-Connected__Item Polaris-Connected__Item--primary">
                            <div class="Polaris-TextField Polaris-TextField--hasValue Polaris-TextField--multiline">
                                <textarea id="embed_script" autocomplete="off" class="Polaris-TextField__Input" type="text" rows="3" aria-labelledby="embed_scriptLabel" aria-invalid="false" aria-multiline="true" data-1p-ignore="true" data-lpignore="true" data-form-type="other" readonly><?php echo $widget_script; ?></textarea>
                                <div class="Polaris-TextField__Backdrop">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!--  -->
                <input type="hidden" name="shop_url" id="shop_url" value="<?php echo $store_url;?>"/>
                <!--  -->
                <br>
                <span style="display:flex;">
                    <button class="Polaris-Button Polaris-Button--pressable Polaris-Button--variantPrimary Polaris-Button--sizeMedium Polaris-Button--textAlignCenter" type="button" onclick="copyEmbedScript()">
                    <span class="">Copy Script</span>
                    </button>
                    <div class="response_message" style="width: 90%; text-align: center;">
                        Copy the script code and paste it into the custom liquid section of the header, to display the Gyata chatbot on the storefront.
                    </div>
                    <!-- <div class="response_message" style="width: 90%; text-align: center;">
                        <?php
                            // // $url = CB_TENANT_SERVER_URL.'/event/list?per_page=10';
                            // // $result = check_key_verification($url, $application_id);
                            // if($application_id =='' || $application_id == null){
                            // echo "Please enter application id";
                            // }else{
                            // // echo $result;
                            // echo $application_id;
                            // }
                        ?>
                    </div>  -->
                </span>
            <!--  -->
            </div>
        </div>
        <!-- end  Crds  -->
    </div>
</div>

<!-- footer -->
<div class="Polaris-FooterHelp">
  <div class="Polaris-FooterHelp__Text">Need help? Visit our <!-- -->
    <a class="Polaris-Link" href="https://www.mylivecart.com/contact-us/" data-polaris-unstyled="true" target="_blank">support page.</a>
  </div>
</div>
<!--  endfooter -->

<script src="./assets/js/jquery-3.6.0.min.js"></script>
<script src="assets/js/script.js"></script>
<script>
    function redirectToDashboard() {
        window.location.href = './dashboard2.php?shop=<?php echo $store_url;?>&return=1';
    }

    function visiblityFunction() {
    var x = document.getElementById("application_id");
    if (x.type === "password") {
        x.type = "text";
    } else {
        x.type = "password";
    }
    }

    function copyEmbedScript() {
        var embedScriptTextarea = document.getElementById("embed_script");
        embedScriptTextarea.select();
        embedScriptTextarea.setSelectionRange(0, 99999); // For mobile devices
        document.execCommand("copy");
        alert("Embed Script copied to clipboard!");
    }
</script>
