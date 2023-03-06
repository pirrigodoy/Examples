<?php

declare(strict_types=1);

namespace Controller;

require_once(__DIR__ . '/../config.php');

use function Config\get_lib_dir;
use function Config\get_model_dir;
use function Config\get_view_dir;

require_once(get_lib_dir() . '/request/request.php');

use Request\Request;

require_once(get_lib_dir() . '/context/context.php');

use Context\Context;

require_once(get_lib_dir() . '/response/response.php');

use Response\Response;

require_once(get_lib_dir() . '/table/table.php');

use Table\Table;

require_once(get_model_dir() . '/model.php');

use function Model\get_csv_path;
use function Model\read_table;

require_once(get_view_dir() . '/view.php');

use function View\get_template_path;
use function View\render_template;



// ############################################################################
// Helper functions
// ############################################################################

// ----------------------------------------------------------------------------
function check_login(Table $user_table, string $user_name, string $user_pass): bool
{

    $get_user_row = fn ($row) => (($row['Name'] . $row['Pass']) == ($user_name . $user_pass));
    $filtered_table  = $user_table->filterRows($get_user_row);

    $login_ok = (count($filtered_table->body) == 1);

    return $login_ok;
}

// This function will crash if $username does not exist. First use check_login().
// ----------------------------------------------------------------------------
function get_user_role(Table $user_table, string $user_name): string
{

    $get_user_row   = fn ($row) => ($row['Name'] == $user_name);
    $filtered_table = $user_table->filterRows($get_user_row);
    $user_row       = $filtered_table->body[0];
    $user_role      = $user_row['Role'];

    return $user_role;
}

// ----------------------------------------------------------------------------
function make_cart_table(Table $part_table, array $cart): Table
{

    $cart_table = new Table(['Name', 'Price', 'Stock']);

    foreach ($cart as $part_id) {

        $get_part_id_row = fn ($row) => ($row['Id'] == $part_id);
        $filtered_table  = $part_table->filterRows($get_part_id_row);
        $part_row        = $filtered_table->body[0];

        $part_name  = $part_row['Name'];
        $part_price = $part_row['Price'];
        $part_stock = $part_row['Stock'];

        $cart_table->appendRow([$part_name, $part_price, $part_stock]);
    }

    return $cart_table;
}


// ############################################################################
// Route handlers
// ############################################################################
// All controller functions receive $request, whether they use it or not.

// ----------------------------------------------------------------------------
function index(Request $request, Context $context): array
{

    // A. If GET request, send form
    if ($request->method == 'GET') {

        // 1. Read parts DB
        $part_table = read_table(get_csv_path('parts'));

        // 2. Fill Template
        $index_body = render_template(
            get_template_path('/body/index'),
            ['part_table' => $part_table]
        );
        $index_view = render_template(
            get_template_path('/skeleton/skeleton'),
            [
                'title'     => 'PcParts',
                'user_name' => $context->user_name,
                'body'      => $index_body
            ]
        );
        // 3. Send response
        $response = new Response($index_view);
        return [$response, $context];
    }


    // B. If POST request, get form data
    if ($request->method == 'POST') {

        // 1. Add form parts to the context cart
        $part_ids      = $request->parameters['part_ids'] ?? [];
        $context->cart = array_merge($context->cart, $part_ids);

        // 2. Redirect to cart
        $response = new Response();
        $response->set_redirection("/cart");
        return [$response, $context];
    }
}

// /cart gets all its parameters through GET requests.
// ----------------------------------------------------------------------------
function cart(Request $request, Context $context): array
{

    // Check request parameters.
    // If action is 'empty', empty the cart.
    // If the action is 'list' or an unknown action, just list the cart.
    $action = $request->parameters['action'] ?? 'list';
    if ($action == 'empty') {
        $context->cart = [];
    }


    // List the cart:

    // 1. Read parts DB
    $part_table = read_table(get_csv_path('parts'));

    // 2. Make cart table
    $cart_table = make_cart_table($part_table, $context->cart);

    // 3. Get total
    $columns_array = $cart_table->getColumns();
    $price_column  = $columns_array['Price'];
    $total_price   = array_sum($price_column);

    // 4. Fill template
    $cart_body = render_template(
        get_template_path('/body/cart'),
        [
            'cart_table'  => $cart_table,
            'total_price' => $total_price
        ]
    );
    $cart_view = render_template(
        get_template_path('/skeleton/skeleton'),
        [
            'title'     => 'Cart',
            'user_name' => $context->user_name,
            'body'      => $cart_body
        ]
    );

    // 5. Send response
    $response = new Response($cart_view);
    return [$response, $context];
}

// ----------------------------------------------------------------------------
//Funcion para que compruebe si el usuario tiene el rol de vendor pueda tener acceso a la ruta stock, si no se le redigira a la ruta 404
function stock(Request $request, Context $context): array
{
    if ($context->user_role == 'vendor') {

        // Check request parameters.
        // If action is 'empty', empty the cart.
        // If the action is 'list' or an unknown action, just list the cart.

        // List the cart:

        // 1. Read parts DB
        $part_table = read_table(get_csv_path('parts'));

        // 2. Make cart table
        $stock_table = make_cart_table($part_table, $context->cart);

        // 4. Fill template
        $cart_body = render_template(
            get_template_path('/body/stock'),
            [
                'cart_table'  => $stock_table,
            ]
        );
        $cart_view = render_template(
            get_template_path('/skeleton/skeleton'),
            [
                'title'     => 'Stock List',
                'user_name' => $context->user_name,
                'body'      => $cart_body
            ]
        );

        // 5. Send response
        $response = new Response($cart_view);
        return [$response, $context];
    } else {

        $response = new Response();
        $response->set_redirection('/error404');

        return [$response, $context];
    }
}

// Only registered users can pay
// ----------------------------------------------------------------------------
function payment(Request $request, Context $context): array
{

    // A. If request is from a customer, list the cart and ask for confirmation
    if ($context->user_role == 'customer') {

        // 1. Read parts DB
        $part_table = read_table(get_csv_path('parts'));

        // 2. Make cart table
        $cart_table = make_cart_table($part_table, $context->cart);

        // 3. Get total
        $columns_array = $cart_table->getColumns();
        $price_column  = $columns_array['Price'];
        $total_price   = array_sum($price_column);

        // 4. Fill template
        $payment_body = render_template(
            get_template_path('/body/payment'),
            [
                'cart_table' => $cart_table,
                'total_price' => $total_price
            ]
        );
        $payment_view = render_template(
            get_template_path('/skeleton/skeleton'),
            [
                'title'     => 'Payment',
                'user_name' => $context->user_name,
                'body'      => $payment_body
            ]
        );

        // 5. Send response
        $response = new Response($payment_view);
        return [$response, $context];


        // B. If request is from a guest, send to login page
    } else {

        $response = new Response();
        $response->set_redirection('/login');

        return [$response, $context];
    }
}

// ----------------------------------------------------------------------------
function login(Request $request, Context $context): array
{


    if ($request->method == 'GET') {

        $login_body = render_template(get_template_path('/body/login'), []);
        $login_view = render_template(
            get_template_path('/skeleton/skeleton'),
            [
                'title'     => 'Login',
                'user_name' => $context->user_name,
                'body'      => $login_body
            ]
        );

        $response = new Response($login_view);
        return [$response, $context];
    } elseif ($request->method == 'POST') {

        $user_name = $request->parameters['user_name'];
        $user_pass = $request->parameters['user_pass'];

        // 1. Check against users DB
        $user_table = read_table(get_csv_path('users'));
        $login_ok   = check_login($user_table, $user_name, $user_pass);

        // A. If login ok
        if ($login_ok) {

            // Change context
            $context->logged_in = true;
            $context->user_name = $user_name;
            $context->user_role = get_user_role($user_table, $user_name);

            // Set redirection
            $response = new Response();
            $response->set_redirection('/');

            // B. If login failed
        } else {

            // Set redirection only
            $response = new Response();
            $response->set_redirection('/login');
        }

        return [$response, $context];
    }
}

// Note: login and logout do not handle correctly the cart. Should be improved.
// ----------------------------------------------------------------------------
function logout(Request $request, Context $context): array
{

    // Change context
    $context->logged_in = false;
    $context->user_name = 'guest';
    $context->user_role = 'guest';
    $context->cart      = [];

    // Set redirection
    $response = new Response();
    $response->set_redirection('/');

    return [$response, $context];
}
// ----------------------------------------------------------------------------

// ----------------------------------------------------------------------------
function error_404(Request $request, Context $context): array
{

    $error404_body = render_template(
        get_template_path('/body/error404'),
        ['request_path' => $request->path]
    );

    $error404_view = render_template(
        get_template_path('/skeleton/skeleton'),
        [
            'title'     => 'Error 404: Not found',
            'user_name' => $context->user_name,
            'body'      => $error404_body
        ]
    );

    $response = new Response($error404_view, 404);
    return [$response, $context];
}

// ----------------------------------------------------------------------------
