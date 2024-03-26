<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <form id="yandex" method="post" action="search_.php">
        <input type="text" name="city_" placeholder="Введите город">
        <input type="submit" name="show" value="Показать">
    </form>
    <title>Создание карты по требованию</title>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
        <!--
            Укажите свой API-ключ. Тестовый ключ НЕ БУДЕТ работать на других сайтах.
            Получить ключ можно в Кабинете разработчика: https://developer.tech.yandex.ru/keys/
        -->
        <script src="https://api-maps.yandex.ru/v3/?apikey=APIKEY&lang=ru_RU"></script>
        <script src='//code.jquery.com/jquery-2.2.4.min.js' integrity='sha256-BbhdlvQf/xTY9gja0Dq3HiwQF8LaCRTXxZKRutelT44=' crossorigin='anonymous'></script>
        <script src="map.js" type="text/javascript"></script>

    <body>
        <input type="button" value="Показать карту" id="toggle"/>
        <p>
            <div id="map" style="width: 700px; height: 500px"></div>
        </p>
    </body>
</body>
</html>

<?php
    $search_q=$_POST['city_'];
    $city =  $search_q;
    

//Создаем данные для запроса
$myCurl = curl_init();
curl_setopt_array($myCurl, array(
    CURLOPT_URL => 'https://api.edu.cdek.ru/v2/oauth/token?parameters',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_SSL_VERIFYHOST => false,
    CURLOPT_POSTFIELDS => http_build_query(array(
    'grant_type' => 'client_credentials',
    'client_id' => 'EMscd6r9JnFiQ3bLoyjJY6eM78JrJceI',
    'client_secret' => 'PjLZkKBHEiLK3YsjtNrt3TGNG0ahs3kG'))
));
curl_setopt($myCurl, CURLOPT_SSL_VERIFYHOST, false);
//Делаем запрос
$response = curl_exec($myCurl);
//Закрываем сеанс
//curl_close($myCurl);

//получаем ответ от СДЕКА, декодируем в массив, что бы забрать токен
$get_token_cdek = get_object_vars(json_decode($response));
$token_cdek = $get_token_cdek["access_token"];
$headers = ["Authorization: Bearer " . $token_cdek]; // создаем заголовок
get_city($myCurl, $headers, $city, $token_cdek);


function get_city($myCurl, $headers, $city, $token_cdek)
{
    $url = 'https://api.edu.cdek.ru/v2/location/cities?city='.$city; 
//Делаем запрос для получения кода города
    curl_setopt_array($myCurl, array(
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_VERBOSE => 1, 
        CURLOPT_POST => false, 
        CURLOPT_URL => $url,
        CURLOPT_SSL_VERIFYHOST => 0,
        CURLOPT_SSL_VERIFYPEER => 0));

    $resul = curl_exec($myCurl);
    $get_code_city = json_decode($resul);//декодируем результат в json формат
    $code_city =  get_object_vars($get_code_city[0]);//создаем массив
    $code = $code_city["code"];//получаем код города

    //Делаем запрос к списку офисов
    curl_setopt_array($myCurl, array(
        CURLOPT_URL => 'https://api.edu.cdek.ru/v2/deliverypoints?city_code='.$code,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'GET',
        CURLOPT_HTTPHEADER => array("Authorization: Bearer " . $token_cdek)));

    $lst_office = curl_exec($myCurl);
    $num_1 = json_decode($lst_office);
    $point_arr = array();// массив для точек
    $all_adress = array();

    foreach($num_1 as $key => $val){
    $a = get_object_vars($num_1[$key]);
    $b = get_object_vars($a["location"]);
    $adress_full = $b["address_full"];//Получаем полный адрес
    $longitude = $b["longitude"];// Получаем долготу
    $latitude = $b["latitude"];//Получаем широту
    $point = ['longitude'=>$longitude, 'latitude'=>$latitude];
    array_push($point_arr, $point);
    #echo '<br>',$adress_full;
    array_push($all_adress, $adress_full);
    };

    if (isset($_POST['show'])) {
        $city = $_POST['city_'];
        $landmarks = array($all_adress);
        /* foreach ($landmarks as $i) {
            foreach ($i as $k) {
                echo $k;
            }
        } */
    
        echo '<select>';
        foreach ($landmarks as $landmark) {
            foreach ($landmark as $i) {
                echo '<option>' . $i . '</option>';
            }
            echo '</select>';
        }
    
    }
    $point_arr_json = json_encode($point_arr);
    echo "<script>var pointArr = JSON.parse('$point_arr_json');</script>";
    
}
//echo json_encode($point_arr);
curl_close($myCurl);

?>
