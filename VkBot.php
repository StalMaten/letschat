<?php
if (!isset($_REQUEST)) {
    return;
}
$confirmationToken = '4a31b60f';
$token = 'vk1.a.SWVz_hA_AA8f31VbcV8YGdeMMV7V1NtvEYCC13T6aSs5uKgo4dpEwpvbEjmneuFanNZyYd24PIuF_B8zanMRmU33gaCYdMGEhsuGnAze09QRH5EqSci5kwGqIq5n36rKhZVMMKaupIZ6AuG_eOJUp971xnycpR75i-Km8zjOxlyZtVxfmc4s3SJM6G33ZvedImKwg7pzhfU6FTuyU8pu0Q';
$secretKey = 'letsChatSecretKeyDai';
$version = 5.131;
$request = json_decode(file_get_contents('php://input'), true);
if ($request['secret'] != $secretKey){
    return;
}
switch ($request['type']) {
    case 'confirmation':
        echo $confirmationToken;
        break;
    case 'message_new':
        $user_id = $request['object']['message']['from_id'];
        switch ($request['object']['message']['text']){
            case 'Начать':
                $message = "Вас приветствует Бот Let's chat. Для получения доступа к сайту запросите уникальный ключ.";
                break;
            case 'Сгенерировать ключ доступа':
                $date = new \DateTime();
                $codeUnique=false;
                $sql = mysqli_connect('localhost','isupovrt_ltschat','Agario2426','isupovrt_ltschat');
                $query = "SELECT * FROM AccessKeys WHERE UserId = " . $user_id;
                $query_result = mysqli_query($sql, $query);
                $result = mysqli_fetch_all($query_result);

                if(!empty($result)){
                    $queryDate = $result[0][3];
                    if($queryDate<$date->format('Y-m-d H:i:s')){
                        $keyId = $result[0][0];
                        $query = "DELETE FROM `AccessKeys` WHERE KeyId = " . $keyId;
                        mysqli_query($sql, $query);
                    } else {
                        $code = $result[0][1];
                        $message = "Ваш ранее генерируемый ключ доступа еще активен. \n Ваш уникальный ключ доступа: " . $code . " \n Авторизация на сайте: http://isupovrt.beget.tech";
                        break;
                    }
                }
                while ($codeUnique == false) {
                    $chars = 'qazxswedcvfrtgbnhyujmkiolp1234567890QAZXSWEDCVFRTGBNHYUJMKIOLP';
                    $length = 16;
	                $size = strlen($chars) - 1; 
	                $codeGen = ''; 
	                while($length--) {
                        $codeGen .= $chars[random_int(0, $size)]; 
                    }
                    $codeGen = str_split($codeGen, 4);
                    $code = implode(" - ", $codeGen);
                    $query = "SELECT AccessKey FROM AccessKeys WHERE AccessKey = '" . $code . "'";
                    $query_result = mysqli_query($sql, $query);
                    $result = mysqli_fetch_all($query_result);
                    if(empty($result)){
                        $codeUnique=true;
                    }
                }
                $date->add(new \DateInterval('PT30M'));
                $message = "Ваш уникальный ключ доступа: " . $code ."
                Ключ доступа будет действителен 30 минут.
                Авторизация на сайте: http://isupovrt.beget.tech";
                $query = "INSERT INTO AccessKeys (AccessKey, UserId, ActiveTo) VALUES ('" . $code ."', '" . $user_id ."', '" . $date->format('Y-m-d H:i:s') . "')";
                mysqli_query($sql, $query);
                break;
            case 'Получить ссылку на сайт':
                $message = "Ссылка на сайт: http://isupovrt.beget.tech";
                break;
            default:
                $message = "Простите, не удалось определить команду. 
                Возможно вы используете не офицальное приложение ВК и плитки меню не отображаются, используйте команды.
                
                &#9654; Сгенерировать ключ доступа

                &#9654; Получить ссылку на сайт";
                break;
        }
        $buttons = [
            0 => [
                0 => [
                    'action' => [
                        'type' => "text",
                        'label' => "Сгенерировать ключ доступа",
                        'payload'=> ""
                    ],
                    'color' => "positive"
                ]
            ],
            1 => [
                0 => [
                    'action' => [
                        'type' => "open_link", 
                        'link' => "http://isupovrt.beget.tech",
                        'label' => "Перейти на сайт Let's chat",
                        'payload'=> ""
                    ],
                ]
            ]
        ];
        $keyboard = [
            'one_time' => true,
            'inline' => false,
            'buttons' => $buttons
        ];
        $request_params = array(
            'message' => $message,
            'access_token' => $token,
            'user_id' => $user_id,
            'random_id' => 0,
            'read_state' => 1,
            'user_ids' => 0,
            'v' => '5.103',
            'keyboard'=> json_encode($keyboard, JSON_UNESCAPED_UNICODE),
        );
        $get_params = http_build_query($request_params);
        file_get_contents('https://api.vk.com/method/messages.send?' . $get_params);
        echo 'ok';
        break;
} 