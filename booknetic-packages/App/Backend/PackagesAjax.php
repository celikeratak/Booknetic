<?php

namespace BookneticAddon\Packages\Backend;


use BookneticAddon\Packages\Helpers\PackageData;
use BookneticAddon\Packages\Helpers\PackageService;
use BookneticAddon\Packages\Model\Package;
use BookneticApp\Models\Service;
use BookneticApp\Models\Workflow;
use BookneticApp\Providers\Common\PaymentGatewayService;
use BookneticApp\Providers\Core\Capabilities;
use BookneticApp\Providers\Helpers\Helper;
use BookneticApp\Providers\UI\TabUI;
use function BookneticAddon\Packages\bkntc__;

class PackagesAjax extends \BookneticApp\Providers\Core\Controller
{

    public function add_new()
    {
        $id = Helper::_post('id', '0', 'int');

        if( $id > 0 )
        {
            Capabilities::must('packages_edit');
        }
        else
        {
            Capabilities::must('packages_add');
        }

        $services = Service::where('is_recurring', 0)
            ->where('is_active', 1)
            ->fetchAll();

        $paymentMethods = PaymentGatewayService::getGateways( true );
        $paymentMethods = array_filter($paymentMethods, fn($p) => $p->isSupportsPackage());

        if( $id > 0 )
        {
            $package = Package::get( $id );
        }
        else
        {
            $package = [
                'id'                =>  0,
                'name'              =>  null,
                'image'             =>  null,
                'color'             =>  null,
                'duration_value'    =>  null,
                'duration'          =>  null,
                'notes'             =>  null,
                'services'          =>  null,
                'payment_methods'   =>  null,
                'price'             =>  null,
                'is_public'         =>  null
            ];

        }

        TabUI::get( 'packages_add_new' )
             ->item( 'details' )
             ->setTitle( bkntc__( 'Details' ) )
             ->addView(__DIR__ . '/view/tabs/packages_add_new_details.php')
             ->setPriority( 1 );

        TabUI::get( 'packages_add_new' )
             ->item( 'services' )
             ->setTitle( bkntc__( 'Services' ) )
             ->addView(__DIR__ . '/view/tabs/packages_add_new_services.php')
             ->setPriority( 2 );

        TabUI::get( 'packages_add_new' )
             ->item( 'price' )
             ->setTitle( bkntc__( 'Price' ) )
             ->addView(__DIR__ . '/view/tabs/packages_add_new_price.php')
             ->setPriority( 3 );

        return $this->modalView('packages_add_new', [
            'info'              =>  $package,
            'services'          =>  $services,
            'payment_methods'   =>  $paymentMethods
        ]);
    }

    public function save()
    {
        $id                 = Helper::_post('id', '0', 'int');

        if( $id > 0 )
        {
            Capabilities::must('packages_edit');
        }
        else
        {
            Capabilities::must('packages_add');
        }

        $name               = Helper::_post('name', '', 'string');
        $duration_value     = Helper::_post('duration_value', '0', 'int');
        $duration           = Helper::_post('duration', '', 'string', ['day', 'week', 'month', 'year']);
        $notes              = Helper::_post('notes', '', 'string');
        $paymentMethods     = Helper::_post('payment_methods', '', 'json');
        $isPublic           = Helper::_post('is_public', '1', 'int', ['0', '1']);
        $price              = Helper::_post('price', '', 'float');
        $services           = Helper::_post('services', '', 'json');
        $removeImage        = Helper::_post('remove_image', 0, 'int', [1]);

        $packageData = new PackageData();

        $packageData->id                = $id;
        $packageData->name              = $name;
        $packageData->image             = PackageService::uploadImage();
        $packageData->durationValue     = $duration_value;
        $packageData->duration          = $duration;
        $packageData->notes             = $notes;
        $packageData->paymentMethods    = $paymentMethods;
        $packageData->isPublic          = $isPublic;
        $packageData->price             = $price;
        $packageData->services          = $services;
        $packageData->removeOldImage    = $removeImage;

        PackageService::createPackage( $packageData );

        return $this->response( true );
    }

    public function workflow_event_package_booking_created()
    {
        $workflowId = Helper::_post('id', -1);

        $params = [
            'called_from'=>[],
            'locale' => get_locale()
        ];

        $data = json_decode(Workflow::get($workflowId)['data'], true);

        if (!empty($data))
        {
            if ( isset($data['locale'] ) ) $params['locale'] = $data['locale'];

            if ( isset ( $data['called_from'] ) ) $params['called_from'] = $data['called_from'];
        }

        $params['call_from'] = [
            'both' => bkntc__('Both'),
            'backend' => bkntc__('Backend'),
            'frontend' => bkntc__('Frontend'),
        ];

        require_once ABSPATH . 'wp-admin/includes/translation-install.php';

        $availableLocales = wp_get_available_translations();

        array_unshift( $availableLocales, [
            'language' => '',
            'iso' => [ '' ],
            'native_name' => bkntc__( 'Any locale' )
        ], [
            'language' => 'en_US',
            'iso' => [ 'en' ],
            'native_name' => 'English (United States)'
        ] );

        $params[ 'locales' ] = $availableLocales;

        return $this->modalView('workflow_event_package_booking_created', $params);
    }

    public function workflow_event_package_booking_created_save()
    {
        $workflowId     = Helper::_post('id', -1);
        $locale         = Helper::_post( 'locale', '', 'string' );
        $called_from    = Helper::_post( 'called_from', '', 'string', ['backend', 'frontend'] );

        $data = [
            'locale' => $locale,
            'called_from' => $called_from,
        ];

        Workflow::where('id', $workflowId)->update(['data' => json_encode($data)]);

        return $this->response(true);
    }

}