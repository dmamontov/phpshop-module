<?php
class Validation
{
    const LOG = '../logs/validate.log';
    private $format = '[%s] ';

    private $valid = array(
        'customers' => array(
            'externalId'        => array('type' => 'string',   'required' => true),
            'firstName'         => array('type' => 'string',   'required' => false),
            'lastName'          => array('type' => 'string',   'required' => false),
            'patronymic'        => array('type' => 'string',   'required' => false),
            'email'             => array('type' => 'email',    'required' => false),
            'number'            => array('type' => 'string',   'required' => false),
            'index'             => array('type' => 'string',   'required' => false),
            'country'           => array('type' => 'string',   'required' => false),
            'region'            => array('type' => 'string',   'required' => false),
            'city'              => array('type' => 'string',   'required' => false),
            'street'            => array('type' => 'string',   'required' => false),
            'building'          => array('type' => 'string',   'required' => false),
            'flat'              => array('type' => 'string',   'required' => false),
            'intercomCode'      => array('type' => 'string',   'required' => false),
            'floor'             => array('type' => 'int',      'required' => false),
            'block'             => array('type' => 'int',      'required' => false),
            'house'             => array('type' => 'string',   'required' => false),
            'metro'             => array('type' => 'string',   'required' => false),
            'notes'             => array('type' => 'string',   'required' => false),
            'text'              => array('type' => 'string',   'required' => false),
            'createdAt'         => array('type' => 'datetime', 'required' => false, 'format' => 'Y-m-d H:i:s'),
            'vip'               => array('type' => 'boolean',  'required' => false),
            'bad'               => array('type' => 'boolean',  'required' => false),
            'commentary'        => array('type' => 'string',   'required' => false),
            'customFields'      => array('type' => 'skip',     'required' => false),
            'contragentType'    => array('type' => 'enum',     'required' => false, 'enum' => array('individual', 'legal-entity', 'enterpreneur')),
            'legalName'         => array('type' => 'string',   'required' => false),
            'legalAddress'      => array('type' => 'string',   'required' => false),
            'INN'               => array('type' => 'string',   'required' => false),
            'OKPO'              => array('type' => 'string',   'required' => false),
            'KPP'               => array('type' => 'string',   'required' => false),
            'OGRN'              => array('type' => 'string',   'required' => false),
            'OGRNIP'            => array('type' => 'string',   'required' => false),
            'certificateNumber' => array('type' => 'string',   'required' => false),
            'certificateDate'   => array('type' => 'datetime', 'required' => false, 'format' => 'Y-m-d'),
            'BIK'               => array('type' => 'string',   'required' => false),
            'bank'              => array('type' => 'string',   'required' => false),
            'bankAddress'       => array('type' => 'string',   'required' => false),
            'corrAccount'       => array('type' => 'string',   'required' => false),
            'bankAccount'       => array('type' => 'string',   'required' => false),
            'managerId'         => array('type' => 'int',      'required' => false),
        ),
        'orders' => array(
            'number'            => array('type' => 'string',   'required' => false),
            'externalId'        => array('type' => 'string',   'required' => true),
            'createdAt'         => array('type' => 'datetime', 'required' => false, 'format' => 'Y-m-d H:i:s'),
            'discount'          => array('type' => 'float',    'required' => false),
            'discountPercent'   => array('type' => 'float',    'required' => false),
            'mark'              => array('type' => 'int',      'required' => false),
            'markDatetime'      => array('type' => 'datetime', 'required' => false, 'format' => 'Y-m-d H:i:s'),
            'lastName'          => array('type' => 'string',   'required' => false),
            'firstName'         => array('type' => 'string',   'required' => false),
            'patronymic'        => array('type' => 'string',   'required' => false),
            'phone'             => array('type' => 'string',   'required' => false),
            'additionalPhone'   => array('type' => 'string',   'required' => false),
            'email'             => array('type' => 'email',    'required' => false),
            'call'              => array('type' => 'boolean',  'required' => false),
            'expired'           => array('type' => 'boolean',  'required' => false),
            'customerComment'   => array('type' => 'string',   'required' => false),
            'managerComment'    => array('type' => 'string',   'required' => false),
            'paymentDetail'     => array('type' => 'string',   'required' => false),
            'statusComment'     => array('type' => 'string',   'required' => false),
            'customFields'      => array('type' => 'skip',     'required' => false),
            'contragentType'    => array('type' => 'enum',     'required' => false, 'enum' => array('individual', 'legal-entity', 'enterpreneur')),
            'legalName'         => array('type' => 'string',   'required' => false),
            'legalAddress'      => array('type' => 'string',   'required' => false),
            'INN'               => array('type' => 'string',   'required' => false),
            'OKPO'              => array('type' => 'string',   'required' => false),
            'KPP'               => array('type' => 'string',   'required' => false),
            'OGRN'              => array('type' => 'string',   'required' => false),
            'OGRNIP'            => array('type' => 'string',   'required' => false),
            'certificateNumber' => array('type' => 'string',   'required' => false),
            'certificateDate'   => array('type' => 'datetime', 'required' => false, 'format' => 'Y-m-d'),
            'BIK'               => array('type' => 'string',   'required' => false),
            'bank'              => array('type' => 'string',   'required' => false),
            'bankAddress'       => array('type' => 'string',   'required' => false),
            'corrAccount'       => array('type' => 'string',   'required' => false),
            'bankAccount'       => array('type' => 'string',   'required' => false),
            'orderType'         => array('type' => 'string',   'required' => false),
            'orderMethod'       => array('type' => 'string',   'required' => false),
            'customerId'        => array('type' => 'string',   'required' => false),
            'managerId'         => array('type' => 'int',      'required' => false),
            'paymentType'       => array('type' => 'string',   'required' => false),
            'paymentStatus'     => array('type' => 'string',   'required' => false),
            'status'            => array('type' => 'string',   'required' => false),
            'sourceId'          => array('type' => 'string',   'required' => false),
            'initialPrice'      => array('type' => 'float',    'required' => false),
            'quantity'          => array('type' => 'float',    'required' => false),
            'properties'        => array('type' => 'skip',     'required' => false),
            'productId'         => array('type' => 'string',   'required' => false),
            'productName'       => array('type' => 'string',   'required' => false),
            'comment'           => array('type' => 'string',   'required' => false),
            'purchasePrice'     => array('type' => 'float',    'required' => false),
            'code'              => array('type' => 'string',   'required' => false),
            'integrationCode'   => array('type' => 'string',   'required' => false),
            'data'              => array('type' => 'skip',     'required' => false),
            'service'           => array('type' => 'skip',     'required' => false),
            'cost'              => array('type' => 'string',   'required' => false),
            'date'              => array('type' => 'datetime', 'required' => false, 'format' => 'Y-m-d'),
            'index'             => array('type' => 'string',   'required' => false),
            'country'           => array('type' => 'string',   'required' => false),
            'region'            => array('type' => 'string',   'required' => false),
            'city'              => array('type' => 'string',   'required' => false),
            'street'            => array('type' => 'string',   'required' => false),
            'building'          => array('type' => 'string',   'required' => false),
            'flat'              => array('type' => 'string',   'required' => false),
            'intercomCode'      => array('type' => 'string',   'required' => false),
            'floor'             => array('type' => 'int',      'required' => false),
            'block'             => array('type' => 'int',      'required' => false),
            'house'             => array('type' => 'string',   'required' => false),
            'metro'             => array('type' => 'string',   'required' => false),
            'notes'             => array('type' => 'string',   'required' => false),
            'text'              => array('type' => 'string',   'required' => false),
        ),
    );

    public function __construct()
    {
        if (function_exists('date_default_timezone_set') && function_exists('date_default_timezone_get')) {
            date_default_timezone_set(@date_default_timezone_get());
        }
        $this->format = sprintf($this->format, date('Y-m-d H:i:s'));
    }

    public function customersCheck($customers)
    {
        if (!is_array($customers) || count($customers) <= 0) {
            return false;
        }

        $customers = self::clearArray($customers);
        $newCustomers = array();

        foreach ($customers as $number => $customer) {
            $newCustomers[$number] = $this->customerCheck($customer);
            if (is_bool($newCustomers[$number]) && $newCustomers[$number] === false) {
                unset($newCustomers[$number]);
                continue;
            }
        }

        return array_chunk($newCustomers, 50);
    }

    public function customerCheck($customer)
    {
        if (!is_array($customer) || count($customer) <= 0) {
            return false;
        }

        $customer = self::clearArray($customer);

        return $this->reformat($customer, 'customers');
    }

    public function ordersCheck($orders)
    {
        if (!is_array($orders) || count($orders) <= 0) {
            return false;
        }

        $orders = self::clearArray($orders);
        $newOrders = array();

        foreach ($orders as $number => $order) {
            $newOrders[$number] = $this->orderCheck($order);
            if (is_bool($newOrders[$number]) && $newOrders[$number] === false) {
                unset($newOrders[$number]);
                continue;
            }
        }

        return array_chunk($newOrders, 50);
    }

    public function orderCheck($order)
    {
        if (!is_array($order) || count($order) <= 0) {
            return false;
        }

        $order = self::clearArray($order);

        return $this->reformat($order, 'orders');
    }

    private function reformat($parameters, $type)
    {
        $formatted = array();

        foreach ($parameters as $key => $param) {
            if (isset($this->valid[$type][$key]) && $this->valid[$type][$key]['type'] == 'skip') {
                $formatted[$key] = $parameters;
            } elseif (isset($this->valid[$type][$key]) && !is_array($param) && $param != '' && !is_null($param)) {
                $formatted[$key] = $this->getFormat($param, $this->valid[$type][$key]);
            } elseif(is_array($param)) {
                $formatted[$key] = $this->reformat($param, $type);
            }

            if (is_null($formatted[$key]) && $this->valid[$type][$key]['required'] === true) {
                $formatted = array();
                error_log($this->format . '(' . $type . ')' . json_encode($parameters), 3, self::LOG);
                break;
            } elseif (is_null($formatted[$key])) {
                unset($formatted[$key]);
            }
        }

        return count($formatted) <= 0 ? false : $formatted;
    }

    private function getFormat($parameters, $valid)
    {
        $format = null;

        switch ($valid['type']) {
            case 'string':
                $format = (string) $parameters;
                break;
            case 'email':
                $format = filter_var($parameters, FILTER_VALIDATE_EMAIL) === false ? null : (string) $parameters;
                break;
            case 'int':
                $format = filter_var($parameters, FILTER_VALIDATE_INT) === false ? null : $parameters;
                break;
            case 'float':
                $format = filter_var($parameters, FILTER_VALIDATE_FLOAT) === false ? null : $parameters;
                break;
            case 'datetime':
                $format = (string) ($parameters instanceof DateTime)
                              ? $parameters->format($valid['format'])
                              : date($valid['format'], (filter_var($parameters, FILTER_VALIDATE_INT) === false) ? strtotime($parameters) : $parameters);
                break;
            case 'boolean':
                $format = (bool) filter_var($parameters, FILTER_VALIDATE_BOOLEAN) === false ? false : true;
                break;
            case 'enum':
                $format = (string) in_array($parameters, $valid['enum']) ? $parameters : null;
                break;
        }

        return $format;
    }

    public static function clearArray($arr)
    {
        if (!is_array($arr)) {
            return $arr;
        }

        $result = array();
        foreach ($arr as $index => $node ) {
            $result[ $index ] = (is_array($node)) ? self::clearArray($node) : trim($node);
            if ($result[ $index ] == '' || count($result[ $index ]) < 1) {
                unset($result[ $index ]);
            }
        }

        return $result;
    }
}
?>