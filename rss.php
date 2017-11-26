<?php
/*
* 2007-2016 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2016 PrestaShop SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*  
*  Changed by Helder S. Viana, to handle with the kuantokusta format: http://www.kuantokusta.com/kuantokusta.xml
*/
include(dirname(__FILE__).'/../../config/config.inc.php');
require_once(dirname(__FILE__).'/../../init.php');

if (!Module::getInstanceByName('feeder')->active)
	exit;

// Get data
$number = ((int)(Tools::getValue('n')) ? (int)(Tools::getValue('n')) : 1000);
$orderBy = Tools::getProductsOrder('by', Tools::getValue('orderby'));
$orderWay = Tools::getProductsOrder('way', Tools::getValue('orderway'));
$id_category = ((int)(Tools::getValue('id_category')) ? (int)(Tools::getValue('id_category')) : Configuration::get('PS_HOME_CATEGORY'));														
$products = Product::getProducts((int)$context->language->id, 0, ($number > 1000 ? 1000 : $number), $orderBy, $orderWay);
$currency = new Currency((int)$context->currency->id);
$affiliate = (Tools::getValue('ac') ? '?ac='.(int)(Tools::getValue('ac')) : '');
$metas = Meta::getMetaByPage('index', (int)$context->language->id);
$shop_uri = Tools::getShopDomainSsl(true, true).__PS_BASE_URI__;
$i = 0;
// Send feed
header("Content-Type:text/xml; charset=utf-8");
echo '<?xml version="1.0" encoding="UTF-8"?>'."\n";
//Configuration::get('PS_SHOP_NAME') shop name
//Configuration::get('PS_SHOP_EMAIL') email
echo "<products>\n";
foreach ($products AS $product)
{
	$image = Image::getImages((int)($cookie->id_lang), $product['id_product']);
	echo "\t<product>\n";
	echo "\t\t<id_product>" . $product['id_product'] . "</id_product>\n";
	echo "\t\t<designation><![CDATA[" . $product['name'] . "]]></designation>\n";
	echo "\t\t<category>" . $product['category_default'] . "</category>\n";
	echo "\t\t<brand>" . $product['manufacturer_name'] . "</brand>\n";
	echo "\t\t<reference>" . $product['reference'] . "</reference>\n";
	echo "\t\t<ean>" . $product['ean13'] . "</ean>\n";

	//echo "\t\t\t<title><![CDATA[".$product['name']." - ".html_entity_decode(Tools::displayPrice(Product::getPriceStatic($product['id_product']), $currency), ENT_COMPAT, 'UTF-8')." ]]></title>\n";
	echo "\t\t<description>";
	$cdata = true;
	$localImageUrl = "";
	if (is_array($image) AND sizeof($image))
	{
		$imageObj = new Image($image[0]['id_image']);
		//echo "<![CDATA[<img src='".$link->getImageLink($product['link_rewrite'], $image[0]['id_image'], 'small_default')."' title='".str_replace('&', '', $product['name'])."' alt='thumb' />";
		$cdata = false;
		$localImageUrl = $link->getImageLink($product['link_rewrite'], $image[0]['id_image'], 'small_default');
	}
	//if ($cdata)
		echo "<![CDATA[";
	echo str_replace('&amp;', '&', strip_tags($product['description']))."]]></description>\n";

	echo "\t\t<product_url><![CDATA[".str_replace('&amp;', '&', htmlspecialchars($link->getproductLink($product['id_product'], $product['link_rewrite'], Category::getLinkRewrite((int)($product['id_category_default']), $cookie->id_lang)))).$affiliate."]]></product_url>\n";
	echo "\t\t<image_url><![CDATA[" . str_replace('&amp;', '&', htmlspecialchars($localImageUrl)) . "]]></image_url>\n";
	echo "\t\t<price>" . round(((($product['price'] / 100) * 23) + $product['price']), 2) . "</price>\n";
	echo "\t\t<promotional_price />\n";		
	echo "\t\t<shipping_value />\n";
	echo "\t\t<store_fee />\n";
	//echo "\t\t<price>" . round(($product['price'] / 100) * $product['tax_rate'], 2) . "</price>\n";
	//echo "\t\t<promotional_price />\n";		
	//echo "\t\t<shipping_value />\n";
	//echo "\t\t<store_fee>" .$i . "</store_fee>\n";
	echo "\t</product>\n";
	$i = $i + 1;
}
echo "</products>\n";
?>

