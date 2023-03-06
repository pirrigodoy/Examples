<?php
declare(strict_types=1);
namespace BlogTest;

use \DateTimeImmutable as DTI;

require_once(__DIR__ . '/../../src/app/config.php');
use function Config\get_lib_dir;
use function Config\get_model_dir;

require_once(get_model_dir() . '/model.php');
use function Model\get_csv_path;
use function Model\read_table;

require_once(get_lib_dir() . '/table/table.php');
use Table\Table;


# Format strings: https://www.php.net/manual/en/class.datetimeimmutable.php
# RFC3339: https://www.rfc-editor.org/rfc/rfc3339
// ----------------------------------------------------------------------------
test('DateTimeImmutable + RFC3339', function () {

    // 1. Create DateTimeImmutable from reference string
    $reference  = '2022-12-01T18:59:23+01:00';
    $date_time  = \DateTimeImmutable::createFromFormat(\DateTimeInterface::RFC3339, $reference);

    // 2. Convert $date_time to string
    $result     = $date_time->format(\DateTimeInterface::RFC3339);

    // 3. Compare result to reference
    expect($result)->toEqual($reference);

});

// ----------------------------------------------------------------------------
test('DateTimeImmutable Table', function () {

    // 1. Read Table
    $blog_table = Table::readCSV(__DIR__ . '/blog.csv', '|');

    // 3. Compare result to reference
    expect(count($blog_table->body))->toEqual(5);

    // 1. Get data
    $blog_table = read_table(get_csv_path('blog'));

    // 2. Reformat dates
    $reformat_timestamp = fn ($timestamp) => DTI::createFromFormat(DTI::RFC3339, $timestamp)->format('Y-m-d H:i:s');
    $reformat_row       = fn ($row) => ['Timestamp' => $reformat_timestamp($row['Timestamp']),
                                        'Message'   => $row['Message'] ];
    $blog_table->body   = array_map($reformat_row, $blog_table->body);

    // 3. Read result
    $reference = Table::readCSV(__DIR__ . '/pretty-blog.csv', '|');


    expect($blog_table)->toEqual($reference);

});

// ----------------------------------------------------------------------------
