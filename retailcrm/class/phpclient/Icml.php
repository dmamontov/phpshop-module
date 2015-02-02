<?php
class Icml
{
    protected $dd, $eCategories, $eOffers;
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

    public function generate($categories, $offers)
    {
        $string = '<?xml version="1.0" encoding="UTF-8"?>
            <yml_catalog date="'.date('Y-m-d H:i:s').'">
                <shop>
                    <name>'.$this->value["shopname"].'</name>
                    <company>'.((!empty($this->value["companyname"])) ? $this->value["companyname"] : $this->value["shopname"]).'</company>
                    <categories/>
                    <offers/>
                </shop>
            </yml_catalog>
        ';

        $xml = new SimpleXMLElement($string, LIBXML_NOENT |LIBXML_NOCDATA | LIBXML_COMPACT | LIBXML_PARSEHUGE);

        $this->dd = new DOMDocument();
        $this->dd->preserveWhiteSpace = false;
        $this->dd->formatOutput = true;
        $this->dd->loadXML($xml->asXML());

        $this->eCategories = $this->dd->getElementsByTagName('categories')->item(0);
        $this->eOffers = $this->dd->getElementsByTagName('offers')->item(0);

        $this->addCategories($categories);
        $this->addOffers($offers);

        $this->dd->saveXML();
        $this->dd->save("../../../../yml/retailcrm.xml");
    }

    private function addCategories($categories)
    {
        foreach($categories as $category) {
            $e = $this->eCategories->appendChild(
                $this->dd->createElement(
                    'category', $category['name']
                )
            );

            $e->setAttribute('id', $category['id']);

            if ($category['parentId'] > 0) {
                $e->setAttribute('parentId', $category['parentId']);
            }
        }
     }

    private function addOffers($offers)
    {
        foreach ($offers as $offer) {

            $e = $this->eOffers->appendChild($this->dd->createElement('offer'));
            $e->setAttribute('id', $offer['id']);
            $e->setAttribute('productId', $offer['productId']);
            $e->setAttribute('quantity', (int) $offer['quantity']);


            $e->appendChild($this->dd->createElement('categoryId', $offer['categoryId']));
            $e->appendChild($this->dd->createElement('name'))->appendChild($this->dd->createTextNode($offer['name']));
            $e->appendChild($this->dd->createElement('productName'))->appendChild($this->dd->createTextNode($offer['name']));
            $e->appendChild($this->dd->createElement('price', $offer['initialPrice']));

            if (isset($offer['purchasePrice'] ) && $offer['purchasePrice'] != '') {
                $e->appendChild($this->dd->createElement('purchasePrice'))->appendChild($this->dd->createTextNode($offer['purchasePrice']));
            }

            if (isset($offer['vendor'] ) && $offer['vendor'] != '') {
                $e->appendChild($this->dd->createElement('vendor'))->appendChild($this->dd->createTextNode($offer['vendor']));
            }

            if (isset($offer['picture'] ) && $offer['picture'] != '') {
                $e->appendChild($this->dd->createElement('picture', $offer['picture']));
            }

            if (isset($offer['url'] ) && $offer['url'] != '') {
                $e->appendChild($this->dd->createElement('url'))->appendChild($this->dd->createTextNode($offer['url']));
            }

            if (isset($offer['xmlId']) && $offer['xmlId'] != '') {
                $e->appendChild($this->dd->createElement('xmlId'))->appendChild($this->dd->createTextNode($offer['xmlId']));
            }

            if (isset($offer['article'] ) && $offer['article'] != '') {
                $sku = $this->dd->createElement('param');
                $sku->setAttribute('name', 'article');
                $sku->appendChild($this->dd->createTextNode($offer['article']));
                $e->appendChild($sku);
            }

            if (isset($offer['size'] ) && $offer['size'] != '') {
                $size = $this->dd->createElement('param');
                $size->setAttribute('name', 'size');
                $size->appendChild($this->dd->createTextNode($offer['size']));
                $e->appendChild($size);
            }

            if (isset($offer['color'] ) && $offer['color'] != '') {
                $color = $this->dd->createElement('param');
                $color->setAttribute('name', 'color');
                $color->appendChild($this->dd->createTextNode($offer['color']));
                $e->appendChild($color);
            }

            if (isset($offer['weight'] ) && $offer['weight'] != '') {
                $weight = $this->dd->createElement('param');
                $weight->setAttribute('name', 'weight');
                $weight->appendChild($this->dd->createTextNode($offer['weight']));
                $e->appendChild($weight);
            }
        }
    }
}

