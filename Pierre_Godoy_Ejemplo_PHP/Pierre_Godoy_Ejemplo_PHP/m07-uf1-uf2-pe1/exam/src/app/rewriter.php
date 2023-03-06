<?php
declare(strict_types=1);
namespace Rewriter;

require_once(__DIR__ . '/config.php');
use function Config\get_app_dir;
use function Config\get_public_dir;
use function Config\get_lib_dir;

require_once(get_lib_dir() . '/request/request.php');
use Request\Request;



// ############################################################################
// About URL Rewriting
// ############################################################################

// About:
// - This file is only needed for PHP's development server.
// - If you use a production web server (Apache, Nginx, Caddy, etc.) you don't need this file.
// - Configure two things in your production web server:
//   1. Enable URL rewriting
//   2. Make the web server execute router.php on each request.


// ############################################################################
// URL Rewriting configuration for the Apache web server (.htaccess file)
// ############################################################################

// RewriteEngine On
// RewriteBase /

// Allow any files or directories that exist to be displayed directly
// RewriteCond %{REQUEST_FILENAME} !-f
// RewriteCond %{REQUEST_FILENAME} !-d

// Rewrite all other URLs to index.php/URL
// RewriteRule .* index.php/$0 [PT]



// ############################################################################
// URL Rewriting for PHP's development web server
// ############################################################################

// About:
// - Execute with: php -S 0.0.0.0:8080 -t public/ src/controller/rewriter.php 
// - This file will be executed on every request.
// Return parameter:
// - If rewriter.php returns false {
//   The development web server will try to serve the local file. }
// - Else {
//   The development web server will do nothing.
//   router.php must echo all the output itself. }
// Request:
// - ::fromGlobals takes its parameters from superglobals.
// - Do not pass the request to the router. The router must be independent from the rewriter.
// References:
// - Official info: https://www.php.net/manual/en/features.commandline.webserver.php
// - Routing HowTo: https://externals.io/message/53869
// - Superglobals:  https://www.php.net/manual/en/language.variables.superglobals.php
// ----------------------------------------------------------------------------
function main() {

    // 1. Main
    $main = get_app_dir() . '/main.php';

    // 2. Request
    $request        = Request::getFromWebServer();
    $local_path     = get_public_dir() . $request->path;
    $file_found     = is_file($local_path);

    // 3. Process request
    if   ($file_found)  { return false; }       // Serve static resource
    else                { require_once $main; } // Serve dynamic resource

}
// ----------------------------------------------------------------------------
return main();           // routing.php must return false to serve files as-is.
// ----------------------------------------------------------------------------
