<?php

require_once(__DIR__ . "/../functions.php");

class AssetDiscoveryTest extends PHPUnit_Framework_TestCase {

  function testGetMatchingPathsSimple() {
    $paths = get_matching_paths("resources", "1/1.coffee");
    $this->assertEquals(
      array('resources/1/1.coffee'),
      $paths,
      "1/1.coffee did not match correctly");
  }

  function testGetMatchingPathsMultiple() {
    $paths = get_matching_paths("resources", "1/*");
    $this->assertEquals(
      array('resources/1/1.coffee', 'resources/1/2.coffee', 'resources/1/3.js'),
      $paths,
      "1/* did not match correctly");
  }

  function testGetMatchingPathsParents() {
    $paths = get_matching_paths("resources", "*/1*");
    $this->assertEquals(
      array('resources/1/1.coffee', 'resources/2/1.js'),
      $paths,
      "*/1* did not match correctly");
  }

  function testGetMatchingPathsPattern() {
    $paths = get_matching_paths("resources", "1/*.coffee");
    $this->assertEquals(
      array('resources/1/1.coffee', 'resources/1/2.coffee'),
      $paths,
      "1/*.coffee did not match correctly");
  }

  function testGetMatchingPathsPatternParent() {
    $paths = get_matching_paths("resources", "*/*.coffee");
    $this->assertEquals(
      array('resources/1/1.coffee', 'resources/1/2.coffee', 'resources/2/2.coffee'),
      $paths,
      "*/*.coffee did not match correctly");
  }

}
