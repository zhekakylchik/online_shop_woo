/*
* Adding or removing available options for sorting products
*/

//  Adding a new option to the dropdown list
add_filter( 'woocommerce_default_catalog_orderby_options', 'truejb_custom_orderby_option' );
add_filter( 'woocommerce_catalog_orderby', 'truejb_custom_orderby_option' );
 
function truejb_custom_orderby_option( $sortby ) {
	$sortby['randomly'] = 'Randomly';
  $sortby[ 'vnalichii' ] = 'Items in stock first';
	return $sortby;
}

//  Applying sorting
add_filter( 'woocommerce_get_catalog_ordering_args', 'truejb_random_order' );
 
function truejb_random_order( $args ) {
 
	if ( isset( $_GET['orderby'] ) && 'randomly' == $_GET['orderby'] ) {
		$args['orderby'] = 'rand';
	}
  if ( isset( $_GET['orderby'] ) && 'vnalichii' == $_GET['orderby'] ) {
		$args[ 'meta_key' ] = '_stock_status';
		$args[ 'orderby' ] = 'meta_value';
		$args[ 'order' ] = 'ASC';
	}
	return $args;
 
}

//  remove unnecessary sort options
add_filter( 'woocommerce_default_catalog_orderby_options', 'truejb_remove_orderby_options' );
add_filter( 'woocommerce_catalog_orderby', 'truejb_remove_orderby_options' );
 
function truejb_remove_orderby_options( $sortby ) {
 
	unset( $sortby[ 'popularity' ] ); // by popularity
	unset( $sortby[ 'rating' ] ); // by rating
	unset( $sortby[ 'date' ] ); // Sort by latest
	unset( $sortby[ 'price' ] ); // Prices: ascending
	unset( $sortby[ 'price-desc' ] ); // Prices: Descending
 
	return $sortby;
 
}





/*
* Sort products by discount %
*/

//Adding a sort option
add_filter( 'woocommerce_catalog_orderby', 'truejb_add_sort_options' );
 
function truejb_add_sort_options( $options ){
 	$options[ 'discount_amount' ] = 'Discount';
	return $options;
}

//  Sort by meta key
add_filter( 'woocommerce_get_catalog_ordering_args', 'truejb_custom_product_sorting' );
 
function truejb_custom_product_sorting( $args ) {
 
	// Sort by discount %
	if( isset( $_GET[ 'orderby' ] ) && 'discount_amount' === $_GET['orderby'] ) {
		$args[ 'meta_key' ] = 'discount_amount';
		$args[ 'order' ] = 'DESC'; // high to low
	}
	return $args;
 
}

//  Create a meta field with a discount %
add_action( 'woocommerce_product_quick_edit_save', 'truejb_meta_discount' );
add_action( 'woocommerce_process_product_meta', 'truejb_meta_discount');
 
function truejb_meta_discount( $product ) {
 
	$product = wc_get_product( $product );
 
	// if the product is not on sale, then delete it meta field and do nothing else
	if( ! $product->is_on_sale() ) {
		delete_post_meta( $product->get_id(), 'discount_amount' );
		return;
	}
 
	$regular = $product->get_regular_price();
	$sale = $product->get_sale_price();
	$discount = round( 100 - ( $sale / $regular * 100), 2 );
 
	// save the discount % in the meta field
	update_post_meta( $product->get_id(), 'discount_amount', $discount );
 
}




/*
* check that the user has already purchased a certain product and display a message about this
*/

add_action( 'woocommerce_after_shop_loop_item', 'truejb_if_user_purchased_product', 25 );
 
function truejb_if_user_purchased_product() {
 
	// if not authorized - do nothing
	if ( ! is_user_logged_in() ) {
		return;
	}
 
	global $product;
	$current_user = wp_get_current_user();
 
	if ( wc_customer_bought_product( $current_user->user_email, $current_user->ID, $product->get_id() ) ) {
		echo '<p>&hearts; ' . $current_user->first_name . ', you have purchased this item before. Do you want to buy again?</p>';
	}
 
}




/*
* Adding short product descriptions to the catalog page
*/

add_action( 'woocommerce_after_shop_loop_item', 'truejb_short_description', 7 );
 
function truejb_short_description() {
	the_excerpt();
}




/*
* Specify the discount % or the amount saved for sale items
*/

add_filter( 'woocommerce_format_sale_price', 'truejb_discount_percentage', 10, 3 );
 
function truejb_discount_percentage( $price, $regular_price, $sale_price ) {
 
	// calculate discount %
	$percentage = round( ( $regular_price - $sale_price ) / $regular_price * 100 ).'%';
 
	// savings message, you can style it with css
	$percentage_message = '<span style="color: #ff7070;">Saving ' . $percentage . '!</span>';
 
 	// price display in new format
	$price = '<del>' . wc_price( $regular_price ) . '</del> <ins>' . wc_price( $sale_price ) . $percentage_message . '</ins>';

 //display amount of money saved not as a %
 // calculate how much saving
 // $discount_amount = $regular_price - $sale_price;
 // wrapping it in function wc_price()
 // $percentage_message = '<span style="color: #ff7070;">Saving ' . wc_price( $discount_amount ) . '!</span>';
 
	// return result
	return $price;
 
}




/*
* Change the number of similar products displayed
*/

add_filter( 'woocommerce_output_related_products_args', 'truejb_rel_products_args', 25 );
 
function truejb_rel_products_args( $args ) {
	$args[ 'posts_per_page' ] = 12; // how many items to displaу
	$args[ 'columns' ] = 4; // how many items per row
	return $args;
}
