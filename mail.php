<?php
require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

/* require 'vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception; */

$mail = new PHPMailer\PHPMailer\PHPMailer(true);
$mail->CharSet = "UTF-8";

try {
    // Настройки сервера
    $mail->isSMTP();
    $mail->Host = 'smtp.yandex.ru';
    $mail->SMTPAuth = true;
    $mail->Username = 'yourmail@yandex.ru';
    $mail->Password = 'pass';
    $mail->SMTPSecure = 'ssl';
    $mail->Port = 465;

    // Настройки сообщения
    $mail->setFrom('yourmail@yandex.ru', 'Имя отправителя');
    $mail->addAddress('yourmail@bk.ru', 'Имя получателя');
    $mail->addAttachment( __DIR__ .'/loc/edited_file.csv');
    $mail->Subject = 'Тема письма';
    $mail->Body = "Текст письма. Время смены: ".date("d/m/Y H:i:s")." MSK. Новое значение:";

    // Отправка сообщения
    $mail->send();
    echo 'Письмо успешно отправлено';
} catch (Exception $e) {
    echo 'Ошибка отправки письма: ' . $mail->ErrorInfo;
}