<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use PHPMailer\PHPMailer;

class EmailController extends Controller
{
	public function sendmail(array $address, $body, array $cc = null, $subject){

		if(!is_array($address)) {
			return false;
		}


		$mail= new PHPMailer\PHPMailer();

		$mail->SMTPDebug = 0;                               // Enable verbose debug output
		$mail->isSMTP();                                      // Set mailer to use SMTP
		$mail->Host = 'a2plcpnl0334.prod.iad2.secureserver.net';  // Specify main and backup SMTP servers
		$mail->SMTPAuth = true;                               // Enable SMTP authentication
		$mail->Username = 'bphn-ehp@ronvolt.com';                 // SMTP username
		$mail->Password = 'Nehan@2016';                       // SMTP password
		$mail->SMTPSecure = 'ssl';                            // Enable TLS encryption, `ssl` also accepted
		$mail->Port = 465;
		$mail->setFrom('bphn-ehp@ronvolt.com', 'New York City Human Resource Administration');
		$mail->addReplyTo('bphn-ehp@ronvolt.com', 'New York City Human Resource Administration');
		$mail->isHTML(true);                                  // Set email format to HTML
		$mail->Subject = $subject;
		$mail->Body    = $body;

		foreach ($address as $key => $value) {
			$mail->addAddress($value);
		}

		if(!is_array($address)) {
			return false;
		} elseif($cc == null) {
			
		} else {
			foreach ($cc as $key => $value) {
				$mail->addCc($value);
			}
		}
		

		if(!$mail->send()) {
		    return response()->json(['success'=>array('Email sent')]);
		} else {
		    return response()->json(['error'=>array('Could not send email')]);
		}   

	}
    
}
