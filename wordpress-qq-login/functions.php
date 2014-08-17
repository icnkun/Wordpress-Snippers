<?php
session_start();
function qq_login(){
	$_SESSION ['state'] = md5 ( uniqid ( rand (), true ) );
    return  "https://graph.qq.com/oauth2.0/authorize?client_id=your_qq_appkey&state=" . $_SESSION ['state'] . "&response_type=code&redirect_uri=" . urlencode (home_url('/?type='.get_query_var("oauth")));//替换成你的appkey
}

function get_url_contents($url) {
    if (ini_get ( "allow_url_fopen" ) == "1")
        return file_get_contents ( $url );
    $ch = curl_init ();
    curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, TRUE );
    curl_setopt ( $ch, CURLOPT_URL, $url );
    $result = curl_exec ( $ch );
    curl_close ( $ch );
    return $result;
}//如果你已经按照我的教程添加了新浪微博登录则不需要重新定义此函数

add_action( 'init', 'signup_qq' );
function signup_qq(){
    $redirect_url = home_url();   
    if($_SERVER['REQUEST_METHOD'] == 'GET')
    {
        if (isset($_GET['code']) && isset($_GET['type']) && $_GET['type'] == 'qq')
        {
            if (!empty($_GET ['state']) && $_GET ['state'] == $_SESSION ['state']) {$code = $_GET['code'];
                $token_url = "https://graph.qq.com/oauth2.0/token?client_id=your_qq_appkey&client_secret=your_qq_secret&grant_type=authorization_code&redirect_uri=".urlencode (home_url())."&code=".$code;//替换你的appkey和appsecret
                $response = get_url_contents ( $token_url );
                if (strpos ( $response, "callback" ) !== false) {
                    $lpos = strpos ( $response, "(" );
                    $rpos = strrpos ( $response, ")" );
                    $response = substr ( $response, $lpos + 1, $rpos - $lpos - 1 );
                    $msg = json_decode ( $response );
                    if (isset ( $msg->error )) {
                        echo "<h3>错误代码:</h3>" . $msg->error;
                        echo "<h3>信息  :</h3>" . $msg->error_description;
                        exit ();
                    }
                }
                $params = array ();
                parse_str ( $response, $params );
                $qq_access_token = $params ["access_token"];} else {
                echo ("The state does not match. You may be a victim of CSRF.");
                exit;
            }
            $graph_url = "https://graph.qq.com/oauth2.0/me?access_token=" . $qq_access_token;
            $str = get_url_contents ( $graph_url );
            if (strpos ( $str, "callback" ) !== false) {
                $lpos = strpos ( $str, "(" );
                $rpos = strrpos ( $str, ")" );
                $str = substr ( $str, $lpos + 1, $rpos - $lpos - 1 );
            }
            $user = json_decode ( $str );
            if (isset ( $user->error )) {
                echo "<h3>错误代码:</h3>" . $user->error;
                echo "<h3>信息  :</h3>" . $user->error_description;
                exit ();
            }
            $qq_openid = $user->openid;
            if(empty($qq_openid)){
                wp_redirect(home_url('/?3'.$qq_openid));//授权错误跳转
                exit;
            }
            if(is_user_logged_in()){
                $this_user = wp_get_current_user();
                update_user_meta($this_user->ID ,"qq_openid",$qq_openid);
                update_user_meta($this_user->ID ,"qq_access_token",$qq_access_token);
                wp_redirect(home_url('/?4'));//已登录授权
            }else{
                $user_fb = get_users(array("meta_key "=>"qq_openid","meta_value"=>$qq_openid));
                if(is_wp_error($user_fb) || !count($user_fb)){
                    $get_user_info = "https://graph.qq.com/user/get_user_info?" . "access_token=" . $qq_access_token . "&oauth_consumer_key=" . Aladdin('qq_appkey') . "&openid=" . $qq_openid . "&format=json";
                    $data = get_url_contents ( $get_user_info );
                    $str  = json_decode($data , true);
                    $username = $str[nickname];
                    $login_name = wp_create_nonce($sina_uid);
                    $random_password = wp_generate_password( $length=12, $include_standard_special_chars=false );
                    $userdata=array(
                        'user_login' => $login_name,
                        'display_name' => $username,
                        'user_pass' => $random_password,
                        'nick_name' => $username
                    );
                    $user_id = wp_insert_user( $userdata );
                    wp_signon(array("user_login"=>$login_name,"user_password"=>$random_password),false);
                    update_user_meta($user_id ,"qq_openid",$qq_openid);
                    update_user_meta($user_id ,"qq_access_token",$qq_access_token);
                    wp_redirect(home_url('/?1'));//新用户注册跳转
                }else{                    
                    wp_set_auth_cookie($user_fb[0]->ID);
                    wp_redirect(home_url('/?2'));//已授权用户登录跳转
                }
            }
        }
    }

}
?>