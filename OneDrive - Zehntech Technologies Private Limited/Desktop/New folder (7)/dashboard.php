<meta name="viewport" content="width=device-width, initial-scale=1">
<?php
header("Access-Control-Allow-Origin: *"); 
ini_set('display_errors', '1');

include('./constant.php');
include('./inc/functions.php');

if (!empty($_REQUEST['return']) && $_REQUEST['return'] == 1) {
  include('./inc/DB.class.php');
}
$db = new DB();
$shop_url = $_GET['shop'];
$table = 'stores_gyata';

$conditions['return_type'] = 'single';
$conditions['where'] = array('shop_url' => $_REQUEST['shop']);
$get_single_result = $db->getRows($table, $conditions);
$access_token = $get_single_result['access_token'];
$sfat = $get_single_result['storefront_access_token'];
$application_id = $get_single_result['application_id'];
$widget_script = $get_single_result['widget_script'];
// $application_id = '';

$shop_url = $_REQUEST['shop'];

$pos = strpos($shop_url, ".myshopify.com");
$storeName = substr($shop_url, 0, $pos);

$shop_details = shopify_call($access_token, $shop_url, "/admin/api/" . API_VERSION . "/shop.json", null, 'GET');
$shop_details = json_decode($shop_details['response'], true);

$shop_name = $shop_details['shop']['name'];

$connect_btn = '<button class="btn btn-neutral btn-sm" onclick="toggleConnection()" id="Conbtn">' . (($application_id != '' && $widget_script != '') ? "Disconnect" : "Connect") . '</button>';

?>

<head>
  <meta name="shopify-api-key" content="<?php echo SHOPIFY_API_KEY; ?>" />
  <link href="https://cdn.jsdelivr.net/npm/daisyui@4.7.3/dist/full.min.css" rel="stylesheet" type="text/css" />
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="./assets/css/style.css">
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
  <div class="flex justify-start gap-2 my-2">
    <button class="btn btn-active btn-ghost btn-sm">Dashboard</button>
    <button class="btn btn-ghost btn-sm" id="configbutton" onclick="redirectToConfiguration();">Configure</button>
  </div>
    <div class="border rounded max-w-2xl py-4 px-4 bg-gray-100">
      <h2 class="text-lg font-semibold" id="accountStatus"><span class="font-bold"> Welcome back </span> <?php echo $shop_name?> !</h2>
      <br>
    <div class="mt-2">
      <?php echo $connect_btn; ?>
    </div>
    </div>
  </div>

  <dialog id="connect_modal" class="modal">
    <div class="modal-box max-w-2xl">
        <h2 class="font-bold text-lg">Connect Account !</h2>
        <p class="py-4 ">If you already have a secret key, then press the <span class="font-bold">'Have Key?'</span> button. Otherwise, if you want to connect to a new account, press the <span class="font-bold">'Connect'</span> button.</p>
        <div class="modal-action">
          <form method="dialog">
            <button class="btn btn-sm btn-circle btn-ghost absolute right-2 top-2">✕</button>
            <buton class="btn btn-sm btn-neutral " onclick="ConnectAccount()">Connect</buton>
            <button class="btn btn-sm btn-neutral" onclick="redirectToConfiguration()">Have Key ?</button>
          </form>
        </div>
    </div>
  </dialog>

  <dialog id="disconnect_modal" class="modal">
    <div class="modal-box max-w-2xl">
      <h2 class="font-bold text-lg">Disonnect Account !</h2>
      <p>Are you want to disconnect ?</p>
      <div class="modal-action">
        <form method="dialog">
          <button class="btn btn-sm btn-circle btn-ghost absolute right-2 top-2">✕</button>
          <buton class="btn btn-neutral btn-sm" onclick="disconnect()">yes</buton>
          <button class="btn btn-base btn-sm" onclick="closeModal()">No</button>
        </form>
      </div>
    </div>
  </dialog>
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
  //  <!-- Close model  -->
  function closeModal() {
    const connect_modal = document.getElementById("connect_modal");
    const disconnect_modal = document.getElementById("disconnect_modal");

    connect_modal.close();
    disconnect_modal.close();

  }
//  <!-- Function to disconnect Account  -->
  function disconnect() {
    const button = document.getElementById("Conbtn");
    const disconnect_modal = document.getElementById("disconnect_modal");
    const accountStatus = document.getElementById("accountStatus");
    // ----------------------------------------
    const formData = new FormData();
    formData.append('disconnect_account', 'true');
    formData.append('shop', "<?php echo $shop_url; ?>");
    fetch('events.php', {
      method: 'POST',
      body: formData,
    })
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        // Close modal
    disconnect_modal.close();
    // Change button text to Connect
    button.textContent = "Connect";
    accountStatus.textContent = 'No account connected !!';
    termsAndConditions.innerHTML = `<p>By clicking <strong>Connect</strong>, you agree to accept Gyata GPT App’s <a class="link text-blue-500" href="https://www.gyata.ai/terms-of-service/" target="_blank"> terms and conditions </a>. You’ll be conected to Gyata GPT WebApp.</p>`;
    location.reload();
      } else {
        console.error('Disconnect failed:', data.message);
      }
    })
    .catch(error => {
      console.error('Disconnect error:', error);
    });  
  }

  
  function toggleConnection() {
    const button = document.getElementById("Conbtn");
    const accountStatus = document.getElementById("accountStatus");
    const isConnected = button.textContent.trim();
    if (isConnected === "Connect") {
      // Check if user is registered 
      accountStatus.textContent = 'No account connected !!';
      const isRegistered = false; // For demonstration purposes
      
      if (!isRegistered) {
        const connect_modal = document.getElementById("connect_modal");
        connect_modal.showModal();
        
      } else {
        // If registered, change button text to Disconnect
        button.textContent = "Disconnect";
        connect_modal.showModal();
      }
    } else {
      // Disconnect logic
      const disconnect_modal = document.getElementById("disconnect_modal");
      disconnect_modal.showModal();
    }
  }

  function ConnectAccount() {
    // window.open("<?php echo WEB_APP_URL_DEV ?>/store-connect?callbackURL=<?php echo GYATAAI_APP_URL ?>/callback.php&storeType=shopify&storeUrl=https://<?php echo $shop_url ?>&accessToken=<?php echo $access_token ?>&storefront_access_token=<?php echo $sfat ?>&storeName=<?php echo $shop_name ?>&api_version=<?php echo API_VERSION ?>", '_blank');
    window.open("<?php echo WEB_APP_URL_DEV ?>/store-connect?callbackURL=<?php echo GYATAAI_APP_URL ?>/callback.php&storeType=shopify&storeUrl=https://<?php echo $shop_url ?>&accessToken=<?php echo $access_token ?>&storefront_access_token=<?php echo $sfat ?>&storeName=<?php echo $shop_name ?>&api_version=<?php echo API_VERSION ?>", '_blank');
    location.reload();
  }

  function redirectToConfiguration() {
    window.location.href = './configuration.php?shop=<?php echo $shop_url;?>';
   
  }
</script>

