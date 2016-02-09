<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       www.cristidev.ro
 * @since      1.0.0
 *
 * @package    Iwm_Scraper
 * @subpackage Iwm_Scraper/admin/partials
 */
?>

<!-- This file should primarily consist of HTML with a little bit of PHP. -->
<div class="wrap">

    <h2><?php echo esc_html(get_admin_page_title()); ?></h2>

    <form method="post" name="iwm_scraper_categories_options" action="options.php">
        <?php
            $options = get_option($this->plugin_name);
            $sitemap_categories = $options['sitemap_categories'];
        ?>
        <?php
            settings_fields($this->plugin_name);
            do_settings_sections($this->plugin_name);
        ?>
        <fieldset>
            <legend class="screen-reader-text"><span><?php _e('Write the url address of the sitemap for categories', $this->plugin_name); ?></span></legend>
            <p>Write the url address of the sitemap for categories</p>
            <input type="url" required="required" class="regular-text" id="<?php echo $this->plugin_name; ?>-sitemap_categories" name="<?php echo $this->plugin_name; ?>[sitemap_categories]" value="<?php if(!empty($sitemap_categories)) echo $sitemap_categories; ?>"/>
        </fieldset>

        <?php submit_button('Save url', 'primary','submit', TRUE); ?>

        <?php if( !empty( $sitemap_categories ) ): ?>
        <p><a id="iwm_importCategories" class="button danger">Import categories now</a></p>
        <?php endif;?>

    </form>
    <div id="iwm_categories_response"></div>
</div>