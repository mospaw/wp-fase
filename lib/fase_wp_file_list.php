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
 * Utility class to traverse a folder and all subfolders to get a list of files
 * matching the provided extentions
 */
class fase_wp_file_list
{
    private $dir;
    private $file_extensions;
    private $file_list;
    private $remove_text; // text to be removed from full paths for "friendly_name"

    public function __construct($dir = null, $file_extensions = null, $verbose = true, $remove_text = '')
    {
        $this->dir = $dir;
        $this->file_extensions = $file_extensions;
        $this->verbose = $verbose;
        $this->remove_text = $remove_text;
    }

    public function get_file_list($dir = null, $count = 0)
    {
        if ($dir === null) {
            $dir = $this->dir;
        }
        $files = scandir($dir);
        foreach ($files as $file) {
            if ($file != '.' && $file != '..') {
                if (in_array(pathinfo($dir.'/'.$file, PATHINFO_EXTENSION), $this->file_extensions)) {
                    //show(".");
                    $fullpath = $dir . '/' . $file;
                    $replace_count = 1;
                    $friendly_name = str_replace($this->remove_text, '', $fullpath, $replace_count);
                    if (substr($friendly_name, 0, 1) == '/') {
                        $friendly_name = substr($friendly_name, 1);
                    }
                    $this->file_list[] = array(
                        'dir' => $dir,
                        'file' => $file,
                        'fullpath' => $fullpath,
                        'friendly_name' => $friendly_name,
                    );
                }
                if (is_dir($dir.'/'.$file)) {
                    $this->get_file_list($dir.'/'.$file, $count + 1);
                }
            }
        }
    }

    public function get_files()
    {
        if (empty($this->file_list)) {
            $this->get_file_list();
        }
        return $this->file_list;
    }
}
