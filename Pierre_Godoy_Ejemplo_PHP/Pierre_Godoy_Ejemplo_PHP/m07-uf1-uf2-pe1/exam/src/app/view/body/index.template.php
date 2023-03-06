<p>PC Parts list:</p>

<form method="post">
    <?php
    foreach ($part_table->body as $row) {
        $id    = $row['Id'];
        $name  = $row['Name'];
        $price = $row['Price'];
        echo <<<END

            <label> 
                <input type="checkbox" name="part_ids[]" value="$id" />
                $name ($price €)
            </label>
            <br>

            END;
    }
    ?><br>
    <input type="submit" value="Add to cart">
</form>

<br>

<a href="/cart"> Go to cart </a><br>
<a href="/stock"> Stock</a>