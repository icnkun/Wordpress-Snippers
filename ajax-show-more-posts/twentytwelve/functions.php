<?php
function ajax_show_more_button(){
    global $wp_query;
    if( 2 > $GLOBALS["wp_query"]->max_num_pages){
        return;
    }
    if(is_category()) $cat_id = ' data-cate="'.get_query_var( 'cat' ).'"';    
    if(is_author()) $author = ' data-author="'.get_query_var('author').'"';
    if(is_tag()) $tag = ' data-tag="'.get_query_var('tag').'"';
    if(is_search()) $search = ' data-search="'.get_query_var('s').'"';
    echo '<a id="show-more" href="javascript:;"'.$cat_id.' data-paged = "2"'.$author.$tag.$search.' data-total="'.$GLOBALS["wp_query"]->max_num_pages.'" class="show-more m-feed--loader">show more</a>';

}


add_action('wp_ajax_nopriv_ajax_index_post', 'ajax_index_post');
add_action('wp_ajax_ajax_index_post', 'ajax_index_post');
function ajax_index_post(){
    $paged = $_POST["paged"];
    $total = $_POST["total"];
    $category = $_POST["category"];
    $author = $_POST["author"];
    $tag = $_POST["tag"];
    $search = $_POST["search"];
    $the_query = new WP_Query( array("posts_per_page"=>get_option('posts_per_page'),"cat"=>$category,"tag"=>$tag,"author"=>$author,"post_status"=>"publish","post_type"=>"post","paged"=>$paged,"s"=>$search) );
    while ( $the_query->have_posts() ){
        $the_query->the_post();
        get_template_part( 'content', get_post_format() );//这里是内容输出，如果你的首页是直接用的代码输出，则直接写在这里，注意PHP的开始结束符

    }
    wp_reset_postdata();
    $nav = '';
    if($category) $cat_id = ' data-cate="'.$category.'"';
    if($author) $author = ' data-author="'.$author.'"';
    if($tag) $tag = ' data-tag="'.$tag.'"';
    if($search) $search = ' data-search="'.$search.'"';
    if ( $total > $paged )    $nav = '<a id="show-more" href="javascript:;"'.$cat_id.$author.$search.' data-total="'.$total.'" data-paged = "'.($paged + 1).'" class="show-more m-feed--loader">show more</a>';
    echo $nav;
    
    die;
}


function twentytwelve_scripts_styles() {
	global $wp_styles;

	
	wp_enqueue_script( 'twentytwelve-navigation', get_template_directory_uri() . '/js/base.js', array( 'jquery' ), '20140318', true );


	wp_enqueue_style( 'twentytwelve-style', get_stylesheet_uri() );


}
add_action( 'wp_enqueue_scripts', 'twentytwelve_scripts_styles' );


function twentytwelve_wp_title( $title, $sep ) {
	global $paged, $page;

	if ( is_feed() )
		return $title;

	// Add the site name.
	$title .= get_bloginfo( 'name', 'display' );

	// Add the site description for the home/front page.
	$site_description = get_bloginfo( 'description', 'display' );
	if ( $site_description && ( is_home() || is_front_page() ) )
		$title = "$title $sep $site_description";

	// Add a page number if necessary.
	if ( $paged >= 2 || $page >= 2 )
		$title = "$title $sep " . sprintf( __( 'Page %s', 'twentytwelve' ), max( $paged, $page ) );

	return $title;
}
add_filter( 'wp_title', 'twentytwelve_wp_title', 10, 2 );


