<?php

namespace PINGLEWARE\Web3;

use JsonRPC\Client;

class Web3 {

    public function __construct() {
    }

    public function json_rpc($url,$method_name,$args) {
        // Create a JSON-RPC client
        $client = new Client($url);
        // Define the JSON-RPC request
        $request = $client->newRequest($method_name,$args);
        try {
            // Send the JSON-RPC request and get the result
            $response = $client->send($request);
        
            // Check if the response is successful
            if ($response->isError()) {
                throw new Error($response->getErrorMessage());
            } else {
                // Access the result of the JSON-RPC call
                $result = $response->getResult();
                return json_encode($result);
            }
        } catch (Exception $e) {
            throw new Error($e->getMessage());
        }
    }

}