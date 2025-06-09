<?php

namespace BookneticAddon\Packages\Frontend;

use BookneticAddon\Packages\Helpers\PackageBookingData;
use BookneticAddon\Packages\Helpers\PackageService;
use BookneticAddon\Packages\Model\Package;
use BookneticAddon\Packages\Model\PackageBooking;
use BookneticApp\Backend\Appointments\Helpers\AppointmentSmartObject;
use BookneticApp\Backend\Customers\Helpers\CustomerData;
use BookneticApp\Backend\Customers\Helpers\CustomerService;
use BookneticApp\Frontend\Controller\AjaxHelper;
use BookneticApp\Models\Customer;
use BookneticApp\Models\Service;
use BookneticApp\Models\ServiceCategory;
use BookneticApp\Providers\Common\PaymentGatewayService;
use BookneticApp\Providers\Core\Capabilities;
use BookneticApp\Providers\Core\FrontendAjax;
use BookneticApp\Providers\Core\Permission;
use BookneticApp\Providers\Helpers\Date;
use BookneticApp\Providers\Helpers\Helper;

use BookneticApp\Providers\Helpers\Math;
use function BookneticAddon\Packages\bkntc__;

class Ajax extends FrontendAjax
{

    public function pacakgesLoad()
    {
        $packageId  = Helper::_post('package_id', 0, 'int');
        $step       = Helper::_post('step_id', '', 'string', ['packages_information', 'packages_confirm_details']);

        if( $packageId <= 0 )
            return $this->response(false);

        if( $step === 'packages_information' )
        {
            return $this->renderInformationStep( $packageId );
        }
        else if( $step === 'packages_confirm_details' )
        {
            return $this->renderConfirmationStep( $packageId );
        }

        return $this->response(false, bkntc__('Something went wrong!'));
    }

    private function renderInformationStep( $packageId )
    {
        $package = Package::withTranslations()->get( $packageId );

        if( ! $package )
            return $this->response(false);

        $name		= '';
        $surname	= '';
        $email		= '';
        $phone 		= '';

        $disableNameInput       = false;
        $disableSurnameInput    = false;
        $disableEmailInput      = false;
        $disablePhoneInput      = false;

        $canChangeEmailInput    = true;

        if( is_user_logged_in() )
        {
            $checkCustomerExists = Customer::where('user_id', get_current_user_id())->fetch();

            if ( $checkCustomerExists )
            {
                $name		= $checkCustomerExists->first_name;
                $surname	= $checkCustomerExists->last_name;
                $email		= $checkCustomerExists->email;
                $phone		= $checkCustomerExists->phone_number;

                $disableNameInput       = !empty($name);
                $disableSurnameInput    = !empty($surname);
                $disableEmailInput      = true;
                $disablePhoneInput      = !empty($phone);
            }
        }

        $emailIsRequired = Helper::getOption('set_email_as_required', 'on');
        $phoneIsRequired = Helper::getOption('set_phone_as_required', 'off');

        $howManyPeopleCanBring = false;

        $parameters = [
            'package_id'                    => $package->id,

            'customer_identifier'           => Helper::getOption('customer_identifier', 'email'),

            'name'				            => $name,
            'surname'			            => $surname,
            'email'				            => $email,
            'phone'				            => $phone,

            'disable_name_input'            => $disableNameInput,
            'disable_surname_input'         => $disableSurnameInput,
            'disable_email_input'           => $disableEmailInput,
            'disable_phone_input'           => $disablePhoneInput,

            'can_change_email_input'        => $canChangeEmailInput,

            'email_is_required'	            => $emailIsRequired,
            'phone_is_required'	            => $phoneIsRequired,

            'default_phone_country_code'    => Helper::getOption('default_phone_country_code', ''),

            'show_only_name'                => Helper::getOption('separate_first_and_last_name', 'on') == 'off',

            'how_many_people_can_bring'     => $howManyPeopleCanBring
        ];

        /* Facebook Login button */
        $facebookLoginEnable = Helper::getOption('facebook_login_enable', 'off', false);
        $facebookAppId = Helper::getOption('facebook_app_id', '', false);
        $facebookAppSecret = Helper::getOption('facebook_app_secret', '', false);

        $showFacebookLoginButton = $facebookLoginEnable == 'on' && !empty( $facebookAppId ) && !empty( $facebookAppSecret );
        $facebookLoginButtonUrl = site_url() . '/?' . Helper::getSlugName() . '_action=facebook_login';

        $parameters['show_facebook_login_button'] = $showFacebookLoginButton;
        $parameters['facebook_login_button_url'] = $facebookLoginButtonUrl;
        /* end */

        /* Google Login button */
        $googleLoginEnable = Helper::getOption('google_login_enable', 'off', false);
        $googleAppId = Helper::getOption('google_login_app_id', '', false);
        $googleAppSecret = Helper::getOption('google_login_app_secret', '', false);

        $showGoogleLoginButton = $googleLoginEnable == 'on' && !empty( $googleAppId ) && !empty( $googleAppSecret );
        $googleLoginButtonUrl = site_url() . '/?' . Helper::getSlugName() . '_action=google_login';

        $parameters['show_google_login_button'] = $showGoogleLoginButton;
        $parameters['google_login_button_url'] = $googleLoginButtonUrl;
        /* end */

        $parameters = apply_filters('bkntc_booking_panel_information_step_parameters', $parameters);

        $viewRender = $this->view(__DIR__ . '/views/booking_panel/packages_information.php', $parameters);

        return $this->response( true, [
            'html'  =>  $viewRender['html']
        ] );
    }

    private function renderConfirmationStep( $packageId )
    {
        $package = Package::withTranslations()->get( $packageId );

        if( ! $package )
            return $this->response(false);

        $services = [];
        $totalServicesPrice = 0;
        foreach ( json_decode($package->services, true) as $service )
        {
            $serviceInf = Service::withTranslations()->get( $service['id'] );
            $sPrice = Math::mul($serviceInf->price, $service['count']);

            $services[] = [
                'name'  =>  $serviceInf->name,
                'count' =>  $service['count'],
                'price' =>  $sPrice
            ];

            $totalServicesPrice = Math::add($sPrice, $totalServicesPrice);
        }

        $discount = Math::sub($totalServicesPrice, $package->price);

        $hide_price_section	    = Helper::getOption('hide_price_section', 'off') == 'on';
        $hideMethodSelecting    = Helper::getOption('disable_payment_options', 'off') == 'on';
        $allowedPaymentMethods  = json_decode( $package->payment_methods ?? '[]' ,true );
        $orderedPaymentGateways = explode(',', Helper::getOption('payment_gateways_order', 'local'));

        usort($allowedPaymentMethods, function( $pg1, $pg2 ) use( $orderedPaymentGateways )
        {
            $pg1Index = ! in_array( $pg1, $orderedPaymentGateways ) ? 99 : array_search($pg1, $orderedPaymentGateways);
            $pg2Index = ! in_array( $pg2, $orderedPaymentGateways ) ? 99 : array_search($pg2, $orderedPaymentGateways);

            return $pg1Index < $pg2Index ? -1 : 1;
        });

        $paymentMethods = [];
        foreach ( $allowedPaymentMethods AS $pmSlug )
        {
            $gateway = PaymentGatewayService::find( $pmSlug );

            if ( ! empty( $gateway ) )
            {
                $paymentMethods[] = $gateway;
            }
        }

        $viewRender = $this->view(__DIR__ . '/views/booking_panel/packages_confirmation.php', [
            'package'               =>  $package,
            'services'              =>  $services,
            'discount'              =>  $discount,
            'payment_methods'       =>  $paymentMethods,
            'hide_payments'		    =>	$hideMethodSelecting,
            'hide_price_section'    =>  $hide_price_section,
        ]);

        return $this->response( true, [
            'html'  =>  $viewRender['html']
        ] );
    }

    public function pacakgesConfirm()
    {
        if( ! Capabilities::tenantCan( 'receive_appointments' ) )
            return $this->response( false );

        try {
            AjaxHelper::validateGoogleReCaptcha();
        } catch ( \Exception $e ) {
            return $this->response( false, $e->getMessage() );
        }

        $packageId          = Helper::_post('package_id', 0, 'int');
        $paymentMethod      = Helper::_post('payment_method', '', 'string' );
        $customerData	    = Helper::_post('customer_data', '[]', 'string');
        $clientTimezone		= Helper::_post('client_time_zone', '-', 'string');

        $customerData = json_decode( $customerData, true );

        $package = Package::withTranslations()->get( $packageId );

        if( ! $package )
            return $this->response(false);

        $allowedPaymentMethods = json_decode( $package->payment_methods ?? '[]', true );

        $paymentGateway = PaymentGatewayService::find( $paymentMethod );

        if ( ! $paymentGateway || ! in_array( $paymentMethod, $allowedPaymentMethods ) ) {
            return $this->response( false, bkntc__( 'Payment method is not supported' ) );
        }

        $createCustomerData = new CustomerData();

        $createCustomerData->first_name = $customerData['first_name'];
        $createCustomerData->last_name  = $customerData['last_name'];
        $createCustomerData->phone      = $customerData['phone'];
        $createCustomerData->email      = $customerData['email'];

        $createWpUser = Helper::getOption('new_wp_user_on_new_booking', 'off', false) == 'on';

        $createCustomer = CustomerService::createCustomerIfDoesntExist( $createCustomerData, $createWpUser );

        $packageBookingData = new PackageBookingData();

        $packageBookingData->packageId          = $packageId;
        $packageBookingData->customerId         = $createCustomer;
        $packageBookingData->locale             = get_locale();
        $packageBookingData->clientTimezone     = $clientTimezone;
        $packageBookingData->paymentStatus      = PackageBookingData::PAYMENT_STATUSES['NOT_PAID'];
        $packageBookingData->paymentMethod      = $paymentMethod;
        $packageBookingData->totalAmount        = $package->price;
        $packageBookingData->paidAmount         = 0;

        $packageBookingId = PackageService::bookPackage($packageBookingData);

        $paymentItems = [
            [
                'name'  =>  $package->name,
                'price' =>  $package->price,
                'image' =>  PackageService::imageUrl( $package->image )
            ]
        ];

        $paymenCustomData = [
            'payent_for'            =>  'packages',
            'package_booking_id'    =>  $packageBookingId
        ];

        $paymentData = $paymentGateway->createPayment( $paymentItems, $paymenCustomData );

        $responseStatus = $paymentData->status ?? false;
        $responseData = $paymentData->data ?? [];

        $responseData['package_booking_id'] = $packageBookingId;
        $responseData['token'] = PackageService::getPackagePrivateToken( $packageBookingId );

        $timeLimit = Helper::getOption('max_time_limit_for_payment', 10);
        $responseData["payment_link_expiration_time"] = $timeLimit * 60;

        return $this->response( $responseStatus, $responseData );
    }

	public function packagesCustomerPanelPayPackageBooking()
	{
		$bookedPackageId    = Helper::_post('packageBookingId', 0, 'int');
		$paymentMethod      = Helper::_post('paymentMethod', '', 'string' );

		if ( $bookedPackageId <= 0 )
			return $this->response(false);

		$bookedPackage = PackageBooking::get( $bookedPackageId );

		if( ! $bookedPackage )
			return $this->response(false);

		$package = Package::get( $bookedPackage->package_id );

		if ( ! $package )
			return $this->response(false);

		if ( $bookedPackage->payment_status != PackageBookingData::PAYMENT_STATUSES['NOT_PAID'] )
			return $this->response(false);

		$amount = Math::sub($bookedPackage->total_amount, $bookedPackage->paid_amount);

		if ( $amount <= 0 )
			return $this->response(false);

		$allowedPaymentMethods = json_decode( $package->payment_methods ?? '[]', true );

		$paymentGateway = PaymentGatewayService::find( $paymentMethod );

		if ( ! $paymentGateway || ! in_array( $paymentMethod, $allowedPaymentMethods ) ) {
			return $this->response( false, bkntc__( 'Payment method is not supported' ) );
		}

		$paymentItems = [
			[
				'name'  =>  $package->name,
				'price' =>  $amount,
				'image' =>  PackageService::imageUrl( $package->image )
			]
		];

		$paymenCustomData = [
			'payent_for'            =>  'packages',
			'package_booking_id'    =>  $bookedPackageId
		];

		$paymentData = $paymentGateway->createPayment( $paymentItems, $paymenCustomData );

		$responseStatus = $paymentData->status ?? false;
		$responseData = $paymentData->data ?? [];

		$responseData['package_booking_id'] = $bookedPackageId;
		$responseData['token'] = PackageService::getPackagePrivateToken( $bookedPackageId ); // burda gedib tezeden dbdan birbasha burda da hashlemek olar

		$timeLimit = Helper::getOption('max_time_limit_for_payment', 10);
		$responseData["payment_link_expiration_time"] = $timeLimit * 60;

		return $this->response( $responseStatus, $responseData );
	}

	public function getAllowedPaymentGateways()
	{
		$bookedPackageId = Helper::_post('id', 0, 'int');

		if ( $bookedPackageId <= 0 )
		{
			return $this->response( false );
		}

		$bookedPackage = PackageBooking::get( $bookedPackageId );

		if ( ! $bookedPackage )
		{
			return $this->response( false );
		}

		$package = Package::get( $bookedPackage->package_id );

		if ( ! $package )
		{
			return $this->response( false );
		}

		$specificPaymentMethods = json_decode( $package->payment_methods ?? '[]', true );

		if( empty( $specificPaymentMethods ) )
		{
			$paymentMethods = PaymentGatewayService::getEnabledGatewayNames();
		}else
		{
			$paymentMethods = $specificPaymentMethods;
		}

		$dataForReturn = [];
		foreach ( $paymentMethods AS $paymentMethod )
		{
			if( $paymentMethod === 'local' )
				continue;

			$paymentMethodService = PaymentGatewayService::find( $paymentMethod );

			$dataForReturn[] = [
				'id'    => $paymentMethod,
				'text'  => $paymentMethodService->getTitle()
			];
		}

		return $this->response(true, [ 'results' => $dataForReturn ] );
	}

    public function packagesFinishBooking()
    {
        $packageBookingId = Helper::_post('package_booking_id', 0, 'int');

        $packageBookingInf = PackageBooking::get( $packageBookingId );

        if( !$packageBookingInf )
            return;

        $services = [];
        foreach ( json_decode( $packageBookingInf->appointments ) AS $slotId => $appointment )
        {
            if( !isset( $services[$appointment->service_id] ) )
            {
                $serviceInf = Service::withTranslations()->noTenant()->get( $appointment->service_id );
                $services[$appointment->service_id] = [
                    'info'      =>  $serviceInf,
                    'category'  =>  ServiceCategory::noTenant()->get( $serviceInf->category_id ),
                    'slots'     =>  []
                ];
            }

            $appointmentInfo = false;
            if( $appointment->appointment_id > 0 )
            {
                $appointmentInfo = new AppointmentSmartObject( $appointment->appointment_id, true );
            }

            $services[$appointment->service_id]['slots'][] = [
                'slot_id'           =>  $slotId,
                'appointment_id'    =>  $appointment->appointment_id,
                'appointment_info'  =>  $appointmentInfo
            ];
        }

        $viewRender = $this->view(__DIR__ . '/views/booking_panel/packages_finish.php', [
            'services'                  =>  $services,

        ]);

        return $this->response( true, [
            'html'  => $viewRender['html']
        ] );
    }

    public function packagesBookingDelete()
    {
        $packageBookingId = Helper::_post('package_booking_id', 0, 'int');
        $token = Helper::_post('token', '', 'string');

        $packageBookingInf = PackageBooking::get( $packageBookingId );

        if( !$packageBookingInf || PackageService::getPackagePrivateToken( $packageBookingId ) !== $token )
            return $this->response( true );

        PackageBooking::where( 'id', $packageBookingId )->delete();

        return $this->response( true );
    }

    public function packagesLoadBookingPanel()
    {
        $packageBookingId   = Helper::_post( 'package_booking_id', 0, 'int' );
        $serviceId          = Helper::_post( 'service_id', 0, 'int' );

        $packageBookingInf  = PackageBooking::noTenant()->get( $packageBookingId );

        if ( ! $packageBookingInf )
            return $this->response(false);

        $serviceExists = false;
        foreach ( json_decode( $packageBookingInf->appointments ) AS $appointment )
        {
            if( $appointment->service_id == $serviceId )
            {
                $serviceExists = true;
                break;
            }
        }

        if( ! $serviceExists )
            return $this->response(false);

        $oldTenantId = Permission::tenantId();
        Permission::setTenantId( $packageBookingInf->tenant_id );

        $bookneticShortcode = do_shortcode('[booknetic service='.$serviceId.']');

        Permission::setTenantId( $oldTenantId );

        return $this->response(true, [
            'html'      =>  $bookneticShortcode,
            'tenant_id' => $packageBookingInf->tenant_id
        ]);
    }

    public function packagesCustomerPanelGetPackageBookings()
    {
        $customerIDs = array_column( CustomerService::getCustomersOfLoggedInUser(), 'id' );
		$packageBookings = [];

		if ( ! empty( $customerIDs ) ) {
			$packageBookings = PackageBooking::noTenant()
			                                 ->where('customer_id', $customerIDs)
//											 ->where('payment_status', '<>', 'not_paid')
			                                 ->fetchAll();
		}

        $viewRender = $this->view(__DIR__ . '/views/customer_panel/packages_tab.php', [
            'package_bookings'  =>  $packageBookings
        ]);

        return $this->response( true, [
            'html'  =>  $viewRender['html']
        ] );
    }

    public function packagesCustomerPanelManagePackageBooking()
    {
        $customerIDs = array_column( CustomerService::getCustomersOfLoggedInUser(), 'id' );

        $packageBookingId = Helper::_post('id', 0, 'int');

        $packageBookingInf = PackageBooking::noTenant()
                                           ->where('id', $packageBookingId)
                                           ->where('customer_id', $customerIDs)
                                           ->fetch();

        if( !$packageBookingInf )
            return $this->response(false);

        $slots = [];
        $saveOldTenantId = Permission::tenantId();
        foreach ( json_decode( $packageBookingInf->appointments ) AS $slotId => $appointment )
        {
            if( !isset( $slots[$appointment->service_id] ) )
            {
                $serviceInf = Service::noTenant()->withTranslations()->get( $appointment->service_id );
                $slots[$appointment->service_id] = [
                    'info'      =>  $serviceInf,
                    'category'  =>  ServiceCategory::noTenant()->get( $serviceInf->category_id ),
                    'slots'     =>  []
                ];
            }

            $appointmentInfo = false;
            $appointmentDate = '';
            $appointmentStartTime = '';
            $appointmentEndTime = '';
            if( $appointment->appointment_id > 0 )
            {
                $appointmentInfo = new AppointmentSmartObject( $appointment->appointment_id, true );

                Permission::setTenantId( $appointmentInfo->getInfo()->tenant_id );

                $duration = (int)$appointmentInfo->getInfo()->ends_at - (int)$appointmentInfo->getInfo()->starts_at;
                $appointmentDate = Date::datee( $appointmentInfo->getInfo()->starts_at );
                $appointmentStartTime = $duration >= 24 * 60 * 60 ? '' : Date::time( $appointmentInfo->getInfo()->starts_at );
                $appointmentEndTime = $duration >= 24 * 60 * 60 ? '' : Date::time( $appointmentInfo->getInfo()->ends_at );

                Permission::setTenantId( $saveOldTenantId );
            }

            $slots[$appointment->service_id]['slots'][] = [
                'slot_id'                   =>  $slotId,
                'appointment_id'            =>  $appointment->appointment_id,
                'appointment_info'          =>  $appointmentInfo,
                'appointment_date'          =>  $appointmentDate,
                'appointment_start_time'    =>  $appointmentStartTime,
                'appointment_end_time'      =>  $appointmentEndTime,
            ];
        }

        $viewRender = $this->view(__DIR__ . '/views/customer_panel/packages_tab_manage_package.php', [
            'info'      =>  $packageBookingInf,
            'slots'     =>  $slots
        ]);

        return $this->response( true, [
            'html'  =>  $viewRender['html']
        ] );
    }

}