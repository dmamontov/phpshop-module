<?
function write_orders($obj, $row, $rout) {
        require "./phpshop/modules/retailcrm/class/Tools.php";
        require "./phpshop/modules/retailcrm/class/phpclient/Validation.php";

        runOrder($_POST['ouid'], 'cart');
}

function runOrder($ouid, $type)
{
    $productsOrm = new PHPShopOrm($GLOBALS['SysValue']['base']['products']);
    $products = $productsOrm->select(array('*'), array('enabled'=>"='1'"),false,array('limit'=>1000000));
    $tmpProduct = array();
    foreach ($products as $product) {
        $tmpProduct[$product["id"]] = $product['uid'];
    }
    
    $PHPShopModules = new PHPShopModules("./phpshop/modules/");
    $PHPShopOrm = new PHPShopOrm($PHPShopModules->getParam("base.retailcrm.retailcrm_system"));
    $data = $PHPShopOrm->select();

    @extract($data);
    $value = Tools::iconvArray(unserialize($value));

    ini_set('memory_limit', '-1');
    $corders = array();

    if ($type == 'cart' && !is_null($ouid)) {
        $orderOrm = new PHPShopOrm($GLOBALS['SysValue']['base']['table_name1']);
        $order = $orderOrm->select(array('*'), array("uid" => "='" . $ouid ."'"), false);

        $order["status"] = unserialize($order["status"]);
        $order["orders"] = unserialize($order["orders"]);
        
        $order = Tools::iconvArray($order);

        $persone = isset($order["orders"]["Person"]["var"]) ? $order["orders"]["Person"]["var"] : array();
        
        $phone = "";
        if (isset($order["orders"]["Person"])) {
            $phone = (!empty($order["orders"]["Person"]["cellphone"])) ?
            $order["orders"]["Person"]["cellphone"] :
            $order["orders"]["Person"]["tel_code"] . $order["orders"]["Person"]["tel_name"];
        }
        $tmp = array(
            "number"          => $order["uid"],
            "externalId"      => $order["id"] . time(),
            "createdAt"       => date("Y-m-d H:i:s", $order["datas"]),
            "discount"        => isset($order["orders"]["Person"]["discount"]) ? $order["orders"]["Person"]["discount"] : 0,
            "phone"           => $phone,
            "email"           => isset($order["orders"]["Person"]["mail"]) ? $order["orders"]["Person"]["mail"] : "",
            "customerComment" => $order["status"]["maneger"],
        
            "contragentType"  => "individual",
            "orderType"       => "eshop-individual",
        
            "orderMethod"     => "shopping-cart",
            "delivery"        => array(
                "address" => array(
                    "region"       => isset($persone["region"]) ? $persone["region"] : "",
                    "city"         => isset($persone["city"]) ? $persone["city"] : "",
                    "street"       => isset($persone["street"]) ? $persone["street"] : "",
                    "building"     => !empty($persone["building"]) ? $persone["building"] : $persone["corpus"],
                    "flat"         => !empty($persone["office"]) ? $persone["office"] : $persone["appartment"],
                    "intercomCode" => isset($persone["domofon"]) ? $persone["domofon"] : "",
                    "floor"        => is_int($persone["floor"]) ? $persone["floor"] : "",
                    "block"        => is_int($persone["entrance"]) ? $persone["entrance"] : "",
                    "house"        => isset($persone["house"]) ? $persone["house"] : "",
                    "metro"        => isset($persone["metro"]) ? $persone["metro"] : "",
                    "notes"        => ($persone["elevator"] > 0) ? "Этаж: " . $persone["elevator"] : "",
                )
            ),
        );
        
        if (!empty($order["orders"]["Person"]["order_metod"]) && isset($value["payment"][$order["orders"]["Person"]["order_metod"]])) {
            $tmp["paymentType"] = $value["payment"][$order["orders"]["Person"]["order_metod"]];
        }
        if ($order["statusi"] == 0) {
            $tmp["status"] = $value["status"]["new"];
        } elseif (!empty($order["statusi"]) && isset($value["status"][$order["statusi"]])) {
            $tmp["status"] = $value["status"][$order["statusi"]];
        }
        
        if (isset($order["orders"]["Cart"]["cart"]) && count($order["orders"]["Cart"]["cart"]) > 0) {
            foreach ($order["orders"]["Cart"]["cart"] as $item) {
                $tmp["items"][] =array(
                    "initialPrice" => $item["price"],
                    "productId"    => $item["id"],
                    "productName"  => $item["name"],
                    "quantity"     => $item["num"],
                    "xmlId"        => isset($tmpProduct[$item["id"]]) ? $tmpProduct[$item["id"]] : "",
                );
            }
        }
        
        if (!empty($order["orders"]["Person"]["dostavka_metod"]) && isset($value["delivery"][$order["orders"]["Person"]["dostavka_metod"]])) {
            $tmp["delivery"]["code"] = $value["delivery"][$order["orders"]["Person"]["dostavka_metod"]];
        }
        
        if ($order["user"] != 0) {
            $tmp["customerId"] = $order["user"];
        } else {
            $tmp["customerId"] = uniqid(time());
            if (!empty($persone["org_name"]) || !empty($persone["org_inn"]) || !empty($persone["org_kpp"])) {
                $tmp["contragentType"] = "legal-entity";
                $tmp["orderType"] = "eshop-legal";
            }
            $tmp = array_merge($tmp, Tools::explodeFio($persone["name_person"]));
            $tmp["legalName"] = isset($persone["org_name"]) ? $persone["org_name"] : "";
            $tmp["INN"] = isset($persone["org_inn"]) ? $persone["org_inn"] : "";
            $tmp["KPP"] = isset($persone["org_kpp"]) ? $persone["org_kpp"] : "";
        }
        $corders = Tools::clearArray($tmp);
    }
    $valid = new Validation();
    $order = $valid->orderCheck($corders);

    $api = new ApiHelper($value["url"], $value["key"]);
    $api->processOrders(array($order));
}

$addHandler = array
(
    'sms' => 'write_orders'
);
?>