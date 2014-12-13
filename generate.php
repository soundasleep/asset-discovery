<?php

/**
 * Search through sets of possible asset include directories for any
 * 'assets.json' files which will define Javascripts, CSS files,
 * images and such.
 */

if (count($argv) < 2) {
  throw new Exception("Needs file root parameter");
}

$root = $argv[1];
if (!file_exists($root . "/asset-discovery.json")) {
  throw new Exception("No asset-discovery.json found in '$root'");
}

$json = json_decode(file_get_contents($root . "/asset-discovery.json"), true /* assoc */);

// add default parameters
$json += array(
  'src' => 'vendor/*/*',
  'js' => 'generated/js/generated.js',
  'css' => 'generated/css/generated.css',
  'coffee' => 'generated/coffee/generated.coffee',
  'scss' => 'generated/scss/generated.scss',
  'images' => 'generated/images/',
  'imagetypes' => array('png', 'gif', 'jpg', 'jpeg'),
  'depth' => 3
);

if (!is_array($json['src'])) {
  $json['src'] = array($json['src']);
}

if (substr($json['images'], -1) !== "/") {
  throw new Exception("'images' parameter needs to be a directory and end in /: '" . $json['images'] . "'");
}

function get_directories_to_search($dirs, $pattern) {
  // convert pattern into a regular expression
  $pattern = str_replace("*", "[^/]+", $pattern);
  $pattern = "#" . $pattern . "$#";

  // find all matching directories
  $result = array();
  foreach ($dirs as $dir) {
    if (preg_match($pattern, $dir)) {
      $result[] = $dir;
    }
  }

  return $result;
}

function get_all_directories($root, $max_depth = 3) {
  $result = array();
  if ($handle = opendir($root)) {
    while (false !== ($entry = readdir($handle))) {
      if ($entry != "." && $entry != ".." && is_dir($root . "/" . $entry)) {
          $result[] = $root . "/" . $entry;
          if ($max_depth > 1) {
            $result = array_merge($result, get_all_directories($root . "/" . $entry, $max_depth - 1));
          }
        }
    }
    closedir($handle);
  }
  return $result;
}

// make target directories as necessary
foreach (array($json['js'], $json['css'], $json['coffee'], $json['scss'], $json['images']) as $path) {
  // remove any filenames
  if (substr($path, -1) !== "/") {
    $path = substr($path, 0, strrpos($path, "/"));
  }
  if (!file_exists($path)) {
    echo "Making directory '$path' recursively...\n";
    mkdir($path, 0777, true);
  }
}

// now load all of the components
$all_dirs = get_all_directories($root, $json['depth']);
echo "Found " . count($all_dirs) . " potential subdirectories\n";
$selected_dirs = array();
foreach ($json['src'] as $pattern) {
  $selected_dirs = array_merge($selected_dirs, get_directories_to_search($all_dirs, $pattern));
}
echo "Filtered to " . count($selected_dirs) . " matching paths\n";

$javascripts = array();
$stylesheets = array();
$coffeescripts = array();
$sasses = array();
$images = array();

if ($selected_dirs) {
  $filename = "assets.json";
  echo "Processing asset components...\n";

  $count = 0;
  foreach ($selected_dirs as $dir) {
    if (file_exists($dir . "/" . $filename)) {
      $assets = json_decode(file_get_contents($dir . "/" . $filename), true);
      if (!$assets) {
        throw new Exception("Could not load JSON from '$dir/$filename'");
      }

      // merge default
      $assets += array(
        "css" => array(),
        "js" => array(),
        "scss" => array(),
        "coffee" => array(),
        "images" => array(),
      );

      // cycle through
      foreach ($assets['css'] as $path) {
        $stylesheets[] = $dir . "/" . $path;
      }
      foreach ($assets['js'] as $path) {
        $javascripts[] = $dir . "/" . $path;
      }
      foreach ($assets['scss'] as $path) {
        $sasses[] = $dir . "/" . $path;
      }
      foreach ($assets['coffee'] as $path) {
        $coffeescripts[] = $dir . "/" . $path;
      }
      foreach ($assets['images'] as $path) {
        $images[] = $dir . "/" . $path;
      }
      $count++;
    }
  }

  echo "Found $count asset-producing components\n";

}

echo "Processing " . count($stylesheets) . " stylesheets...\n";
$fp = fopen($json['css'], "w");
if (!$fp) {
  throw new Exception("Could not open destination file '" . $json['css'] . "' for writing");
}
fwrite($fp, "/**
 * Assets discovered by soundasleep/asset-discovery.
 * @generated file DO NOT MODIFY
 */
");
foreach ($stylesheets as $include) {
  fwrite($fp, "/* '$include' */\n");
  fwrite($fp, file_get_contents($include));
  fwrite($fp, "\n");
}
fclose($fp);

echo "Processing " . count($sasses) . " SCSS stylesheets...\n";
$fp = fopen($json['scss'], "w");
if (!$fp) {
  throw new Exception("Could not open destination file '" . $json['scss'] . "' for writing");
}
fwrite($fp, "/**
 * Assets discovered by soundasleep/asset-discovery.
 * @generated file DO NOT MODIFY
 */
");
foreach ($sasses as $include) {
  fwrite($fp, "/* '$include' */\n");
  fwrite($fp, file_get_contents($include));
  fwrite($fp, "\n");
}
fclose($fp);

echo "Processing " . count($javascripts) . " javascripts...\n";
$fp = fopen($json['js'], "w");
if (!$fp) {
  throw new Exception("Could not open destination file '" . $json['js'] . "' for writing");
}
fwrite($fp, "/**
 * Assets discovered by soundasleep/asset-discovery.
 * @generated file DO NOT MODIFY
 */
");
foreach ($javascripts as $include) {
  fwrite($fp, "/* '$include' */\n");
  fwrite($fp, file_get_contents($include));
  fwrite($fp, "\n");
}
fclose($fp);

echo "Processing " . count($coffeescripts) . " coffeescripts...\n";
$fp = fopen($json['coffee'], "w");
if (!$fp) {
  throw new Exception("Could not open destination file '" . $json['coffee'] . "' for writing");
}
fwrite($fp, "###
 # Assets discovered by soundasleep/asset-discovery.
 # @generated file DO NOT MODIFY
###
");
foreach ($coffeescripts as $include) {
  fwrite($fp, "# '$include' \n");
  fwrite($fp, file_get_contents($include));
  fwrite($fp, "\n");
}
fclose($fp);

echo "Processing " . count($images) . " image paths...\n";
// recursive copy
foreach ($images as $include) {
  $paths = get_all_images($include, "", $json['imagetypes']);
  echo "Copying " . count($paths) . " images from $include...\n";
  foreach ($paths as $path) {
    copy($path, $json['images'] . str_replace($include, "", $path));
  }
}

function get_all_images($root, $relative, $filetypes) {
  $result = array();
  if ($handle = opendir($root . $relative)) {
    while (false !== ($entry = readdir($handle))) {
      if ($entry != "." && $entry != "..") {
        if (is_dir($root . $relative . $entry)) {
          // is directory?
          $result = array_merge($result, get_all_images($root, $relative . $entry . "/", $filetypes));
        } elseif (strpos($entry, ".") !== false) {
          // is file?
          $extension = strtolower(substr($entry, strrpos($entry, ".") + 1));
          if (in_array($extension, $filetypes)) {
            $result[] = $root . $entry;
          }
        }
      }
    }
    closedir($handle);
  }
  return $result;
}
