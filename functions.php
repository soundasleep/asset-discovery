<?php

function get_matching_paths($dir, $path) {
  // a simple way to match paths: just find all files that match, and
  // then filter
  $result = get_all_files($dir);

  // filter out
  $filtered = array();
  foreach ($result as $value) {
    if (fnmatch($dir . "/" . $path, $value)) {
      $filtered[] = $value;
    }
  }

  // sort and renumber
  sort($filtered);

  return $filtered;
}

function get_all_files($root, $max_depth = 3) {
  $result = array();
  if ($handle = opendir($root)) {
    while (false !== ($entry = readdir($handle))) {
      if ($entry != "." && $entry != "..") {
          if (is_dir($root . "/" . $entry)) {
            if ($max_depth > 1) {
              $result = array_merge($result, get_all_files($root . "/" . $entry, $max_depth - 1));
            }
          } else {
            $result[] = $root . "/" . $entry;
          }
        }
    }
    closedir($handle);
  }
  return $result;
}
