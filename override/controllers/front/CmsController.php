<?php
/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/OSL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to https://devdocs.prestashop.com/ for more information.
 *
 * @author    PrestaShop SA and Contributors <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */
class CmsController extends CmsControllerCore
{
    /**
     * Assign template vars related to page content.
     *
     * @see FrontController::initContent()
     */
    /*
    * module: wkwebp
    * date: 2023-01-12 11:40:09
    * version: 4.1.0
    */
    public function initContent()
    {
        if ($this->assignCase == 1) {
            $cmsVar = $this->objectPresenter->present($this->cms);
            $filteredCmsContent = Hook::exec(
                'filterCmsContent',
                ['object' => $cmsVar],
                $id_module = null,
                $array_return = false,
                $check_exceptions = true,
                $use_push = false,
                $id_shop = null,
                $chain = true
            );
            if (!empty($filteredCmsContent['object'])) {
                $cmsVar = $filteredCmsContent['object'];
            }
            if (Module::isEnabled('wkwebp')
                && Configuration::get('WK_WEBP_ENABLE_MODULE')
                && !Context::getContext()->cookie->wk_webp_safari
            ) {
                $matches = array();
                preg_match_all('/<img src\s*=\s*"(.+?)"/', $cmsVar['content'], $matches);
                if ($matches) {
                    foreach ($matches[1] as $match) {
                        $file = pathinfo($match);
                        $cmsName = $file['filename'];
                        if (file_exists(_PS_MODULE_DIR_.'wkwebp/views/img/cms/'.urldecode($cmsName).'.webp')) {
                            $dest = Tools::getShopDomainSsl(true, true).__PS_BASE_URI__.
                            '/modules/wkwebp/views/img/cms/' . $cmsName . '.webp';
                            
                            $cmsVar['content'] = str_replace($match, $dest, $cmsVar['content']);
                        }
                    }
                }
            }
            
             
            $this->context->smarty->assign([
                'cms' => $cmsVar,
            ]);
            if ($this->cms->indexation == 0) {
                $this->context->smarty->assign('nobots', true);
            }
            $this->setTemplate(
                'cms/page',
                ['entity' => 'cms', 'id' => $this->cms->id]
            );
        } elseif ($this->assignCase == 2) {
            $cmsCategoryVar = $this->getTemplateVarCategoryCms();
            $filteredCmsCategoryContent = Hook::exec(
                'filterCmsCategoryContent',
                ['object' => $cmsCategoryVar],
                $id_module = null,
                $array_return = false,
                $check_exceptions = true,
                $use_push = false,
                $id_shop = null,
                $chain = true
            );
            if (!empty($filteredCmsCategoryContent['object'])) {
                $cmsCategoryVar = $filteredCmsCategoryContent['object'];
            }
            $this->context->smarty->assign($cmsCategoryVar);
            $this->setTemplate('cms/category');
        }
        parent::initContent();
    }
}
