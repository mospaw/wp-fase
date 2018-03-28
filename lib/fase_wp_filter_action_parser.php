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
 *	Expects array of arrays in this format:
 *		'dir' => $dir,
 *		'file' => $file,
 *		'fullpath' => $dir . '/' . $file,
 *
 *
 */
class fase_wp_filter_action_parser
{
    public $processed_files; // Holder for files that were found, separated by file.
    public $processed_finds; // Holder for actouns/filters that were found, separated by action/filter type.

    private $file_list;
    private $parsed_files;
    private $tokens;
    private $strings_to_parse = array(
        'add_action',
        'add_filter',
        'apply_filters',
        'do_action',
        'do_action_ref_array',
    );
    private $options;

    const TOKEN_TYPE = 0;
    const TOKEN_TEXT = 1;
    const TOKEN_LINE_NUMBER = 2;

    // Do a little setup
    public function __construct($file_list = null, $options = array())
    {
        $this->file_list = $file_list;

        $defaults = array(
            'verbose' => true,
            'format' => 'html'
        );
        $this->options = array_merge($defaults, $options);
    }

    /**
     * Iterate files, parse them, trigger reports
     */
    public function parse_file_list()
    {
        if (is_array($this->file_list)) {
            foreach ($this->file_list as $file) {
                $this->parse_file($file);
            }
        }
        $this->assemble_reports($this->options['format']);
    }

    /**
     * Pull out gathered information and output reports.
     */
    public function assemble_reports($type = 'html')
    {
        $name = 'output_' . $type ;
        $report = new $name($this);

        echo $report->get_output();
    }

    /**
     * Parse an indvidual file
     */
    public function parse_file($file_name)
    {
        // Check if file exists
        if (!file_exists($file_name['fullpath'])) {
            show("FILE NOT FOUND: " . $file_name['fullpath']);
            // TODO: error processing here
            return;
        }

        // Read contents of file
        show("PROCESSING: " . $file_name['fullpath']);
    
        $file_contents = file_get_contents($file_name['fullpath']);
        $file_lines = preg_split('/$\R?^/m', $file_contents);

        // Split file contents into PHP tokens
        $this->tokens = token_get_all($file_contents);

        // Iterate processed tokens
        foreach ($this->tokens as $key => $token) {
            if (is_array($token) && ($token[self::TOKEN_TYPE] == T_STRING)) {
                if (in_array($token[self::TOKEN_TEXT], $this->strings_to_parse)) {
                    if ($this->is_function_definition($key) == false) {
                        $find = $this->process_raw_find($key);
                        $processor = 'processor_'.$token[self::TOKEN_TEXT];
                        if (method_exists($this, $processor)) {
                            $this->$processor($token, $find, $file_name);
                        } else {
                            echo "ERROR: PROCESSOR $processor DOES NOT EXIST.\n";
                        }
                    }
                }
            }
        }
    }

    /**
     * If a match is found, process that match
     */
    public function process_raw_find($key)
    {
        // What did we find?
        $find_type = $this->tokens[$key][self::TOKEN_TEXT];

        // Search back for docblock
        $docblock = $this->process_raw_docblock($key);

        // Gather parameters
        $parameters = array();

        // find next "("
        while ($key < count($this->tokens)) {
            if ($this->tokens[$key] == '(') {
                $key++;
                break;
            }
            $key++;
        }
        $paren_count = 1;
        // The closing parenthesis is the delimiter, but they might be nested...
        while ($key < count($this->tokens)) {
            if ($this->tokens[$key] == '(') {
                $paren_count++;
            }
            if ($this->tokens[$key] == ')') {
                $paren_count--;
            }

            // Add information until ")" count is zero
            if ($paren_count > 0) {
                $processed = $this->process_raw_parameters($key);

                // Add the parameter string
                $parameters[] = $processed[self::TOKEN_TYPE];
                // Add the offset
                $key += $processed[self::TOKEN_TEXT];
            }
            // End of logical line or function call...
            if ($this->tokens[$key] == ';' || $this->tokens[$key] == ')') {
                break;
            }
            $key++;
        }
        return array('parameters' => $parameters, 'docblock' => $docblock);
    }

    /**
     * Find parameters by key, noting that parentheses can be nested
     */
    public function process_raw_parameters($key)
    {
        $paren_count = 0;
        $parameter = '';
        $offset = 0;
        while ($key < count($this->tokens) && ($paren_count >= 0)) {
            if ($this->tokens[$key] == ';'|| ($this->tokens[$key] == ')' && $paren_count == 0)) {
                break;
            }
            if ($this->tokens[$key] == '(') {
                $paren_count++;
            }
            if ($this->tokens[$key] == ')') {
                $paren_count--;
            }
            // Break on commas unless they're inside a paren
            if (($this->tokens[$key] != ',' || $paren_count > 0)) {
                // Add anything that's a non-whitespace token
                if (is_array($this->tokens[$key]) && ($this->tokens[$key][self::TOKEN_TYPE] !== T_WHITESPACE)) {
                    $parameter .= $this->tokens[$key][self::TOKEN_TEXT];
                } elseif (is_string($this->tokens[$key]) && (($this->tokens[$key] != ')' || $paren_count >= 0))) {
                    $parameter .= $this->tokens[$key];
                }
            } else {
                break;
            }
            $key++;
            $offset++;
        }
        return array($parameter, $offset);
    }


    /**
     * Find docblock info based on the key, return raw contents
     */
    public function process_raw_docblock($key)
    {
        $docblock = null;
        while ($key > 0) {
            // Detect closing curle braces or ; which indicate code that's not related
            if ($this->tokens[$key] == ';' || $this->tokens[$key] == '}') {
                break;
            }
            if (is_array($this->tokens[$key]) && $this->tokens[$key][self::TOKEN_TYPE] == T_DOC_COMMENT) {
                return $this->tokens[$key][self::TOKEN_TEXT];
            }
            $key--;
        }
        return $docblock;
    }


    /**
     * See if the given key is a function definition
     */
    public function is_function_definition($key)
    {
        while ($key > 0) {
            // Ignore whitespace
            if (is_array($this->tokens[$key]) && ($this->tokens[$key][self::TOKEN_TYPE] == T_WHITESPACE)) {
                $key--;
                continue;
            }
            // Is it T_FUNCTION? Yes, then we've got a function definition
            if (is_array($this->tokens[$key]) && ($this->tokens[$key][self::TOKEN_TYPE] == T_FUNCTION)) {
                return true;
            }
            // Anything else it's not a function definition
            if (! is_array($this->tokens[$key])) {
                return false;
            }
            $key--;
        }
        return false;
    }


    /**
     * Convert quoted strings with no "$" in them to non-quoted strings
     * If there is a "$" present, convert all single quotes to double quotes for consistency
     */
    public function normalize_tag_names($tag)
    {
        if (strpos($tag, '$') === false) {
            $tag = str_replace(array('"', "'"), '', $tag);
        } else {
            $tag = str_replace(array("'"), '"', $tag);
        }
        return $tag;
    }

    /** HERE BE THE PROCESSORS **/

    // http://codex.wordpress.org/Function_Reference/add_filter
    public function processor_add_filter($token, $find, $file_name)
    {
        $tag = $this->normalize_tag_names(array_shift($find['parameters']));
        $function_to_add = array_shift($find['parameters']);
        
        if (count($find['parameters']) > 0) {
            $priority = array_shift($find['parameters']);
        } else {
            $priority = '10 (default)';
        }

        if (count($find['parameters']) > 0) {
            $arguments = array_shift($find['parameters']);
        } else {
            $arguments = '1 (default)';
        }

        $this->processed_files[$file_name['friendly_name']][$token[self::TOKEN_LINE_NUMBER]] = array(
            'token' => $token,
            'type' => 'add_filter',
            'hook' => $tag,
            'function_to_add' => $function_to_add,
            'priority' => $priority,
            'arguments' => $arguments,
        );

        $this->processed_finds['add_filter'][$tag][] = array(
            'token' => $token,
            'file' => $file_name,
            'hook' => $tag,
            'function_to_add' => $function_to_add,
            'priority' => $priority,
            'arguments' => $arguments,
        );
        return;
    }


    // http://codex.wordpress.org/Function_Reference/apply_filters
    public function processor_apply_filters($token, $find, $file_name)
    {
        $tag = $this->normalize_tag_names(array_shift($find['parameters']));
        $value = array_shift($find['parameters']);
        $vars = $find['parameters'];

        $this->processed_files[$file_name['friendly_name']][$token[self::TOKEN_LINE_NUMBER]] = array(
            'token' => $token,
            'type' => 'apply_filters',
            'hook' => $tag,
            'value_modified' => $value,
            'optional_vars' => $vars,
            'data' => $find,
        );

        $this->processed_finds['apply_filters'][$tag][] = array(
            'token' => $token,
            'file' => $file_name,
            'hook' => $tag,
            'value_modified' => $value,
            'optional_vars' => $vars,
            'data' => $find,
        );
        return;
    }

    // http://codex.wordpress.org/Function_Reference/add_action
    public function processor_add_action($token, $find, $file_name)
    {
        $tag = $this->normalize_tag_names(array_shift($find['parameters']));
        $function_to_add = array_shift($find['parameters']);
        
        if (count($find['parameters']) > 0) {
            $priority = array_shift($find['parameters']);
        } else {
            $priority = '10 (default)';
        }

        if (count($find['parameters']) > 0) {
            $arguments = array_shift($find['parameters']);
        } else {
            $arguments = '1 (default)';
        }

        $this->processed_files[$file_name['friendly_name']][$token[self::TOKEN_LINE_NUMBER]] = array(
            'token' => $token,
            'type' => 'add_action',
            'hook' => $tag,
            'function_to_add' => $function_to_add,
            'priority' => $priority,
            'arguments' => $arguments,
        );

        $this->processed_finds['add_action'][$tag][] = array(
            'token' => $token,
            'file' => $file_name,
            'hook' => $tag,
            'function_to_add' => $function_to_add,
            'priority' => $priority,
            'arguments' => $arguments,
        );
        return;
    }

    // http://codex.wordpress.org/Function_Reference/do_action
    public function processor_do_action($token, $find, $file_name)
    {
        $tag = $this->normalize_tag_names(array_shift($find['parameters']));
        //$value = array_shift($find['parameters']);
        $vars = $find['parameters'];

        $this->processed_files[$file_name['friendly_name']][$token[self::TOKEN_LINE_NUMBER]] = array(
            'token' => $token,
            'type' => 'do_action',
            'hook' => $tag,
            'optional_vars' => $vars,
            'data' => $find,
        );

        $this->processed_finds['do_action'][$tag][] = array(
            'token' => $token,
            'file' => $file_name,
            'hook' => $tag,
            'optional_vars' => $vars,
            'data' => $find,
        );
        return;
    }

    // http://codex.wordpress.org/Function_Reference/do_action_ref_array
    public function processor_do_action_ref_array($token, $find, $file_name)
    {
        $tag = $this->normalize_tag_names(array_shift($find['parameters']));
        $arguments = $find['parameters'];

        $this->processed_files[$file_name['friendly_name']][$token[self::TOKEN_LINE_NUMBER]] = array(
            'token' => $token,
            'type' => 'do_action_ref_array',
            'hook' => $tag,
            'optional_vars' => $arguments,
            'data' => $find,
        );

        $this->processed_finds['do_action_ref_array'][$tag][] = array(
            'token' => $token,
            'file' => $file_name,
            'hook' => $tag,
            'optional_vars' => $arguments,
            'data' => $find,
        );
        return;
    }
}
