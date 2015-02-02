<?php 
require "../class/Tools.php";
require "../class/phpclient/Validation.php";

$shortopts  = "";
$shortopts .= "e:";

$options = getopt($shortopts);

if (isset($options['e'])) {
    switch ($options['e']) {
        case "icml":
            runICMLExport();
            break;
        case "history":
            runOrderHistory();
            break;
        case "export":
            runExport();
            break;
    }
}

function runICMLExport()
{
    $db = new Query();
    $icml = new Icml();
    $icml->generate($db->getCategories(), $db->getOffers());
};

function runOrderHistory()
{   
    $db = new Query();
    $PHPShopBase = new PHPShopBase("../../../inc/config.ini");
    $PHPShopModules = new PHPShopModules("../../../modules/");
    $PHPShopOrm = new PHPShopOrm($PHPShopModules->getParam("base.retailcrm.retailcrm_system"));
    $data = $PHPShopOrm->select();
    
    @extract($data);
    $value = Tools::iconvArray(unserialize($value));
    
    $api = new ApiHelper($value["url"], $value["key"]);
    $history = $api->orderHistory();
    var_dump($history);
    $ids = $db->updateOrders($history);
    if (count($ids) > 0) {
        $api->orderFixExternalIds($ids);
    }
};

function runExport()
{
    $valid = new Validation();
    $db = new Query();
    $customers = $valid->customersCheck($db->getCustomers());
    $orders = $valid->ordersCheck($db->getOrders($customers));

    $PHPShopBase = new PHPShopBase("../../../inc/config.ini");
    $PHPShopModules = new PHPShopModules("../../../modules/");
    $PHPShopOrm = new PHPShopOrm($PHPShopModules->getParam("base.retailcrm.retailcrm_system"));
    $data = $PHPShopOrm->select();

    @extract($data);
    $value = Tools::iconvArray(unserialize($value));

    $api = new ApiHelper($value["url"], $value["key"]);
    $api->processExport($customers, $orders);
};


?>