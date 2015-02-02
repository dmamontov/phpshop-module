<?php
error_reporting(E_ALL);

$_classPath="../../../";
include($_classPath."class/obj.class.php");
PHPShopObj::loadClass("base");
PHPShopObj::loadClass("system");
PHPShopObj::loadClass("security");
PHPShopObj::loadClass("orm");

$PHPShopBase = new PHPShopBase($_classPath."inc/config.ini");
include($_classPath."admpanel/enter_to_admin.php");

PHPShopObj::loadClass("modules");
$PHPShopModules = new PHPShopModules($_classPath."modules/");

PHPShopObj::loadClass("admgui");
$PHPShopGUI = new PHPShopGUI();

$PHPShopOrm = new PHPShopOrm($PHPShopModules->getParam("base.retailcrm.retailcrm_system"));

require("../class/Tools.php");

function actionUpdate() {
    global $PHPShopOrm;
    $post = Tools::clearArray($_POST);
    if ('/' != substr($post["siteurl"], strlen($post["siteurl"]) - 1, 1)) {
        $post["siteurl"] .= '/';
    }
    if ('/' != substr($post["url"], strlen($post["url"]) - 1, 1)) {
        $post["url"] .= '/';
    }
    $PHPShopOrm->sql="update phpshop_modules_retailcrm_system set value='" . serialize($post) . "' where code='options'";
    $action = $PHPShopOrm->update();
    return $action;
}

function actionStart() {
    global $PHPShopGUI, $PHPShopSystem, $SysValue, $_classPath, $PHPShopOrm;
    
    $PHPShopGUI->dir=$_classPath."admpanel/";
    $PHPShopGUI->title="��������� ������ retailCRM";
    $PHPShopGUI->size="610,550";

    $data = $PHPShopOrm->select();
    @extract($data);

    $value = unserialize($value);

    $PHPShopGUI->addCSSFiles("retailcrm.css");
    $PHPShopGUI->setHeader("��������� ������ 'retailCRM'", "���������", $PHPShopGUI->dir . "img/i_display_settings_med[1].gif");

    $field1 = $PHPShopGUI->setInputText('�������� ��������:&nbsp', 'shopname', ((isset($value["shopname"])) ? $value["shopname"] : ""));
    $field1 .= $PHPShopGUI->setInputText('�������� ��������:', 'companyname', ((isset($value["companyname"])) ? $value["companyname"] : ""));
    $field1 .= $PHPShopGUI->setInputText("����� �����:&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp", 'siteurl', ((isset($value["siteurl"])) ? $value["siteurl"] : ""));
    $tab1 = $PHPShopGUI->setField('��������� ���������', $field1, "none", 0, true);

    $field2 = $PHPShopGUI->setInputText('����� RetailCRM:&nbsp&nbsp&nbsp&nbsp', 'url', ((isset($value["url"])) ? $value["url"] : ""));
    $field2 .= $PHPShopGUI->setInputText('���� �����������:&nbsp&nbsp', 'key', ((isset($value["key"])) ? $value["key"] : ""));
    $tab1 .= $PHPShopGUI->setField('��������� ����������', $field2, "none", 0, true);

    if (isset($value["url"]) && isset($value["key"]) && $helper = new ApiHelper($value["url"], $value["key"])) {
        $field1 = "";
        $tab2 = "";
        // ������� ��������
        try {
            $response = $helper->api->deliveryTypesList();
        } catch (CurlException $e) {
            Tools::logger("������� ��������. ������ ����������� � retailCRM: " . $e->getMessage(), "connect");
        }

        $deliveryTypes[] = array("", "", false);
        if ($response->isSuccessful()) {
            foreach ($response->deliveryTypes as $code => $params) {
                $deliveryTypes[] = array(Tools::iconvArray($params["name"], "UTF-8", "WINDOWS-1251"), $params["code"], false);
            }
        } else {
            Tools::logger("������� ��������. ������ ����������� � retailCRM: " . $e->getMessage(), "connect");
        }

        $deliveryOrm = new PHPShopOrm($GLOBALS['SysValue']['base']['delivery']);
        $delivery = $deliveryOrm->select(array('*'), array('is_folder' => "='0'"));
        $deliveryOrm = new PHPShopOrm($GLOBALS['SysValue']['base']['delivery']);
        $pdelivery = $deliveryOrm->select(array('*'), array('is_folder' => "='1'"));

        $parents = array();
        foreach ($pdelivery as $parent) {
            $parents[$parent["id"]]['title'] = $parent["city"];
            foreach ($delivery as $del) {
                if ($parent["id"] == $del["PID"]) {
                    $tmpDeliveryTypes = $deliveryTypes;
                    if (isset($value["delivery"][ $del["id"] ])) {
                        foreach ($tmpDeliveryTypes as $key => $val) {
                            if ($val[1] == $value["delivery"][ $del["id"] ]) {
                                $tmpDeliveryTypes[$key][2] = "selected";
                                break;
                            }
                        }
                    }

                    $tmp = "";
                    $tmp .= $PHPShopGUI->setDiv("left", $del["city"] . ":");
                    $tmp .= $PHPShopGUI->setSelect('delivery['. $del["id"] .']', $tmpDeliveryTypes, 200) . "<br>";
                    $parents[$parent["id"]]["items"] .= $tmp;
                }
            }
            if (!isset($parents[$parent["id"]]["items"]) || count($parents[$parent["id"]]["items"]) < 1) {
                unset($parents[$parent["id"]]);
            }
        }

        foreach ($parents as $delivery) {
            $field1 .= $PHPShopGUI->setField($delivery["title"], $delivery["items"], "none", 0, true);
        }

        $tab2 .= $PHPShopGUI->setField('������� ��������', $field1, "none", 0, true);

        // ������� ������
        try {
            $response = $helper->api->paymentTypesList();
        } catch (CurlException $e) {
            Tools::logger("������� ��������. ������ ����������� � retailCRM: " . $e->getMessage(), "connect");
        }

        $paymentTypes[] = array("", "", false);
        if ($response->isSuccessful()) {
            foreach ($response->paymentTypes as $code => $params) {
                $paymentTypes[] = array(Tools::iconvArray($params["name"], "UTF-8", "WINDOWS-1251"), $params["code"], false);
            }
        } else {
            Tools::logger("������� ��������. ������ ����������� � retailCRM: " . $e->getMessage(), "connect");
        }

        $paymentOrm = new PHPShopOrm($GLOBALS['SysValue']['base']['payment_systems']);
        $payment = $paymentOrm->select(array('*'));

        $field2 = "";
        foreach ($payment as $paymentValue) {
            $tmpPaymentTypes = $paymentTypes;
            if (isset($value["payment"][ $paymentValue["id"] ])) {
                foreach ($tmpPaymentTypes as $key => $val) {
                    if ($val[1] == $value["payment"][ $paymentValue["id"] ]) {
                        $tmpPaymentTypes[$key][2] = "selected";
                        break;
                    }
                }
            }
            $field2 .= $PHPShopGUI->setDiv("left", $paymentValue["name"] . ":");
            $field2 .= $PHPShopGUI->setSelect('payment['. $paymentValue["id"] .']', $tmpPaymentTypes, 200) . "<br>";
        }

        $tab2 .= $PHPShopGUI->setField('������� ������', $field2, "none", 0, true);

        try {
            $response = $helper->api->statusesList();
        } catch (CurlException $e) {
            Tools::logger("������� ��������. ������ ����������� � retailCRM: " . $e->getMessage(), "connect");
        }

        $statuses[] = array("", "", false);
        if ($response->isSuccessful()) {
            foreach ($response->statuses as $code => $params) {
                $statuses[] = array(Tools::iconvArray($params["name"], "UTF-8", "WINDOWS-1251"), $params["code"], false);
            }
        } else {
            Tools::logger("������� ��������. ������ ����������� � retailCRM: " . $e->getMessage(), "connect");
        }

        $statusesOrm = new PHPShopOrm($GLOBALS['SysValue']['base']['order_status']);
        $status = $statusesOrm->select(array('*'));

        $field3 = "";
        array_unshift($status, array("id" => "new", "name" => "����� �����"));
        foreach ($status as $statusValue) {
            $tmpStatuses = $statuses;
            if (isset($value["status"][ $statusValue["id"] ])) {
                foreach ($tmpStatuses as $key => $val) {
                    if ($val[1] == $value["status"][ $statusValue["id"] ]) {
                        $tmpStatuses[$key][2] = "selected";
                        break;
                    }
                }
            }
            $field3 .= $PHPShopGUI->setDiv("left", $statusValue["name"] . ":");
            $field3 .= $PHPShopGUI->setSelect('status['. $statusValue["id"] .']', $tmpStatuses, 200) . "<br>";
        }

        $tab2 .= $PHPShopGUI->setField('�������', $field3, "none", 0, true);

        if (isset($GLOBALS['SysValue']['base']['oneclick'])) {
            $field3 = "";
            $status = array(
                array("id" => 1, "name" => "�����"),
                array("id" => 2, "name" => "������� �����������"),
                array("id" => 3, "name" => "����������"),
                array("id" => 4, "name" => "��������"),
            );
            foreach ($status as $statusValue) {
                $tmpStatuses = $statuses;
                if (isset($value["status-oneclick"][ $statusValue["id"] ])) {
                    foreach ($tmpStatuses as $key => $val) {
                        if ($val[1] == $value["status-oneclick"][ $statusValue["id"] ]) {
                            $tmpStatuses[$key][2] = "selected";
                            break;
                        }
                    }
                }
                $field3 .= $PHPShopGUI->setDiv("left", $statusValue["name"] . ":");
                $field3 .= $PHPShopGUI->setSelect('status-oneclick['. $statusValue["id"] .']', $tmpStatuses, 200) . "<br>";
            }
            $tab2 .= $PHPShopGUI->setField('������� (����� � ���� ����)', $field3, "none", 0, true);
        }

        if (isset($GLOBALS['SysValue']['base']['returncall'])) {
            $field3 = "";
            $status = array(
                array("id" => 1, "name" => "�����"),
                array("id" => 2, "name" => "������� �����������"),
                array("id" => 3, "name" => "����������"),
                array("id" => 4, "name" => "��������"),
            );
            foreach ($status as $statusValue) {
                $tmpStatuses = $statuses;
                if (isset($value["status-oneclick"][ $statusValue["id"] ])) {
                    foreach ($tmpStatuses as $key => $val) {
                        if ($val[1] == $value["status-returncall"][ $statusValue["id"] ]) {
                            $tmpStatuses[$key][2] = "selected";
                            break;
                        }
                    }
                }
                $field3 .= $PHPShopGUI->setDiv("left", $statusValue["name"] . ":");
                $field3 .= $PHPShopGUI->setSelect('status-returncall['. $statusValue["id"] .']', $tmpStatuses, 200) . "<br>";
            }
            $tab2 .= $PHPShopGUI->setField('������� (�������� ������)', $field3, "none", 0, true);
        }

        /* $field1 = "";
        $sql = "select * from " . $SysValue['base']['table_name20'] . " where category != 0";
        $result = mysql_query($sql);
        $retailOffers = array(
            "article" => "������� ������",
            "size"    => "������ ������",
            "color"   => "���� ������",
            "weight"  => "��� ������",
            "vendor"  => "������������� ������"
        );
        $offers[] = array("", "", false);
        while ($row = mysql_fetch_array($result)) {
            $offers[] = array($row["name"], $row["id"], false);
        }
        foreach ($retailOffers as $offersKey => $offersValue) {
            $tmpOffers = $offers;
            if (isset($value["offer"][ $offersKey ])) {
                foreach ($tmpOffers as $key => $val) {
                    if ($val[1] == $value["offer"][ $offersKey ]) {
                        $tmpOffers[$key][2] = true;
                        break;
                    }
                }
            }
            $field1 .= $PHPShopGUI->setDiv("left", $offersValue . ":");
            $field1 .= $PHPShopGUI->setSelect('offer['. $offersKey .']', $tmpOffers, 200) . "<br>";
        }
        $tab3 = $PHPShopGUI->setField('��������', $field1, "none", 0, true); */

        $PHPShopGUI->setTab(array("����� ���������", $tab1),
                            array("��������� ������������", $tab2)/* ,
                            array("C����������� ������� �������", $tab3) */);
    } else {
        $PHPShopGUI->setTab(array("����� ���������", $tab1));
    }

    $ContentFooter = $PHPShopGUI->setInput("button", "", "������", "right", 70, "return onCancel();", "but") .
                     $PHPShopGUI->setInput("submit", "edit", "���������", "right", 70, "", "but", "actionUpdate");
    $PHPShopGUI->setFooter($ContentFooter);
    return true;
}

if ($UserChek->statusPHPSHOP < 2) {
    $PHPShopGUI->setLoader($_POST['edit'],'actionStart');
    $PHPShopGUI->getAction();
} else {
    $UserChek->BadUserFormaWindow();
}
?>