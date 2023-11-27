<?php

namespace RefinedDigital\CMS\Modules\Settings\Http\Controllers;

use Illuminate\Http\Request;
use RefinedDigital\CMS\Modules\Core\Aggregates\ModuleAggregate;
use RefinedDigital\CMS\Modules\Core\Http\Controllers\CoreController;
use RefinedDigital\CMS\Modules\Settings\Http\Repositories\SettingRepository;

class SettingController extends CoreController
{
    protected $model = 'RefinedDigital\CMS\Modules\Settings\Models\Setting';
    protected $prefix = 'settings::';
    protected $route = 'settings';
    protected $heading = 'Settings';
    protected $button = '';

    private $settingRepository;

    public function __construct(SettingRepository $settingRepository)
    {
        $this->settingRepository = $settingRepository;
        $this->settingRepository->setModel($this->model);

        $route = explode('/', request()->path());
        $key = array_search('settings', $route);

        if ($key > 0) {
            $this->settingRepository->setSettingModel($route[$key - 1]);
        }

        $this->routes = new \stdClass();
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $config = $this->getConfig();

        $routeSettings = $this->findRouteSettings();

        // get the data listing
        $data = $this->settingRepository->getAll();
        $config['data'] = $data;
        $config['showHeader'] = false;
        $config['settingModel'] = $this->settingRepository->getSettingModel();

        if (isset($routeSettings->heading) && $routeSettings->heading) {
            $config['heading'] = $routeSettings->heading;
        }

        return parent::loadView('index', $config);
    }

    public function updateSettings(Request $request, $model)
    {
        $this->settingRepository->updateSettings($request, $model);

        return response()->json([
            'success' => 1,
        ]);
    }

    private function findRouteSettings()
    {
        $routeAggregate = app(ModuleAggregate::class);
        $menu = $routeAggregate->getMenuItems();
        $route = explode('/', request()->path());
        return $this->routeSetting($menu, $route[1] ?? null);
    }

    private function routeSetting($items, $route)
    {
        $settings = false;
        if (is_array($items) && sizeof($items)) {
            foreach ($items as $item) {
                if ($settings) {
                    break;
                }
                if (
                    isset($item->route)
                    && is_array($item->route)
                    && in_array($route, $item->route)
                    && in_array('settings.index', $item->route)
                ) {
                    $settings = $item;
                    continue;
                }

                if (isset($item->children)) {
                    $settings = $this->routeSetting($item->children, $route);
                }
            }
        }

        return $settings;
    }


}
