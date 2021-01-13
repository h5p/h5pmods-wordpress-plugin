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
 * Details on semantics structure: {@link} https://h5p.org/semantics
 *
 * In this example we change the text label for multiple choice questions and
 * the default value for the "Show Solution" button where the version is < 2.0.0
 *
 * @param object &$semantics The same as in semantics.json
 * @param string $name The machine readable name of the library.
 * @param int $majorVersion First part of the version number.
 * @param int $minorVersion Second part of the version number.
 */
function h5pmods_alter_semantics(&$semantics, $name, $majorVersion, $minorVersion) {
  if ($name === 'H5P.MultiChoice' && $majorVersion < 2) {

    /*
     * Change the "text" label of answer options to "Option text"
     * Note that find_semantics_path expects the full path to a field
     */
    $options_text = find_semantics_path('answers/answer/text', $semantics);
    if (isset($options_text)) {
      $options_text->label = 'Option text';
    }

    /*
     * Hide the "Show Solution" button by default (for new content)
     * Note that find_semantics_field expects the field name only and retrieves
     * the first field of that name found.
     */
    $enableSolutionsButton = find_semantics_field('enableSolutionsButton', $semantics);
    if (isset($enableSolutionsButton)) {
      $enableSolutionsButton->default = false;
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
 * The path can be relative to wp-content/uploads/h5p, so
 * 'path' => '/score-tracking.js',
 * would try to load wp-content/uploads/h5p/score-tracking.js
 *
 * The path can be absolute, so
 * 'path' => 'http://mydomain.org/score-tracking.js',
 * would to try to load the script from the URL
 *
 * The path can be retrieved using WordPress functions, so for instance
 * 'path' => plugin_dir_url( __FILE__ ) . 'scripts/score-tracking.js',
 * will try to load scripts/score-tracking.js inside this plugin's folder
 *
 * @param object &$scripts List of JavaScripts that will be loaded.
 * @param array $libraries The libraries which the scripts belong to.
 * @param string $embed_type Possible values are: div, iframe, external, editor.
 */
function h5pmods_alter_scripts(&$scripts, $libraries, $embed_type) {
  if (isset($libraries['H5P.DragQuestion'])) {
    $scripts[] = (object) array(
      // Path can be relative to wp-content/uploads/h5p or absolute.
      'path' => plugin_dir_url( __FILE__ ) . 'scripts/score-tracking.js',
      'version' => '?ver=1.2.3' // Cache buster
    );
  }
}
add_action('h5p_alter_library_scripts', 'h5pmods_alter_scripts', 10, 3);

/**
 * Allows you to alter which stylesheets are loaded for H5P. This is
 * useful for adding your own custom styles or replacing existing once.
 *
 * The path can be relative to wp-content/uploads/h5p, so
 * 'path' => '/mystyles.css',
 * would try to load wp-content/uploads/h5p/mystyles.css
 *
 * The path can be absolute, so
 * 'path' => 'http://mydomain.org/custom-h5p-styling.css',
 * would to try to load the styles from the URL
 *
 * The path can be retrieved using WordPress functions, so for instance
 * 'path' => plugin_dir_url( __FILE__ ) . 'styles/general.css',
 * will try to load styles/general.css inside this plugin's folder
 *
 * @param object &styles List of stylesheets that will be loaded.
 * @param array $libraries The libraries which the styles belong to.
 * @param string $embed_type Possible values are: div, iframe, external, editor.
 */
function h5pmods_alter_styles(&$styles, $libraries, $embed_type) {
  $styles[] = (object) array(
    /*
     * Path can be relative to wp-content/uploads/h5p or absolute or set using
     * WordPress functions
     */
    'path' => plugin_dir_url( __FILE__ ) . 'styles/general.css',
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

/**
 * Allows you to alter a user's score before it's saved, or you can use this
 * action to send the score to another system or plugin.
 *
 * @param object &$data Has the following properties score,max_score,opened,finished,time
 * @param int $result_id Only set if updating result
 * @param int $content_id Identifier of the H5P Content
 * @param int $user_id Identfieri of the User
 */
function h5pmods_alter_user_result(&$data, $result_id, $content_id, $user_id) {

  // Here we can send the results data to another plugin, or we can make sure
  // that the admin always get a full score:
  if (current_user_can('disable_h5p_security')) {
    $data['score'] = $data['max_score'];
  }
}
add_filter('h5p_alter_user_result', 'h5pmods_alter_user_result', 10, 4);

/*
 * ============================================================================
 * Utility functions
 * ============================================================================
 */

/**
 * Find semantics for a particular path/field.
 *
 * Helps to retrieve a specific field in semantics that is identified by its
 * path including the field name.
 *
 * @param string $path Path/field to find, use / to separate levels.
 * @param object &$semantics Reference to semantics to look in.
 * @return object|null Semantics field or null if not found.
 */
function find_semantics_path($path, &$semantics) {
  // Sanitization for user convenience
  $path = (substr($path, 0, 1) === '/') ? substr($path, 1) : $path;
  $path = (substr($path, -1) === '/') ? substr($path, 0, -1) : $path;

  $path_segments = explode('/', $path);

  if (!is_object($semantics)) {
    // Array
    foreach($semantics as $object) {
      if ($object->name === $path_segments[0]) {
        return find_semantics_path($path, $object);
      }
    }
  }
  elseif (sizeof($path_segments) === 1 && $path_segments[0] === $semantics->name) {
    // Found
    return $semantics;
  }
  elseif (isset($semantics->field)) {
    // List
    array_shift($path_segments);
    $path_short = implode($path_segments, '/');
    return find_semantics_path($path_short, $semantics->field);
  }
  elseif (isset($semantics->fields)) {
    // Group
    array_shift($path_segments);
    $path_short = implode($path_segments, '/');
    return find_semantics_path($path_short, $semantics->fields);
  }
  else {
    // Not found
    return null;
  }
}

/**
 * Find semantics for first matching field.
 *
 * Helps to retrieve a specific field in semantics but will only return the
 * first match if there are multiple fields bearing the same name.
 *
 * @param string $field Field to find.
 * @param object &$semantics Reference to semantics to look in.
 * @return object|null Semantics field or null if not found.
 */
function find_semantics_field($field, &$semantics) {
  if (!is_object($semantics)) {
    // Array
    $found = null;
    foreach($semantics as $object) {
      $found = find_semantics_field($field, $object);
      if ($found !== null) {
        break; // Return first matching field
      }
    }
    return $found;
  }
  elseif ($semantics->name === $field) {
    // Found
    return $semantics;
  }
  elseif (isset($semantics->field)) {
    // List
    return find_semantics_field($field, $semantics->field);
  }
  elseif (isset($semantics->fields)) {
    // Group
    return find_semantics_field($field, $semantics->fields);
  }
  else {
    // Not found
    return null;
  }
}
