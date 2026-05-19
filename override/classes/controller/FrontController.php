<?php
if (!defined('_PS_VERSION_')) { exit; }
class FrontController extends FrontControllerCore
{
    
    
    /*
    * module: ets_seo
    * date: 2026-05-07 13:59:06
    * version: 2.8.7
    */
    protected $redirectionExtraExcludedKeys = ['rewrite', 'category'];
    /*
    * module: ets_seo
    * date: 2026-05-07 13:59:06
    * version: 2.8.7
    */
    protected function redirect()
    {
        if (Module::isEnabled('ets_seo')) {
            Hook::exec('actionFrontControllerRedirectBefore', ['redirect_after' => $this->redirect_after, 'controller' => $this]);
        }
        parent::redirect();
    }
}
