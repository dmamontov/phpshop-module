<?php
include("../../../class/obj.class.php");
PHPShopObj::loadClass("base");
PHPShopObj::loadClass("system");
PHPShopObj::loadClass("security");
PHPShopObj::loadClass("orm");
PHPShopObj::loadClass("modules");

class Query
{
    private $value;

    public function __construct()
    {
        $PHPShopBase = new PHPShopBase("../../../inc/config.ini");
        $PHPShopModules = new PHPShopModules("../../../modules/");
        $PHPShopOrm = new PHPShopOrm($PHPShopModules->getParam("base.retailcrm.retailcrm_system"));
        $data = $PHPShopOrm->select();

        @extract($data);
        $this->value = Tools::iconvArray(unserialize($value));
    }

    public function getCategories()
    {
        $categories = array();

        $categoryOrm = new PHPShopOrm($GLOBALS['SysValue']['base']['categories']);
        $categorys = Tools::iconvArray($categoryOrm->select(array('*')));

        foreach ($categorys as $category) {
            $categories[] = array(
                "name"     => $category['name'],
                "id"       => (int) $category['id'],
                "parentId" => (int) $category['parent_to']
            );
        }

        return Tools::clearArray($categories);
    }

    public function getOffers()
    {
        $offers = array();

        $productsOrm = new PHPShopOrm($GLOBALS['SysValue']['base']['products']);
        $products = Tools::iconvArray($productsOrm->select(array('*'), array('enabled'=>"='1'"),false,array('limit'=>1000000)));
        $PHPShopSystem = new PHPShopSystem();
        $percent = $PHPShopSystem->getValue('percent');

        foreach ($products as $product) {
            $offers[] = array(
                "id"           => $product["id"],
                "productId"    => $product["id"],
                "quantity"     => $product['items'],
                "categoryId"   => $product['category'],
                "name"         => $product['name'],
                "productName"  => $product['name'],
                "initialPrice" => $product['price']+(($product['price']*$percent)/100),
                "picture"      => $this->value["siteurl"] . substr($product['pic_big'], 1),
                "url"          => $this->value["siteurl"] . "shop/UID_" . $product['id'] . ".html",
                "xmlId"        => $product['uid'],
                "article"      => $product['uid']
            );
        }

        return Tools::clearArray($offers);
    }

    public function getCustomers()
    {
        $customers = array();

        $userOrm = new PHPShopOrm($GLOBALS['SysValue']['base']['shopusers']);
        $users = Tools::iconvArray($userOrm->select(array('*'), array(),false));
        
        foreach ($users as $user) {
            $tmp = array(
                "externalId" => $user["id"],
                "email"      => $user["mail"],
                "phones"     => array(
                    array("number" => $user["tel_code"] . $user["tel"]),
                    array("number" => $user["fax"])
                ),
                "address"    => array(
                    "region"       => $user["region"],
                    "city"         => $user["city"],
                    "street"       => $user["street"],
                    "building"     => !empty($user["building"]) ? $user["building"] : $user["corpus"],
                    "flat"         => !empty($user["office"]) ? $user["office"] : $user["appartment"],
                    "intercomCode" => $user["domofon"],
                    "floor"        => is_int($user["floor"]) ? $user["floor"] : "",
                    "block"        => is_int($user["entrance"]) ? $user["entrance"] : "",
                    "house"        => $user["house"],
                    "metro"        => $user["metro"],
                    "notes"        => ($user["elevator"] > 0) ? "Ğ­Ñ‚Ğ°Ğ¶: " . $user["elevator"] : "",
                    "text"         => $user["adres"]
                ),
                "createdAt"      => date("Y-m-d H:i:s", $user["datas"]),
                "contragentType" => ($user["type"] == "ur") ? "legal-entity" : "individual",
                "legalName"      => $user["company"],
                "legalAddress"   => $user["ur_address"],
                "INN"            => $user["inn"],
                "OKPO"           => $user["ur_okpo"],
                "KPP"            => $user["kpp"],
                "BIK"            => $user["ur_bik"],
                "bank"           => $user["ur_bank"],
                "corrAccount"    => $user["ur_ks"],
                "bankAccount"    => $user["ur_rs"]
            );
            $tmp = array_merge($tmp, Tools::explodeFio($user["name"]));
            $customers[$user["id"]] = Tools::clearArray($tmp);
        }

        return $customers;
    }

    public function getOrders($customers)
    {
        error_reporting(E_ERROR);
        ini_set('memory_limit', '-1');
        $corders = array();

       if (isset($GLOBALS['SysValue']['base']['returncall'])) {
            $orderOrm = new PHPShopOrm($GLOBALS['SysValue']['base']['returncall']["returncall_jurnal"]);
            $startId = 0;
            $stopId = 4000;
            while (true) {
                $orders = Tools::iconvArray($orderOrm->select(array('*'), array("id" => ">" . $startId . " and id<=" . $stopId), false));
                if (!is_null($orders)) {
                    $startId += 4000;
                    $stopId += 4000;
                } else {
                    break;
                }
                foreach ($orders as $order) {
                    $tmp = array(
                        "number"          => "RC" . $order["id"],
                        "externalId"      => "rc-" . $order["id"],
                        "createdAt"       => date("Y-m-d H:i:s", $order["date"]),
                        "phone"           => $order["tel"],
                        "customerComment" => $order["message"],
        
                        "contragentType"  => "individual",
                        "orderType"       => "eshop-individual",
        
                        "orderMethod"     => "one-click",
                        "status"          => $this->value["status-oneclick"][$order["status"]],
                        "items"        => array(
                            array(
                                "initialPrice" => preg_replace('/[^0-9]/', '', $order["product_price"]),
                                "productId"    => $order["product_id"],
                                "productName"  => $order["product_name"],
                                "quantity"     => 1,
                            )
                        ),
                    );
                    $tmp = array_merge($tmp, Tools::explodeFio($order["name"]));
                    $corders[] = Tools::clearArray($tmp);
                }
            }
        }
        
        if (isset($GLOBALS['SysValue']['base']['oneclick'])) {
            $orderOrm = new PHPShopOrm($GLOBALS['SysValue']['base']['oneclick']["oneclick_jurnal"]);
            $startId = 0;
            $stopId = 4000;
            while (true) {
                $orders = Tools::iconvArray($orderOrm->select(array('*'), array("id" => ">" . $startId . " and id<=" . $stopId), false));
                if (!is_null($orders)) {
                    $startId += 4000;
                    $stopId += 4000;
                } else {
                    break;
                }
                foreach ($orders as $order) {
                    $tmp = array(
                        "number"          => "OC" . $order["id"],
                        "externalId"      => "oc-" . $order["id"],
                        "createdAt"       => date("Y-m-d H:i:s", $order["date"]),
                        "phone"           => $order["tel"],
                        "customerComment" => $order["message"],
                    
                        "contragentType"  => "individual",
                        "orderType"       => "eshop-individual",
                    
                        "orderMethod"     => "one-click",
                        "status"          => $this->value["status-oneclick"][$order["status"]],
                        "items"        => array(
                            array(
                                "initialPrice" => preg_replace('/[^0-9]/', '', $order["product_price"]),
                                "productId"    => $order["product_id"],
                                "productName"  => $order["product_name"],
                                "quantity"     => 1,
                            )
                        ),
                    );
                    $tmp = array_merge($tmp, Tools::explodeFio($order["name"]));
                    $corders[] = Tools::clearArray($tmp);
                }
            }
        }

        $orderOrm = new PHPShopOrm($GLOBALS['SysValue']['base']['table_name1']);
        $startId = 0;
        $stopId = 4000;

        while (true) {
            $orders = $orderOrm->select(array('*'), array("id" => ">" . $startId . " and id<=" . $stopId), false);
            if (!is_null($orders)) {
                $startId += 4000;
                $stopId += 4000;
            } else {
                break;
            }
            foreach ($orders as $order) {
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
                    "externalId"      => $order["id"],
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
                            "notes"        => ($persone["elevator"] > 0) ? "İòàæ: " . $persone["elevator"] : "",
                        )
                    ),
                );
            
                if (!empty($order["orders"]["Person"]["order_metod"]) && isset($this->value["payment"][$order["orders"]["Person"]["order_metod"]])) {
                    $tmp["paymentType"] = $this->value["payment"][$order["orders"]["Person"]["order_metod"]];
                }
                if ($order["statusi"] == 0) {
                    $tmp["status"] = $this->value["status"]["new"];
                } elseif (!empty($order["statusi"]) && isset($this->value["status"][$order["statusi"]])) {
                    $tmp["status"] = $this->value["status"][$order["statusi"]];
                }

                if (isset($order["orders"]["Cart"]["cart"]) && count($order["orders"]["Cart"]["cart"]) > 0) {
                    foreach ($order["orders"]["Cart"]["cart"] as $item) {
                        $tmp["items"][] =array(
                            "initialPrice" => $item["price"],
                            "productId"    => $item["id"],
                            "productName"  => $item["name"],
                            "quantity"     => $item["num"],
                        );
                    }
                }

                if (!empty($order["orders"]["Person"]["dostavka_metod"]) && isset($this->value["delivery"][$order["orders"]["Person"]["dostavka_metod"]])) {
                    $tmp["delivery"]["code"] = $this->value["delivery"][$order["orders"]["Person"]["dostavka_metod"]];
                }

                if ($order["user"] != 0 && isset($customers[$order["user"]])) {
                    $tmpCustomers = $customers[$order["user"]];
                    $tmp["customerId"] = $order["user"];
                    unset(
                        $tmpCustomers["externalId"],
                        $tmpCustomers["email"],
                        $tmpCustomers["phones"],
                        $tmpCustomers["address"],
                        $tmpCustomers["createdAt"]
                    );
                    $tmp = array_merge($tmp, $tmpCustomers);
                } else {
                    if (!empty($persone["org_name"]) || !empty($persone["org_inn"]) || !empty($persone["org_kpp"])) {
                        $tmp["contragentType"] = "legal-entity";
                        $tmp["orderType"] = "eshop-legal";
                    }
                    $tmp = array_merge($tmp, Tools::explodeFio($persone["name_person"]));
                    $tmp["legalName"] = isset($persone["org_name"]) ? $persone["org_name"] : "";
                    $tmp["INN"] = isset($persone["org_inn"]) ? $persone["org_inn"] : "";
                    $tmp["KPP"] = isset($persone["org_kpp"]) ? $persone["org_kpp"] : "";
                }
                $corders[] = Tools::clearArray($tmp);
            }
        }

        return $corders;
    }

    public function updateOrders($orders)
    {
        $fixId = array();
        $productsOrm = new PHPShopOrm($GLOBALS['SysValue']['base']['products']);
        $tmpproducts = Tools::iconvArray($productsOrm->select(array('*'), array('enabled'=>"='1'"),false,array('limit'=>1000000)));
        $products = array();
        foreach ($tmpproducts as $product) {
            $products[$product["id"]] = array(
                'user' => $product["user"],
                'uid' => $product["uid"]
            );
        }
        $orders = Tools::iconvArray($orders, "UTF-8", "WINDOWS-1251");

        foreach ($orders as $order) {
            if (isset($order["created"])) {
                $arr = array();
                if (isset($order["customer"]["externalId"])) {
                    $userOrm = new PHPShopOrm($GLOBALS['SysValue']['base']['shopusers']);
                    $users = $userOrm->select(array('*'), array("id" => "=" . $order["customer"]["externalId"]),false);
                    if (is_null($users)) {
                        $order["customer"]["externalId"] = 0;
                    }
                } else {
                    $order["customer"]["externalId"] = 0;
                }

                $arr[] = "datas='" . (isset($order["createdAt"]) ? strtotime($order["createdAt"]) : time()) ."'";
                $arr[] = "uid='" . (isset($order["number"]) ? $order["number"] : $order["id"]) ."'";
                $arr[] = "user='" . $order["customer"]["externalId"] ."'";
                $arr[] = "magaz=1";
                if ($search = array_search($order["status"], $this->value["status"]) && $order["status"] != 'new') {
                    $arr[] = "statusi=" . $search;
                } else {
                    $arr[] = "statusi=0";
                }

                $status = array(
                    'maneger' => '',
                    'time' => date("d-m-Y H:i", isset($order["createdAt"]) ? strtotime($order["createdAt"]) : time())
                );
                if (isset($order["customerComment"])) {
                    $status['maneger'] = $order["customerComment"];
                }
                $arr[] = "status='" . serialize($status) . "'";
                
                $name = implode(
                        ' ',
                        array(
                            isset($order["lastName"]) ? $order["lastName"]  : '',
                            isset($order["firstName"]) ? $order["firstName"] : '',
                            isset($order["patronymic"]) ? $order["patronymic"] : ''
                        )
                );

                if ($search = array_search($order["delivery"]["code"], $this->value["delivery"])) {
                    $delivery = $search;
                } else {
                    $delivery = "";
                }

                $cart = array(
                    'cart'     => array(),
                    'num'      => 0,
                    'sum'      => 0,
                    'weight'   => 0,
                    'dostavka' => "",
                );
                
                $discount = isset($order["discount"]) ? $order["discount"] : 0;
                
                if (isset($order["discountPercent"])) {
                    $discount += $order["summ"] / 100 * $order["discountPercent"];
                }
                
                foreach ($order["items"] as $item) {
                    if (isset($products[$item["offer"]["externalId"]])) {
                        $cart["num"] += $item["quantity"];
                        $cart["sum"] += $item["initialPrice"];
                        $cart["cart"][$item["offer"]["externalId"]] = array(
                            'id'    => $item["offer"]["externalId"],
                            'name'  => $item["offer"]["name"],
                            'price' => $item["initialPrice"],
                            'uid'   => $products[$item["offer"]["externalId"]]['uid'],
                            'num'   => $item["quantity"],
                            'weight' => '',
                            'ed_izm' => '',
                            'user'   => $products[$item["offer"]["externalId"]]['user']
                        );
                    }
                }

                if ($search = array_search($order["paymentType"], $this->value["payment"])) {
                    $orderMetod = $search;
                } else {
                    $orderMetod = 3;
                }

                $text = "ok";

                $tmpOrders = array(
                    'Cart' => $cart,
                    'Person' => array(
                        'ouid'        => isset($order["number"]) ? $order["number"] : $order["id"],
                        'data'        => isset($order["createdAt"]) ? strtotime($order["createdAt"]) : time(),
                        'time'        => date("h:i a", isset($order["createdAt"]) ? strtotime($order["createdAt"]) : time()),
                        'mail'        => isset($order["email"]) ? $order["email"] : "",
                        'name_person' => $name,
                        'org_name'    => isset($order["legalName"]) ? $order["legalName"] : "",
                        'org_inn'     => isset($order["INN"]) ? $order["INN"] : "",
                        'org_kpp'     => isset($order["KPP"]) ? $order["KPP"] : "",
                        'tel_code'    => "",
                        'tel_name'    => isset($order["phone"]) ? $order["phone"] : "",
                        'cellphone'   => isset($order["phone"]) ? $order["phone"] : "",
                        'adr_name'    => $text,
                        'dostavka_metod' => $delivery,
                        'discount'    => $discount,
                        'user_id'     => $order["customer"]["externalId"] != 0 ? $order["customer"]["externalId"] : "",
                        'dos_ot'      => "",
                        'dos_do'      => "",
                        'order_metod' => $orderMetod,
                        'var'         => array(
                            'ouid'        => isset($order["number"]) ? $order["number"] : $order["id"],
                            'data'        => isset($order["createdAt"]) ? strtotime($order["createdAt"]) : time(),
                            'time'        => date("h:i a", isset($order["createdAt"]) ? strtotime($order["createdAt"]) : time()),
                            'mail'        => isset($order["email"]) ? $order["email"] : "",
                            'name_person' => $name,
                            'org_name'    => isset($order["legalName"]) ? $order["legalName"] : "",
                            'org_inn'     => isset($order["INN"]) ? $order["INN"] : "",
                            'org_kpp'     => isset($order["KPP"]) ? $order["KPP"] : "",
                            'tel_code'    => "",
                            'tel_name'    => isset($order["phone"]) ? $order["phone"] : "",
                            'cellphone'   => isset($order["phone"]) ? $order["phone"] : "",
                            'city'        => isset($order["delivery"]["address"]["city"]) ? $order["delivery"]["address"]["city"] : "",
                            'metro'       => isset($order["delivery"]["address"]["metro"]) ? $order["delivery"]["address"]["metro"] : "",
                            'street'      => isset($order["delivery"]["address"]["street"]) ? $order["delivery"]["address"]["street"] : "",
                            'house'       => isset($order["delivery"]["address"]["house"]) ? $order["delivery"]["address"]["house"] : "",
                            'corpus'      => isset($order["delivery"]["address"]["building"]) ? $order["delivery"]["address"]["building"] : "",
                            'building'    => isset($order["delivery"]["address"]["building"]) ? $order["delivery"]["address"]["building"] : "",
                            'entrance'    => isset($order["delivery"]["address"]["block"]) ? $order["delivery"]["address"]["block"] : "",
                            'office'      => isset($order["delivery"]["address"]["flat"]) ? $order["delivery"]["address"]["flat"] : "",
                            'floor'       => isset($order["delivery"]["address"]["floor"]) ? $order["delivery"]["address"]["floor"] : "",
                            'appartment'  => isset($order["delivery"]["address"]["flat"]) ? $order["delivery"]["address"]["flat"] : "",
                            'domofon'     => isset($order["delivery"]["address"]["intercomCode"]) ? $order["delivery"]["address"]["intercomCode"] : "",
                            'adr_name'    => $text,
                            'dostavka_metod' => $delivery,
                            'discount'    => $discount,
                            'user_id'     => $order["customer"]["externalId"] != 0 ? $order["customer"]["externalId"] : "",
                            'dos_ot'      => "",
                            'dos_do'      => "",
                            'magazv'      => "RetailCRM",
                            'ns'          => 1,
                            'nrg'         => "",
                            'order_metod' => $orderMetod,
                        ),
                    ),
                );
                $arr[] = "orders='" . serialize($tmpOrders) . "'";
                $insertOrm = new PHPShopOrm();
                $insertOrm->query("insert phpshop_orders set " . implode(", ", $arr));
                
                $orderOrm = new PHPShopOrm($GLOBALS['SysValue']['base']['table_name1']);
                $id = $orderOrm->select(array('*'), array("uid" => "='" . (isset($order["number"]) ? $order["number"] : $order["id"]) . "'" ), false);
                
                $fixId[] = array(
                    "id" => $order["id"],
                    "externalId" => $id["id"]
                );
            } elseif (!isset($order["deleted"])) {
                $arr = array();
                
                $tmporder = $orderOrm->select(array('*'), array("uid" => "='" . (isset($order["number"]) ? $order["number"] : $order["id"]) . "'" ), false);
                
                if (is_null($tmporder)) {
                    continue;
                }

                $tmporder["status"] = unserialize($tmporder["status"]);
                $tmporder["orders"] = unserialize($tmporder["orders"]);
                
                if (isset($order["customer"]["externalId"])) {
                    $userOrm = new PHPShopOrm($GLOBALS['SysValue']['base']['shopusers']);
                    $users = $userOrm->select(array('*'), array("id" => "=" . $order["customer"]["externalId"]),false);
                    if (!is_null($users)) {
                        $order["customer"]["externalId"] = $users["id"];
                        $tmporder["orders"]["Person"]["var"]["user_id"] = $order["customer"]["externalId"];
                        $tmporder["orders"]["Person"]["user_id"] = $order["customer"]["externalId"];
                    }
                }

                if (isset($order["createdAt"])) {
                    $arr[] = "datas='" . strtotime($order["createdAt"]) ."'";
                    $tmporder["status"]["time"] = strtotime($order["createdAt"]);
                    $tmporder["orders"]["Person"]["var"]["data"] = strtotime($order["createdAt"]);
                    $tmporder["orders"]["Person"]["data"] = strtotime($order["createdAt"]);
                    $tmporder["orders"]["Person"]["var"]["time"] = date("h:i a", strtotime($order["createdAt"]));
                    $tmporder["orders"]["Person"]["time"] = date("h:i a", strtotime($order["createdAt"]));
                }
                if (isset($order["number"])) {
                    $arr[] = "uid='" . $order["number"] ."'";
                    $tmporder["orders"]["Person"]["var"]["ouid"] = $order["number"];
                    $tmporder["orders"]["Person"]["ouid"] = $order["number"];
                }
                if (isset($order["customer"]["externalId"])) {
                    $arr[] = "user='" . $order["customer"]["externalId"] ."'";
                }
                if (isset($order["customerComment"])) {
                    $tmporder["status"]['maneger'] = $order["customerComment"];
                }

                $arr[] = "status='" . serialize($tmporder["status"]) . "'";
                
                if (isset($order["status"]) && $search = array_search($order["status"], $this->value["status"]) && $order["status"] != 'new') {
                    $arr[] = "statusi=" . $search;
                }

                if (isset($order["delivery"])) {
                    if (isset($order["delivery"]["code"]) && $search = array_search($order["delivery"]["code"], $this->value["delivery"])) {
                        $delivery = $search;
                        $tmporder["orders"]["Person"]["dostavka_metod"] = $search;
                        $tmporder["orders"]["Person"]["var"]["dostavka_metod"] = $search;
                    }
                    if (isset($order["delivery"]["address"])) {
                        if (isset($order["delivery"]["address"]["city"])) {
                            $tmporder["orders"]["Person"]["var"]["city"] = $order["delivery"]["address"]["city"];
                        }
                        if (isset($order["delivery"]["address"]["metro"])) {
                            $tmporder["orders"]["Person"]["var"]["metro"] = $order["delivery"]["address"]["metro"];
                        }
                        if (isset($order["delivery"]["address"]["street"])) {
                            $tmporder["orders"]["Person"]["var"]["street"] = $order["delivery"]["address"]["street"];
                        }
                        if (isset($order["delivery"]["address"]["house"])) {
                            $tmporder["orders"]["Person"]["var"]["house"] = $order["delivery"]["address"]["house"];
                        }
                        if (isset($order["delivery"]["address"]["building"])) {
                            $tmporder["orders"]["Person"]["var"]["corpus"] = $order["delivery"]["address"]["building"];
                            $tmporder["orders"]["Person"]["var"]["building"] = $order["delivery"]["address"]["building"];
                        }
                        if (isset($order["delivery"]["address"]["block"])) {
                            $tmporder["orders"]["Person"]["var"]["entrance"] = $order["delivery"]["address"]["block"];
                        }
                        if (isset($order["delivery"]["address"]["flat"])) {
                            $tmporder["orders"]["Person"]["var"]["office"] = $order["delivery"]["address"]["flat"];
                            $tmporder["orders"]["Person"]["var"]["appartment"] = $order["delivery"]["address"]["flat"];
                        }
                        if (isset($order["delivery"]["address"]["floor"])) {
                            $tmporder["orders"]["Person"]["var"]["floor"] = $order["delivery"]["address"]["floor"];
                        }
                        if (isset($order["delivery"]["address"]["intercomCode"])) {
                            $tmporder["orders"]["Person"]["var"]["domofon"] = $order["delivery"]["address"]["intercomCode"];
                        }
                    }
                }
                
                if (isset($order["email"])) {
                    $tmporder["orders"]["Person"]["var"]["mail"] = $order["email"];
                    $tmporder["orders"]["Person"]["mail"] = $order["email"];
                }

                if (isset($order["lastName"]) || isset($order["firstName"]) || isset($order["patronymic"])) {
                    
                    $name = implode(
                        ' ',
                        array(
                            isset($order["lastName"]) ? $order["lastName"]  : '',
                            isset($order["firstName"]) ? $order["firstName"] : '',
                            isset($order["patronymic"]) ? $order["patronymic"] : ''
                        )
                    );
                    
                    $tmporder["orders"]["Person"]["var"]["name_person"] = $name;
                    $tmporder["orders"]["Person"]["name_person"] = $name;
                }
                
                if (isset($order["phone"])) {
                    $tmporder["orders"]["Person"]["var"]["tel_nam"] = $order["phone"];
                    $tmporder["orders"]["Person"]["tel_nam"] = $order["phone"];
                    $tmporder["orders"]["Person"]["var"]["cellphone"] = $order["phone"];
                    $tmporder["orders"]["Person"]["cellphone"] = $order["phone"];
                }
                
                if (isset($order["legalName"])) {
                    $tmporder["orders"]["Person"]["var"]["org_name"] = $order["legalName"];
                    $tmporder["orders"]["Person"]["org_name"] = $order["legalName"];
                }
                if (isset($order["INN"])) {
                    $tmporder["orders"]["Person"]["var"]["org_inn"] = $order["INN"];
                    $tmporder["orders"]["Person"]["org_inn"] = $order["INN"];
                }
                if (isset($order["KPP"])) {
                    $tmporder["orders"]["Person"]["var"]["org_kpp"] = $order["KPP"];
                    $tmporder["orders"]["Person"]["org_kpp"] = $order["KPP"];
                }
                $discount = isset($order["discount"]) ? $order["discount"] : null;
                if (isset($order["discountPercent"])) {
                    $discount += $order["summ"] / 100 * $order["discountPercent"];
                }
                if (!is_null($discount)) {
                    $tmporder["orders"]["Person"]["var"]["discoun"] = $discount;
                    $tmporder["orders"]["Person"]["discoun"] = $discount;
                }

                if (isset($order["paymentType"]) && $search = array_search($order["paymentType"], $this->value["payment"])) {
                    $tmporder["orders"]["Person"]["var"]["order_metod"] = $search;
                    $tmporder["orders"]["Person"]["order_metod"] = $search;
                }

                foreach ($order["items"] as $item) {
                    if (isset($tmporder["orders"]["Cart"]["cart"][$item["offer"]["externalId"]])) {
                        if (isset($item["deleted"])) {
                            $tmporder["orders"]["Cart"]["num"] = ((int)$tmporder["orders"]["Cart"]["num"]) - $item["quantity"];
                            $tmporder["orders"]["Cart"]["sum"] = ((float)$tmporder["orders"]["Cart"]["sum"]) - $item["initialPrice"];
                            unset($tmporder["orders"]["Cart"]["cart"][$item["offer"]["externalId"]]);
                        } else {
                            if (isset($item["quantity"])) {
                                $tmporder["orders"]["Cart"]["num"] = ((int)$tmporder["orders"]["Cart"]["num"]) - ((int)$tmporder["orders"]["Cart"]["cart"][$item["offer"]["externalId"]]['num']) + $item["quantity"];
                                $tmporder["orders"]["Cart"]["cart"][$item["offer"]["externalId"]]['num'] = $item["quantity"];
                            }
                            if (isset($item["initialPrice"])) {
                                $tmporder["orders"]["Cart"]["sum"] = ((int)$tmporder["orders"]["Cart"]["sum"]) - ((int)$tmporder["orders"]["Cart"]["cart"][$item["offer"]["externalId"]]['sum']) + $item["initialPrice"];
                                $tmporder["orders"]["Cart"]["cart"][$item["offer"]["externalId"]]['sum'] = $item["initialPrice"];
                            }
                        }
                    } elseif (isset($products[$item["offer"]["externalId"]])) {
                        $tmporder["orders"]["Cart"]["num"] = ((int)$tmporder["orders"]["Cart"]["num"]) + $item["quantity"];
                        $tmporder["orders"]["Cart"]["sum"] = ((float)$tmporder["orders"]["Cart"]["sum"]) + $item["initialPrice"];
                        $tmporder["orders"]["Cart"]["cart"][$item["offer"]["externalId"]] = array(
                            'id'    => $item["offer"]["externalId"],
                            'name'  => $item["offer"]["name"],
                            'price' => $item["initialPrice"],
                            'uid'   => $products[$item["offer"]["externalId"]]['uid'],
                            'num'   => $item["quantity"],
                            'weight' => '',
                            'ed_izm' => '',
                            'user'   => $products[$item["offer"]["externalId"]]['user']
                        );
                    }
                }
                $arr[] = "orders='" . serialize($tmporder["orders"]) . "'";
                $updateOrm = new PHPShopOrm();
                $updateOrm->query("update phpshop_orders set " . implode(", ", $arr) . " where id=" . $order["externalId"]);

            }

        }
        return $fixId;
    }
}
?>