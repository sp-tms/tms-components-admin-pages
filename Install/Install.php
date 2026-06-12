<?php

namespace Apps\Tms\Components\Pages\Install;

use System\Base\BasePackage;
use System\Base\Providers\ModulesServiceProvider\MenuInstaller;
use System\Base\Providers\ModulesServiceProvider\WidgetInstaller;

class Install extends BasePackage
{
    protected $menuInstaller;

    protected $widgetInstaller;

    public function init()
    {
        $this->menuInstaller = new MenuInstaller;

        $this->widgetInstaller = new WidgetInstaller;

        return $this;
    }

    public function install()
    {
        $this->installMenu();

        $this->installWidget();

        return true;
    }

    protected function installMenu()
    {
        $this->menuInstaller->installMenu($this);

        return true;
    }

    protected function installWidget()
    {
        $this->widgetInstaller->installWidget($this);

        return true;
    }

    public function uninstall($remove = false)
    {
        if ($remove) {
            $this->menuInstaller->uninstallMenu($this);

            $this->widgetInstaller->uninstallWidget($this);
        }

        return true;
    }
}