<?
require_once 'database/db_connection.php';
class Service{
    private $lang;
    
    function __construct($lang){
        $this->lang = $lang;
    }
    
    function getText($keyword)
    {
        global $db;
        $res = "";
        $keyword = $db->real_escape_string($keyword);
        $result = $db->query("SELECT * FROM `ml_categories` WHERE `keyword` = '{$keyword}' LIMIT 1");
        $arr = $result->fetch_assoc();
        if (isset($arr['info'])) {
            $res = $arr['info'];
        }
        return $res;
    }
    
    function getReagentsNameByKeyword($keyword){
        global $db;
        $res = array();
        $keyword = $db->real_escape_string($keyword);
        $result = $db->query("SELECT * FROM `ml_reagents` WHERE `name` LIKE '%$keyword%' AND status = 'active' LIMIT 10");
        while($arr = $result->fetch_assoc()){
            if (isset($arr['name'])) {
                $res[] = $arr;
            }
        }
        
        return $res;
    }
    
    function getReagentsNameById($id){
        global $db;
        $res = array();
        $id = $db->real_escape_string($id);
        $result = $db->query("
            SELECT r.name as reagent,r.manufacturer as manufacturer, c.name as company, c.addres, c.refer_point, c.location, c.phone_number, c.description FROM ml_companies_reagents as cr
            JOIN ml_reagents as r ON r.id = cr.reagent_id
            JOIN ml_companies as c ON c.id = cr.company_id
            WHERE cr.reagent_id = '$id' AND c.status = 'active' ORDER by c.sort asc
        ");
        while($arr = $result->fetch_assoc()){
            $res[] = $arr;
        }
        
        return $res;
    }
    
    function getReagentsNameByText($key,$start){
        global $db;
        $res = array();
        $key = $db->real_escape_string($key);
        $result = $db->query("SELECT * FROM ml_reagents WHERE name LIKE '%$key%' AND status = 'active' LIMIT 15");
        while($arr = $result->fetch_assoc()){
            $res[] = $arr;
        }
        
        return $res;
    }
    
    function getAllCategories()
    {
        global $db;
        $res = [];
        $keyword = $db->real_escape_string($keyword);
        $result = $db->query("SELECT * FROM `ml_categories`");
        while($arr = $result->fetch_assoc()){
            if(isset($arr[$this->lang])){
                $res[] = $arr[$this->lang];
            }
        }
        return $res;
    }
    
    function getCategoryByName($text){
        global $db;
        $id = 0;
        $keyword = $db->real_escape_string($text);
        $result = $db->query("SELECT * FROM `ml_categories` WHERE $this->lang = '$keyword' LIMIT 1");
        $arr = $result->fetch_assoc();
        if(isset($arr['id'])){
            $id = $arr['id'];
        }
        return $id;
    }
    
    function getCategoriesArrayLike($text){
        global $db;
        $res = [];
        $keyword = $db->real_escape_string($text);
        $query = "SELECT * FROM `ml_categories` WHERE keyword LIKE '{$keyword}%'";
        $result = $db->query($query);
        while($arr = $result->fetch_assoc()){
            if(isset($arr[$this->lang])){
                $res[] = $arr[$this->lang];
            }
        }
        return $res;
    }
    
    function getSubCategoriesArrayLike($id, $first = 0, $count = 20){
        global $db;
        $res = [];
        $first = $db->real_escape_string($first);
        $count = $db->real_escape_string($count);
        $query = "SELECT * FROM `ml_categories` WHERE category_id = '$id'";
        $result = $db->query($query);
        $i = 0;
        while($arr = $result->fetch_assoc()){
            if(isset($arr[$this->lang])){
                $res[$i]['id'] = $arr['id'];
                $res[$i]['name'] = $arr[$this->lang];
                $res[$i]['paid'] = $arr['paid'];
            }
            $i ++;
        }
        return $res;
    }
    
    function getSubCategoriesInfo($id){
        global $db;
        $res = [];
        $query = "SELECT * FROM `ml_categories` WHERE id = '$id' LIMIT 1";
        $result = $db->query($query);
        $i = 0;
        $arr = $result->fetch_assoc();
        
        $res['info'] = $arr['info'];
        $res['category_id'] = $arr['category_id'];
        $res['type'] = $arr['type'];
        
        return $res;
    }
    
    function setFeedback($chatID, $text){
        global $db;
        $query = "INSERT INTO `ml_feedback` (chatID,feedback) VALUES ('$chatID','$text')";
        file_put_contents('error.txt',$query,FILE_APPEND);
        $result = $db->query($query);
        return $result;
    }
    
    function setSearchText($text){
        global $db;
        $text = $db->real_escape_string($text);
        $query = "INSERT INTO `ml_stat` (text) VALUES ('$text')";
        $result = $db->query($query);
        return $result;
    }
    
    function setReplyFeedback($chatID, $text){
        global $db;
        $query = "UPDATE ml_feedback SET reply=1,reply_text='$text' WHERE chatID = '$chatID' AND reply='0'";
        $result = $db->query($query);
        return $result;
    }
    
    function getUsersArrayLike(){
        global $db;
        $res = [];
        $query = "SELECT chatID FROM `ml_users`";
        $result = $db->query($query);
        while($arr = $result->fetch_assoc()){
            if(isset($arr['chatID'])){
                $res[] = $arr['chatID'];
            }
        }
        return $res;
    }
    
    function setShareUserCount($chatID){
        global $db;
        $query = "UPDATE ml_users SET counter = counter + 1 WHERE chatID = '$chatID'";
        $result = $db->query($query);
        return $result;
    }
    
    function getShareUserCount($chatID){
        global $db;
        $query = "SELECT counter FROM ml_users WHERE chatID = '$chatID'";
        $result = $db->query($query);
        while($arr = $result->fetch_assoc()){
            if(isset($arr['counter'])){
                $res = $arr['counter'];
            }
        }
        return $res;
    }
    
    function getUserPlace($chatID){
        global $db;
        $num = 0;
        $query = "SELECT * from ml_users ORDER BY counter DESC LIMIT 10";
        $result = $db->query($query);
        while($arr = $result->fetch_assoc()){
            $num ++;
            if(isset($arr['counter']) && $arr['chatID'] == $chatID){
                return $num;
            }
        }
        return "10 dan past";
    }
}
?>