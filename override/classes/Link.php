<?php
if (!defined('_PS_VERSION_')) { exit; }
class Link extends LinkCore
{
    /*
    * module: ets_seo
    * date: 2026-05-07 13:59:06
    * version: 2.8.7
    */
    protected function getLangLink($idLang = null, Context $context = null, $idShop = null)
    {
        $langLink = parent::getLangLink($idLang, $context, $idShop);
        if (!Module::isEnabled('ets_seo')) {
            return $langLink;
        }
        if (!$context) {
            $context = Context::getContext();
        }
        if (!$idLang) {
            $idLang = $context->language->id;
        }
        if (Language::isMultiLanguageActivated($idShop) && (int) Configuration::get('ETS_SEO_ENABLE_REMOVE_LANG_CODE_IN_URL') && $idLang == (int) Configuration::get('PS_LANG_DEFAULT')) {
            return '';
        }
        return $langLink;
    }
    /*
    * module: ets_seo
    * date: 2026-05-07 13:59:06
    * version: 2.8.7
    */
    public function getCategoryLink(
        $category,
        $alias = null,
        $idLang = null,
        $selectedFilters = null,
        $idShop = null,
        $relativeProtocol = false
    ) {
        if (!Module::isEnabled('ets_seo')) {
            return parent::getCategoryLink($category, $alias, $idLang, $selectedFilters, $idShop, $relativeProtocol);
        }
        
        $dispatcher = Dispatcher::getInstance();
        if (!$idLang) {
            $idLang = Context::getContext()->language->id;
        }
        $url = $this->getBaseLink($idShop, null, $relativeProtocol) . $this->getLangLink($idLang, null, $idShop);
        $params = [];
        if (Validate::isLoadedObject($category)) {
            $params['id'] = $category->id;
        } elseif (isset($category['id_category'])) {
            $params['id'] = $category['id_category'];
        } elseif (is_int($category) or ctype_digit($category)) {
            $params['id'] = (int) $category;
        } else {
            throw new \InvalidArgumentException('Invalid category parameter');
        }
        $selectedFilters = null === $selectedFilters ? '' : $selectedFilters;
        if (empty($selectedFilters)) {
            $rule = 'category_rule';
        } else {
            $rule = 'layered_rule';
            $params['selected_filters'] = $selectedFilters;
        }
        if (!$alias) {
            $category = $this->getCategoryObject($category, $idLang);
        }
        $params['rewrite'] = (!$alias) ? $category->link_rewrite : $alias;
        if ($dispatcher->hasKeyword($rule, $idLang, 'parent_rewrite', $idShop)) {
            $params['parent_rewrite'] = '';
            try {
                $cats = [];
                
                $currentCategory = $this->getCategoryObject($category, $idLang);
                foreach ($currentCategory->getParentsCategories($idLang) as $cat) {
                    if (!in_array($cat['id_category'], [1, 2, $currentCategory->id])) {
                        $cats[] = $cat['link_rewrite'];
                    }
                }
                if (count($cats)) {
                    $params['parent_rewrite'] = implode('/', array_reverse($cats));
                }
            } catch (PrestaShopException $e) {
            }
        }
        if ($dispatcher->hasKeyword($rule, $idLang, 'meta_keywords', $idShop)) {
            $category = $this->getCategoryObject($category, $idLang);
            $params['meta_keywords'] = Tools::str2url($category->getFieldByLang('meta_keywords'));
        }
        if ($dispatcher->hasKeyword($rule, $idLang, 'meta_title', $idShop)) {
            $category = $this->getCategoryObject($category, $idLang);
            $params['meta_title'] = Tools::str2url($category->getFieldByLang('meta_title'));
        }
        return $url . $dispatcher->createUrl($rule, $idLang, $params, $this->allow, '', $idShop);
    }
}
