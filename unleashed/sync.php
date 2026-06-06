<?php 
require_once __DIR__ .'/../vendor/autoload.php';

use GuzzleHttp\Client;
class SyncProcess
{
    private $products_to_sync;
    private $product_prices_to_sync;
    private $stock_to_sync;
    private $filtered_product_groups = [];
    
    public function __construct($products, $product_prices, $stock)
    {
        $this->products_to_sync = $products;
        $this->product_prices_to_sync = $product_prices;
        $this->stock_to_sync = $stock;

        // I should really change the actual key of this, but hey ho
        $filtered_product_type_string = strtolower(carbon_get_theme_option('unleashed_supported_product_types'));
        if($filtered_product_type_string && strlen($filtered_product_type_string) > 0){
            $this->filtered_product_groups = array_map('trim', explode(',', $filtered_product_type_string));
        }

    }

    public function run(){
        
        $this->sync_products();
        $this->sync_product_prices();
        $this->sync_stock();
    }

    private function create_product($product){
        // Create a new post of the custom post type 'hcp-product'
        if ( is_null( $product->Notes ) ) {
            $notes = '';
        } else {
            $notes = $product->Notes;
        }
        
        $post_id = wp_insert_post(array(
            'post_title'  => $product->ProductDescription,
            'post_content' => $notes,
            'post_status' => 'publish',
            'post_type'   => 'product',
        ));

        // Check if the post was created successfully
        if ($post_id !== 0 && !is_wp_error($post_id)) {
            $this->productMetaEdits($post_id, $product, true);
        }

    }

    private function productMetaEdits($post_id, $product, $is_creation = false){
        carbon_set_post_meta($post_id, 'default_price', $product->DefaultSellPrice);

        for ($i = 1; $i <= 10; $i++) {
            $unleashed_tier_key = 'SellPriceTier' . $i;
            $tier_key = 'sellpricetier' . $i;
            $tier_value = $product->{$unleashed_tier_key}->Value;
            carbon_set_post_meta($post_id, $tier_key, $tier_value);
        }

        carbon_set_post_meta($post_id, 'unleashed_product_code', $product->ProductCode);
        carbon_set_post_meta($post_id, 'unleashed_guid', $product->Guid);
        carbon_set_post_meta($post_id, 'unleashed_barcode', $product->Barcode);
        if($product->ProductGroup){
            carbon_set_post_meta($post_id, 'unleashed_product_group', $product->ProductGroup->GroupName);
        }
        carbon_set_post_meta($post_id, 'unleashed_product_description', $product->ProductDescription);
        carbon_set_post_meta($post_id, 'unleashed_image_url', $product->ImageUrl);
        carbon_set_post_meta($post_id, 'unleashed_notes', $product->Notes);
        carbon_set_post_meta($post_id, 'unleashed_sellable', $product->IsSellable);
        carbon_set_post_meta($post_id, 'unleashed_minimum_sale_quantity', $product->MinimumSaleQuantity);
        carbon_set_post_meta($post_id, 'unleashed_minimum_order_quantity', $product->MinimumOrderQuantity);
        
        // woocommerce code
        update_post_meta($post_id, '_sku', $product->ProductCode);
        update_post_meta($post_id, '_global_unique_id', $product->Barcode);
        
        if($is_creation){
            carbon_set_post_meta($post_id, 'visible', false);
        }
    }


    private function getWPQueryBasedOnMetaArgs($guid){
        return [
            'post_type' => 'product',
            'posts_per_page' => -1,
            'meta_query' => [
                [
                    'key' => 'unleashed_guid',
                    'value' => $guid,
                    'compare' => '='
                ]
            ]
        ];
    }

    private function sync_products() {
        $runs = 0;

        foreach($this->products_to_sync as $product){

            // We must apply product group filtering here
            if(count($this->filtered_product_groups) > 0){
                if(!$product->ProductGroup){
                    continue;
                }

                if(!in_array(strtolower($product->ProductGroup->GroupName), $this->filtered_product_groups)){
                    continue;
                }   
            }
            
            $query = new WP_Query($this->getWPQueryBasedOnMetaArgs($product->Guid));
            if ($query->have_posts()) {
                $post_id = $query->posts[0]->ID;
                $this->productMetaEdits($post_id, $product, false);
            } else {
                $this->create_product($product);
            }
            $runs++;
        }
    }

    private function sync_product_prices() {
        $runs = 0;
        $customer_pricing = [];

        foreach($this->product_prices_to_sync as $product_price) {
            // Find the product it belongs to -> Need to have an existing product in order to update prices
            $product_guid = $product_price->Product->Guid;
            $query = new WP_Query($this->getWPQueryBasedOnMetaArgs($product_guid));

            if ($query->have_posts()) {
                $post_id = $query->posts[0]->ID;
                if (!array_key_exists($post_id, $customer_pricing)) {
                    $customer_pricing[$post_id] = [];
                }

                if ($product_price->Customer !== null) {
                    if (!array_key_exists($product_price->Customer->Guid, $customer_pricing[$post_id])) {
                        $customer_pricing[$post_id][$product_price->Customer->Guid] = [];
                    }

                    $customer_pricing[$post_id][$product_price->Customer->Guid][] = [
                        'MinQty' => $product_price->MinimumQuantity ?? 0,
                        'Price' => $product_price->CustomerPrice
                    ];
                } else {
                    if (!array_key_exists('general', $customer_pricing[$post_id])) {
                        $customer_pricing[$post_id]['general'] = [];
                    }

                    $customer_pricing[$post_id]['general'][] = [
                        'MinQty' => $product_price->MinimumQuantity ?? 0,
                        'Price' => $product_price->CustomerPrice
                    ];
                }
            }
            $runs++;
        }

        foreach($customer_pricing as $product_id => $customer_price) {
            $stringified_price = json_encode($customer_price);
            carbon_set_post_meta($product_id, 'customer_prices', $stringified_price);
        }
    }

    private function sync_stock() {
        $runs = 0;
        foreach($this->stock_to_sync as $stock) {
            $product_guid = $stock->ProductGuid;

            $query = new WP_Query($this->getWPQueryBasedOnMetaArgs($product_guid));
            if ($query->have_posts()) {
                /**
                 * @todo
                 * AllocatedQty vs AvailableQty vs QtyOnHand?
                 * OnPurchase?
                 * Which of the above do we need?
                 */
                $post_id = $query->posts[0]->ID;
                carbon_set_post_meta($post_id, 'unleashed_available_qty', $stock->AvailableQty);
                // woocommerce code
                update_post_meta($post_id, '_manage_stock', 'yes');
                update_post_meta($post_id, '_stock', $stock->AvailableQty);
            }
            $runs++;
        }
    }
}





