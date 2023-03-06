<?php
declare(strict_types=1);
namespace Model;

require_once(__DIR__ . '/../config.php');
use function Config\get_lib_dir;
use function Config\get_db_dir;

require_once(get_lib_dir() . '/table/table.php');
use Table\Table;

require_once(get_lib_dir() . '/utils/utils.php');
use function Utils\join_paths;

use \DateTimeImmutable as DTI;



// ############################################################################
// Table functions
// ############################################################################

// Example: '/manga' => '/app/db/manga.csv'
// ----------------------------------------------------------------------------
function get_csv_path(string $csv_id): string {

    $csv_suffix        = '.csv';
    $csv_relative_path = $csv_id . $csv_suffix;
    $csv_full_path     = join_paths(get_db_dir(), $csv_relative_path);

    return $csv_full_path;
}

// ----------------------------------------------------------------------------
function read_table(string $csv_filename): Table {

    $data = Table::readCSV($csv_filename);
    return $data;
}

// ----------------------------------------------------------------------------
function add_blog_message(string $csv_filename, string $message): void {

    // 1. Read Table
    $blog_data = Table::readCSV($csv_filename);

    // 2. Get current time
    $timestamp     = new DTI('now');
    $timestamp_str = $timestamp->format(DTI::RFC3339);

    // 3. Append new row
    $blog_data->prependRow([$timestamp_str , $message]);

    // 4. Write Table
    $blog_data->writeCSV($csv_filename);
}

// ----------------------------------------------------------------------------
