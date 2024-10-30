<?php
/*
Plugin Name: Britely Embeds
Plugin URI: http://www.britely.com/wordpress/plugin
Description: Insert britely embeds via the shortcode syntax [britely=URL]
Version: 1.0.1
Author: MashLogic Inc
Author URI: http://www.britely.com/
License: GPLv2 or later
*/

/*
 * USAGE:
 * Insert a brite in via the syntax [britely=URL width=SIZE],
 * where URL is the url of the brite you want to embed,
 * and width is an optional size of 700 (default), 620 or 460.
 * You can use this in pages, posts or any custom content.
 */

// create a directory of our own for the cache of html embeds
@mkdir(get_cache_dir(), 0755);

// Main Function
function eggs_shortcode($atts, $content = null) {

  // [britely url="http://www.britely.com/:user/:book" width="620"] for instance
  extract(shortcode_atts(array( 'url' => ''
                              , 'width' => 700
                              ), $atts));

  // grok [britely=http://www.britely.com/:user/:book] besides [britely url="…"]
  if (isset($atts[0]) && $url == '')
    $url = ltrim($atts[0], '="');
  $url = preg_replace('/[?"].*/', '', $url);

  // helpful errors
  if (!$url) return '[britely=please add the url of the brite to embed here]';
  if (!preg_match('@^https?://www\.britely\.com/[^/]+/[^/]+@', $url))
    return '[britely: "' . $url . '" is not a brite url!' .
           ' Find one at www.britely.com]';

  // already cached this embed locally?
  $url        = $url . '/embed_html?w=' . trim($width);
  $cache_file = get_cache_dir() . sha1($url) . '.html';
  $embed_html = file_get_contents($cache_file);
  if ($embed_html != false) return $embed_html;

  // no; cache it first
  $embed_html = file_get_contents($url, false);
  file_put_contents($cache_file, $embed_html);

  return $embed_html;
}

function get_cache_dir() {
  $uploads = wp_upload_dir();
  if ($uploads['error']) {
    $uploads['basedir'] = WP_CONTENT_DIR . '/uploads';
    // make sure it exists and we have permission
    if ( !wp_mkdir_p($uploads['basedir']) ) {
        printf("<p><em>Unable to create directory %s.  Is its parent directory writable by the server?</em></p>", $uploads['basedir']);
    }
  }
  return $uploads['basedir'] . '/britely-embed-cache/';
}

// Create Shortcode
add_shortcode('britely', 'eggs_shortcode');

?>
