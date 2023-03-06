<?php
declare(strict_types=1);
namespace TableTest;

require_once(__DIR__ . '/../../src/app/config.php');
use function Config\get_lib_dir;

require_once(get_lib_dir() . '/table/table.php');
use Table\Table;


// ----------------------------------------------------------------------------
test('Table::readCSV', function () {

    $table = Table::readCSV(__DIR__ . '/manga.csv');

    // Check Header
    expect($table->header)->toBe(['Title', 'Volumes']);

    // Check number of rows
    $num_rows = count($table->body);
    expect($num_rows)->toBe(4);

    // Check first manga
    $first_manga = $table->body[0];
    expect($first_manga['Title'])->toBe('Chainsaw JavaScript');
});

// ----------------------------------------------------------------------------
