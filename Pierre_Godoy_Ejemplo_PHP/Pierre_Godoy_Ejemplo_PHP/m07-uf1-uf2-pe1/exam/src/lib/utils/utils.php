<?php
declare(strict_types=1);
namespace Utils;



// ############################################################################
// Copy utils
// ############################################################################

// https://www.phptutorial.net/php-oop/php-clone-object/
// ----------------------------------------------------------------------------
function clone_deeply(mixed $object): mixed {

	return unserialize(serialize($object));
}



// ############################################################################
// Boolean utils
// ############################################################################

// ----------------------------------------------------------------------------
function all_true(array $bool_array): bool {

    $all_count  = count($bool_array);
    $true_count = count(array_filter($bool_array));

    $all_are_true = ($all_count == $true_count);

    return $all_are_true;
}

// ----------------------------------------------------------------------------
function any_true(array $bool_array): bool {

    $true_count        = count(array_filter($bool_array));
    $at_least_one_true = ($true_count >= 1);

    return $at_least_one_true;
}



// ############################################################################
// Array utils
// ############################################################################


// Prepends element and returns array as a new copy.
// array_unshift() modifies the array.
// ----------------------------------------------------------------------------
function array_prepend(mixed $element, array $array): array {

    // Make copy
    $result = clone_deeply($array);
    array_unshift($result, $element);

    return $result;
}

// Prepends element and returns array as a new copy.
// array_unshift() modifies the array.
// ----------------------------------------------------------------------------
function array_append(array $array, mixed $element): array {

    // Make copy
    $result = clone_deep($array);
    array_push($result, $element);

    return $result;
}



// ############################################################################
// String utils
// ############################################################################


// - Problem:
//   - PHP's empty() is a keyword, not a function.
//   - Because of that, we cannot pass it as a parameter to array_map(), array_filter(), etc.
// - Solution:
//   - Make our own 'is_empty_str()' function.
//   - Caveat: Do not use empty(). It returns true for strings starting with a zero!
//   - Use a type-strict comparison with === ''.
// - URLs
//   - https://stackoverflow.com/questions/732979/php-whats-an-alternative-to-empty-where-string-0-is-not-treated-as-empty
//   - https://stackoverflow.com/questions/718986/why-a-function-checking-if-a-string-is-empty-always-returns-true
//   - https://www.php.net/manual/en/types.comparisons.php
// ----------------------------------------------------------------------------
function is_empty_str(string $text): bool {
    $result = ($text === '');
    return $result;
}

// Print Line. Appends an EOL at the end
// ALWAYS use parenthesis when using 'and', 'or'. They have the lowest precedence.
// ----------------------------------------------------------------------------
function convert_to_string(mixed $something = '', $pretty = true): string {

    $not_pretty     = 0;
    $pretty_options = JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES;
    $json_options   = ($pretty) ? $pretty_options : $not_pretty;

    $prefer_json    = ( is_null($something) or
                        is_bool($something) or
                        is_array($something)  );

    if   ($prefer_json)  { $result = json_encode($something, $json_options); }
    else                 { $result = (string) $something;                    }

    return $result;
}

// Print Line. Appends an EOL at the end
// ----------------------------------------------------------------------------
function println(mixed $something = '', $pretty = true): void {

    echo( convert_to_string($something, $pretty) . PHP_EOL );
}



// ############################################################################
// Regex utils
// ############################################################################
// Some helper functions to make regex easier to use.

// ----------------------------------------------------------------------------
function match_regex(string $regex, string $text, int $flags = 0, int $offset = 0): array {

    $match_array = [];
    preg_match($regex, $text, $match_array, $flags, $offset);

    return $match_array;
}

// ----------------------------------------------------------------------------
function is_regex_match(string $regex, string $text, int $flags = 0, int $offset = 0): bool {

    $match_array = match_regex($regex, $text, $flags, $offset);
    $is_match    = (!empty($match_array));

    return $is_match;
}



// ############################################################################
// Json utils
// ############################################################################


// Returns an array with the contents of $json_filename
// ----------------------------------------------------------------------------
function read_json(string $json_filename): array {

    $json_str = file_get_contents($json_filename);
    $result   = json_decode($json_str, true);

    return $result;
}


// ############################################################################
// Path utils
// ############################################################################


// - Files with empty names do not exist.
//   - https://unix.stackexchange.com/questions/83785/how-do-you-create-a-file-with-an-empty-name
// ----------------------------------------------------------------------------
function join_paths(string ...$path_array): string {

    // Check for empty strings
    $empty_path_found = any_true(array_map('Utils\is_empty_str', $path_array));
    if ($empty_path_found) { throw new \Exception('Error: join_paths() on an empty path.'); }

    // Join paths
    $joint_path = join(DIRECTORY_SEPARATOR, $path_array);

    // Remove any repeated slashes
    $repeated_slash_regex = '~'.DIRECTORY_SEPARATOR.'{2,}'.'~';
    $single_slash_str = DIRECTORY_SEPARATOR;
    $clean_path = preg_replace( $repeated_slash_regex,
                                $single_slash_str,
                                $joint_path );

    return $clean_path;
}

// Rewrites all links in $old_path_array to have $new_parent_path as their dirname.
// Useful to prepare links for to the public/ deployment.
// ----------------------------------------------------------------------------
function rewrite_paths(array $old_path_array, string $new_parent_path): array {

    $make_new_link   = fn ($file) => join_paths($new_parent_path, basename($file));

    $web_image_array = array_map($make_new_link, $old_path_array);

    return $web_image_array;
}



// ############################################################################
// File utils
// ############################################################################


// Checks for existence to avoid warnings
// ----------------------------------------------------------------------------
function ensure_dir(string $dir): void {

    if (!is_dir($dir)) { mkdir($dir, recursive: true); }
}

// ----------------------------------------------------------------------------
function copy_files(array $source_file_array, string $target_dir): void {

    foreach($source_file_array as $source_file) {

        $target_file = join_paths($target_dir, basename($source_file));

        copy($source_file, $target_file); 
    }
}

// ----------------------------------------------------------------------------
