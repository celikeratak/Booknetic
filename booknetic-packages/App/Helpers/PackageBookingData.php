<?php

namespace BookneticAddon\Packages\Helpers;

class PackageBookingData
{

    const PAYMENT_STATUSES = [
        'PAID'      =>  'paid',
        'NOT_PAID'  =>  'not_paid'
    ];

    public $id;
    public $packageId;
    public $customerId;
    public $note;
    public $locale;
    public $clientTimezone;
    public $paymentMethod;
    public $paymentStatus;
    public $totalAmount;
    public $paidAmount;

}