<?php
declare(strict_types=1);
namespace Main;

require_once(__DIR__ . '/config.php');
use function Config\get_lib_dir;
use function Config\get_context_dir;

require_once(get_lib_dir() . '/request/request.php');
use Request\Request;

require_once(get_lib_dir() . '/cookie/cookie.php');
use Cookie\Cookie;

require_once(get_lib_dir() . '/context/context.php');
use Context\Context;
use Response\Response;

use function Context\get_new_browser_id;

require_once(get_lib_dir() . '/router/router.php');
use function Router\process_request;

require_once(get_lib_dir() . '/table/table.php');
use Table\Table;


// Check if the request has the 'browser_id' cookie and we have the 'browser_id.json' context
// If there is no browser_id, we use the invalid -1 id.
// There is no context with a negative id, and thus it will always return false.
// ----------------------------------------------------------------------------
function check_context(array $request_parameters): bool {

    $browser_id     = $request_parameters['browser_id'] ?? 'no-browser-id';
    $context_file   = get_context_dir() . "/$browser_id.json";
    $context_exists = file_exists($context_file);

    return $context_exists;
}


// IMPORTANT: Server paths in $route_table must not end in a slash '/'. The root document is ''.
// ----------------------------------------------------------------------------
function main(): void {

    // 1. Get request and route table
    $route_table = Table::readCSV(__DIR__ . '/routes.csv');
    $request     = Request::getFromWebServer();

    // 2. Check if the request has the 'browser_id' cookie and we have the 'browser_id.json' context
    $have_context = check_context($request->parameters);

    // 3. If context: Use it to process request
    if ($have_context) {

        // 1. Read context from disk
        $browser_id = $request->parameters['browser_id'];
        $context = Context::readFromDisk($browser_id);

        // 2. Process request
        [$response, $context] = process_request($request, $context, $route_table);

        // 3. Save context
        $context->writeToDisk($browser_id);

        // 4. Send response
        $response->send();
    }

    // 4. If no context: create context, set cookie and force reload
    if (!$have_context) {

        // 1. Get new unique browser id
        $browser_id = get_new_browser_id();

        // 2. Create context (session) browser_id.json
        $context = new Context();
        $context->writeToDisk($browser_id);

        // 3. Create cookie
        $browser_id_cookie = new Cookie('browser_id', $browser_id);

        // 4. Attach cookie to response
        $response = new Response();
        $response->add_cookie($browser_id_cookie);

        // 5. Tell browser to reload page
        $response->set_redirection('/');
        $response->send();
    }

}

// ----------------------------------------------------------------------------
main();
// ----------------------------------------------------------------------------
