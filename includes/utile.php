<?php 
/*  
    Collection de foncitons utiles 
*/
function send_mail (string $mail_to, string $mail_from,  string $sujet, string $name, string $tel, string $email, string $msg): bool 
// send email contat
{
    $headers  = "" ;
	$headers  = "From: '$mail_from' \n" ; 
	$headers .= "Content-Type: text/html; charset='UTF-8' \n" ; 
	$headers .= "Content-Transfer-Encoding: 8bit \n" ; 
    $message = " Name : " . htmlentities($name) . "\n Tel : " .   htmlentities($tel) 
                . "\n Email : " . htmlentities ($email). "\n Message \n " . htmlentities($msg) . "\n";
 	return mail ($mail_to, $sujet, $message, $headers) ;
} 
?>