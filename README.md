#WP-FASE

##(WordPress Filter and Action Syntax Extractor)

This package is designed to traverse and arbitrary folder and find all instances of WordPress `add_action`, `add_filter`, `apply_filters`, `do_action`, and `do_action_ref_array` with HTML, JSON or plain text output. Other output format filters can be programmed and are easily added.

Output is directed to the console and should be redirected for capturing. Future versions will have more output options.

This project is very much "alpha" and still in very early development.

**Current Version:** 0.17 (2014-01-23)

## Invoking

> php extract.php [options]

### Options

Some options, such as the directory, are required. The following options are available:

	-d 	path to the directory to traverse. REQUIRED

	-r  text to remove from file paths. Defaults to value given in '-d' if not specified.

	-f 	File Types, comma separated (no spaces). Defaults to 'php,inc'.

	-t 	Output Type. HTML, JSON or text. Defaults to HTML. Invalid values go to default.

	-v 	Verbose mode, no parameters - turns on verbose reporting

## Immediate TODO

- Override CSS styling for HTML 

## Planned Features

- Split output into multiple files
	- broken down by actions, filters, files
- Specify a folder for multiple-file output on the command line
- Specify a single file for output on the command line
- HTML output filter with JavaScript / Ajax navigation

# License

Copyright 2013-2018 Crowd Favorite and Chris Mospaw

Licensed under the Apache License, Version 2.0 (the "License"); you may not use this file except in compliance with the License. You may obtain a copy of the license at:

> http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software distributed under the License is distributed on an "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. See the License for the specific language governing permissions and limitations under the License.

See LICENSE.txt
