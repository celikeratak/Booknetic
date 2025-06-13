<?php

namespace BookneticAddon\AdvancedReports;

use BookneticApp\Providers\UI\MenuUI;
use BookneticAddon\AdvancedReports\Backend\Ajax;
use BookneticAddon\AdvancedReports\Backend\Controller;
use BookneticApp\Providers\Core\AddonLoader;
use BookneticApp\Providers\Core\Capabilities;
use BookneticApp\Providers\Core\Route;

function bkntc__ ( $text, $params = [], $esc = true )
{
    return \bkntc__( $text, $params, $esc, AdvancedReportsAddon::getAddonTextDomain() );
}

class AdvancedReportsAddon extends AddonLoader
{

    public function init ()
    {
	    Capabilities::registerTenantCapability( 'advancedreports', bkntc__('AdvancedReports module') );

	    if( ! Capabilities::tenantCan( 'advancedreports' ) )
		    return;

	    Capabilities::register( 'advancedreports', bkntc__('AdvancedReports'));

	    add_filter( 'bkntc_localization' , function ($lang){
	        return array_merge(
	            ['appointment_count'					        => bkntc__('Appointment count')],
                $lang
            );
        });
    }

    public function initBackend()
{
    if(!Capabilities::tenantCan('advancedreports')){
        return;
    }

    if(Capabilities::userCan('advancedreports')){
        Route::get('advancedreports', Controller::class);
        Route::post('advancedreports', Ajax::class);

        MenuUI::get('advancedreports')
            ->setTitle(bkntc__('Advanced Reports'))
            ->setIcon('fa fa-university')
            ->setPriority(111);

        add_action('admin_enqueue_scripts', function(){
            wp_enqueue_style(
                'datatables-css',
                'https://cdn.datatables.net/2.2.2/css/dataTables.dataTables.min.css',
                [],
                '2.2.2'
            );
            wp_enqueue_script(
                'datatables-js',
                'https://cdn.datatables.net/2.2.2/js/dataTables.min.js',
                ['jquery'],
                '2.2.2',
                true
            );

            wp_enqueue_script(
                'bkntc-advancedreports-js',
                AdvancedReportsAddon::loadAsset('assets/backend/js/advancedreports.js'),
                ['jquery','datatables-js'],
                '1.0.0',
                true
            );

            wp_localize_script(
                'bkntc-advancedreports-js',
                'booknetic',
                [
                    'ajaxUrl' => admin_url('admin-ajax.php'),
                ]
            );
        }, 99);
    }
}

}