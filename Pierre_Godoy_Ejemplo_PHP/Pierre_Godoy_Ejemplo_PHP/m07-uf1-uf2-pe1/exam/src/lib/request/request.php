<?php
declare(strict_types=1);
namespace Request;



// ############################################################################
// Request helper functions
// ############################################################################

// ----------------------------------------------------------------------------
function get_canonical_path(string $url_path): string {

    $url_path_sanitized = filter_var($url_path, FILTER_SANITIZE_URL);
    $url_path_trimmed   = rtrim($url_path_sanitized, '/');

    $result = $url_path_trimmed;
    return $result;
}



// ############################################################################
// Request Class
// ############################################################################
// URL parts:
// - https://en.wikipedia.org/wiki/URL
// - https://www.geeksforgeeks.org/components-of-a-url/
// - URI = scheme ":" ["//" authority] path ["?" query] ["#" fragment]
// URL parsing:
// - URL parsing: https://www.php.net/manual/en/function.parse-url.php
// - Query parsing (not used): https://www.php.net/manual/en/function.parse-str.php
// URL fragments:
// - Fragments are never sent to the server. No point in trying to parse them.
// REQUEST_URI:
// - $_SERVER['REQUEST_URI'] contains only the query and the fragment.
// URLDECODE():
// - $_GET and $REQUEST have already the url decoded. Never reapply!
// - https://www.php.net/manual/en/function.urldecode.php
// HTML Special Chars
// - https://stackoverflow.com/questions/6249151/how-can-i-properly-escape-html-form-input-default-values-in-php
// - Do htmlspecialchars() before outputting to the user.
// - In the database, save the raw data for editing and auditing.
// - https://stackoverflow.com/questions/7245440/should-htmlspecialchars-be-used-on-information-on-input-or-just-before-output


class Request {

    public string $path;
    public string $method;
    public array  $parameters;

    // Main Constructor
    // ------------------------------------------------------------------------
    public function __construct(string $url_path   = '/' ,
                                string $method     = 'GET',
                                array  $parameters = []  ) {

        $this->method     = $method;
        $this->path       = get_canonical_path($url_path);
        $this->parameters = $parameters;
    }

    // Alternative constructor.
    // It's called "FromWebServer" but it's from the Super Globals actually.
    // $_REQUEST includes $_GET and $_POST. It also used to include $_COOKIE but not anymore.
    // https://stackoverflow.com/questions/8928733/php-request-doesnt-contain-cookies
    // ------------------------------------------------------------------------
    public static function getFromWebServer(): self {

        // 1. Get path
        $url_path_and_query = $_SERVER['REQUEST_URI'];
        $url_path           = urldecode(parse_url($url_path_and_query, PHP_URL_PATH));

        // 2. Get method
        $method = $_SERVER['REQUEST_METHOD'];

        // 3. Get parameters
        $parameters = array_merge($_GET, $_POST, $_COOKIE);
        
        // 4. Make Request object
        $request = new Request($url_path, $method, $parameters);

        return $request;
    }

    // ------------------------------------------------------------------------
    public function __toString(): string {

        $parameters_json_str = json_encode($this->parameters, JSON_PRETTY_PRINT);

        $request_str = <<<END

            Method: $this->method
            Path:   $this->path
            Parameters: $parameters_json_str

            END;

        return $request_str;
    }

}

// ----------------------------------------------------------------------------
