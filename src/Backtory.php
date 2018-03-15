<?php
namespace VSG24\Backtory;

class Backtory
{
    static $BASE_REQUESTS_ADDR = 'https';

    static $BacktoryAuthenticationIdHeaderKey = 'X-Backtory-Authentication-Id';
    static $BacktoryAuthenticationId = '';

    static $BacktoryAuthenticationKeyHeaderKey = 'X-Backtory-Authentication-Key';
    static $BacktoryAuthenticationKeyClient = '';
    static $BacktoryAuthenticationKeyMaster = '';

    static $BacktoryStorageIdHeaderKey = 'X-Backtory-Storage-Id';
    static $BacktoryStorageId = '';

    static $BacktoryAuthorizationToken = '';

    static $httpErrors = false;

    static $proxyUrl = "";

    static function disableHttpsForRequests() {
        self::$BASE_REQUESTS_ADDR = 'http';
    }

    static function enableHttpsForRequests() {
        self::$BASE_REQUESTS_ADDR = 'https';
    }
}