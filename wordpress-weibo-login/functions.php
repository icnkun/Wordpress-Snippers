<?php
function do_post($url, $data) {
    $ch = curl_init ();
    curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, TRUE );
    curl_setopt ( $ch, CURLOPT_POST, TRUE );
    curl_setopt ( $ch, CURLOPT_POSTFIELDS, $data );
    curl_setopt ( $ch, CURLOPT_URL, $url );
    curl_setopt ( $ch, CURLOPT_SSL_VERIFYPEER, FALSE);
    $ret = curl_exec ( $ch );
    curl_close ( $ch );
    return $ret;
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
}

add_action( 'init', 'signup_social' );
function signup_social(){

    if($_SERVER['REQUEST_METHOD'] == 'GET')
    {
       
        if (isset($_GET['code']) && isset($_GET['type']) && $_GET['type'] == 'sina')
        {
            $code = $_GET['code'];
            $url = "https://api.weibo.com/oauth2/access_token";
            $data = "client_id=your_sina_appkey&your_client_secret=sina_client_secret&grant_type=authorization_code&redirect_uri=".urlencode (home_url())."&code=".$code;//替换成你自己的appkey和appsecret
            $output = json_decode(do_post($url,$data));
            $sina_access_token = $output->access_token;
            $sina_uid = $output->uid;
            if(empty($sina_uid)){
                wp_redirect(home_url('/?3'));//获取失败的时候直接返回首页
                exit;
            }
            if(is_user_logged_in()){
            
            $this_user = wp_get_current_user();
            update_user_meta($this_user->ID ,"sina_uid",$sina_uid);
            update_user_meta($this_user->ID ,"sina_access_token",$sina_access_token);
            wp_redirect(home_url('/me/setting?4'));//已登录用户授权
            }else{
            $user_fb = get_users(array("meta_key "=>"sina_uid","meta_value"=>$sina_uid));
            if(is_wp_error($user_fb) || !count($user_fb)){
                $get_user_info = "https://api.weibo.com/2/users/show.json?uid=".$sina_uid."&access_token=".$sina_access_token;
                $data = get_url_contents ( $get_user_info );
                $str  = json_decode($data , true);
                $username = $str['screen_name'];
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
                update_user_meta($user_id ,"sina_uid",$sina_uid);
                update_user_meta($user_id ,"sina_access_token",$sina_access_token);
                wp_redirect(home_url('/?1'));//创建帐号成功

            }else{
                update_user_meta($user_fb[0]->ID ,"sina_access_token",$sina_access_token);
                wp_set_auth_cookie($user_fb[0]->ID);
                
                wp_redirect(home_url('/?2'));//已绑定，直接登录。
            }
            }
        }
    }

}
function sina_login(){

    return  "https://api.weibo.com/oauth2/authorize?client_id=your_sina_appkey&response_type=code&redirect_uri=" . urlencode (home_url('/?type=sina'));//替换成你的appkey

}
?>