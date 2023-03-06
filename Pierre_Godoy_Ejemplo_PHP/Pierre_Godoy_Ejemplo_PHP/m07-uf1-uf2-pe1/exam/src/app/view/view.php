<?php
declare(strict_types=1);
namespace View;

use \DateTimeImmutable as DTI;

require_once(__DIR__ . '/../config.php');
use function Config\get_lib_dir;

require_once(get_lib_dir() . '/table/table.php');
use Table\Table;

require_once(get_lib_dir() . '/utils/utils.php');
use function Utils\join_paths;
use function Utils\clone_deeply;



// ############################################################################
// Table functions
// ############################################################################

// ----------------------------------------------------------------------------
function prettify_blog(Table $blog): Table {

    // 1. Helper functions
    $reformat_timestamp = fn ($timestamp)   => DTI::createFromFormat(DTI::RFC3339, $timestamp)->format('Y-m-d H:i:s');
    $reformat_row       = fn ($row)         => ['Timestamp' => $reformat_timestamp($row['Timestamp']),
                                                'Message'   => $row['Message'] ];

    // 2. Copy blog and prettify
    $pretty_blog       = clone_deeply($blog);
    $pretty_blog->body = array_map($reformat_row, $blog->body);

    return $pretty_blog;
}



// ############################################################################
// Template functions
// ############################################################################

// Example: '/body/index' => '/app/src/view/body/index.template.php'
// ----------------------------------------------------------------------------
function get_template_path(string $template_id): string {

     $template_suffix        = '.template.php';
     $template_relative_path = $template_id . $template_suffix;

     $view_dir           = __DIR__;
     $template_full_path = join_paths($view_dir, $template_relative_path);

     return $template_full_path;
}

// Params start with an underscore and all caps to avoid collisions in $_TEMPLATE_VARS.
// --------------------------------------------------------------------
function render_template(string $_TEMPLATE_FILENAME, array $_TEMPLATE_VARS): string {

     extract($_TEMPLATE_VARS);
 
     ob_start();
     require($_TEMPLATE_FILENAME);
     $_RESULT = ob_get_contents();
     ob_end_clean();
 
     return $_RESULT;
}



// ############################################################################
// HTML Conversion functions
// ############################################################################
// - To avoid XSS attacks, always use htmlspecialchars() when outputting user input
// - https://stackoverflow.com/questions/46483/htmlentities-vs-htmlspecialchars
// - https://stackoverflow.com/questions/19584189/when-used-correctly-is-htmlspecialchars-sufficient-for-protection-against-all-x


// 1. Convert table header to string
// ----------------------------------------------------------------------------
function get_html_header(array $header): string {

     $add_th_tags = fn ($column_name) => '<th>'.htmlspecialchars($column_name).'</th>';

     $th_tags_array = array_map($add_th_tags, $header);
     $th_tags_str   = implode(' ', $th_tags_array);
     $html_header   = "<tr> $th_tags_str </tr>" . PHP_EOL;

     return $html_header;
}

// 2. Convert table row to string
// ----------------------------------------------------------------------------
function get_html_row(array $row): string {

     $add_td_tags = fn ($row_field)   => '<td>'.htmlspecialchars($row_field).'</td>';

     $td_tags_array = array_map($add_td_tags, $row);
     $td_tags_str   = implode(' ', $td_tags_array);
     $html_row   = "<tr> $td_tags_str </tr>" . PHP_EOL;

     return $html_row;
}

// 3. Convert table body to string
// ----------------------------------------------------------------------------
function get_html_body(array $body): string {

     $html_row_array = array_map('View\get_html_row', $body);
     $html_body      = implode('', $html_row_array);

     return $html_body;
}

// ----------------------------------------------------------------------------
