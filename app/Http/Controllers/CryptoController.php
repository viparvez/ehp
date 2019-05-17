<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CryptoController extends Controller
{
    public static function encrypt($string, $key=5) {
    	$result = '';
    	for($i=0, $k= strlen($string); $i<$k; $i++) {
    		$char = substr($string, $i, 1);
    		$keychar = substr($key, ($i % strlen($key))-1, 1);
    		$char = chr(ord($char)+ord($keychar));
    		$result .= $char;
    	}
    	return base64_encode($result);
    }

    public static function decrypt($string, $key=5) {
    	$result = '';
    	$string = base64_decode($string);
    	for($i=0,$k=strlen($string); $i< $k ; $i++) {
    		$char = substr($string, $i, 1);
    		$keychar = substr($key, ($i % strlen($key))-1, 1);
    		$char = chr(ord($char)-ord($keychar));
    		$result.=$char;
    	}
    	return $result;
    }

    public static function my_simple_crypt( $string, $action = 'e' ) {

    	if (empty($string)) {
    		return null;
    	}

        $secret_key = 'my_simple_secret_key';
        $secret_iv = 'my_simple_secret_iv';
     
        $output = false;
        $encrypt_method = "AES-256-CBC";
        $key = hash( 'sha256', $secret_key );
        $iv = substr( hash( 'sha256', $secret_iv ), 0, 16 );
     
        if( $action == 'e' ) {
            $output = base64_encode( openssl_encrypt( $string, $encrypt_method, $key, 0, $iv ) );
        }
        else if( $action == 'd' ){
            $output = openssl_decrypt( base64_decode( $string ), $encrypt_method, $key, 0, $iv );
        }
     
        return $output;
    }
}
