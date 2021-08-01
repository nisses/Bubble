<!-- V0LT - Bubble -->
<?php

include "../store/products.php"; // Include the product database
include "../store/config.php"; // Include the configuration script
include "../store/authentication.php"; // Include the authentication system

$ordersArray = unserialize(file_get_contents('./ordersdatabase.txt')); // Load the orders database

$selected = 0; // Placeholder variable used to keep track of what color we are currently on while cycling through them on the product tiles.
?>
<!DOCTYPE html>
<html lang="en" style="background:<?php echo $background_gradient_bottom; ?>;">
    <head>
	    <meta charset="utf-8">

	    <meta name="viewport" content="width=device-width, initial-scale=1.0">
	    <title>Bubble - Order</title>

	    <link rel="stylesheet" href="/bubble/assets/css/Projects-Clean.css">
	    <link rel="stylesheet" href="/bubble/assets/bootstrap/css/bootstrap.min.css">
	</head>

	<body style="color:#111111;">
		<div class="projects-clean" style="background:linear-gradient(0deg, <?php echo $background_gradient_bottom; ?>, <?php echo $background_gradient_top; ?>);color:#111111;">
		    <div class="container" style="padding-top:100px;">
	 	        <main>
                    <a class="btn btn-light" role="button" href="index.php" style="margin:8px;padding:9px;background-color:#888888;border-color:#333333;border-radius:10px;">Back</a>
                    <?php
                    // Only allow the current user to continue if they are signed in as an administrator. 
                    if ($username !== $admin_account) {
                        echo "<br><br><p style='color:black;text-align:center;'>Error: You are not authorized to be here! If you do actually have permission to use the point-of-sale console, please ensure you are signed in with the correct account.</p>"; // Display an error message to the user.
                        exit(); // Quit loading the page.
                    }
                    ?>
				    <div class="intro">
				        <h2 class="text-center" style="color:#dddddd">
                            <img src="/bubble/assets/img/bubblelogosmall.svg" alt="Bubble logo" style="height:50px;margin-right:20px;">
                            Edit Order
                        </h2>
				    </div>



    			    <div class="row projects" style="padding-left:5%;padding-right:5%;">
                        <?php
                            if ($panic_switch == true) {
                                echo "<p style='color:inherit;'>The owner of this store has temporarily disabled it. This might mean something has gone wrong, or the server is being maintained. Please check back later. If you have any questions, contact customer support at <a style='color:white;text-decoration:underline;' href='";
                                echo $support_email;
                                echo "'>";
                                echo $support_email;
                                echo "</a></p>";
                                
                                exit(); // Stop loading the rest of the page.
                            }

                            // Pull the variables from the URL data.
                            $order_number = $_GET["order_number"];
                            $product_id = $_GET["product_id"];
                            $quantity = (int)$_GET["quantity"];

                            $item_to_remove = $_GET["item_to_remove"]; // Get the product ID of the item the user would like to remove from the order, assuming it exists.


                            if ($item_to_remove !== "" and $item_to_remove !== null) {
                                unset($ordersArray[$store_id][$order_number][$item_to_remove]); // Remove the item from the order that the user has indicated.
                                file_put_contents('./ordersdatabase.txt', serialize($ordersArray)); // Write array changes to disk
                                header("Location: ./order.php?order_number=" . $order_number);
                            } else if ($order_number == 0 or $order_number == null) { // If not order number is set, then automatically fill out the form with the next sequential order number.
                                $order_number = count($ordersArray[$store_id]) + 1;

                            } else { // If the order number is defined, then the user has almost certain just submitted a product to add to the current order. Therefore, we should validate and save variables from POST data.
                                if ($quantity <= 0 or $quantity == null) { // Make sure the quantity variable exists and is set to an appropriate number.
                                    echo "<p style='color:#ff8888;text-align:center;width:100%;'>Error: Please enter a valid quantity!</p>";
                                } else {
                                    if ($product_id == null or $product_id == "") { // Make sure the user has entered in a product ID. We will check to make sure that this product ID actually exists next.
                                        echo "<p style='color:#ff8888;text-align:center;width:100%;'>Error: Please enter a Product ID!</p>";
                                    } else {
                                        if ($productsArray[$store_id][$product_id]["inperson"] !== true) { // Make sure the product ID entered actually exists, and that it is an "in person" product.
                                            echo "<p style='color:#ff8888;text-align:center;width:100%;'>Error: Please make sure the Product ID that you've entered matches up with an product marked as 'in-person' in the product database!</p>";
                                        } else { // All of the validation has checked out, so add the product to the current order.
                                            $ordersArray[$store_id][$order_number][$product_id] = $ordersArray[$store_id][$order_number][$product_id] + $quantity;
                                            file_put_contents('./ordersdatabase.txt', serialize($ordersArray)); // Write array changes to disk
                                        }
                                    }
                                }
                            }


                            // Show the form to add products to this order.
                            echo '
                                <form style="text-align:center;color:white;width:100%;margin-bottom:50px;" method="GET">
                                    Order Number: <input type="number" placeholder="Order Number" value="' . $order_number . '" name="order_number"><br>
                                    Product ID: <input type="text" placeholder="Product ID" name="product_id"><br>
                                    Quantity: <input type="number" placeholder="Quantity" value="1" name="quantity"><br>
                                    <input type="submit" value="Add To Order"><br>
                                </form>
                                <hr>
                            ';

	                        foreach ($ordersArray[$store_id][$order_number] as $key => $element) {
                                echo '<div class="col-sm-6 col-lg-4 item" style="color:white;background-color:';
                                echo $product_tile_colors[$selected]; $selected++; if ($selected >= count($product_tile_colors)) { $selected = 0; } 
                                echo ';margin:0;border-radius:';
                                echo $product_tile_border_radius;
                                echo 'px">';
                                if ($element["alt"] == "") {
                                    echo '<img class="img-fluid" style="max-height:250px;" style="max-height:250px;" src="' . $productsArray[$store_id][$key]["icon"] . '" alt="' . $productsArray[$store_id][$key]["name"] .' icon">'; // Display this product's icon with automatically generated alt text.
                                } else {
                                    echo '<img class="img-fluid" style="max-height:250px;" style="max-height:250px;" src="' . $productsArray[$store_id][$key]["icon"] . '" alt="' . $productsArray[$store_id][$key]["alt"] .'">'; // Display this product's icon with automatically generated alt text.
                                }
                                echo '<h3 class="name" style="color:#ffffff;">' . $productsArray[$store_id][$key]["name"] . '</h3>'; // Display this product's name
                                echo "<p style='color:white;font-size:17px;'>Quantity: " . $element . "</p>";
                                echo "<p style='color:white;font-size:20px;'>Subtotal: " . (float)$productsArray[$store_id][$key]["price"] * (int)$element . " BCH</p>";

                                echo '<a class="btn btn-light" role="button" href="order.php?item_to_remove=' . $key . '&order_number=' . $order_number . '" style="margin:8px;padding:9px;background-color:#dd8888;border-color:#333333;border-radius:10px;">Remove</a>';

                                echo "</div>";
                            }
                            

                        ?>
                    </div>
                    <?php
                    if ($v0lt_credit == 2) {
                        echo '<p class="description" style="font-size:15px;color:#cccccc;margin-top:30px;margin-bottom:100px;text-align:center;"><a href="https://v0lttech.com" style="text-decoration:underline;color:inherit;">Bubble - Made By V0LT</p>';
                    }
                    if ($v0lt_credit == 3) {
                        echo '<div style="position:fixed;right:0;bottom:0;margin-right:10px;margin-bottom:10px;padding-left:5px;padding-right:5px;border-radius:5px;background:rgba(0, 0, 0, 0.75);"><p style="margin-bottom:7px;margin-top:7px;"><a href="https://v0lttech.com/" style="text-decoration:underline;color:white;">Bubble - Made by V0LT</a></p></div>';
                    }
                    ?>
	            </main>
            </div>
        </div>
    </body>
</html>
