<?php 
add_action('wp_ajax_nopriv_do_comment_rate', 'do_comment_rate');
add_action('wp_ajax_do_comment_rate', 'do_comment_rate');
function do_comment_rate(){
    if (!isset($_POST["comment_id"]) || !isset($_POST["event"])) {

        $data = array("status"=>500,"data"=>'?');
        echo json_encode($data);

    } else {

        $comment_id = $_POST["comment_id"];
        $event = $_POST["event"];
        $expire = time() + 99999999;
        $domain = ($_SERVER['HTTP_HOST'] != 'localhost') ? $_SERVER['HTTP_HOST'] : false; // make cookies work with localhost
        setcookie('comment_rated_'.$comment_id,$comment_id,$expire,'/',$domain,false);
        $_comment_up = get_comment_meta($comment_id,'_comment_up',true);
        $_comment_down = get_comment_meta($comment_id,'_comment_down',true);
        if ($event == "up") {

            if (!$_comment_up) {

                update_comment_meta($comment_id, '_comment_up', 1);

            } else {

                update_comment_meta($comment_id, '_comment_up', ($_comment_up + 1));
            }
        } else {
            if (!$_comment_down || $_comment_down == '' || !is_numeric($_comment_down)) {

                update_comment_meta($comment_id, '_comment_down', 1);

            } else {

                update_comment_meta($comment_id, '_comment_down', ($_comment_down + 1));

            }

        }
        $data = array();
        $_comment_up = get_comment_meta($comment_id,'_comment_up',true);
        $_comment_down = get_comment_meta($comment_id,'_comment_down',true);
        $data = array("status"=>200,"data"=>array("event"=>$event,"_comment_up"=>$_comment_up,"_comment_down"=>$_comment_down));
        echo json_encode($data);
    }
    die;
}

function comment_rate($comment_ID = 0,$echo = true){

    $_comment_up = get_comment_meta($comment_ID,'_comment_up',true) ? get_comment_meta($comment_ID,'_comment_up',true) : 0;
    $_comment_down = get_comment_meta($comment_ID,'_comment_down',true) ? get_comment_meta($comment_ID,'_comment_down',true) : 0 ;
    $done = "";
    if (isset($_COOKIE['comment_rated_'.$comment_ID])) $done = " rated";
    $content = '<span class="comment--like'.$done.'" data-commentid="'.$comment_ID.'"><a href="javascript:;" data-event="up"><i class="iconfont icon-arrowup"></i><em class="count">'.$_comment_up.'</em></a><a href="javascript:;" data-event="down"><i class="iconfont icon-arrowdown"></i><em class="count">'.$_comment_down.'</em></a></span>';

    if ($echo) {

        echo $content;

    } else {

        return $content;

    }

}

add_action('delete_comment', 'delete_comment_ratings_fields');
function delete_comment_ratings_fields($comment_ID) {
    global $wpdb;
    delete_comment_meta($comment_ID, '_comment_up');
    delete_comment_meta($comment_ID, '_comment_down');
}

?>