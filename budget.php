<?php
/**
 * Plugin Name: Budget WP
 * Plugin URI: http://lbideias.com.br
 * Description: System for registration of products and budget requests.
 * Author: leobaiano
 * Author URI: http://lbideias.com.br/
 * Version: 1.0.0
 * License: GPLv2 or later
 * Text Domain: lb_bdg
 * Domain Path: /languages/
 */

	// Exit if accessed directly.
	if ( ! defined( 'ABSPATH' ) ) exit;

	// Sets the plugin path.
	define( 'PLUGIN_PATH', plugin_dir_path( __FILE__ ) );

	/**
	 * Class WP Budget
	 * @version 1.0.0
	 * @author Leo Baiano <leobaiano@lbideias.com.br>
	 */
	class WP_Budget {

		/**
		 * Version
		 */
		const VERSION = '1.0.0';

		private function __construct() {
			// Load text domain.
			add_action( 'init', array( $this, 'load_textdomain' ) );
		}

		/**
		 * Load text domain
		 */
		public function load_textdomain() {
			load_plugin_textdomain( 'lb_bdg', FALSE, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
		}

		/**
		 * Search values ​​in an array key
		 *
		 * @param string $value - Value to be searched
		 * @param string $key - Key where search is made
		 * @param array $array - Array where the search will be made
		 * @return Returns the index where the result is found and false if not found
		 */
		public function search_by_keyword( $value, $key, $array ) {
		    $i = 0;
		    foreach ( $array as $item ) {
		        if ( isset( $item[$key] ) && $item[$key] == $value ) {
		            return  $i;
		    	}
		        $i++;
		    }
		    return false;
		}

		/**
		 * Get items from cart
		 *
		 * @return Array with items from the cart or false if empty
		 */
		public static function get_cart() {
			if( !isset( $_SESSION['lb_bdg_cart'] ) || empty( $_SESSION['lb_bdg_cart'] ) ){
				return false;
			}
			else{
				return $_SESSION['lb_bdg_cart'];
			}
		}

		/**
		 * Counting items added to cart
		 *
		 * @return Amount of items added to cart
		 */
		public static function count_items_cart() {
			if( !isset( $_SESSION['lb_bdg_cart'] ) || empty( $_SESSION['lb_bdg_cart'] ) ){
				return 0;
			}
			else{
				foreach( $_SESSION['lb_bdg_cart'] as $item ){
		            $amount += $item['amount'];
		        }
		        return $amount;
			}
		}

		/**
		* Add item to cart
		* @param int $id
		* @param string $name
		* @param int $amount
		* @param string $thumb - Url thumb
		* @param float $value
		* @return boolean
		*/
		public static function add_item_cart( $id, $name, $amount, $thumb = null, $value = null ) {
			if( !isset( $_SESSION['lb_bdg_cart'] ) || empty( $_SESSION['lb_bdg_cart'] ) ){
		        $_SESSION['lb_bdg_cart'][] = array( 'id' => $id, 'name' => $name, 'amount' => $amount, 'thumb' => $thumb, 'value' => $value );
		        return true;
		    }
		    else{
		        $check = self::search_by_keyword( $id, 'id', $_SESSION['lb_bdg_cart'] );
		        if( is_bool( $check ) ){
		            array_push( $_SESSION['lb_bdg_cart'], array( 'id' => $id, 'name' => $name, 'amount' => $amount, 'thumb' => $thumb, 'value' => $value ) );
		            return true;
		        }
		        else{
		            $amount_actualy = $_SESSION['lb_bdg_cart'][$check]['amount'];
		            $new_amount = $amount_actualy + 1;
		            $_SESSION['lb_bdg_cart'][$check]['amount'] = $new_amount;
		            return true;
		        }
		        return false;
		    }
		    return false;
		}

		/**
		* Edit quantity of items in cart
		* @param int $id
		* @param int $amount
		* @return boolean
		*/
		public static function update_item_cart( $id, $amount ) {
			if( !isset( $_SESSION['lb_bdg_cart'] ) || empty( $_SESSION['lb_bdg_cart'] ) ){
		        return false;
		    }
		    else{
		        $check = self::search_by_keyword( $id, 'id', $_SESSION['lb_bdg_cart'] );
		        if(is_bool( $check ) ){
		            return false;
		        }
		        else{
		            $_SESSION['lb_bdg_cart'][$check]['amount'] = $amount;
		            return true;
		        }
		        return false;
		    }
		    return false;
		}

		/**
		* Remove item from cart
		* @param int $id
		* @return boolean
		*/
		public static function remove_item_cart( $id ) {
			if( !isset( $_SESSION['lb_bdg_cart'] ) || empty( $_SESSION['lb_bdg_cart'] ) ){
		        return false;
		    }
		    else{
		        $check = self::search_by_keyword( $id, 'id', $_SESSION['lb_bdg_cart'] );
		        if( is_bool( $check ) ){
		            return false;
		        }
		        else{
		            unset( $_SESSION['lb_bdg_cart'][$check] );
		            foreach( $_SESSION['lb_bdg_cart'] as $items ){
		                $array[] = $items;
		            }
		            unset( $_SESSION['lb_bdg_cart'] );
		            $_SESSION['lb_bdg_cart'] = $array;
		            return true;
		        }
		        return false;
		    }
		    return false;
		}

		/**
		* Catch amount
		* @param string $currency - default null, return not formmat, R$ - return in real Brazilian or $ - return in dollar
		* @param string $total
		*/
		public static function get_total( $currency = null ){
		    if( !isset( $_SESSION['lb_bdg_cart'] ) || empty( $_SESSION['lb_bdg_cart'] ) ){
		        return false;
		    }
		    else{
		        foreach( $_SESSION['lb_bdg_cart'] as $item){
		            $total += $item['value'] * $item['amount'];
		        }
		        switch ( $currency ) {
		        	case 'R$':
		        		return "R$ " . number_format($total, '2', ',', '');
		        		break;
		        	case '$':
		        		return "$ " . number_format($total, '2', '.', '');
		        		break;

		        	default:
		        		return $total;
		        		break;
		        }
		    }
		    return false;
		}
	}

	WP_Budget();

	// AJAX - Add Items to Cart
	add_action( 'wp_ajax_lb_bdg_add', 'ajax_lb_bdg_add' );
	add_action( 'wp_ajax_nopriv_ajax_lb_bdg_add', 'ajax_ajax_lb_bdg_add' );
	function ajax_lb_bdg_add() {
		$id = $_GET['id'];
	    $name = $_GET['name'];
	    $amount = $_GET['amount'];
	    $thumb = $_GET['thumb'];
	    $value = $_GET['value'];
		
	    $add = WP_Budget::add_item_cart( $id, $name, $amount, $thumb, $value );
	    if($add){
	        $status = array(
	            'status' => 1,
	            'message' => __( 'Items added successfully.', 'lb_bdg' )
	        );
	    }
	    else{
	        $status = array(
	            'status' => 0,
	            'message' => __( 'A mistake and the product was not added to cart, please occurred, please try again later.', 'lb_bdg' )
	        );
	    }
	    echo json_encode($status);
		die();
	}

	// AJAX - Remove items to cart
	add_action( 'wp_ajax_lb_bdg_remove', 'ajax_lb_bdg_remove' );
	add_action( 'wp_ajax_nopriv_lb_bdg_remove', 'ajax_lb_bdg_remove' );
	function ajax_lb_bdg_remove() {
	    $id = $_GET['id'];
	    $currency = $_GET['currency'];
	    $remove_item = WP_Budget::remove_item_cart( $id );
	    $new_amount= WP_Budget::count_items_cart();
	    $value_total = get_total();
	    $value_int = get_total( $currency );
	    if( $remove_item ){
	         $status = array(
	            'status' => 1,
	            'message' => __( 'Item successfully deleted.', 'lb_bdg' ),
	            'id' => $id,
	            'new_amount' => $new_amount,
	            'value_total' => $value_total,
	            'value_int' => $value_int
	        );
	    }
	    else{
	        $status = array(
	            'status' => 0,
	            'message' => __( 'A mistake and the product was not added to cart, please occurred, please try again later.', 'lb_bdg' ),
	            'id' => $id
	        );
	    }
	    echo json_encode($status);
	    die();
	}










































