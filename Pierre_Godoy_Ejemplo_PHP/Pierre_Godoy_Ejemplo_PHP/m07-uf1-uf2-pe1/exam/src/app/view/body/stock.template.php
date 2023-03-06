<table>

     <?php
     require_once(__DIR__ . '/../../config.php');

     use function Config\get_view_dir;

     require_once(get_view_dir() . '/view.php');

     echo View\get_html_header($cart_table->header);
     echo View\get_html_body($cart_table->body);
     ?>

</table>



<br>

<a href="/"> Back to the part list </a> <br>