<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;
Inc::lib('PHPMailer/src/Exception');
Inc::lib('PHPMailer/src/PHPMailer');
Inc::lib('PHPMailer/src/SMTP');

class Email{
    static $PHPMailer;
    static $config;
    static $error = false;

    static function init(){
        try {
            self::$config = Inc::config('email');
            // 
            self::$PHPMailer = new PHPMailer(true);
            // self::$PHPMailer->Name = 'Service';
            self::$PHPMailer->Port = 25;
            //
            if(DEV){ self::$PHPMailer->SMTPDebug = SMTP::DEBUG_SERVER; }            //Enable verbose debug output
            self::$PHPMailer->isSMTP();                                            //Send using SMTP
            self::$PHPMailer->Host       = self::$config['host'];                     //Set the SMTP server to send through
            self::$PHPMailer->SMTPAuth   = true;                                   //Enable SMTP authentication
            self::$PHPMailer->Username   = self::$config['email'];                     //SMTP username
            self::$PHPMailer->Password   = self::$config['pass'];                               //SMTP password
            self::$PHPMailer->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;            //Enable implicit TLS encryption
            self::$PHPMailer->Port       = (int)self::$config['port'];
            self::$PHPMailer->CharSet    = "utf-8";
            self::$PHPMailer->setFrom(self::$config['email'], self::$config['name']);
            self::$PHPMailer->isHTML(true);                                  //Set email format to HTML
            // 
            return true;
        }catch(\Throwable $th){ self::error(true); return false; }
    }

    static function error($set=null){
        if(is_null($set)){ return self::$error; }
        else{ self::$error = $set; }
    }

    static function to($address){
        if(self::$error){ return self::class; }
        try{
            //Recipients
            // self::$PHPMailer->addAddress('joe@example.net', 'Joe User');     //Add a recipient
            // self::$PHPMailer->addAddress('ellen@example.com');               //Name is optional
            self::$PHPMailer->addAddress(...func_get_args());
            // self::$PHPMailer->addReplyTo('info@example.com', 'Information');
            // self::$PHPMailer->addCC('cc@example.com');
            // self::$PHPMailer->addBCC('bcc@example.com');
        }catch(\Throwable $th){ self::error(true); }
        return self::class;
    }

    static function subject($subject){
        if(self::$error){ return self::class; }
        try {
            self::$PHPMailer->Subject = $subject;
        }catch(\Throwable $th){ self::error(true); }
        return self::class;
    }
    static function content($content){
        if(self::$error){ return self::class; }
        try {
            self::$PHPMailer->Body    = $content;
            // self::$PHPMailer->AltBody = 'This is the body in plain text for non-HTML mail clients';
        }catch(\Throwable $th){ self::error(true); }
        return self::class;
    }
    static function attachment(){
        if(self::$error){ return self::class; }
        try {
            // self::$PHPMailer->addAttachment('/var/tmp/file.tar.gz');         //Add attachments
            // self::$PHPMailer->addAttachment('/tmp/image.jpg', 'new.jpg');    //Optional name
        }catch(\Throwable $th){ self::error(true); }
        return self::class;
    }

    static function send(){
        if(self::$error){ self::error(false); return false; }
        try {
            self::$PHPMailer->send();
            return true;
        }catch(\Throwable $th){ return false; }
    }

    static function errorMsg(){ return self::$PHPMailer->ErrorInfo; }
}