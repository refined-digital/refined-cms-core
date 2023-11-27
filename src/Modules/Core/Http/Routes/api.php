<?php

use RefinedDigital\CMS\Modules\Core\Aggregates\RouteAggregate;
use RefinedDigital\CMS\Modules\Core\Aggregates\CustomModuleRouteAggregate;
use RefinedDigital\CMS\Modules\Core\Aggregates\PublicRouteAggregate;

Route::middleware(['web'])
    ->prefix('api')
    ->namespace('RefinedDigital\CMS\Modules\Pages\Http\Controllers')
    ->group(function() {
        Route::get('/menu/{id}',        ['uses' => 'ApiController@menu']);
        Route::get('/setup',            ['uses' => 'ApiController@setup']);
        Route::get('/pages/',           ['uses'=>'ApiController@render'])->where('uri', '(.*)');
        Route::get('/pages/{uri}',      ['uses'=>'ApiController@render'])->where('uri', '(.*)');
    })
;