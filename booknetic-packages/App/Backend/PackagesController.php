<?php

namespace BookneticAddon\Packages\Backend;

use BookneticAddon\Packages\Helpers\PackageService;
use BookneticAddon\Packages\Model\Package;
use BookneticApp\Models\Service;
use BookneticApp\Providers\Core\Capabilities;
use BookneticApp\Providers\Helpers\Helper;
use BookneticApp\Providers\Helpers\Math;
use BookneticApp\Providers\UI\Abstracts\AbstractDataTableUI;
use BookneticApp\Providers\UI\DataTableUI;
use function BookneticAddon\Packages\bkntc__;


class PackagesController extends \BookneticApp\Providers\Core\Controller
{

    public function index()
    {
        Capabilities::must('packages');

        $packages = new Package();

        $dataTable = new DataTableUI( $packages );

        if (Capabilities::userCan('packages_edit'))
            $dataTable->addAction('edit', bkntc__('Edit'));

        if (Capabilities::userCan('packages_delete'))
        {
            $dataTable->addAction('delete', bkntc__('Delete'), function ($ids)
            {
                foreach ( $ids AS $id )
                {
                    PackageService::deletePackage( $id );
                }
            }, AbstractDataTableUI::ACTION_FLAG_BULK_SINGLE);
        }

        $dataTable->setTitle(bkntc__('Packages'));

        if (Capabilities::userCan('packages_add'))
            $dataTable->addNewBtn(bkntc__('ADD PACKAGE'));

        $dataTable->searchBy(['id', 'name', 'notes']);

        $dataTable->addColumns(bkntc__('ID'), 'id');
        $dataTable->addColumns(bkntc__('NAME'), 'name');

        $dataTable->addColumns(bkntc__( 'SERVICES' ), function ( $row )
        {
            $result = '';
            foreach ( json_decode($row->services, true) AS $service )
            {
                $serviceInf = Service::get($service['id']);
                $serviceName = htmlspecialchars($serviceInf->name);
                $result .= "<div>{$serviceName} x{$service['count']}</div>";
            }

            return $result;
        }, [ 'is_html' => true ], true );

        $dataTable->addColumns(bkntc__('TOTAL PRICE'), function( $row )
        {
            $total = 0;
            foreach ( json_decode($row->services, true) AS $service )
            {
                $serviceInf = Service::get($service['id']);
                $total = Math::add($total, Math::mul($serviceInf->price, $service['count']));
            }

            return Helper::price( $total );
        });
        $dataTable->addColumns(bkntc__('DISCOUNT'), function( $row )
        {
            $total = 0;
            foreach ( json_decode($row->services, true) AS $service )
            {
                $serviceInf = Service::get($service['id']);
                $total = Math::add($total, Math::mul($serviceInf->price, $service['count']));
            }
            $discountAmount = $total - $row[ 'price' ];

            return Helper::price( $discountAmount );
        });
        $dataTable->addColumns(bkntc__('FINAL PRICE'), function( $row )
        {
            return Helper::price( $row[ 'price' ] );
        }, ['order_by_field' => 'price']);

        $table = $dataTable->renderHTML();

        $this->view( 'package_bookings_index', ['table' => $table]);
    }

}
