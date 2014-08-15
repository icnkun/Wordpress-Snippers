jQuery(document).on("click", "#show-more",
function() {
    if (jQuery(this).hasClass('is-loading')) {
        return false;
    }
     else {
        var paged = jQuery(this).data("paged"),
        total = jQuery(this).data("total"),
        category = jQuery(this).data("cate"),
        tag = jQuery(this).data("tag"),
        search = jQuery(this).data("search"),
        author = jQuery(this).data("author");
        var ajax_data = {
            action: "ajax_index_post",
            paged: paged,
            total: total,
            category:category,
            author:author,
            tag:tag,
            search:search
        };
        jQuery(this).html('loading...').addClass('is-loading')
         jQuery.post('/wp-admin/admin-ajax.php', ajax_data,
        function(data) {
            jQuery('#show-more').remove();
            jQuery("#content").append(data);//这里是包裹文章的容器名
        });
        return false;
    }
});