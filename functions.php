<?php
add_action('rest_api_init', function () {

    register_rest_route('themename/v1', 'instagram', array(
        'methods' => 'GET',
        'callback' => 'instagram_local_download',
        'permission_callback' => '__return_true'
    ));
}); 

// Function for fetching Instagram Latest posts from API
function themename_instagram_api_curl_connect($api_url)
{
    $connection_c = curl_init(); // initializing
    curl_setopt($connection_c, CURLOPT_URL, $api_url); // API URL to connect
    curl_setopt($connection_c, CURLOPT_RETURNTRANSFER, 1); // return the result, do not print
    curl_setopt($connection_c, CURLOPT_TIMEOUT, 20);
    $json_return = curl_exec($connection_c); // connect and get json data
    curl_close($connection_c); // close connection
    return json_decode($json_return); // decode and return
}

function themename_get_upload_dir_var( $param, $subfolder = '' ) {
    $upload_dir = wp_upload_dir();
    $url = $upload_dir[ $param ];
 
    if ( $param === 'baseurl' && is_ssl() ) {
        $url = str_replace( 'http://', 'https://', $url );
    }
 
    return $url . $subfolder;
}

function themename_save_image_from_url($url){
    $ch             =   curl_init($url);
    $dir            =   themename_get_upload_dir_var( 'basedir', '/instagram' );
    $fileName       =   basename($url);
    $saveFilePath   =   $dir . "/" . strtok($fileName, '?');
    $fp             =   fopen($saveFilePath, 'wb');
    curl_setopt($ch, CURLOPT_FILE, $fp);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_exec($ch);
    curl_close($ch);
    fclose($fp);

    $local_url = wp_upload_dir();

    $image_url = $local_url['baseurl'] . '/instagram/' . strtok($fileName, '?');
    return $image_url;
}

function instagram_local_download() {

    /** 
     * Follow this Tutorial to get access token:
     * https://developers.facebook.com/docs/instagram-basic-display-api/getting-started
     * Remember that first access token is short-lived and You need to change it to long-lived token.
     * Make sure that You set up CRON JOB at server side in PRODUCTION to refresh token before it expires.
     */

    $access_token = 'YOUR_TOKEN';
    $posts_limit = 4;
    
    $posts = themename_instagram_api_curl_connect("https://graph.instagram.com/me/media?fields=id,media_url,permalink,thumbnail_url&limit=" . $posts_limit . "&access_token=" . $access_token);
   
    var_dump($posts);
    // Check if new posts exists
    if ( isset($posts->data) ) {

        global $wpdb;

        $table_name = $wpdb->prefix . 'insta_latest_posts';

        /*
            Delete instagram folder content
        */
        // Folder path to be flushed
        $folder_path = themename_get_upload_dir_var( 'basedir', '/instagram' );
        
        // List of file names inside specified folder
        $files = glob($folder_path.'/*'); 
        
        // Deleting all the files in the list if new posts are downloaded
        foreach($files as $file) {
        
            if(is_file($file)) 
            
                // Delete the given file
                unlink($file); 
        }
        /*
            Drop instagram posts DB table
        */
        $delete = $wpdb->query("TRUNCATE TABLE $table_name");


        /*
            Write new database table and download images
        */
        foreach ($posts->data as $key => $post) :
        
            /* 
                Download photos to uploads/instagram
            */
            // Download video thumbnail and save url
            if ( isset($post->thumbnail_url) ) {
                $video_thumb_url = themename_save_image_from_url($post->thumbnail_url);
            } else {
                // Download original photo and save url
                $photo_url = themename_save_image_from_url($post->media_url);
            }

            $wpdb->insert($table_name, array(
                'ID' => null,
                'post_id' => $post->id,
                'post_permalink' => $post->permalink,
                'post_media_url' => strtok($photo_url, '?'),
                'thumbnail_url' => isset($post->thumbnail_url) ? strtok($video_thumb_url, '?'): '',
            ));

        endforeach;
    }
}

function themename_get_instagram_local_posts() {
    global $wpdb;
    // this adds the prefix which is set by the user upon instillation of wordpress
    $table_name = $wpdb->prefix . "insta_latest_posts";
    // this will get the data from your table
    $retrieve_data = $wpdb->get_results( "SELECT * FROM $table_name" );

    return $retrieve_data;
}

?>