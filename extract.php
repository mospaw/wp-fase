<?php
/**
 * @package WP-FASE
 *
 * This file is part of the WP-FASE, The WordPress Filter and Action Syntax Extractor
 *
 * https://github.com/crowdfavorite/WP-FASE
 *
 * Copyright 2013-2014 Crowd Favorite, Ltd. and Chris Mospaw
 *
 * Licensed under the Apache License, Version 2.0 (the "License"); you may not use
 * this file except in compliance with the License. You may obtain a copy of the
 * license at:
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software distributed
 * under the License is distributed on an "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS
 * OF ANY KIND, either express or implied. See the License for the specific language
 * governing permissions and limitations under the License.
 *
 * See LICENSE.txt
 */

 /*
 * Bootstrap file
 *
 * - Load utility functions
 * - Set autolaoder
 * - Instantiate extractor and run
 */

require_once('lib/fase_wp_functions.php');

function wp_fase_autoload($class_name)
{
    if (strpos($class_name, 'output_') !== false) {
        $class_name = str_replace('output_', 'output/', $class_name);
    }
    include 'lib/'.$class_name . '.php';
}
spl_autoload_register('wp_fase_autoload');

$extractor = new fase_wp_extractor();
$extractor->extract();
