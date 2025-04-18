<?php declare(strict_types=1);
/**
 * Copyright © 2024 cclilshy
 * Email: jingnigg@gmail.com
 *
 * This software is licensed under the MIT License.
 * For full license details, please visit: https://opensource.org/licenses/MIT
 *
 * By using this software, you agree to the terms of the license.
 * Contributions, suggestions, and feedback are always welcome!
 */

namespace Ripple\Http\Enum;

enum Status
{
    public const CONTINUE                        = 100;
    public const SWITCHING_PROTOCOLS             = 101;
    public const PROCESSING                      = 102;
    public const EARLY_HINTS                     = 103;
    public const OK                              = 200;
    public const CREATED                         = 201;
    public const ACCEPTED                        = 202;
    public const NON_AUTHORITATIVE_INFORMATION   = 203;
    public const NO_CONTENT                      = 204;
    public const RESET_CONTENT                   = 205;
    public const PARTIAL_CONTENT                 = 206;
    public const MULTI_STATUS                    = 207;
    public const ALREADY_REPORTED                = 208;
    public const IM_USED                         = 226;
    public const MULTIPLE_CHOICES                = 300;
    public const MOVED_PERMANENTLY               = 301;
    public const FOUND                           = 302;
    public const SEE_OTHER                       = 303;
    public const NOT_MODIFIED                    = 304;
    public const USE_PROXY                       = 305;
    public const SWITCH_PROXY                    = 306;
    public const TEMPORARY_REDIRECT              = 307;
    public const PERMANENT_REDIRECT              = 308;
    public const BAD_REQUEST                     = 400;
    public const UNAUTHORIZED                    = 401;
    public const PAYMENT_REQUIRED                = 402;
    public const FORBIDDEN                       = 403;
    public const NOT_FOUND                       = 404;
    public const METHOD_NOT_ALLOWED              = 405;
    public const NOT_ACCEPTABLE                  = 406;
    public const PROXY_AUTHENTICATION_REQUIRED   = 407;
    public const REQUEST_TIMEOUT                 = 408;
    public const CONFLICT                        = 409;
    public const GONE                            = 410;
    public const LENGTH_REQUIRED                 = 411;
    public const PRECONDITION_FAILED             = 412;
    public const PAYLOAD_TOO_LARGE               = 413;
    public const URI_TOO_LONG                    = 414;
    public const UNSUPPORTED_MEDIA_TYPE          = 415;
    public const RANGE_NOT_SATISFIABLE           = 416;
    public const EXPECTATION_FAILED              = 417;
    public const IM_A_TEAPOT                     = 418;
    public const MISDIRECTED_REQUEST             = 421;
    public const UNPROCESSABLE_ENTITY            = 422;
    public const LOCKED                          = 423;
    public const FAILED_DEPENDENCY               = 424;
    public const TOO_EARLY                       = 425;
    public const UPGRADE_REQUIRED                = 426;
    public const PRECONDITION_REQUIRED           = 428;
    public const TOO_MANY_REQUESTS               = 429;
    public const REQUEST_HEADER_FIELDS_TOO_LARGE = 431;
    public const UNAVAILABLE_FOR_LEGAL_REASONS   = 451;
    public const INTERNAL_SERVER_ERROR           = 500;
    public const NOT_IMPLEMENTED                 = 501;
    public const BAD_GATEWAY                     = 502;
    public const SERVICE_UNAVAILABLE             = 503;
    public const GATEWAY_TIMEOUT                 = 504;
    public const HTTP_VERSION_NOT_SUPPORTED      = 505;
    public const VARIANT_ALSO_NEGOTIATES         = 506;
    public const INSUFFICIENT_STORAGE            = 507;
    public const LOOP_DETECTED                   = 508;
    public const NOT_EXTENDED                    = 510;
    public const NETWORK_AUTHENTICATION_REQUIRED = 511;

    public const MESSAGES = [
        Status::CONFLICT                        => 'Conflict',
        Status::CREATED                         => 'Created',
        Status::OK                              => 'OK',
        Status::BAD_REQUEST                     => 'Bad Request',
        Status::FORBIDDEN                       => 'Forbidden',
        Status::GONE                            => 'Gone',
        Status::INTERNAL_SERVER_ERROR           => 'Internal Server Error',
        Status::METHOD_NOT_ALLOWED              => 'Method Not Allowed',
        Status::MOVED_PERMANENTLY               => 'Moved Permanently',
        Status::MULTIPLE_CHOICES                => 'Multiple Choices',
        Status::NOT_FOUND                       => 'Not Found',
        Status::NOT_IMPLEMENTED                 => 'Not Implemented',
        Status::NOT_MODIFIED                    => 'Not Modified',
        Status::PAYLOAD_TOO_LARGE               => 'Payload Too Large',
        Status::SERVICE_UNAVAILABLE             => 'Service Unavailable',
        Status::UNAUTHORIZED                    => 'Unauthorized',
        Status::UNSUPPORTED_MEDIA_TYPE          => 'Unsupported Media Type',
        Status::ACCEPTED                        => 'Accepted',
        Status::ALREADY_REPORTED                => 'Already Reported',
        Status::EARLY_HINTS                     => 'Early Hints',
        Status::EXPECTATION_FAILED              => 'Expectation Failed',
        Status::FAILED_DEPENDENCY               => 'Failed Dependency',
        Status::IM_A_TEAPOT                     => 'I\'m a teapot',
        Status::IM_USED                         => 'IM Used',
        Status::INSUFFICIENT_STORAGE            => 'Insufficient Storage',
        Status::LENGTH_REQUIRED                 => 'Length Required',
        Status::LOCKED                          => 'Locked',
        Status::LOOP_DETECTED                   => 'Loop Detected',
        Status::MULTI_STATUS                    => 'Multi-Status',
        Status::NETWORK_AUTHENTICATION_REQUIRED => 'Network Authentication Required',
        Status::NON_AUTHORITATIVE_INFORMATION   => 'Non-Authoritative Information',
        Status::NOT_ACCEPTABLE                  => 'Not Acceptable',
        Status::NOT_EXTENDED                    => 'Not Extended',
        Status::NO_CONTENT                      => 'No Content',
        Status::PARTIAL_CONTENT                 => 'Partial Content',
        Status::PAYMENT_REQUIRED                => 'Payment Required',
        Status::PERMANENT_REDIRECT              => 'Permanent Redirect',
        Status::PRECONDITION_FAILED             => 'Precondition Failed',
        Status::PRECONDITION_REQUIRED           => 'Precondition Required',
        Status::PROCESSING                      => 'Processing',
        Status::PROXY_AUTHENTICATION_REQUIRED   => 'Proxy Authentication Required',
        Status::RANGE_NOT_SATISFIABLE           => 'Range Not Satisfiable',
        Status::RESET_CONTENT                   => 'Reset Content',
        Status::SEE_OTHER                       => 'See Other',
        Status::SWITCHING_PROTOCOLS             => 'Switching Protocols',
        Status::TEMPORARY_REDIRECT              => 'Temporary Redirect',
        Status::TOO_EARLY                       => 'Too Early',
        Status::TOO_MANY_REQUESTS               => 'Too Many Requests',
        Status::UNAVAILABLE_FOR_LEGAL_REASONS   => 'Unavailable For Legal Reasons',
        Status::UNPROCESSABLE_ENTITY            => 'Unprocessable Entity',
        Status::UPGRADE_REQUIRED                => 'Upgrade Required',
        Status::URI_TOO_LONG                    => 'URI Too Long',
        Status::VARIANT_ALSO_NEGOTIATES         => 'Variant Also Negotiates',
        Status::FOUND                           => 'Found',
        Status::USE_PROXY                       => 'Use Proxy',
        Status::SWITCH_PROXY                    => 'Switch Proxy',
        Status::REQUEST_TIMEOUT                 => 'Request Timeout',
        Status::MISDIRECTED_REQUEST             => 'Misdirected Request',
        Status::REQUEST_HEADER_FIELDS_TOO_LARGE => 'Request Header Fields Too Large',
        Status::BAD_GATEWAY                     => 'Bad Gateway',
        Status::GATEWAY_TIMEOUT                 => 'Gateway Timeout',
        Status::HTTP_VERSION_NOT_SUPPORTED      => 'HTTP Version Not Supported',
    ];
}
