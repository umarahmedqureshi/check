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
  <link href="https://cdn.jsdelivr.net/npm/daisyui@4.7.3/dist/full.min.css" rel="stylesheet" type="text/css" />
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="./assets/css/style.css">
  <link href="../assets/css/polaris.css" rel="stylesheet">
</head>

<body class="bg-white">
<nav class="bg-dark navbar bg-base-300 border-b-2">
    <div class="flex-1">
      <a class="btn btn-ghost text-xl">Gyata GPT</a>
    </div>
    <div class="flex-none">
    <button class="btn btn-square btn-ghost">
      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="inline-block w-5 h-5 stroke-current"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h.01M12 12h.01M19 12h.01M6 12a1 1 0 11-2 0 1 1 0 012 0zm7 0a1 1 0 11-2 0 1 1 0 012 0zm7 0a1 1 0 11-2 0 1 1 0 012 0z"></path></svg>
    </button>
    </div>
  </nav>
  
<div class="container mx-auto">
  <div class="flex justify-start gap-2 mt-2">
    <button class="btn btn-ghost btn-sm" onclick="redirectToDashboard()">Dashboard</button>
    <button class="btn btn-active btn-ghost btn-sm">Configure</button>
  </div>

</div>
  <div class="container mx-auto">
    <div class="border rounded w-full py-2 px-4 bg-gray-100 mt-2">
      <label for="appId" class="label font-bold text-sm">Application Id</label>
      <div class="join">
        <input id="appId" class="input input-bordered input-sm w-full max-w-lg rounded-r-none" type="password" value="<?php echo $application_id; ?>" readonly>
        <button id="button" class="btn btn-active btn-ghost btn-sm rounded-l-none" type="button" onclick="showhide()">
          <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
            <path d="M12.015 7c4.751 0 8.063 3.012 9.504 4.636-1.401 1.837-4.713 5.364-9.504 5.364-4.42 0-7.93-3.536-9.478-5.407 1.493-1.647 4.817-4.593 9.478-4.593zm0-2c-7.569 0-12.015 6.551-12.015 6.551s4.835 7.449 12.015 7.449c7.733 0 11.985-7.449 11.985-7.449s-4.291-6.551-11.985-6.551zm-.015 3c-2.209 0-4 1.792-4 4 0 2.209 1.791 4 4 4s4-1.791 4-4c0-2.208-1.791-4-4-4z" />
          </svg>
        </button>
      </div>
      <br>

      <label for="embed_script" class="label font-bold text-sm mt-4">Embed Script</label>
      <div class="join" style="width: 100%;">
        <textarea id="embed_script" class="textarea w-full" readonly style="border: 2px solid;"><?php echo $widget_script; ?></textarea>
        <button id="copy_button" class="btn btn-active btn-ghost btn-sm rounded-l-none" type="button" onclick="copyEmbedScript()">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24">
          <path d="M16 1H4c-1.1 0-2 .9-2 2v14h2V3h12V1zm-1 4H8c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h10c1.1 0 2-.9 2-2V9l-5-4zM8 21V7h6v5h5v9H8z"/>
        </svg>
        </button>
      </div>
      <br>


      <div class="mt-4">
        <input class="input input-bordered input-xs w-full max-w-md" type="hidden" name="shop_url" id="shop_url" value="<?php echo $store_url; ?>" />
        <button class="btn btn-neutral btn-sm" id="verify_btn">
          Submit
        </button>
      </div>
    </div>
  </div>
  <footer class="footer footer-center p-4 bg-base-300 text-base-content">
  <aside>
  <div class="text-container" id="termsAndConditions">
    <p>Copyright © 2024 - All right reserved by Gyata GPT </p>
      <p><strong>Terms and Conditions</strong>
      <a href="https://www.gyata.ai/terms-of-service/" class="link text-blue-500" target="_blank">Gyata GPT Terms and Conditions page</a>
      </p>    
    </div>
  </aside>
</footer>
</body>

<script>
  const appId = document.getElementById('appId');
  const button1 = document.getElementById('button');
  // const verify_btn  = document.getElementById('verify_btn');

  function showhide() {
    console.log("Show Hide Working...");
    if (appId.type === 'password') {
      appId.type = 'text';
      button.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><path d="M19.604 2.562l-3.346 3.137c-1.27-.428-2.686-.699-4.243-.699-7.569 0-12.015 6.551-12.015 6.551s1.928 2.951 5.146 5.138l-2.911 2.909 1.414 1.414 17.37-17.035-1.415-1.415zm-6.016 5.779c-3.288-1.453-6.681 1.908-5.265 5.206l-1.726 1.707c-1.814-1.16-3.225-2.65-4.06-3.66 1.493-1.648 4.817-4.594 9.478-4.594.927 0 1.796.119 2.61.315l-1.037 1.026zm-2.883 7.431l5.09-4.993c1.017 3.111-2.003 6.067-5.09 4.993zm13.295-4.221s-4.252 7.449-11.985 7.449c-1.379 0-2.662-.291-3.851-.737l1.614-1.583c.715.193 1.458.32 2.237.32 4.791 0 8.104-3.527 9.504-5.364-.729-.822-1.956-1.99-3.587-2.952l1.489-1.46c2.982 1.9 4.579 4.327 4.579 4.327z"/></svg>';
    } else {
      appId.type = 'password';
      button.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><path d="M12.015 7c4.751 0 8.063 3.012 9.504 4.636-1.401 1.837-4.713 5.364-9.504 5.364-4.42 0-7.93-3.536-9.478-5.407 1.493-1.647 4.817-4.593 9.478-4.593zm0-2c-7.569 0-12.015 6.551-12.015 6.551s4.835 7.449 12.015 7.449c7.733 0 11.985-7.449 11.985-7.449s-4.291-6.551-11.985-6.551zm-.015 3c-2.209 0-4 1.792-4 4 0 2.209 1.791 4 4 4s4-1.791 4-4c0-2.208-1.791-4-4-4z"/></svg>';
    }
  }

  function copyEmbedScript() {
    var embedScriptTextarea = document.getElementById("embed_script");
    embedScriptTextarea.select();
    embedScriptTextarea.setSelectionRange(0, 99999); // For mobile devices
    document.execCommand("copy");
    alert("Embed Script copied to clipboard!");
  }
  
  function redirectToDashboard() {
    window.location.href = './dashboard.php?shop=<?php echo $store_url; ?>&return=1';
  }
  
</script>