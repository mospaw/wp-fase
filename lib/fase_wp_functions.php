<?php
/**
 * @package WP-FASE
 *
 * This file is part of the WP-FASE, The WordPress Filter and Action Syntax Extractor
 *
 * https://github.com/mospaw/WP-FASE
 *
 * Copyright 2013-2018 Chris Mospaw
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

/**
 * Helper functions
 * @param string $message
 */
function show($message)
{
    global $verbose;
    if ($verbose == true) {
        if (is_array($message) || is_object($message)) {
            print_r($message);
        } else {
            echo $message . "\n";
        }
    }
}
