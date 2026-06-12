<?php

namespace Apps\Tms\Components\Pages;

use Carbon\Carbon;
use System\Base\Providers\ModulesServiceProvider\Modules\Components\ComponentsWidgets;

class Widgets extends ComponentsWidgets
{
    public function htmlCode($widget, $dashboardWidget)
    {
        return $this->getWidgetContent($widget, $dashboardWidget);
    }

    public function carousel($widget, $dashboardWidget)
    {
        if (isset($dashboardWidget['settings']['slides']) && count($dashboardWidget['settings']['slides']) > 0) {
            foreach ($dashboardWidget['settings']['slides'] as $slideKey => $slideValue) {
                if (isset($slideValue['content'])) {
                    if (str_starts_with($slideValue['content'], 'getWidgetContent(')) {
                        preg_match('/\d/', $slideValue['content'], $pageWidgetId);

                        if (!isset($pageWidgetId[0])) {
                            continue;
                        }

                        $pageWidget = $this->componentObj->basepackages->pageswidgets->getPageWidgetById((int) $pageWidgetId[0]);

                        if (isset($pageWidget['settings']['html_code'])) {
                            $dashboardWidget['settings']['slides'][$slideKey]['content'] = $pageWidget['settings']['html_code'];
                        }
                    }
                }
            }
        }

        return $this->getWidgetContent($widget, $dashboardWidget);
    }

    public function accordion($widget, $dashboardWidget)
    {
        if (isset($dashboardWidget['settings']['accordions']) && count($dashboardWidget['settings']['accordions']) > 0) {
            foreach ($dashboardWidget['settings']['accordions'] as $accordionKey => $accordionValue) {
                if (isset($accordionValue['content'])) {
                    if (str_starts_with($accordionValue['content'], 'getWidgetContent(')) {
                        preg_match('/\d/', $accordionValue['content'], $pageWidgetId);

                        if (!isset($pageWidgetId[0])) {
                            continue;
                        }

                        $pageWidget = $this->componentObj->basepackages->pageswidgets->getPageWidgetById((int) $pageWidgetId[0]);

                        if (isset($pageWidget['settings']['html_code'])) {
                            $dashboardWidget['settings']['accordions'][$accordionKey]['content'] = $pageWidget['settings']['html_code'];
                        }
                    }
                }
            }
        }

        return $this->getWidgetContent($widget, $dashboardWidget);
    }
}