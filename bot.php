<?php
error_reporting(0);
set_time_limit(0);
$API_KEY = 'Token'; # -- Token -- #
define('API_KEY', $API_KEY);
function bot($method, $datas = []){
    $url = "https://api.telegram.org/bot" . API_KEY . "/" . $method;
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $datas);
    $res = curl_exec($ch);
    if (curl_error($ch)) {
        var_dump(curl_error($ch));
    } else {
        return json_decode($res);
}}
$update = json_decode(file_get_contents('php://input'));
$text = $update->message->text;
$chat_id = $update->message->chat->id;
# -----
if(!file_exists("data")){mkdir("data");}
if($text == '/start'){
	bot('sendMessage',[
	'chat_id' => $chat_id,
	'text' => "Hello\nTo delete your channel messages, give me the admin permission in the channel and forward the last channel message here"
	]);
}
elseif($update->message->forward_from_chat){
	$channel = $update->message->forward_from_chat->username;
	$message_id = $update->message->forward_from_message_id;
	$botid = json_decode(file_get_contents('https://api.telegram.org/bot'.API_KEY.'/getme'))->result->id;
	$truechannel = json_decode(file_get_contents('https://api.telegram.org/bot'.API_KEY."/getChatMember?chat_id=@$channel&user_id=".$botid))->result->status;
	if($truechannel == 'administrator'){
		file_put_contents("data/$chat_id.txt","count|$channel|$message_id");
		bot('sendMessage',[
		'chat_id' => $chat_id,
		'text' => "Send the number of messages you want to delete\nThe number must be between 1 and 100"
		]);
	}else{
		bot('sendMessage',[
		'chat_id' => $chat_id,
		'text' => "First give me the admin permission"
		]);
	}
}
elseif(strpos(file_get_contents("data/$chat_id.txt"),"count|") !== false){
	if($text >= 1 and $text <= 100){
		$get = explode("|",file_get_contents("data/$chat_id.txt"));
		$channel = $get[1];
		$message_id = $get[2];
		unlink("data/$chat_id.txt");
		bot('sendMessage',[
		'chat_id' => $chat_id,
		'text' => "Please wait"
		]);
		$next = $message_id - $text;
		for($x = $message_id; $x >= $next; $x--){
		    bot('deleteMessage',[
			'chat_id' => "@$channel",
			'message_id' => $x
			]);
			if($x < 1 ){ break; }
		}
		bot('sendMessage',[
		'chat_id' => $chat_id,
		'text' => "clearing messages finished"
		]);
	}else{
		bot('sendMessage',[
		'chat_id' => $chat_id,
		'text' => "Send the number of messages you want to delete\nThe number must be between 1 and 100"
		]);
	}
}
?>