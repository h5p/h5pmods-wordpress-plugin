<?php
/**
 * H5P Mods Plugin.
 *
 * Alters the way the H5P plugin works.
 *
 * @package   H5P
 * @author    Joubel <contact@joubel.com>
 * @license   MIT
 * @link      http://joubel.com
 * @copyright 2015 Joubel
 *
 * @wordpress-plugin
 * Plugin Name:       H5P Mods
 * Plugin URI:        http://h5p.org/
 * Description:       Allows you to alter how the H5P plugin works.
 * Version:           0.0.1
 * Author:            Joubel
 * Author URI:        http://joubel.com
 * Text Domain:       h5pmods
 * License:           MIT
 * License URI:       http://opensource.org/licenses/MIT
 * Domain Path:       /languages
 * GitHub Plugin URI: https://github.com/h5p/h5pmods-wordpress-plugin
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
  die;
}

/**
 * Allows you to alter the H5P library semantics, i.e. changing how the
 * editor looks and how content parameters are filtered.
 *
 * In this example we change the preview label for the collage tool where
 * version is < 1.0.0.
 *
 * @param object &$semantics The same as in semantics.json
 * @param string $name The machine readable name of the library.
 * @param int $majorVersion First part of the version number.
 * @param int $minorVersion Second part of the version number.
 */
function h5pmods_alter_semantics(&$semantics, $name, $majorVersion, $minorVersion) {
  if ($name === 'H5P.Collage' && $majorVersion < 1) {

    // Find correct field
    for ($i = 0, $l = count($semantics); $i < $l; $i++) {
      $field = $semantics[$i];

      if ($field->name === 'collage') {

        // Found our field, change label
        $field->label = 'Altered Label';
        return;
      }
    }
  }
}
add_action('h5p_alter_library_semantics', 'h5pmods_alter_semantics', 10, 4);

/**
 * Allows you to alter the parameters of H5P content after it has been filtered
 * through semantics. This is useful for adapting the content to the current
 * context.
 *
 * In this example we add a paragraph to the question text on all the multiple
 * choice tasks.
 *
 * @param object &$paramters The content input used to "start" the library.
 * @param string $name The machine readable name of the library.
 * @param int $majorVersion First part of the version number.
 * @param int $minorVersion Second part of the version number.
 */
function h5pmods_alter_parameters(&$parameters, $name, $majorVersion, $minorVersion) {
  if ($name === 'H5P.MultiChoice') {
    $parameters->question .= '<p>Generated at ' . time() . '.</p>';
  }
}
add_action('h5p_alter_filtered_parameters', 'h5pmods_alter_parameters', 10, 4);

/**
 * Allows you to alter which JavaScripts are loaded for H5P. This is
 * useful for adding your own custom scripts or replacing existing once.
 *
 * In this example we're going add a custom script which keeps track of the
 * scoring for drag 'n drop tasks.
 *
 * @param object &$scripts List of JavaScripts that will be loaded.
 * @param array $libraries The libraries which the scripts belong to.
 * @param string $embed_type Possible values are: div, iframe, external, editor.
 */
function h5pmods_alter_scripts(&$scripts, $libraries, $embed_type) {
  if (isset($libraries['H5P.DragQuestion'])) {
    $scripts[] = (object) array(
      // Path can be relative to wp-content/uploads/h5p or absolute.
      'path' => '/score-tracking.js',
      'version' => '?ver=1.2.3' // Cache buster
    );
  }
}
add_action('h5p_alter_library_scripts', 'h5pmods_alter_scripts', 10, 3);

/**
 * Allows you to alter which stylesheets are loaded for H5P. This is
 * useful for adding your own custom styles or replacing existing once.
 *
 * In this example we're going add a custom script which keeps track of the
 * scoring for drag 'n drop tasks.
 *
 * @param object &styles List of stylesheets that will be loaded.
 * @param array $libraries The libraries which the styles belong to.
 * @param string $embed_type Possible values are: div, iframe, external, editor.
 */
function h5pmods_alter_styles(&$styles, $libraries, $embed_type) {
  $styles[] = (object) array(
    // Path can be relative to wp-content/uploads/h5p or absolute.
    'path' => 'http://mydomain.org/custom-h5p-styling.css',
    'version' => '?ver=1.3.7' // Cache buster
  );
}
add_action('h5p_alter_library_styles', 'h5pmods_alter_styles', 10, 3);

/**
 * Allows other plugins to change the access permission for the
 * embedded iframe's content.
 *
 * In this example we make sure that content with id 1 always can be embedded.
 *
 * @param bool $access
 * @param int $content_id
 * @return bool New access permission
 */
function h5pmods_embed_access($access, $content_id) {
  if ($content_id === '1') {
    $access = TRUE;
  }
  return $access;
}
add_filter('h5p_embed_access', 'h5pmods_embed_access', 10, 2);
