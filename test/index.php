<?php

require '../src/web3.php';

use PINGLEWARE\Web3\Web3;

try {
    $web3 = null;
    $arch = Web3::detectArchitecture();
    $os = strtolower(PHP_OS);
    if ($arch == "ARM") {
        $web3 = new Web3("http://192.168.0.103:8545",dirname(__FILE__)."../bin/pingleware-deploy-cli_$os-arm64");
    } else if ($arch == "x64") {
        $web3 = new Web3("http://192.168.0.103:8545",dirname(__FILE__)."../bin/pingleware-deploy-cli_$os-x64");
    } else {
        echo "architecture is UNKNOWN?\n";
        exit;
    }
    
    echo 'Balance: '.$web3->eth_getBalance("0x5EaF72deD2e4E255C228f9070501974D3572c5d4")." ETH\n";

    $contractAddress = $web3->deployContract("0x5EaF72deD2e4E255C228f9070501974D3572c5d4","./reg3a11.json","./reg3a11.bin","0xbd7a53B05592497624d71f0fF9e12AdCc20c69d6,FL,REG 3(a)(11),REG3A11,4000000,5,0xFDf076CF850f67103A13a347f968305cE85831E2,0xB5466EC8A290913dB16C639e4cEF98C1411e0b9F,0x6336972E8F3AaCcF4b1Fbc0913708255Fc2EeB6F,0x84ce497f283E659c0a2db85C8Fd008F319CEfF73");
    echo 'Contract Address: ' . $contractAddress[0] . "\n";   
} catch(Exception $ex) {
    echo $ex->getMessage();
}
