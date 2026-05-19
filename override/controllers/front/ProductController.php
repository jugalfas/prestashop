<?php
if (!defined('_PS_VERSION_')) { exit; }
class ProductController extends ProductControllerCore
{
    
    /*
    * module: ets_seo
    * date: 2026-05-07 13:59:06
    * version: 2.8.7
    */
    protected $redirectionExtraExcludedKeys = ['category', 'rewrite', 'id_product_attribute'];
    /*
    * module: ets_seo
    * date: 2026-05-07 13:59:06
    * version: 2.8.7
    */
    public function getTemplateVarPage()
    {
        $page = parent::getTemplateVarPage();
        if (isset($page['meta']['title'])) {
            $moduleSeo = Module::getInstanceByName('ets_seo');
            if ($moduleSeo) {
                $price = $this->getPriceProduct();
                $discount_price = $this->getPriceProduct(true);
                $brand = $this->getBrandName();
                $desc = $this->product->description_short;
                $pageTitle = $moduleSeo->formatSeoMeta($this->product->name, ['post_title' => '', 'is_title' => true, 'category' => $this->category->name, 'price' => $price, 'description' => $desc, 'brand' => $brand, 'discount_price' => $discount_price], 'product');
                $page['meta']['title'] = $moduleSeo->formatSeoMeta($page['meta']['title'], ['post_title' => $pageTitle, 'is_title' => true, 'category' => $this->category->name, 'price' => $price, 'description' => $desc, 'brand' => $brand, 'discount_price' => $discount_price], 'product');
            }
        }
        return $page;
    }
    /*
    * module: ets_seo
    * date: 2026-05-07 13:59:06
    * version: 2.8.7
    */
    public function getPriceProduct($getDiscount = false)
    {
        $id_customer = ($this->context->customer->id) ? (int) ($this->context->customer->id) : 0;
        $id_group = null;
        if ($id_customer) {
            $id_group = Customer::getDefaultGroupId((int) $id_customer);
        }
        if (!$id_group) {
            $id_group = (int) Group::getCurrent()->id;
        }
        $group = new Group($id_group);
        if ($group->price_display_method) {
            $tax = false;
        } else {
            $tax = true;
        }
        if ($getDiscount) {
            return Tools::displayPrice($this->product->getPrice($tax));
        }
        return Tools::displayPrice($this->product->getPriceWithoutReduct(!$tax));
    }
    /*
    * module: ets_seo
    * date: 2026-05-07 13:59:06
    * version: 2.8.7
    */
    public function getBrandName()
    {
        if ($this->product->id_manufacturer && ($manuf = new Manufacturer($this->product->id_manufacturer, $this->context->language->id)) && $manuf->id) {
            return $manuf->name;
        }
        return '';
    }
}
