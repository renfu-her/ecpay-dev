<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class IndexController extends Controller
{

    /*
    * 測試環境
    * 特店編號 (MerchantID)：3002607
    * 特店後台登入帳號：stagetest3
    * 特店後台登入密碼：test1234
    * 特店統一編號：00000000
    * 串接金鑰 HashKey：pwFHCqoQZGmho4w6
    * 串接金鑰 HashIV：EkRm7iFT261dpevs
    */
    // index method
    public function index()
    {

        $merchantID = '3002607';
        $hashKey = 'pwFHCqoQZGmho4w6';
        $hashIV = 'EkRm7iFT261dpevs';

        $jsonString = [
            "MerchantID" => $merchantID,
            "RememberCard" => 1,
            "PaymentUIType" => 2,
            "ChoosePaymentList" => "1,2,3",
            "OrderInfo" => [
                "MerchantTradeNo" => date('YmdHis'),
                "MerchantTradeDate" => date('Y/m/d H:i:s'),
                "TotalAmount" => 100,
                "ReturnURL" => env('APP_URL') . '/ecpay',
            ],
            "CardInfo" => [
                "OrderResultURL" => "https://yourOrderResultURL.com",
                "CreditInstallment" => "3,6,9,12"
            ],
            "ATMInfo" => [
                "ExpireDate" => 3
            ],
            "ConsumerInfo" => [
                "MerchantMemberID" => "test123456",
                "Email" => "customer@email.com",
                "Phone" => "0912345678",
                "Name" => "Test",
                "CountryCode" => "158"
            ]
        ];

        $strURLEncode = urlencode(json_encode($jsonString));

        $result = preg_replace_callback('/%[A-Fa-f0-9]{2}/', function ($matches) {
            return strtolower($matches[0]);
        }, $strURLEncode);

        $strURLDecode = urldecode($strURLEncode);

        $strEncrypt = $this->encrypt($strURLEncode, $hashKey, $hashIV);
        $strDecrypt = $this->decrypt($strEncrypt, $hashKey, $hashIV);
        $strDecryptDecode = urldecode($strDecrypt);

        dd($result, $strURLDecode, $strEncrypt, $strDecrypt, $strDecryptDecode);

        $res = Http::post('https://ecpg-stage.ecpay.com.tw/Merchant/GetTokenbyTrade', [
            'MerchantID' => $merchantID,
            "RqHeader" => [
                "Timestamp" => time()
            ],
            "Data" => $result
        ]);

        dd($res->body(), $jsonString);
        return view('index');
    }

    // ecpay method
    public function ecpay()
    {
        return view('ecpay');
    }

    // 加密

    function encrypt($data, $key, $iv)
    {

        // 先對資料進行 URL Encode
        $data = urlencode($data);

        // 使用 AES-128-CBC 進行加密
        $encrypted = openssl_encrypt($data, 'aes-128-cbc', $key, 0, $iv);

        return $encrypted;
    }

    // 解密
    function decrypt($encryptedData, $key, $iv)
    {

        // 使用 AES-128-CBC 進行解密
        $decrypted = openssl_decrypt($encryptedData, 'aes-128-cbc', $key, 0, $iv);

        // 對解密後的資料進行 URL Decode
        $decrypted = urldecode($decrypted);

        return $decrypted;
    }
}
