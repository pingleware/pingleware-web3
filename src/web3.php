<?php

namespace PINGLEWARE\Web3;


class Web3 {
    var $_url = "";

    public function __construct($url) {
        $this->_url = $url;
    }

    /**
     * eth_ functions
     */

    public function eth_getTransactionCount($fromAddress) {
        return file_get_contents($this->_url, false, stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => 'Content-type: application/json',
                'content' => json_encode([
                    'jsonrpc' => '2.0',
                    'id' => 1,
                    'method' => 'eth_getTransactionCount',
                    'params' => [$fromAddress, 'latest'],
                ]),
            ],
        ]));
    }

    /**
     * $rawTransaction = [
     *  'nonce' => $nonce,
     *  'gasPrice' => $gasPrice,
     *  'gasLimit' => $gasLimit,
     *  'to' => $contractAddress,
     *  'value' => '0x0', // No Ether sent with method call
     *  'data' => $transactionData,
     * ];
     */
    public function eth_sendRawTransaction($rawTransaction) {
        // Serialize the raw transaction
        $serializedTransaction = '0x' . implode('', array_map('bin2hex', $rawTransaction));

        // Construct the JSON-RPC request to send the signed transaction
        $requestData = json_encode([
            'jsonrpc' => '2.0',
            'id' => 1,
            'method' => 'eth_sendRawTransaction',
            'params' => [$serializedTransaction],
        ]);

        return $this->curl($requestData);
    }

    public function eth_getBalance($wallet) {
        // JSON-RPC request data
        $requestData = json_encode([
            'jsonrpc' => '2.0',
            'id' => 1,
            'method' => 'eth_getBalance',
            'params' => [$wallet, 'latest'], // Replace 'latest' with block number or block tag if needed
        ]);

        $response = $this->curl($requestData);

        // Decode the JSON response
        $responseData = json_decode($response, true);

        // Check if the response is successful
        if (isset($responseData['result'])) {
            // Convert the hexadecimal balance to wei and then to ether
            $balanceHex = $responseData['result'];
            $balanceWei = hexdec($balanceHex);
            $balanceEther = $balanceWei / 1e18;

            return $balanceEther;
        } elseif (isset($responseData['error'])) {
            throw new Error($responseData['error']['message']);
        } else {
            throw new Error('Unknown Error');
        }
    }

    /**
     * helpers
     */

    public function getNonce($wallet) {
        return '0x' . bin2hex( $this->eth_getTransactionCount($wallet) );
    }

    public function getBalance($wallet) {
        return $this->eth_getBalance($wallet);
    }

    public function callContractMethod($wallet,$contractAddress,$abi_filename,$methodName,$methodParams,$gasLimit,$gasPrice) {
        $contractAbi = file_get_contents($abi_filename);

        // Construct the data for the method call
        $contract = new stdClass();
        $contract->address = $contractAddress;
        $contract->abi = json_decode($contractAbi);

        $web3 = new stdClass();
        $web3->eth = new stdClass();
        $web3->eth->contract = $contract;

        $transactionData = $web3->eth->contract
            ->at($contractAddress)
            ->$methodName(...$methodParams)
            ->getData();
        
        $nonce = $this->getNonce($wallet);

        $rawTransaction = [
            'nonce' => $nonce,
            'gasPrice' => $gasPrice,
            'gasLimit' => $gasLimit,
            'to' => $contractAddress,
            'value' => '0x0', // No Ether sent with method call
            'data' => $transactionData,
        ];
        
        $response = $this->eth_sendRawTransaction($rawTransaction);

        // Decode the JSON response
        $responseData = json_decode($response, true);

        // Check if the response is successful
        if (isset($responseData['result'])) {
            $transactionHash = $responseData['result'];
            return $transactionHash;
        } elseif (isset($responseData['error'])) {
            throw new Error($responseData['error']['message']);
        } else {
            throw new Error('Unknown Error');
        }
    }

    /**
     * string $constructor_arguments 0xbd7a53B05592497624d71f0fF9e12AdCc20c69d6,FL,REG 3(a)(11),REG3A11,4000000,5,0xFDf076CF850f67103A13a347f968305cE85831E2,0xB5466EC8A290913dB16C639e4cEF98C1411e0b9F,0x6336972E8F3AaCcF4b1Fbc0913708255Fc2EeB6F,0x84ce497f283E659c0a2db85C8Fd008F319CEfF73
     */
    public function deployContract($wallet,$abi_filename,$bytecode_filename,$contructor_arguments) {
        exec("pingleware-deploy-cli --url=$this->_url --account=$wallet --abi=$abi_filename --bytecode=$bytecode_filename --arguments='$contructor_arguments'", $contractAddress);
        return $contractAddress;
    }

    private function curl($request) {
        // Set up the HTTP headers
        $headers = [
            'Content-Type: application/json',
            'Content-Length: ' . strlen($request),
        ];

        // Initialize cURL session
        $ch = curl_init($this->_url);

        // Set cURL options
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_POSTFIELDS, $request);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        // Execute cURL session and get the response
        $response = curl_exec($ch);

        // Check for cURL errors
        if (curl_errno($ch)) {
            echo 'cURL Error: ' . curl_error($ch);
            exit;
        }

        // Close cURL session
        curl_close($ch);

        // Decode the JSON response
        //$responseData = json_decode($response, true);
        return $response;
    }

    private function detectArchitecture() {
        $uname = php_uname('m'); // Get machine hardware name from uname
    
        // Check if the machine hardware name contains 'arm' (case-insensitive)
        if (stripos($uname, 'arm') !== false) {
            return 'ARM';
        } elseif (stripos($uname, 'x86_64') !== false || stripos($uname, 'amd64') !== false) {
            return 'x64'; // x86_64 or amd64 typically indicates a 64-bit Intel/AMD architecture
        } else {
            return 'Unknown';
        }
    }
}