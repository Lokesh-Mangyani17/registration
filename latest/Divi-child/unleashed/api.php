<?php 
require_once __DIR__ .'/../vendor/autoload.php';

use GuzzleHttp\Client;
class UnleashedApi
{
    private $api_id;
    private $api_key;
    private $supported_routes;
    private $client;

    public function __construct()
    {
        $this->api_id = carbon_get_theme_option('unleashed_api_id');
        $this->api_key = carbon_get_theme_option('unleashed_api_key');
        $this->supported_routes = [
            'Products',
            'ProductPrices',
            'Customers',
            'Warehouses'
        ];
        $this->client = new Client([
            'base_uri' => 'https://api.unleashedsoftware.com',
            'timeout' => 30.0,
        ]);
    }

    private function get_signature($request, $key)
    {
        return base64_encode(hash_hmac('sha256', $request, $key, true));
    }

    public function get_all($route)
    {
        if (!$this->api_id || !$this->api_key) {
            // DO NOT continue if pointing at production code
            return;
        }
        if(!in_array($route, $this->supported_routes)){
            throw new Exception('Unsupported Route.');
            return;
        }
        $items = [];
        $page_number = 1;
        
        do
        {
            $response = $this->client->request('GET', "/$route/$page_number", [
                'headers' => [
                    'api-auth-id' => $this->api_id,
                    'api-auth-signature' => $this->get_signature("", $this->api_key),
                    'Content-Type' => 'application/json',
					'client-type' => 'nubupharma/wordpress',
                    'Accept' => 'application/json',
                ]
            ]);

            $body = $response->getBody();
            $json = json_decode((string)$body);
            $items = array_merge($items, $json->Items);
            $page_number++;
        } while ( $page_number <= $json->Pagination->NumberOfPages);

        return $items;


    }

    public function get_products()
    {
        return $this->get_all('Products');
    }
    public function get_product_prices()
    {
        return $this->get_all('ProductPrices');
    }

    public function get_stock()
    {
        if (!$this->api_id || !$this->api_key) {
            // DO NOT continue if pointing at production code
            return;
        }
        $query = "warehouseCode=RM-11&pageSize=1000";
        
        $response = $this->client->request('GET', "/StockOnHand?$query", [
            'headers' => [
                'api-auth-id' => $this->api_id,
                'api-auth-signature' => $this->get_signature($query, $this->api_key),
                'Content-Type' => 'application/json',
				'client-type' => 'nubupharma/wordpress',
                'Accept' => 'application/json',
            ]
        ]);

        $body = $response->getBody();
        $json = json_decode((string)$body);
        return $json->Items;
    }

    public function get_customers()
    {
        return $this->get_all('Customers');
    }

    public function get_warehouses()
    {
        return $this->get_all('Warehouses');
    }

    public function get_customer_delivery_addresses($customer_code)
    {
        $queryVar = "customerCode=$customer_code";
        $response = $this->client->request('GET', "/CustomerDeliveryAddresses?$queryVar", [
                'headers' => [
                    'api-auth-id' => $this->api_id,
                    'api-auth-signature' => $this->get_signature($queryVar, $this->api_key),
                    'Content-Type' => 'application/json',
					'client-type' => 'nubupharma/wordpress',
                    'Accept' => 'application/json',
                ]
            ]);
        $body = $response->getBody();
        $json = json_decode((string)$body);
        return $json->Items;
    }

    public function get_customer_by_customer_code($customer_code) 
    {
        $queryVar = "customerCode=$customer_code";
        $response = $this->client->request('GET', "/Customers?$queryVar", [
            'headers' => [
                'api-auth-id' => $this->api_id,
                'api-auth-signature' => $this->get_signature($queryVar, $this->api_key),
                'Content-Type' => 'application/json',
				'client-type' => 'nubupharma/wordpress',
                'Accept' => 'application/json'
            ]
        ]);
        $body = $response->getBody();
        $json = json_decode((string)$body);

        // Now have $customer->Guid, $customer->SellPriceTierReference->Reference (eg. SellPriceTier3)
        $customer = $json->Items[0] ?? null;
        return $customer;
    }
    
    public function create_sales_order($sales_order)
    {
        if (!$this->api_id || !$this->api_key) {
            // DO NOT continue if pointing at production code
            return;
        }

        try {
            $response = $this->client->request('POST', "/SalesOrders/$sales_order->Guid", [
                'headers' => [
                    'api-auth-id' => $this->api_id,
                    'api-auth-signature' => $this->get_signature('', $this->api_key),
                    'Content-Type' => 'application/json',
					'client-type' => 'nubupharma/wordpress',
                    'Accept' => 'application/json'
                ],
                'body' => json_encode($sales_order)
            ]);

            // Successful response:
            // $response->statusCode = 201
            // $response->reasonPhrase = "Created"
            return $response->getStatusCode() == 201;
        } catch (Exception $e) {
            error_log('Error: ' . $e->getMessage());
            return false;
        }
    }
}