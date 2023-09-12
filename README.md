# PHP Library for Web3

A minimal Web3 composer library for interacting with a blockchain including GANACHE.

## Prerequisites

The following packages must be installed prior to use,

PHP 7.8,

    sudo apt install php

CURL,

    sudo app install curl

pingleware-deploy-cli

    sudo snapcraft install pingleware-deploy-cli


## Sample code

```
    require "vendor/autoload.php";

    use PINGLEWARE\Web3\Web3;

    $web3 = null;
    $arch = Web3::detectArchitecture();
    $os = strtolower(PHP_OS);
    if ($arch == "ARM") {
        $web3 = new Web3("http://192.168.0.103:8545",dirname(__FILE__)."/pingleware-deploy-cli_$os-arm64");
    } else if ($arch == "x64") {
        $web3 = new Web3("http://192.168.0.103:8545",dirname(__FILE__)."/pingleware-deploy-cli_$os-x64");
    } else {
        echo "architecture is UNKNOWN?\n";
        exit;
    }
  
    echo 'Balance: '.$web3->eth_getBalance("0x5EaF72deD2e4E255C228f9070501974D3572c5d4")." ETH\n";


```
