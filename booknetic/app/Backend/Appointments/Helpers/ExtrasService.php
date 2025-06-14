<?php

namespace BookneticApp\Backend\Appointments\Helpers;

class ExtrasService
{

	public static function calcExtrasPrice( $serviceExtras )
	{
		$extrasPrice = 0;

		foreach ( $serviceExtras AS $extraInf )
		{
			$extrasPrice += $extraInf['quantity'] * $extraInf['price'];
		}

		return $extrasPrice;
	}

	public static function calcExtrasDuration( $serviceExtras )
	{
		$extrasDuration = 0;

        if (empty($serviceExtras)) return 0;

		$uniqueByExtraId = [];
		foreach ( $serviceExtras AS $extra )
		{
			$id = $extra['id'];
			$duration = (int)$extra['duration'] * (int)$extra['quantity'];

			if( !isset( $uniqueByExtraId[ $id ]  ) )
				$uniqueByExtraId[ $id ] = 0;

			$uniqueByExtraId[ $id ] = max( $uniqueByExtraId[ $id ], $duration );
		}

		foreach ( $uniqueByExtraId AS $duration )
		{
			$extrasDuration += $duration;
		}

		return $extrasDuration;
	}

}