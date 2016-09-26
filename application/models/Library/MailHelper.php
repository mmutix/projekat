<?php
class Application_Model_Library_MailHelper
{
    
    public function sendmail($to_email, $from_email, $from_name, $message) {
    $mail = new Zend_Mail('UTF-8');
    $mail->setSubject('Message from contact form');
    $mail->addTo($to_email);
    $mail->setFrom('$from_email', $from_name);
    $mail->setBodyHTML($message);
    $mail->setBodyText($message);
    
    return $result = $mail->send();
    }
}