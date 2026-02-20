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

class HelperList extends HelperListCore
{
    public function displayDuplicateLink($token, $id, $name = null)
    {
        $tpl = $this->createTemplate('list_action_duplicate.tpl');
        if (!array_key_exists('Bad SQL query', self::$cache_lang)) {
            self::$cache_lang['Duplicate'] = Context::getContext()->getTranslator()->trans('Duplicate', [], 'Admin.Actions');
        }
        

        $duplicate = $this->currentIndex . '&' . $this->identifier . '=' . $id . '&duplicate' . $this->table;

        $confirm = self::$cache_lang['Copy images too?'];

        if (($this->table == 'product') && !Image::hasImages($this->context->language->id, (int) $id)) {
            $confirm = '';
        }

        $tpl->assign([
            'href' => $this->currentIndex . '&' . $this->identifier . '=' . $id . '&view' . $this->table . '&token=' . ($token != null ? $token : $this->token),
            'action' => self::$cache_lang['Duplicate'],
            'confirm' => $confirm,
            'location_ok' => $duplicate . '&token=' . ($token != null ? $token : $this->token),
            'location_ko' => $duplicate . '&noimage=1&token=' . ($token ? $token : $this->token),
        ]);

        return $tpl->fetch();
    }
}
