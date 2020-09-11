<?php
//include_once "../config/autoload.php";

if (!defined('_PS_VERSION_'))
    exit;

class ordersexport extends Module
{
    public function __construct(){
        $this->name = 'ordersexport';
        $this->tab = 'Orders Export';
        $this->version = '1.0.0';
        $this->author = 'Arkadiusz Tomczak';
        $this->need_instance = 1;
        $this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_);
        $this->bootstrap = 1;

        parent::__construct();

        $this->displayName = $this->l('Orders Export');
        $this->description = $this->l('Wygeneruj zestawienie produktów. Po zainstalowaniu, aplikacja będzie dostępna w zakładce Zamówienia / Przygotuj zestawienie zamówień. Zadanie rekrutacyjne Convertis nr 2.');

        $this->confirmUninstall = $this->l('Czy chcesz usunąć moduł?');
    }

    public function install()
    {
        return parent::install();
    }

    public function uninstall()
    {
        if (!parent::uninstall())
            return false;
        return true;
    }

    public function enable($force_all = false)
    {
        return parent::enable($force_all)
            && $this->installTab()
            ;
    }

    public function disable($force_all = false)
    {
        return parent::disable($force_all)
            && $this->uninstallTab()
            ;
    }

    private function installTab()
    {
        $tabId = (int) Tab::getIdFromClassName('OrdersExportController');
        if (!$tabId) {
            $tabId = null;
        }

        $tab = new Tab($tabId);
        $tab->active = 1;
        $tab->class_name = 'OrdersExportController';
        // Only since 1.7.7, you can define a route name
        $tab->route_name = 'admin_my_symfony_routing';
        $tab->name = array();
        foreach (Language::getLanguages() as $lang) {
            $tab->name[$lang['id_lang']] = 'Przygotuj zestawienie zamówień';
        }
        $tab->id_parent = (int) Tab::getIdFromClassName('AdminParentOrders');
        $tab->module = $this->name;

        return $tab->save();
    }

    private function uninstallTab()
    {
        $tabId = (int) Tab::getIdFromClassName('OrdersExportController');
        if (!$tabId) {
            return true;
        }

        $tab = new Tab($tabId);

        return $tab->delete();
    }
}