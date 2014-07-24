<?php

    function default_admin_sidebar() { ?>
        <div class="postbox" id="support">
            <h3 class="hndle">
                <span><?php _e( 'Need Help?', 'cpt-helper' ); ?></span>
            </h3>
            <div class="inside">
                <p><?php printf( __( 'Please use our <a href="%s">free support forum</a>.', 'cpt-helper' ), 'http://rtcamp.com/support/forum/wordpress-cpt/' ); ?></p>
            </div>
        </div>

        <div class="postbox" id="social">
            <h3 class="hndle">
                <span><?php _e( 'Getting Social is Good', 'cpt-helper' ); ?></span>
            </h3>
            <div style="text-align:center;" class="inside">
                <a class="cpt-helper-facebook" title="<?php _e( 'Become a fan on Facebook', 'cpt-helper' ); ?>" target="_blank" href="http://www.facebook.com/rtCamp.solutions/"><i class="fa fa-facebook"></i></a>
                <a class="cpt-helper-twitter" title="<?php _e( 'Follow us on Twitter', 'cpt-helper' ); ?>" target="_blank" href="https://twitter.com/rtcamp/"><i class="fa fa-twitter"></i></a>
                <a class="cpt-helper-gplus" title="<?php _e( 'Add to Circle', 'cpt-helper' ); ?>" target="_blank" href="https://plus.google.com/110214156830549460974/posts"><i class="fa fa-google-plus"></i></a>
                <a class="cpt-helper-rss" title="<?php _e( 'Subscribe to our feeds', 'cpt-helper' ); ?>" target="_blank" href="http://feeds.feedburner.com/rtcamp/"><i class="fa fa-rss"></i></a>
            </div>
        </div>

        <div class="postbox" id="useful-links">
            <h3 class="hndle">
                <span><?php _e( 'Useful Links', 'cpt-helper' ); ?></span>
            </h3>
            <div class="inside">
                <ul role="list">
                    <li role="listitem">
                        <a href="https://rtcamp.com/wordpress-cpt/" title="<?php _e( 'WordPress-Nginx Solutions', 'cpt-helper' ); ?>"><?php _e( 'WordPress-Nginx Solutions', 'cpt-helper' ); ?></a>
                    </li>
                    <li role="listitem">
                        <a href="https://rtcamp.com/services/wordPress-themes-design-development/" title="<?php _e( 'WordPress Theme Devleopment', 'cpt-helper' ); ?>"><?php _e( 'WordPress Theme Devleopment', 'cpt-helper' ); ?></a>
                    </li>
                    <li role="listitem">
                        <a href="http://rtcamp.com/services/wordpress-plugins/" title="<?php _e( 'WordPress Plugin Development', 'cpt-helper' ); ?>"><?php _e( 'WordPress Plugin Development', 'cpt-helper' ); ?></a>
                    </li>
                    <li role="listitem">
                        <a href="http://rtcamp.com/services/custom-wordpress-solutions/" title="<?php _e( 'WordPress Consultancy', 'cpt-helper' ); ?>"><?php _e( 'WordPress Consultancy', 'cpt-helper' ); ?></a>
                    </li>
                    <li role="listitem">
                        <a href="https://rtcamp.com/easyengine/" title="<?php _e( 'easyengine (ee)', 'cpt-helper' ); ?>"><?php _e( 'easyengine (ee)', 'cpt-helper' ); ?></a>
                    </li>        
                </ul>
            </div>
        </div>

        <div class="postbox" id="latest_news">
            <div title="<?php _e( 'Click to toggle', 'cpt-helper' ); ?>" class="handlediv"><br /></div>
            <h3 class="hndle"><span><?php _e( 'Latest News', 'cpt-helper' ); ?></span></h3>
            <div class="inside"></div>
        </div><?php
    } // End of default_admin_sidebar()
