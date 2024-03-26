<?php
require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';


$hostname = '{imap.yandex.ru:993/imap/ssl}INBOX';
$username = 'yourmail@yandex.ru';
$password = 'yourpassword';

// Подключение к почтовому серверу
$inbox = imap_open($hostname, $username, $password) or die('Не удалось подключиться к почтовому серверу: ' . imap_last_error());

 
// Получение списка писем
$emails = imap_search($inbox, 'FROM "bk.ru" TO "yourmail@yandex.ru"');
$yesterday_date = date('Y-m-d', strtotime('-1 day'));
// Перебор полученных писем
if ($emails) {
    foreach ($emails as $email_number) {
        // Получение заголовков письма
        $headers = imap_headerinfo($inbox, $email_number);
        $structure = imap_fetchstructure($inbox, $email_number);
        

        // Получение даты получения письма
        $date = strtotime($headers->date);
        
        /* $date = strtotime('-1 day', $get_date);*/
        
        // Проверка, что письмо получено за последний день
        if (date('Y-m-d', $date) === date('Y-m-d')) {
        
            // Проверка наличия вложений
            if (isset($structure->parts)) {
                // Перебор частей письма
                foreach ($structure->parts as $part_number => $part) {
                    // Проверка типа части
                    if (isset($part->subtype) && $part->subtype === 'HTML') {
                        // Получение содержимого части письма
                        $part_content = imap_fetchbody($inbox, $email_number, $part_number + 1);
                        $content_base64 = base64_decode($part_content);
                        
                        // Поиск ссылки или кнопки в содержимом письма
                        /* $pattern = '/<a\s+(?:[^>]*?\s+)?href=(["\'])(.*?)\1/'; */
                        $pattern = '/<a\s+(?:[^>]*?\s+)?href=(["\'])(.*?)\1.*(tinkoff)/';
                        preg_match($pattern, $content_base64, $matches);
                        if (!empty($matches)) {
                            $link = $matches[2];
                            // Здесь можно выполнить дальнейшую обработку ссылки
                            $filename = __DIR__ . '/loc/file.csv';
                            // Скачиваем файл по ссылке
                            $file_content = file_get_contents($link);
                            preg_match('/location.href="(.*?)";/', $file_content, $matches);
                            if (isset($matches[1])) {
                                $file_url = $matches[1];
                                $file_data = file_get_contents($filename);//*
                                file_put_contents($filename, $file_data);
                                echo "Файл успешно скачан";

                                // Чтение файла
                                $file_data = file_get_contents($filename);

                                // Разделение файла на строки
                                $lines = explode(PHP_EOL, $file_data);

                                // Создание нового файла для записи отредактированных данных
                                $new_file_path = __DIR__ . '/loc/bank_'.$yesterday_date.'.csv';
                                $new_file = fopen($new_file_path, 'w');
                                
                                $count=0;
                                // Проход по каждой строке и проверка условий удаления
                                foreach ($lines as $line) {
                                    $count++; // Увеличение номера строки
                                    $line = preg_replace('/^' . pack('H*', 'EFBBBF') . '/', '', $line); // Удаление BOM
                                    $fields = str_getcsv($line, ';');
                                    $operation_type = $fields[2]; // Поле "Тип операции"
                                    $counterparty = $fields[12]; // Поле "Контрагент"
                                    $inn = $fields[13]; // Поле "ИНН"
                                
                                    // Проверка условий удаления строки
                                    if (
                                        strpos($operation_type, 'Debit') !== false ||
                                        strpos($counterparty, 'Тинькофф') !== false ||
                                        $inn == '1111111111' ||
                                        $inn == '1111111111'
                                    ) {
                                        // Запись строки в новый файл                                        
                                    }
                                    else {
                                        // Преобразование полей в строку с разделителями
                                       /*  foreach($fields as $textcsv){
                                            
                                            fwrite($new_file, iconv("UTF-8", "UTF-8", $textcsv.";"));
                                        } */
                                        
                                        // Преобразование полей в строку с разделителями 
                                        $csv_line = implode(';', $fields);
                                        // Запись строки в новый файл с указанной кодировкой
                                        fwrite($new_file, iconv(mb_detect_encoding($csv_line), "WINDOWS-1251", $csv_line) . "\n");
                                        /* $fields = array_map(function($field) { 1 споособ
                                            return iconv('UTF-8', 'WINDOWS-1251', $field);
                                        }, $fields);
                                        
                                        fputcsv($new_file, $fields, ';'); */
                                        
                                    }
                                }

                                // Закрытие файла
                                fclose($new_file);

                                echo "Файл успешно отредактирован и сохранен в $new_file_path";
                                                                
                            } else {
                                echo "Не удалось найти ссылку на скачивание файла";
                            }
                        } 
                        break; // Прекратить перебор частей после нахождения ссылки или кнопки
                    }
                }
            }
        }
    }
}
// Закрытие соединения с почтовым сервером
imap_close($inbox);

/* require __DIR__ .'\mail.php'; */

/* $mail = new PHPMailer\PHPMailer\PHPMailer(true);
$mail->CharSet = "UTF-8";

try {
    // Настройки сервера
    $mail->isSMTP();
    $mail->Host = 'smtp.yandex.ru';
    $mail->SMTPAuth = true;
    $mail->Username = 'd.murzin1@yandex.ru';
    $mail->Password = 'ovgyqmcdktpcxacn';
    $mail->SMTPSecure = 'ssl';
    $mail->Port = 465;

    // Настройки сообщения
    $mail->setFrom('bank@semena74.com', 'Имя отправителя');
    $mail->addAddress('ip-team@bk.ru', 'Имя получателя');
    $mail->addAttachment( __DIR__ .'/loc/bank_'.$yesterday_date.'.csv');
    $mail->Subject = 'Тема письма';
    $mail->Body = "В интернет-магазине сменился согласно расписанию отправитель для документов. Время смены: ".date("d/m/Y H:i:s")." MSK. Новое значение:";

    // Отправка сообщения
    $mail->send();
    echo 'Письмо успешно отправлено';
} catch (Exception $e) {
    echo 'Ошибка отправки письма: ' . $mail->ErrorInfo;
} */