<?php
declare(strict_types=1);
namespace Table;

require_once(__DIR__ . '/../../app/config.php');
use function Config\get_lib_dir;

require_once(get_lib_dir() . '/utils/utils.php');
use function Utils\array_prepend;
use function Utils\is_empty_str;



// ############################################################################
// Table __toString() helper functions
// ############################################################################

// Transposes rows <-> columns.
// Works only on arrays of arrays (matrices).
// https://stackoverflow.com/questions/797251/transposing-multidimensional-arrays-in-php
// ----------------------------------------------------------------------------
function transpose(array $header, array $matrix): array {

    $num_columns = count($header);
    $num_rows    = count($matrix);

    // 0. Zero rows
    if ($num_rows == 0) {
        $result = array_fill(0, $num_columns, []);

    // 1. One row
    } elseif ($num_rows == 1) {
        $result = array_map(fn ($x) => [$x], ...$matrix);

    // 2. Two or more rows
    } else {
        $result = array_map(null, ...$matrix); 
    }

    return $result;

}

// Helper function: Get max length of all columns
// ----------------------------------------------------------------------------
function get_column_widths(array $header, array $body): array {

    // Combine header and body
    $header_and_body = array_prepend($header, $body);

    // Arrange data by columns, including column names
    $columns       = transpose($header, $header_and_body);
    $named_columns = array_combine($header, $columns);

    // Functions to calculate column widths
    $get_width        = fn ($field)  => strlen( (string) $field);
    $get_column_width = fn ($column) => max(array_map($get_width, $column));

    // Calculate widths
    $column_widths = array_map($get_column_width, $named_columns);

    return $column_widths;
}

// String conversion
// ----------------------------------------------------------------------------
function convert_table_to_string(array $header, array $body, string $separator = ' | '): string {

    // Get all column widths
    $column_widths = get_column_widths($header, $body);

    // Functions to convert rows to string lines
    $convert_field_to_string = fn ($field, $width) => str_pad( (string) $field, $width, ' ', STR_PAD_RIGHT);
    $convert_row_to_line     = fn ($row)           => implode( $separator, array_map($convert_field_to_string, $row, $column_widths) );

    // Convert header and body to string
    $header_and_body = array_prepend($header, $body);
    $result = implode( PHP_EOL, array_map($convert_row_to_line, $header_and_body) );

    return $result;
}



// ############################################################################
// Table CSV helper functions
// ############################################################################

// ----------------------------------------------------------------------------
function split_csv_str(string $csv_str, string $separator): array {

    // 1. Split into lines
    $line_array = explode(PHP_EOL, $csv_str);

    // 2. Remove empty lines anywhere
    $has_data        = fn ($line) => !is_empty_str($line);
    $data_line_array = array_filter($line_array, $has_data);

    // 3. Explode each line + trim each field
    $split_line  = fn ($line) => array_map('trim', explode($separator, $line));
    $data_matrix = array_map($split_line, $data_line_array);

    return $data_matrix;
}

// ----------------------------------------------------------------------------
function get_header(array $data_matrix): array {

    $empty = !count($data_matrix);

    if ($empty) { throw new \Exception('Error: No header!'); }
    else        { $header = $data_matrix[0];                 }

    return $header;
}

// Important: Rows are associative arrays. Keys are the column names.
// ----------------------------------------------------------------------------
function get_body(array $data_matrix): array {

    $header = get_header($data_matrix);
    $body   = $data_matrix; array_shift($body);

    // $decorate_row   = fn ($row) => array_combine($header, $row);
    // $decorated_body = array_map($decorate_row, $body);

    return $body;
}

// Rows become associative arrays. Keys are the column names in the header.
// ----------------------------------------------------------------------------
function decorate_body(array $header, array $body): array {

    $decorate_row   = fn ($row) => array_combine($header, $row);
    $decorated_body = array_map($decorate_row, $body);

    return $decorated_body;
}

// ----------------------------------------------------------------------------
function read_csv(string $csv_filename, string $separator): Table {

    $csv_str         = file_get_contents($csv_filename);
    $trimmed_csv_str = trim($csv_str);
    $data_matrix     = split_csv_str($trimmed_csv_str, $separator);

    $header = get_header($data_matrix);
    $body   = get_body($data_matrix);
    $result = new Table($header, $body);

    return $result;
}

// ----------------------------------------------------------------------------
function write_csv(Table $table, string $csv_filename, string $separator = ' | '): void {

    // 1. Check if table contains the separator string
    $contents_str  = convert_table_to_string($table->header, $table->body, '');
    $has_separator = str_contains($contents_str, $separator);

    // 2. If separator found: Abort
    $error_msg = "Write error: Table contains '$separator' already. Cannot use it as a separator in CSV file.";
    if ($has_separator) { throw new \Exception($error_msg); }

    // 3. Else: Return string using separator
    $table_str = convert_table_to_string($table->header, $table->body, $separator);
    file_put_contents($csv_filename, $table_str);
}



// ############################################################################
// Rows and Columns helper functions
// ############################################################################

// When using indexed arrays, remember to call array_values() after array_filter().
// https://stackoverflow.com/questions/3401850/after-array-filter-how-can-i-reset-the-keys-to-go-in-numerical-order-starting
// ----------------------------------------------------------------------------
function filter_rows(Table $table, callable $filter): Table {

    $header         = $table->header;
    $filtered_body  = array_values(array_filter($table->body, $filter));

    $filtered_table = new Table($header, $filtered_body);

    return $filtered_table;
}

// ----------------------------------------------------------------------------
function get_columns(Table $table): array {

    $columns        = transpose($table->header, $table->body);
    $named_columns  = array_combine($table->header, $columns);

    return $named_columns;
}





// ############################################################################
// Table Class
// ############################################################################
// Rows are associative arrays. Keys are the column names in the header.


class Table {

    public array $header;
    public array $body;

    // ------------------------------------------------------------------------
    public function __construct(array $header = [] ,
                                array $body   = []  ) {

        $this->header = $header;
        $this->body   = decorate_body($header, $body);
    }

    // ------------------------------------------------------------------------
    public function __toString(): string {

        $string = convert_table_to_string($this->header, $this->body);
        return $string;
    }

    // ------------------------------------------------------------------------
    public static function readCSV( string $csv_filename,
                                    string $separator = '|'): self {

        $table = read_csv($csv_filename, $separator);
        return $table;
    }

    // ------------------------------------------------------------------------
    public function writeCSV( string $csv_filename,
                              string $separator = ' | '): void {

        write_csv($this, $csv_filename, $separator);
    }

    // ------------------------------------------------------------------------
    public function appendRow( array $row ): void {

        $decorated_row = array_combine($this->header, $row);
        array_push($this->body, $decorated_row);
    }

    // ------------------------------------------------------------------------
    public function prependRow( array $row ): void {

        $decorated_row = array_combine($this->header, $row);
        array_unshift($this->body, $decorated_row);
    }

    // $filter function recieves a single parameter: a table row
    // Returns a new Table object. Does not modify the original table.
    // ------------------------------------------------------------------------
    public function filterRows(callable $filter): self {

        $result = filter_rows($this, $filter);
        return $result;
    }

    // ------------------------------------------------------------------------
    public function getColumns(): array {

        $result = get_columns($this);
        return $result;
    }

}
    // ------------------------------------------------------------------------
