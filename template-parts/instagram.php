<?php 

$retrieve_data = themename_get_instagram_local_posts();

if ( $retrieve_data != [] ) :
?>
    <section class="insta">
        <div class="container">
            <h4>@your_insta_acc_name</h4>
        </div>
        <div class="container">
            <div class="insta__container">

                <?php
                // data-image-url is passed to background image style with intersectionObserver JS when image is scrolled to
                foreach ($retrieve_data as $post) : ?>
                    <a href="<?= $post->post_permalink ?>" target="_blank" class="insta__item" data-image-url="<?= $post->thumbnail_url ? $post->thumbnail_url : $post->post_media_url; ?>" style="background-image: url('');">
                        <div class="insta__item__overlay">
                            <img src="<?= get_template_directory_uri(); ?>/dist/assets/images/insta-white.svg" alt="">
                        </div>
                    </a>

                <?php endforeach; ?>
            </div>
        </div>
    </section>
<?php endif; ?>