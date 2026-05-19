<?php
if (!defined('_PS_VERSION_')) { exit; }
class Dispatcher extends DispatcherCore
{
    /*
    * module: ets_seo
    * date: 2026-05-07 13:59:06
    * version: 2.8.7
    */
    public $etsSeoDispatcher;
    /*
    * module: ets_seo
    * date: 2026-05-07 13:59:06
    * version: 2.8.7
    */
    protected function __construct()
    {
        if (Module::isEnabled('ets_seo')) {
            if (file_exists(_PS_MODULE_DIR_ . 'ets_seo/classes/dispatcher/EtsSeoDispatcher.php')) {
                require_once _PS_MODULE_DIR_ . 'ets_seo/classes/dispatcher/EtsSeoDispatcher.php';
            }
            if (class_exists('EtsSeoDispatcher')) {
                $this->etsSeoDispatcher = EtsSeoDispatcher::getDispatcher();
                if ($this->enabledRemoveIdInUrl() && (int) Configuration::get('PS_REWRITING_SETTINGS')) {
                    $this->default_routes = $this->etsSeoDispatcher->getDefaultRouteNoId();
                }
            }
            parent::__construct();
            $this->etsSeoDispatcher->mergeRssRoute($this);
        } else {
            parent::__construct();
        }
    }
    /*
    * module: ets_seo
    * date: 2026-05-07 13:59:06
    * version: 2.8.7
    */
    public function getController($id_shop = null)
    {
        if (!Module::isEnabled('ets_seo')) {
            return parent::getController($id_shop);
        }
        if (Tools::getIsset('controller') && ('' != Tools::getValue('controller'))) {
            return parent::getController($id_shop);
        }
        $this->etsSeoDispatcher && $this->etsSeoDispatcher->checkForRedirect($this->request_uri);
        parent::getController($id_shop);
        if ($this->etsSeoDispatcher) {
            if ($this->etsSeoDispatcher && $this->enabledRemoveIdInUrl()) {
                $this->controller = $this->etsSeoDispatcher->getController($this, $this->controller);
            }
            if (('404' == $this->controller || 'pagenotfound' == $this->controller) && (int) Configuration::get('ETS_SEO_ENABLE_REDRECT_NOTFOUND')) {
                if ($this->enabledRemoveIdInUrl()) {
                    $this->etsSeoDispatcher->redirectToOldUrl($this, true);
                } else {
                    $this->etsSeoDispatcher->redirectToOldUrl($this);
                }
            }
        }
        return $this->controller;
    }
    /*
    * module: ets_seo
    * date: 2026-05-07 13:59:06
    * version: 2.8.7
    */
    public function getControllerForRedirect($id_shop = null)
    {
        if ($this->etsSeoDispatcher) {
            $this->controller = null;
            unset($_GET['controller']);
            $controller = parent::getController($id_shop);
            $this->controller = $this->etsSeoDispatcher->getSitemapAndRssController($this, $controller, $this->request_uri);
        }
        return $this->controller;
    }
    /*
    * module: ets_seo
    * date: 2026-05-07 13:59:06
    * version: 2.8.7
    */
    public function getControllerCore($id_shop = null)
    {
        return parent::getController($id_shop);
    }
    /*
    * module: ets_seo
    * date: 2026-05-07 13:59:06
    * version: 2.8.7
    */
    public function getControllerChecking($id_shop = null)
    {
        $this->controller = null;
        unset($_GET['controller']);
        return $this->getController($id_shop);
    }
    /*
    * module: ets_seo
    * date: 2026-05-07 13:59:06
    * version: 2.8.7
    */
    public function getRoutes()
    {
        return $this->routes;
    }
    /*
    * module: ets_seo
    * date: 2026-05-07 13:59:06
    * version: 2.8.7
    */
    public function setRoutes($routes)
    {
        $this->routes = $routes;
    }
    /*
    * module: ets_seo
    * date: 2026-05-07 13:59:06
    * version: 2.8.7
    */
    public function enabledRemoveIdInUrl()
    {
        return (int) Configuration::get('ETS_SEO_ENABLE_REMOVE_ID_IN_URL')
            && !defined('_PS_ADMIN_DIR_')
            && (int) Configuration::get('PS_REWRITING_SETTINGS');
    }
    /*
    * module: ets_seo
    * date: 2026-05-07 13:59:06
    * version: 2.8.7
    */
    public function setOldRoutes($id_shop = null)
    {
        $context = Context::getContext();
        if (isset($context->shop) && null === $id_shop) {
            $id_shop = (int) $context->shop->id;
        }
        $language_ids = Language::getIDs();
        if (isset($context->language) && !in_array($context->language->id, $language_ids)) {
            $language_ids[] = (int) $context->language->id;
        }
        foreach ($this->default_routes as $id => $route) {
            if (method_exists($this, 'computeRoute')) {
                $route = $this->computeRoute(
                    $route['rule'],
                    $route['controller'],
                    $route['keywords'],
                    isset($route['params']) ? $route['params'] : []
                );
                foreach ($language_ids as $id_lang) {
                    $this->routes[$id_shop][$id_lang][$id] = $route;
                }
            } else {
                foreach ($language_ids as $id_lang) {
                    $this->addRoute(
                        $id,
                        $route['rule'],
                        $route['controller'],
                        $id_lang,
                        $route['keywords'],
                        isset($route['params']) ? $route['params'] : [],
                        $id_shop
                    );
                }
            }
        }
        if ($this->use_routes) {
            $sql = 'SELECT m.page, ml.url_rewrite, ml.id_lang
					FROM `' . _DB_PREFIX_ . 'meta` m
					LEFT JOIN `' . _DB_PREFIX_ . 'meta_lang` ml ON (m.id_meta = ml.id_meta' . Shop::addSqlRestrictionOnLang('ml', (int) $id_shop) . ')
					ORDER BY LENGTH(ml.url_rewrite) DESC';
            if ($results = Db::getInstance()->executeS($sql)) {
                foreach ($results as $row) {
                    if ($row['url_rewrite']) {
                        $this->addRoute(
                            $row['page'],
                            $row['url_rewrite'],
                            $row['page'],
                            $row['id_lang'],
                            [],
                            [],
                            $id_shop
                        );
                    }
                }
            }
            if (!$this->empty_route) {
                $this->empty_route = [
                    'routeID' => 'index',
                    'rule' => '',
                    'controller' => 'index',
                ];
            }
        }
    }
    /*
    * module: ets_seo
    * date: 2026-05-07 13:59:06
    * version: 2.8.7
    */
    public function validateRoute($route_id, $rule, &$errors = [])
    {
        if (!Module::isEnabled('ets_seo')) {
            return parent::validateRoute($route_id, $rule, $errors);
        }
        if ((int) Tools::getValue('ETS_SEO_ENABLE_REMOVE_ID_IN_URL')
            || ((int) Configuration::get('ETS_SEO_ENABLE_REMOVE_ID_IN_URL') && !(int) Configuration::get('PS_REWRITING_SETTINGS'))) {
            $errors = [];
            if (!isset($this->default_routes[$route_id])) {
                return false;
            }
            foreach ($this->default_routes[$route_id]['keywords'] as $keyword => $data) {
                if (isset($data['param']) && ('module' == $route_id || ('module' != $route_id && 'rewrite' == $keyword)) && !preg_match('#\{([^{}]*:)?' . $keyword . '(:[^{}]*)?\}#', $rule)) {
                    $errors[] = $keyword;
                }
            }
            return (count($errors)) ? false : true;
        }
        return parent::validateRoute($route_id, $rule, $errors);
    }
    /*
    * module: ets_seo
    * date: 2026-05-07 13:59:06
    * version: 2.8.7
    */
    public function getFontController()
    {
        return $this->front_controller;
    }
    /*
    * module: ets_seo
    * date: 2026-05-07 13:59:06
    * version: 2.8.7
    */
    public function setFrontController($controller)
    {
        $this->front_controller = $controller;
    }
    /*
    * module: ets_seo
    * date: 2026-05-07 13:59:06
    * version: 2.8.7
    */
    public function getUseDefaultController()
    {
        return $this->useDefaultController();
    }
    /*
    * module: ets_seo
    * date: 2026-05-07 13:59:06
    * version: 2.8.7
    */
    public function setDefaultRoutes($routes)
    {
        $this->default_routes = $routes;
    }
    /*
    * module: ets_seo
    * date: 2026-05-07 13:59:06
    * version: 2.8.7
    */
    public function publicLoadRoutes()
    {
        parent::loadRoutes();
    }
    /*
    * module: ets_seo
    * date: 2026-05-07 13:59:06
    * version: 2.8.7
    */
    protected function setRequestUri()
    {
        parent::setRequestUri();
        if (!isset(${'_GET'}['isolang']) && Module::isEnabled('ets_seo') && (int) Configuration::get('ETS_SEO_ENABLE_REMOVE_LANG_CODE_IN_URL') && ($idLang = (int) Configuration::get('PS_LANG_DEFAULT')) && $idLang && ($iso = Language::getIsoById($idLang))) {
            $_GET['isolang'] = $iso;
        }
    }
}
