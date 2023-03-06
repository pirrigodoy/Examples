<?php
declare(strict_types=1);
namespace Router;

require_once(__DIR__ . '/../../app/config.php');
use function Config\get_lib_dir;
use function Config\get_controller_dir;

require_once(get_lib_dir() . '/table/table.php');
use Table\Table;

require_once(get_lib_dir() . '/request/request.php');
use Request\Request;

require_once(get_lib_dir() . '/response/response.php');
use Response\Response;

require_once(get_lib_dir() . '/context/context.php');
use Context\Context;

require_once(get_lib_dir() . '/utils/utils.php');
use function Utils\is_regex_match;
use function Utils\match_regex;

require_once(get_controller_dir() . '/controller.php');
// Required for $controller_function in match_route()



// ############################################################################
// Path matching functions
// ############################################################################
// - Path  = Part of an URL.
// - Route = Path in server + HTTP Method + Controller function.
// - Route paths can include colon variables (:name) and star variables (*anything).
// - Path matching functions create regex from route paths to match path parameters in the request.


// - Colon variables (:var) stop at slashes (/).
// - Star  variables (*var) stop at the end of the path.
// - cvncr = colon_var_named_capture_regex
// - svncr = star_var_named_capture_regex
// - Regex delimeters are waves (~).
// - We force $server_path_regex to match from beginning (^) to end ($) of the path.
// ----------------------------------------------------------------------------
function get_server_path_regex($server_path): string {

    $colon_var                     = '~:([^/]+)~';      //   /info/:name/age
    $colon_var_named_capture_regex = '(?<$1>[^/]+)';    //   /info/(?<name>[^/]+)/age
    $server_path_with_cvncr        = preg_replace($colon_var, $colon_var_named_capture_regex, $server_path);

    $star_var                         = '~\*(.*)~';         //   /info/*anything
    $star_var_named_capture_regex     = '(?<$1>.*)';        //   /info/(?<anything>.*)
    $server_path_with_cvncr_and_svncr = preg_replace($star_var, $star_var_named_capture_regex, $server_path_with_cvncr);

    $server_path_regex = '~^' . $server_path_with_cvncr_and_svncr . '$~';
    return $server_path_regex;
}

// Route = Associative array with keys: path, method, controller_function
// ----------------------------------------------------------------------------
function has_matching_path(array $route, string $request_path): bool {

    $server_path        = $route['path'];
    $server_path_regex  = get_server_path_regex($server_path);
    $is_match           = is_regex_match($server_path_regex, $request_path);

    return $is_match;
}



// ############################################################################
// Path parameter functions
// ############################################################################


// If the server path has no path variables, returns emtpy array.
// preg_match() returns named captures twice:
// 1. text key   => value
// 2. number key => value
// We don't need numerical keys, we delete them.
// This includes the match with the zero key, which is the global match.
// ----------------------------------------------------------------------------
function get_path_parameters($request_path, $server_path_regex): array {

    // 1. Match regex to request path
    $match_array = match_regex($server_path_regex, $request_path);

    // 2. Remove entries with numerical keys from matches.
    $has_text_key    = fn ($key) =>  !is_numeric($key);
    $path_parameters = array_filter($match_array, $has_text_key, ARRAY_FILTER_USE_KEY);

    return $path_parameters;
}

// Route = Associative array with these keys: path, method, controller_function
// ----------------------------------------------------------------------------
function merge_path_parameters(Request $request, string $server_path): Request {

    // 1. Get regex
    $server_path_regex = get_server_path_regex($server_path);

    // 2. Get path parameters
    $path_parameters = get_path_parameters($request->path, $server_path_regex);

    // 3. Merge parameters. Overwrites params with the same key!
    $merged_parameters = array_merge($request->parameters, $path_parameters);

    $result = new Request($request->path,
                          $request->method,
                          $merged_parameters);

    return $result;
}



// ############################################################################
// Method functions
// ############################################################################


// Route = Associative array with these keys: path, method, controller_function
// ----------------------------------------------------------------------------
function has_supported_method(array $route, string $request_method): bool {

    $supported_methods = $route['methods'];
    $is_supported      = str_contains($supported_methods, $request_method);

    return $is_supported;
}



// ############################################################################
// Default error 404 function
// ############################################################################


// Default function to be called if there is no custom 404 error controller.
// ----------------------------------------------------------------------------
function default_error_404(Request $request): Response {

    $response = new Response('Error 404 - Not found', 404);
    return $response;
}



// ############################################################################
// Main routing function
// ############################################################################
// - Path  = Part of an URL.
// - Route = Path in server + HTTP Method + Controller function.
// - Routes can include colon variables (:name) and star variables (*anything)
// - Colon variables stop at a slash. Star variables stop at the end of the path.


// Route = Associative array with these keys: path, method, controller_function
// ----------------------------------------------------------------------------
function process_request(Request $request, Context $context, Table $route_table): array {

    // 1. Append default_error_404 route
    $route_table->appendRow( [$request->path, $request->method, 'Router\default_error_404'] );

    // 2. Make function for filtering routes
    $is_matching_route  = fn ($route) =>  has_matching_path($route, $request->path) and
                                          has_supported_method($route, $request->method);

    // 3. Filter routes that match the request
    $matching_route_table = $route_table->filterRows($is_matching_route);

    // 4. Pick first matching route. At least there is always default_error_404.
    $matched_route = $matching_route_table->body[0];
    
    // 5. Merge path parameters
    $request_with_path_parameters = merge_path_parameters($request, $matched_route['path']);

    // 6. Call controller function.
    [$response, $context] = $matched_route['controller_function']($request_with_path_parameters, $context);

    return [$response, $context];
}

// ----------------------------------------------------------------------------
