<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'Telegram.php';
require_once 'User.php';
require_once 'Pages.php';
require_once 'Texts.php';
require_once 'Service.php';

$bot_token =
    '6034564501:AAF9dWufD89Re5RtCsodEJD2dGk_NFbF0lk';
    
$rootPath = "https://med-lab.uz/medlab";

$telegram = new Telegram($bot_token);

$callback_query = $telegram->Callback_Query();
$callback_data = $telegram->Callback_Data();
$inline_query = $telegram->Inline_Query();

$data = $telegram->getData();
$message = $data['message'];
$text = $message['text'];
$chatID = $telegram->ChatID();

$pages = new Pages();
$user = new User($chatID);
$texts = new Texts($user->getLanguage());
$service = new Service($user->getLanguage());

$ADMINS_CHAT_IDS = [
    632238799,676959290,5496232053
];
//,676959290,5496232053
ini_set('precision', 100);

// callback buttons
if ($callback_query !== null && $callback_query != '') {
    $callback_data = $telegram->Callback_Data();
    $chatID = $telegram->Callback_ChatID();
    
    if (isContains($callback_data, "reply_user_feedback")) {
        // $content = ['chat_id' => $chatID, 'message_id' => $telegram->MessageID()];
        // $telegram->deleteMessage($content);
        $id = substr($callback_data, 19);
        $user->setReplyId($id);
        $user->setPage(Pages::PAGE_REPLY);
        sendMessage("Javob matnini kiriting:");
    }elseif (isContains($callback_data, "back_to_sub_category")) {
        $content = ['chat_id' => $chatID, 'message_id' => $telegram->MessageID()];
        $telegram->deleteMessage($content);
        $id = substr($callback_data, 20);
        showSubcategories($id);
    } elseif (isContains($callback_data, "sub_categories_")) {
        $content = ['chat_id' => $chatID, 'message_id' => $telegram->MessageID()];
        $telegram->deleteMessage($content);
        $id = substr($callback_data, 15);
        showLinkPage($id);
    } elseif (isContains($callback_data, "back_to_category")) {
        $content = ['chat_id' => $chatID, 'message_id' => $telegram->MessageID()];
        $telegram->deleteMessage($content);
        showFreeMaterials();
    } elseif (isContains($callback_data, "uz")) {
        $user->setLanguage("uz");
        $content = ['chat_id' => $chatID, 'message_id' => $telegram->MessageID()];
        $telegram->deleteMessage($content);
        $texts = new Texts($user->getLanguage());
        $service = new Service($user->getLanguage());
        showMainPage();
    } elseif (isContains($callback_data, 'ru')) {
        $user->setLanguage("ru");
        $content = ['chat_id' => $chatID, 'message_id' => $telegram->MessageID()];
        $telegram->deleteMessage($content);
        $texts = new Texts($user->getLanguage());
        $service = new Service($user->getLanguage());
        showMainPage();
    }
    //answer nothing with answerCallbackQuery, because it is required
    $content = ['callback_query_id' => $telegram->Callback_ID(), 'text' => "", 'show_alert' => false];
    $telegram->answerCallbackQuery($content);

} elseif($inline_query !== null && $inline_query != ''){
    $user->setPage(Pages::PAGE_SEARCH);
    $query_Id = $inline_query['id'];
    $query_Text = $inline_query['query'];
    $ids = array();
    $row = $service->getReagentsNameByKeyword($query_Text);
    $service->setSearchText($query_Text);
    $i = 0;
    foreach($row as $item){
        $one_item = array(
            'type' => 'article',
            'id' => $item['id'],
            'title' => $item['name'],
            'description' => $item['manufacturer'],
            'thumbnail_url' => 'https://med-lab.uz/assets/flask.png',
            'input_message_content' => array(
                'message_text' => $item['id']
            )
        );
        
        $ids[] = $one_item;
    }
    $content = array(
        'inline_query_id' => $query_Id,
        'results' => $ids
    );
    $apiUrl = 'https://api.telegram.org/bot' . $bot_token . '/';
    $ch = curl_init($apiUrl . 'answerInlineQuery');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($content));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_exec($ch);
    curl_close($ch);
} elseif ($text === '/start') {
    showStart();
} elseif (isContains($text, '/start')) {
    $id = substr($text,7);
    if($id == $chatID){
        sendMessage($texts->getText("error_with_share_id"));
    }else{
        $service->setShareUserCount($id);
    }
    showStart();
} else {
    switch ($user->getPage()) {
        case Pages::PAGE_MAIN:
            switch ($text) {
                case $texts->getText("categories"):
                    showFreeMaterials();
                    break;
                case $texts->getText("main_menu_about"):
                    showAbout();
                    break;
                case $texts->getText("settings"):
                    showSettings();
                    break;
                case $texts->getText("main_menu_check"):
                    checkStatus();
                    break;
                case $texts->getText("menu_feedback"):
                    sendFeedback();
                    break;
                case $texts->getText("menu_news"):
                    sendMessage($texts->getText("news_text"));
                    break;
                case $texts->getText("search_btn_text"):
                    showSearchPage();
                    break;
                default:
                    showAbout();
                    break;
            }
            break;
        case Pages::PAGE_SEARCH:
            switch ($text) {
                case $texts->getText("categories"):
                    showFreeMaterials();
                    break;
                case $texts->getText("main_menu_about"):
                    showAbout();
                    break;
                case $texts->getText("settings"):
                    showSettings();
                    break;
                case $texts->getText("main_menu_check"):
                    checkStatus();
                    break;
                case $texts->getText("menu_feedback"):
                    sendFeedback();
                    break;
                case $texts->getText("menu_news"):
                    sendMessage($texts->getText("news_text"));
                    break;
                case $texts->getText("menu_back"):
                    showSearchPage();
                    break;
                case $texts->getText('go_main_page'):
                    showMainPage();
                    break;
                case $texts->getText("search_btn_text"):
                    showSearchPage();
                    break;
                default:
                    if (is_numeric($text)) {
                        $content = ['chat_id' => $chatID, 'message_id' => $telegram->MessageID()];
                        $telegram->deleteMessage($content);
                        showSearchResult($text);
                    } else{
                        showSearchResultWithText($text,0);
                    }
                    break;
            }
            break;
        case Pages::PAGE_SEARCH_WITH_TEXT:
            switch ($text) {
                case $texts->getText("menu_back"):
                    showSearchPage();
                    break;
                case $texts->getText('go_main_page'):
                    showMainPage();
                    break;
                default:
                    $id = substr($text,0,strpos($text,"-"));
                    if (is_numeric($id)) {
                        showSearchResult($id);
                    } else{
                        showSearchResultWithText($text,0);
                    }
                    
            }
            break;
        case Pages::PAGE_CATEGORIES:
            switch ($text) {
                case $texts->getText("menu_back"):
                    showMainPage();
                    break;
                case $texts->getText('go_main_page'):
                    showMainPage();
                    break;
                default:
                    $id = $service->getCategoryByName($text);
                    if($user->getUserStatus() != 'active' && $id == 3){
                        $textToSend = $texts->getText("user_status_text_no");
                        sendMessage($textToSend);
                        showSell();
                    }else{
                        showSubcategories($id);
                    }
                    break;
            }
            break;
        case Pages::PAGE_SUB_CATEGORIES:
            switch ($text) {
                case $texts->getText("menu_back"):
                    showFreeMaterials();
                    break;
                case $texts->getText('go_main_page'):
                    showMainPage();
                    break;
                default:
                    $id = $service->getCategoryByName($text);
                    // showSubcategories($id);
                    sendMessage($id);
                    break;
            }
            break;
        case Pages::PAGE_ABOUT:
            switch ($text) {
                case $texts->getText("menu_back"):
                    showMainPage();
                    break;
                case $texts->getText('go_main_page'):
                    showMainPage();
                    break;
                default:
                    showAbout();
                    break;
            }
            break;
        case Pages::PAGE_SETTINGS:
            switch ($text) {
                case $texts->getText("menu_back"):
                    $user->setPage(Pages::PAGE_MAIN);
                    showMainPage();
                    break;
                case $texts->getText('go_main_page'):
                    showMainPage();
                    break;
                case $texts->getText("lang"):
                    showStart();
                    break;
                case $texts->getText("change_phone"):
                    showChangePhoneNumber();
                    break;
                case $texts->getText("share_button"):
                    showSharePage();
                    break;
                default:
                    showMainPage();
                    break;
            }
            break;
        case Pages::FEEDBACK:
            switch ($text) {
                case $texts->getText("menu_back"):
                    showMainPage();
                    break;
                case $texts->getText('go_main_page'):
                    showMainPage();
                    break;
                default:
                    sendMessage($texts->getText("feedback_result"));
                    showMainPage();
                    $service->setFeedback($chatID, $text);
                    foreach ($ADMINS_CHAT_IDS as $admin_chat_id) {
                        $options[] = [$telegram->buildInlineKeyboardButton("Javob berish", "", 'reply_user_feedback' . $chatID)];
                        $keyb = $telegram->buildInlineKeyBoard($options);
                        $datas = array(
                            'chat_id' => $admin_chat_id,
                            'reply_markup' => $keyb,
                            'text' => $text,
                            'parse_mode' => 'HTML'
                        );
                        $telegram->sendMessage($datas);
                        $options = [];
                        // $content = ['chat_id' => $admin_chat_id, 'from_chat_id' => $chatID, 'message_id' => $telegram->MessageID(), 'reply_markup' => $keyb];
                        // $telegram->forwardMessage($content,false);
                    }
                    break;
            }
            break;
        case Pages::PAGE_NEWS:
            switch ($text) {
                case $texts->getText("menu_back"):
                    showMainPage();
                    break;
                case $texts->getText('go_main_page'):
                    showMainPage();
                    break;
                default:
                    sendMessage($texts->getText("news_text"));
                    break;
            }
            break;
        case Pages::PAGE_SELL_CONTACT:
            switch ($text) {
                case $texts->getText("menu_back"):
                    showMainPage();
                    break;
                case $texts->getText('go_main_page'):
                    showMainPage();
                    break;
                default:
                    if ($message['contact']['phone_number'] != "") {
                        $user->setPhoneNumber($message['contact']['phone_number']);
                        showCardNumber();
                    } else {
                        if(!(strlen($text)<=9 && preg_match("/^[0-9]{9}+$/", $text))){
                            sendMessage($texts->getText("error_phone_number"));
                            showSell();
                        }else{
                            $user->setPhoneNumber($text);
                            showCardNumber();
                        }
                    }
                    break;
            }
            break;
        case Pages::PAGE_CHANGE_PHONE:
            switch ($text) {
                case $texts->getText("menu_back"):
                    showSettings();
                    break;
                case $texts->getText('go_main_page'):
                    showMainPage();
                    break;
                default:
                    if ($message['contact']['phone_number'] != "") {
                        $user->setPhoneNumber($message['contact']['phone_number']);
                        sendMessage($texts->getText("success_phone"));
                        showSettings();
                    } else {
                        if(!(strlen($text)<=9 && preg_match("/^[0-9]{9}+$/", $text))){
                            sendMessage($texts->getText("error_phone_number"));
                            showChangePhoneNumber();
                        }else{
                            $user->setPhoneNumber($text);
                            sendMessage($texts->getText("success_phone"));
                            showSettings();
                        }
                    }
                    break;
            }
            break;
        case Pages::PAGE_ADMIN:
            switch ($text) {
                case "Malumot qoshish":
                    showFreeMaterials($pages = Pages::PAGE_ADMIN);
                    break;
                case "POST rasm bilan":
                    sendMessage("Rasm yuboring");
                    $user->setPage(Pages::PAGE_POST_PHOTO);
                    break;
                case "POST rasmsiz faqat matn":
                    sendMessage("Matnni yuboring");
                    $user->setPage(Pages::PAGE_POST_TEXT);
                    break;
                case $texts->getText('menu_back'):
                    showMainPage();
                    break;
                case $texts->getText('go_main_page'):
                    showMainPage();
                    break;
                default:
                    $id = $service->getCategoryByName($text);
                    addInfoPage($id);
                    break;
            }
            break;
        case Pages::PAGE_ADD_INFO:
            switch ($text) {
                case $texts->getText('menu_back'):
                    showFreeMaterials($pages = Pages::PAGE_ADMIN);
                    break;
                default:
                    sendMessage($text);
                    break;
            }
            break;
        break;
        case Pages::PAGE_POST_TEXT:
            switch ($text) {
                case $texts->getText('menu_back'):
                    showAdminMainPage();
                    break;
                default:
                    sendPost($text);
                    break;
            }
            break;
        break;
        case Pages::PAGE_POST_PHOTO:
            switch ($text) {
                case $texts->getText("menu_back"):
                    showAdminMainPage();
                    break;
                default:
                    if (!$message['photo']) {
                        showAdminSendProductPhoto();
                    } else {
                        $photo = end($message['photo']);
                        $result = $telegram->getFile($photo['file_id']);
                        $filePath = $result['result']['file_path'];
                        $product = $user->getProduct();
                        $cnt = file_get_contents('photoCounter.txt');
                        $localFilePath = 'photos/' . $cnt . "." . explode(".", $filePath)[1];
                        $telegram->downloadFile($filePath, $localFilePath);
                        file_put_contents('photoCounter.txt', $cnt + 1);
                        $product['photoUrl'] = $localFilePath;
                        $user->setProduct($product);
                        $user->setPage(Pages::PAGE_PHOTO_TEXT);
                        sendMessage("Rasm muvaffaqiyatli yuklandi. Matn kiriting (agar faqat rasm yubormoqchi bo`lsangiz bo`sh qoldiring va yuborish tugmasini bosing)");
                    }
                    break;
            }
            break;
        break;
        case Pages::PAGE_PHOTO_TEXT:
            switch($text){
                case $texts->getText("menu_back"):
                    showAdminMainPage();
                    break;
                default:
                    sendPostWithPhoto($text);
            }
            break;
        case Pages::PAGE_REPLY:
            $res = $service->setReplyFeedback($user->getReplyId(),$text);
            // sendMessage($res);
            if($res){
                sendMessage("Xabar yuborildi");
                $content = array('chat_id' => $user->getReplyId(), 'text' => $text, 'parse_mode' => "HTML");
                $telegram->sendMessage($content);
                showMainPage();
            }else{
                sendMessage("Xabar yuborilmadi. Avvalroq javob berilgan yoki nimadir xato ketdi");
            }
        break;
    }
}

// user pages

function showStart()
{
    global $telegram, $chatID, $user;
    $user->setPage(Pages::START);
    $option[] = [$telegram->buildInlineKeyBoardButton("🇺🇿 O'zbekcha", "", "uz")];
    $option[] = [$telegram->buildInlineKeyBoardButton("🇷🇺 Русский", "", "ru")];
    $keyboard = $telegram->buildInlineKeyBoard($option);
    $datas = array(
        'chat_id' => $chatID,
        'reply_markup' => $keyboard,
        'text' => "🇺🇿 Tilni tanlang\n🇷🇺 Выбрать язык",
        'parse_mode' => 'HTML'
    );
    $telegram->sendMessage($datas);
}

function showMainPage()
{
    global $user, $texts, $telegram, $chatID, $ADMINS_CHAT_IDS;
    $user->setPage(Pages::PAGE_MAIN);
    
    // if (in_array($chatID, $ADMINS_CHAT_IDS)) {
        $options = [
            [$telegram->buildKeyboardButton($texts->getText('search_btn_text'))],
            [$telegram->buildKeyboardButton($texts->getText('categories'))],
            [$telegram->buildKeyboardButton($texts->getText('main_menu_check')),$telegram->buildKeyboardButton($texts->getText('settings'))],
            [$telegram->buildKeyboardButton($texts->getText('menu_feedback')),$telegram->buildKeyboardButton($texts->getText('menu_news'))],
            [$telegram->buildKeyboardButton($texts->getText('main_menu_about'))]
        ];
    // }else{
    //     $options = [
    //         [$telegram->buildKeyboardButton($texts->getText('categories'))],
    //         [$telegram->buildKeyboardButton($texts->getText('main_menu_check')),$telegram->buildKeyboardButton($texts->getText('settings'))],
    //         [$telegram->buildKeyboardButton($texts->getText('menu_feedback')),$telegram->buildKeyboardButton($texts->getText('menu_news'))],
    //         [$telegram->buildKeyboardButton($texts->getText('main_menu_about'))]
    //     ];
    // }
    $keyb = $telegram->buildKeyBoard($options, $onetime = false, $resize = true);
    $content = array('chat_id' => $chatID, 'reply_markup' => $keyb, 'text' => $texts->getText("page_main_text"), 'parse_mode' => "HTML");
    $telegram->sendMessage($content);
}

function showSearchPage(){
    global $telegram, $chatID, $user, $texts;
    $user->setPage(Pages::PAGE_SEARCH);
    $option[] = [$telegram->buildInlineKeyBoardButton($texts->getText("search_btn_show_inline"), "", "",null,"")];
    // $option[] = [$telegram->buildInlineKeyBoardButton($texts->getText("search_btn_choose"), "", "ru")];
    $keyboard = $telegram->buildInlineKeyBoard($option);
    $datas = array(
        'chat_id' => $chatID,
        'reply_markup' => $keyboard,
        'text' => $texts->getText("search_page_text"),
        'parse_mode' => 'HTML'
    );
    $telegram->sendMessage($datas);
}

function showSearchResult($id){
    global $telegram, $chatID, $user, $service;
    $text = "";
    
    $row = $service->getReagentsNameById($id);
    $i = 0;
    foreach($row as $item){
        $text .= "<b>№ ".++$i."</b>\n";
        $text .= "<b>Mahsulot:</b> ".$item['reagent']."\n";
        
        if($item['manufacturer'] != ""){
            $text .= "<b>Ishlab chiqaruvchi:</b>  ".$item['manufacturer']."\n";
        }
        
        $text .= "<b>🏥 Tashkilot:</b>  ".$item['company']."\n";
        $text .= "<b>☎️ : </b>  ".$item['phone_number']."\n";
        
        if($item['addres'] != ""){
            $text .= "<b>☎️ : </b>  ".$item['addres']."\n\n";
        }

        $text .= "➖➖➖➖➖➖➖➖➖"."\n";
    }
    
    $content = array('chat_id' => $chatID, 'text' => $text, 'parse_mode' => "HTML");
    $telegram->sendMessage($content);
}

function showSearchResultWithText($text, $start){
    global $telegram, $chatID, $user, $service, $texts;
    
    $service->setSearchText($text);
    $row = $service->getReagentsNameByText($text, $start);
    if(empty($row)){
        $result_text = $texts->getText("not_found_text");
    }else{
        $user->setPage(Pages::PAGE_SEARCH_WITH_TEXT);
        $result_text = $texts->getText("page_main_text");
        foreach($row as $item){
            $options[] = [$telegram->buildKeyboardButton($item['id']."-".$item['name']." | ".$item['manufacturer'])];  
        }
    }
    $options[] = [
        $telegram->buildKeyboardButton($texts->getText('menu_back')),
        $telegram->buildKeyboardButton($texts->getText('go_main_page'))
    ];
    $keyb = $telegram->buildKeyBoard($options, $onetime = false, $resize = true);
    $content = array('chat_id' => $chatID, 'reply_markup' => $keyb, 'text' => $result_text, 'parse_mode' => "HTML");
    $telegram->sendMessage($content);    
}

function showAbout()
{
    global $user, $texts, $telegram, $chatID;
    $user->setPage(Pages::PAGE_ABOUT);
    $options = [
        [$telegram->buildKeyboardButton($texts->getText('menu_back')),$telegram->buildKeyboardButton($texts->getText('go_main_page'))]
    ];
    $keyb = $telegram->buildKeyBoard($options, $onetime = false, $resize = true);
    $content = array('chat_id' => $chatID, 'reply_markup' => $keyb, 'text' => $texts->getText("page_about_text"), 'parse_mode' => "HTML");
    $telegram->sendMessage($content);
    $content = ['chat_id' => $chatID, 'from_chat_id' => '@med_lab1', 'message_id' => 1125];
    $telegram->forwardMessage($content);
}

function showSell()
{
    global $user, $texts, $telegram, $chatID;
    $user->setPage(Pages::PAGE_SELL_CONTACT);
    $options = [
        [$telegram->buildKeyboardButton($texts->getText('share_phone_number'), true, false)],
        [$telegram->buildKeyboardButton($texts->getText('menu_back')),$telegram->buildKeyboardButton($texts->getText('go_main_page'))]
    ];
    $keyb = $telegram->buildKeyBoard($options, $onetime = false, $resize = true);
    $content = ['chat_id' => $chatID, 'text' => $texts->getText('ask_phone_number'), 'reply_markup' => $keyb];
    $telegram->sendMessage($content);
    sendTextWithKeyboard($buttons, $textToSend);
}

function showCardNumber(){
    global $user, $texts, $telegram, $chatID;
    $option[] = [$telegram->buildInlineKeyBoardButton($texts->getText('payme'), "https://payme.uz/5ed65ce8672f9f51945194c1", "")];
    $option[] = [$telegram->buildInlineKeyBoardButton($texts->getText('send_check'), "https://t.me/medlabuzz", "")];
    $keyboard = $telegram->buildInlineKeyBoard($option);
    $datas = array(
        'chat_id' => $chatID,
        'reply_markup' => $keyboard,
        'text' => $texts->getText('sell_text'),
        'parse_mode' => 'HTML'
    );
    $telegram->sendMessage($datas);
}

function showSettings()
{
    global $user, $texts, $telegram, $chatID;
    $user->setPage(Pages::PAGE_SETTINGS);
    $options = [
        [$telegram->buildKeyboardButton($texts->getText('lang'))],
        [$telegram->buildKeyboardButton($texts->getText('change_phone'))],
        [$telegram->buildKeyboardButton($texts->getText('share_button'))],
        [$telegram->buildKeyboardButton($texts->getText('menu_back')),$telegram->buildKeyboardButton($texts->getText('go_main_page'))]
    ];
    $keyb = $telegram->buildKeyBoard($options, $onetime = false, $resize = true);
    // $textToSend = '';
    $content = array('chat_id' => $chatID, 'reply_markup' => $keyb, 'text' => $texts->getText('page_main_text'), 'parse_mode' => "HTML");
    $telegram->sendMessage($content);
}

function showSharePage(){
    global $user, $texts, $telegram, $chatID, $service;
    $count = $service->getShareUserCount($chatID);
    $place = $service->getUserPlace($chatID);
    $textToSend = "👨‍💻 Sizning ushbu oyda (".date("m.Y").") taklif qilgan do'stlaringiz soni: <b>".$count."</b>\n🚀 Sizning ushbu oyda chegirma olish imkoniyatigiz o'rni: <b>".$place."</b>";
    $datas = array(
        'chat_id' => $chatID,
        'text' => $textToSend,
        'parse_mode' => 'HTML'
    );
    $telegram->sendMessage($datas);
    
    $textToSend = $texts->getText('share_text')."\n\n\nhttps://t.me/medlabuzbot?start=".$chatID;
    $option[] = [$telegram->buildInlineKeyBoardButton($texts->getText('share_button'), "", "",$textToSend)];
    $keyboard = $telegram->buildInlineKeyBoard($option);
    $datas = array(
        'chat_id' => $chatID,
        'reply_markup' => $keyboard,
        'text' => $textToSend,
        'parse_mode' => 'HTML'
    );
    $telegram->sendMessage($datas);
}

function showFreeMaterials($pages = Pages::PAGE_CATEGORIES)
{
    global $user, $texts, $service;
    $user->setPage($pages);
    $buttons = $service->getCategoriesArrayLike("categories");
    // $buttons = [$telegram->buildKeyboardButton($texts->getText('go_main_page'))];
    $textToSend = $texts->getText("page_main_text");
    sendTextWithKeyboardCustom($buttons, $textToSend, true);
}

function showSubcategories($id)
{
    global $user, $texts, $service, $telegram, $chatID;
    $arr["user_id"] = $chatID;
    $arr["chat_id"] = "@med_lab1";
    $result = $telegram->getChatMember($arr);
    file_put_contents('error.txt', print_r($result), FILE_APPEND);
    if($result["ok"] && ($result["result"]["status"] == "member" || $result["result"]["status"] == "creator" || $result["result"]["status"] == "administrator")){
        $buttons = $service->getSubCategoriesArrayLike($id);
        $options = [];
        for($i = 0; $i < count($buttons); $i += 1){
            if($buttons[$i]['paid'] == 'no'){
                $name = "✅ ".$buttons[$i]['name'];
            }else{
                $name = "💸 ".$buttons[$i]['name'];
            }
            $options[] = [$telegram->buildInlineKeyboardButton($name, "", 'sub_categories_'.$buttons[$i]['id'])];
        }
        $options[] = [$telegram->buildInlineKeyboardButton($texts->getText('menu_back'), "", 'back_to_category')];
        $keyb = $telegram->buildInlineKeyBoard($options);
        $datas = array(
            'chat_id' => $chatID,
            'reply_markup' => $keyb,
            'text' => $texts->getText('page_main_text'),
            'parse_mode' => 'HTML'
        );
        $telegram->sendMessage($datas);
    }else{
        $option[] = [$telegram->buildInlineKeyBoardButton($texts->getText('obuna_bulish'), "https://t.me/med_lab1", "")];
        $option[] = [$telegram->buildInlineKeyBoardButton($texts->getText('davom_etish'), "https://t.me/med_lab1", "")];
        $keyboard = $telegram->buildInlineKeyBoard($option);
        $datas = array(
            'chat_id' => $chatID,
            'reply_markup' => $keyboard,
            'text' => $texts->getText('channel_obuna_text'),
            'parse_mode' => 'HTML'
        );
        $telegram->sendMessage($datas);
    }
    
}

function showLinkPage($id){
    global $user, $texts, $service, $telegram, $chatID;
    
    $buttons = $service->getSubCategoriesInfo($id);
    if($buttons['type'] == 'file'){
        $content = ['chat_id' => $chatID, 'from_chat_id' => $chatID, 'message_id' => $telegram->MessageID()];
        $telegram->forwardMessage($content);
    }elseif($buttons['type'] == 'text'){
        $options[] = [$telegram->buildInlineKeyboardButton($texts->getText('menu_back'), "", 'back_to_sub_category'.$buttons["category_id"])];
        $keyb = $telegram->buildInlineKeyBoard($options);
        $datas = array(
            'chat_id' => $chatID,
            'reply_markup' => $keyb,
            'text' => $buttons['info'],
            'parse_mode' => 'HTML'
        );
        $telegram->sendMessage($datas);
    }
    
}

function showNewsPage(){
    global $user, $texts, $service;
    $user->setPage($pages);
    $buttons = $service->getCategoriesArrayLike("categories");
    // $buttons = [$telegram->buildKeyboardButton($texts->getText('go_main_page'))];
    $textToSend = $texts->getText("page_main_text");
    sendTextWithKeyboardCustom($buttons, $textToSend, true);
}

function sendFeedback(){
    global $user, $texts, $service, $chatID, $telegram;
    $user->setPage(Pages::FEEDBACK);
    $options = [
        [$telegram->buildKeyboardButton($texts->getText('menu_back')),$telegram->buildKeyboardButton($texts->getText('go_main_page'))]
    ];
    $keyb = $telegram->buildKeyBoard($options, $onetime = false, $resize = true);
    // $textToSend = '';
    $content = array('chat_id' => $chatID, 'reply_markup' => $keyb, 'text' => $texts->getText('send_feedback_text'), 'parse_mode' => "HTML");
    $telegram->sendMessage($content);
}

// function sendFeedbackToAdmin($text){
//     global $user, $texts, $service;
//     $user->setPage(Pages::FEEDBACK);
//     if (isset($message['reply_to_message']['forward_from']['id'])) {
//         if (in_array($chatID, $ADMINS_CHAT_IDS)) {
//             $content = ['chat_id' => $message['reply_to_message']['forward_from']['id'], 'text' => $text];
//             $telegram->sendMessage($content);
//         }
//     }
//     sendMessage($texts->getText("send_feedback_text"));
// }

function showChangePhoneNumber(){
    global $user, $telegram, $chatID, $texts;
    $user->setPage(Pages::PAGE_CHANGE_PHONE);
    $options = [
        [$telegram->buildKeyboardButton($texts->getText('share_phone_number'), true, false)],
        [$telegram->buildKeyboardButton($texts->getText('menu_back')),$telegram->buildKeyboardButton($texts->getText('go_main_page'))]
    ];
    $keyb = $telegram->buildKeyBoard($options, $onetime = false, $resize = true);
    $t = $texts->getText('ask_phone_number')."\nSizning raqamingiz: ".$user->getPhoneNumber();
    $content = ['chat_id' => $chatID, 'text' => $t, 'reply_markup' => $keyb];
    $telegram->sendMessage($content);
    // sendTextWithKeyboard($buttons, $textToSend);
}

function checkStatus()
{
    global $user, $texts;
    $user->setPage(Pages::PAGE_MAIN);
    if($user->getUserStatus() == 'active'){
        $textToSend = $texts->getText("user_status_text_ok");
        sendMessage($textToSend);
    }else{
        $textToSend = $texts->getText("user_status_text_no");
        sendMessage($textToSend);
        showSell();
    }
}

function replyUserFeedback($id){
    global $user, $texts, $service, $telegram, $chatID;
    $datas = array(
        'chat_id' => $id,
        'text' => "Mutaxasislar xabarni qabul qildi",
        'parse_mode' => 'HTML'
    );
    $telegram->sendMessage($datas);
}

function askBirthDate()
{
    global $texts;
    $buttons = [$texts->getText('back_btn')];
    sendTextWithKeyboard($buttons, $texts->getText('birth_date'));
}
function askGender()
{
    global $texts, $chatID, $telegram;
    $option[] = [$telegram->buildInlineKeyBoardButton($texts->getText('male'), "", "male")];
    $option[] = [$telegram->buildInlineKeyBoardButton($texts->getText('female'), "", "female")];
    $keyboard = $telegram->buildInlineKeyBoard($option);
    $datas = array(
        'chat_id' => $chatID,
        'reply_markup' => $keyboard,
        'text' => $texts->getText('gender'),
        'parse_mode' => 'HTML'
    );
    $telegram->sendMessage($datas);
}

//admin page

function showAdminMainPage(){
    global $user, $texts, $telegram, $chatID;
    $user->setPage(Pages::PAGE_ADMIN);
    $options = [
        [$telegram->buildKeyboardButton("POST rasm bilan")],
        [$telegram->buildKeyboardButton("POST rasmsiz faqat matn")],
        [$telegram->buildKeyboardButton($texts->getText('menu_back'))]
    ];
    $keyb = $telegram->buildKeyBoard($options, $onetime = false, $resize = true);
    $content = array('chat_id' => $chatID, 'reply_markup' => $keyb, 'text' => $texts->getText('page_main_text'), 'parse_mode' => "HTML");
    $telegram->sendMessage($content);
}

function sendPost($text){
    global $user, $texts, $telegram, $chatID, $ADMINS_CHAT_IDS, $service;
    $i = 0;
    $users = $service->getUsersArrayLike();
    
    // foreach($users as $chat_id){
    //     if($chat_id != ""){
    //         $temp = $text." | ".$chat_id." | ".$i;
    //         $content = ['chat_id' => $chatID, 'text' => $temp, 'parse_mode' => "HTML"];
    //         $res = $telegram->sendMessage($content);
    //         if($res){
    //             $i ++;
    //         }
    //     }
    // }
    // $options = [
    //     [$telegram->buildKeyboardButton($texts->getText('menu_back'))]
    // ];
    // $reply_result = $i." ta userga yuborildi";
    // $keyb = $telegram->buildKeyBoard($options, $onetime = false, $resize = true);
    // $content = array('chat_id' => $chatID, 'reply_markup' => $keyb, 'text' => $reply_result, 'parse_mode' => "HTML");
    // $telegram->sendMessage($content);
}

function sendPostWithPhoto($text){
    global $user, $texts, $telegram, $chatID, $ADMINS_CHAT_IDS, $rootPath;
    $i = 0;
    // $product = $user->getProduct();
    // $photoUrl = $rootPath."/".$product['photoUrl'];
    // foreach($ADMINS_CHAT_IDS as $admin_chat_id){
    //     $content = ['chat_id' => $admin_chat_id, 'photo' => $photoUrl, 'caption' => $text];
    //     $res = $telegram->sendPhoto($content);
    //     if($res){
    //         $i ++;
    //     }
    // }
    // $options = [
    //     [$telegram->buildKeyboardButton($texts->getText('menu_back'))]
    // ];
    // $reply_result = $i." ta userga yuborildi";
    // $keyb = $telegram->buildKeyBoard($options, $onetime = false, $resize = true);
    // $content = array('chat_id' => $chatID, 'reply_markup' => $keyb, 'text' => $reply_result, 'parse_mode' => "HTML");
    // $telegram->sendMessage($content);
}

function addInfoPage($id){
    global $user, $texts, $telegram, $chatID;
    $user->setPage(Pages::PAGE_ADD_INFO);
    $options = [
        [$telegram->buildKeyboardButton($texts->getText('menu_back'))]
    ];
    $keyb = $telegram->buildKeyBoard($options, $onetime = false, $resize = true);
    $content = array('chat_id' => $chatID, 'reply_markup' => $keyb, 'text' => "uz: Uzbekcha nomi\nru: Ruscha nomi\ninfo: Malumotlar shu yerga yoziladi(agar fayl yuborilishi kerak bo'lsa messageId yoziladi)\ntype: text/file(agar fayl yuborilishi kerak bo'lsa 'file')\npaid:yes/no", 'parse_mode' => "HTML");
    $telegram->sendMessage($content);
}

function test(){
    global $user, $texts, $telegram, $chatID;
    if($telegram->getChatMember(array('@med_lab1',$chatID))){
        sendMessage("yes");
    }else{
        sendMessage("no");
    }
}
// helper functions

function sendMessage($text)
{
    global $telegram, $chatID;
    $telegram->sendMessage(['chat_id' => $chatID, 'text' => $text]);
}

function sendTextWithKeyboard($buttons, $text, $backBtn = false)
{
    global $telegram, $chatID, $texts;
    $option = [];
    if (count($buttons) % 2 == 0) {
        for ($i = 0; $i < count($buttons); $i += 2) {
            $option[] = array($telegram->buildKeyboardButton($buttons[$i]), $telegram->buildKeyboardButton($buttons[$i + 1]));
        }
    } else {
        for ($i = 0; $i < count($buttons) - 1; $i += 2) {
            $option[] = array($telegram->buildKeyboardButton($buttons[$i]), $telegram->buildKeyboardButton($buttons[$i + 1]));
        }
        $option[] = array($telegram->buildKeyboardButton(end($buttons)));
    }
    if ($backBtn) {
        $option[] = [$telegram->buildKeyboardButton($texts->getText("menu_back"))];
    }
    $keyboard = $telegram->buildKeyBoard($option, $onetime = false, $resize = true);
    $content = array('chat_id' => $chatID, 'reply_markup' => $keyboard, 'text' => $text, 'parse_mode' => "HTML");
    $telegram->sendMessage($content);
}

function sendTextWithKeyboardCustom($buttons, $text, $backBtn = false)
{
    global $telegram, $chatID, $texts;
    $option = [];
    for ($i = 0; $i < count($buttons); $i += 1) {
        $option[] = array($telegram->buildKeyboardButton($buttons[$i]));
    }
    if ($backBtn) {
        $option[] = [$telegram->buildKeyboardButton($texts->getText("menu_back")),$telegram->buildKeyboardButton($texts->getText('go_main_page'))];
    }
    $keyboard = $telegram->buildKeyBoard($option, $onetime = false, $resize = true);
    $content = array('chat_id' => $chatID, 'reply_markup' => $keyboard, 'text' => $text, 'parse_mode' => "HTML");
    $telegram->sendMessage($content);
}

function isContains($string, $needle)
{
    return strpos($string, $needle) !== false;
}