<?php
require_once "Autoloader.php";
Autoloader::register();
class Tools
{
    public static function getDate($log){
        if (file_exists($log)) {
            return file_get_contents($log);
        } else {
            return date('Y-m-d H:i:s', strtotime('-1 days', strtotime(date('Y-m-d H:i:s'))));
        }
    }

    public static function logger($message, $type, $errors = null){
        $format = "[" . date('Y-m-d H:i:s') . "]";
        if (!is_null($errors) && is_array($errors)) {
            $message .= ":\n";
            foreach ($errors as $error) {
                $message .= "\t" . $error . "\n";
            }
        } else {
            $message .= "\n";
        }

        switch ($type) {
            case 'connect':
                $path = "../logs/connect-error.log";
                error_log($format . " " . $message, 3, $path);
                break;
            case 'customers':
                $path = "../logs/customers-error.log";
                error_log($format . " " . $message, 3, $path);
                break;
            case 'orders':
                $path = "../logs/orders-error.log";
                error_log($format . " " . $message, 3, $path);
                break;
            case 'history':
                $path = "../logs/history-error.log";
                error_log($format . " " . $message, 3, $path);
                break;
            case 'history-log':
                $path = "../logs/history.log";
                file_put_contents($path, $message);
                break;
        }
    
        /* $app_settings_model = new waAppSettingsModel();
         $settings = json_decode($app_settings_model->get(array('shop', 'retailcrm'), 'options'), true);
    
         if ($type != 'history-log') {
         $subject = "Ошибка обмена ";
         if (isset($settings["siteurl"]) && !empty($settings["siteurl"])) {
         $subject .= "на сайте" . $settings["siteurl"] . "\r\n";
         } else {
         $subject .= "retailCRM";
         }
         $mail = new waMailMessage($settings["email"], $subject, $message, "support@retailcrm.com");
         $mail->send();
         } */
    }
    
    public static function iconvArray($arg, $in = "WINDOWS-1251", $out = "UTF-8") {
        if (is_array($arg)) {
            foreach ($arg as $key => $val) {
                $arg[iconv($in, $out, $key)] = (is_array($val)) ? self::iconvArray($val, $in, $out) : iconv($in, $out, $val);
            }
    
            return $arg;
        } elseif(is_string($arg)) {
    
            return iconv($in, $out, $arg);
        }
    
        return $arg;
    }
    
    public static function clearArray($arr)
    {
        if (!is_array($arr)) {
            return $arr;
        }
    
        $result = array();
        foreach ($arr as $index => $node ) {
            $result[ $index ] = (is_array($node)) ? self::clearArray($node) : trim($node);
            if ($result[ $index ] == '' || $index === "actionList" || $index === "edit" || count($result[ $index ]) < 1) {
                unset($result[ $index ]);
            }
        }
    
        return $result;
    }
    
    public static function explodeFio($fio)
    {
        $fio = (!$fio) ? false : explode(" ", $fio, 3);
    
        switch (count($fio)) {
            default:
            case 0:
                $newFio['firstName']  = 'ФИО  не указано';
                break;
            case 1:
                $newFio['firstName']  = $fio[0];
                break;
            case 2:
                $newFio = array(
                'lastName'  => $fio[0],
                'firstName' => $fio[1]
                );
                break;
            case 3:
                $newFio = array(
                'lastName'   => $fio[0],
                'firstName'  => $fio[1],
                'patronymic' => $fio[2]
                );
                break;
        }
    
        return $newFio;
    }
}
?>