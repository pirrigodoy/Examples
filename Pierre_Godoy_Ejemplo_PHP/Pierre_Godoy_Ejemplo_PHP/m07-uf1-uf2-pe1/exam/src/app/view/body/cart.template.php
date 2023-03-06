<table>

     <?php
     require_once(__DIR__ . '/../../config.php');
     use function Config\get_view_dir;

     require_once(get_view_dir() . '/view.php');

     echo View\get_html_header($cart_table->header);
     echo View\get_html_body($cart_table->body);
     ?>

</table>

<?php  if (empty($cart_table->body)) { echo '<p> The cart is empty. </p>'; }  ?>

<p> Total price is: <?=$total_price?> € </p>

<br>

<a href="/">                  Back to the part list  </a> <br>
<a href="/cart?action=empty"> Empty the cart         </a> <br>
<a href="/payment">           Go to the payment page </a> <br>
