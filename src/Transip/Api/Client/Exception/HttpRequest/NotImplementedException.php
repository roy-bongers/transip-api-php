<?php

namespace Transip\Api\Client\Exception\HttpRequest;

use Transip\Api\Client\Exception\HttpRequestException;

class NotImplementedException extends HttpRequestException
{
    CONST STATUS_CODE = 501;
}
