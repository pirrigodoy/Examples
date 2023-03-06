<h3> Total price is <?=$total_price?> €. Proceed? </h3>

<p> <a href="/">                  Back to main page </a> </p>
<p> <a href="/cart?action=empty"> Confirm payment   </a> </p>

<table>

     <?php
     require_once(__DIR__ . '/../../config.php');
     use function Config\get_view_dir;

     require_once(get_view_dir() . '/view.php');

     echo View\get_html_header($cart_table->header);
     echo View\get_html_body($cart_table->body);
     ?>

</table>
