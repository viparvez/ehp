<?php

namespace App\Custom;

/**
 * 
 */
class Custom
{
	
	function __construct()
	{
		# code...
	}

	public function convertDate($date = null, $format = null){
		
		if ($date == null || $format ==null) {
			return null;
		}

		$trunc_date = explode("-",$date);

		return $trunc_date[2].'-'.$trunc_date[0].'-'.$trunc_date[1];

	}


	public function dateToView($date = null, $format = null){
		
		if ($date == null || $format ==null) {
			return null;
		}

		return date($format, strtotime($date));

	}
	
}

?>