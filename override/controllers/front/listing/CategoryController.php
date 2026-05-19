<?php
class CategoryController extends CategoryControllerCore
{
    /*
    * module: sturls
    * date: 2022-02-24 12:43:13
    * version: 1.1.12
    */
    public function canonicalRedirection($canonicalURL = '')
    {
        return;
    }

    public function init()
    {
        parent::init();
        $id_category = $this->category->id;

        $sql = '
        SELECT cp.id_product
        FROM ' . _DB_PREFIX_ . 'category_product cp
        WHERE cp.id_category = ' . (int)$id_category;

        $product_ids = Db::getInstance()->executeS($sql);

        $ids = array_column($product_ids, 'id_product');

        $this->context->smarty->assign('productIds', $ids);
    }
}
