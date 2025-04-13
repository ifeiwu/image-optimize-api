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

namespace Ripple\Http\Server;

use Closure;
use Ripple\Http\Enum\Method;
use Ripple\Http\Server\Exception\FormatException;
use Ripple\Http\Server\Upload\MultipartHandler;
use Ripple\Socket;
use Ripple\Stream\Exception\RuntimeException;
use Ripple\Utils\Output;
use Throwable;

use function array_merge;
use function count;
use function explode;
use function in_array;
use function intval;
use function is_array;
use function is_string;
use function json_decode;
use function max;
use function parse_str;
use function parse_url;
use function preg_match;
use function rawurldecode;
use function str_contains;
use function str_replace;
use function strlen;
use function strpos;
use function strtok;
use function strtoupper;
use function substr;

use const PHP_URL_PATH;

class Connection
{
    /*** @var int */
    private int $step;

    /*** @var array */
    private array $query;

    /*** @var array */
    private array $request;

    /*** @var array */
    private array $attributes;

    /*** @var array */
    private array $cookies;

    /*** @var array */
    private array $files;

    /*** @var array */
    private array $server;

    /*** @var string */
    private string $content;

    /*** @var string */
    private string $buffer = '';

    /*** @var MultipartHandler|null */
    private MultipartHandler|null $multipartHandler;

    /*** @var int */
    private int $bodyLength;

    /*** @var int */
    private int $contentLength;

    /**
     * @param Socket $stream
     */
    public function __construct(private readonly Socket $stream)
    {
        $this->reset();
    }

    /**
     * @return void
     */
    private function reset(): void
    {
        $this->step             = 0;
        $this->query            = array();
        $this->request          = array();
        $this->attributes       = array();
        $this->cookies          = array();
        $this->files            = array();
        $this->server           = array();
        $this->content          = '';
        $this->multipartHandler = null;
        $this->bodyLength       = 0;
        $this->contentLength    = 0;
    }

    /**
     * @param Closure $builder
     *
     * @return void
     */
    public function listen(Closure $builder): void
    {
        $this->stream->onClose(function () {
            if (isset($this->multipartHandler)) {
                $this->multipartHandler->cancel();
            }
        });

        $this->stream->onReadable(function (Socket $stream) use ($builder) {
            try {
                $content = $stream->read(8192);
            } catch (Throwable) {
                $stream->close();
                return;
            }

            if ($content === '') {
                if ($stream->eof()) {
                    $stream->close();
                }
                return;
            }

            try {
                foreach ($this->tick($content) as $requestInfo) {
                    $builder($requestInfo);
                }
            } catch (Throwable $exception) {
                Output::warning($exception->getMessage());
                $stream->close();
                return;
            }
        });
    }

    /**
     * @param string $content
     *
     * @return array
     * @throws FormatException
     * @throws RuntimeException
     */
    private function tick(string $content): array
    {
        $list = [];

        $this->buffer .= $content;

        if ($this->step === 0) {
            $this->handleInitialStep();
        }

        if ($this->step === 1) {
            $this->handleContinuousTransfer();
        }

        if ($this->step === 3) {
            $this->handleFileTransfer();
        }

        if ($this->step === 2) {
            $list[] = $this->finalizeRequest();
            if ($this->buffer !== '') {
                foreach ($this->tick('') as $item) {
                    $list[] = $item;
                }
            }
        }

        return $list;
    }

    /**
     * @return void
     * @throws FormatException
     * @throws RuntimeException
     */
    private function handleInitialStep(): void
    {
        if ($headerEnd = strpos($this->buffer, "\r\n\r\n")) {
            $buffer = $this->readBuffer();

            $this->step = 1;
            $header     = substr($buffer, 0, $headerEnd);
            $firstLine  = strtok($header, "\r\n");

            if (count($base = explode(' ', $firstLine)) !== 3) {
                throw new RuntimeException('Request head is not match: ' . $firstLine);
            }

            $this->initializeRequestParams($base);
            $this->parseHeaders();
            if ($this->server['REQUEST_METHOD'] === 'GET') {
                $body         = '';
                $this->buffer = substr($buffer, $headerEnd + 4);
            } else {
                $body = substr(
                    $buffer,
                    $headerEnd + 4,
                    max(0, $this->contentLength - $this->bodyLength)
                );

                $this->buffer     = substr($buffer, $headerEnd + 4 + strlen($body));
                $this->bodyLength += strlen($body);
            }

            $this->handleRequestBody($base[0], $body);
        }
    }

    /**
     * @param int $length
     *
     * @return string
     */
    private function readBuffer(int $length = 0): string
    {
        if ($length === 0) {
            $buffer       = $this->buffer;
            $this->buffer = '';
            return $buffer;
        }
        $buffer       = substr($this->buffer, 0, $length);
        $this->buffer = substr($this->buffer, $length);
        return $buffer;
    }

    /**
     * @param array $base
     *
     * @return void
     */
    private function initializeRequestParams(array $base): void
    {
        $method  = $base[0];
        $url     = $base[1];
        $version = $base[2];

        $urlExploded = explode('?', $url);
        $path        = parse_url($url, PHP_URL_PATH);

        if (isset($urlExploded[1])) {
            $this->parseQuery($urlExploded[1]);
        }

        $this->server['REQUEST_URI']     = $path;
        $this->server['REQUEST_METHOD']  = $method;
        $this->server['SERVER_PROTOCOL'] = $version;
    }

    /**
     * @param string $queryString
     *
     * @return void
     */
    private function parseQuery(string $queryString): void
    {
        $queryArray = explode('&', $queryString);
        foreach ($queryArray as $item) {
            $item = explode('=', $item);
            if (count($item) === 2) {
                $this->query[$item[0]] = $item[1];
            }
        }
    }

    /**
     * @return void
     */
    private function parseHeaders(): void
    {
        while ($line = strtok("\r\n")) {
            $lineParam = explode(': ', $line, 2);
            if (count($lineParam) >= 2) {
                $this->server['HTTP_' . str_replace('-', '_', strtoupper($lineParam[0]))] = $lineParam[1];
            }
        }
    }

    /**
     * @param string $method
     * @param string $body
     *
     * @return void
     * @throws FormatException
     * @throws RuntimeException
     */
    private function handleRequestBody(string $method, string $body): void
    {
	    $methodEnum = Method::tryFrom($method);

	    if (null === $methodEnum) {
		    $this->handleOtherMethods();
	    } elseif ($methodEnum->hasBody()) {
		    $this->handlePostRequest($body);
	    } else {
		    $this->bodyLength = 0;
		    $this->step = 2;
	    }
    }

    /**
     * @param string $body
     *
     * @return void
     * @throws FormatException
     * @throws RuntimeException
     */
    private function handlePostRequest(string $body): void
    {
        $contentType = $this->server['HTTP_CONTENT_TYPE'] ?? '';
        if (!isset($this->server['HTTP_CONTENT_LENGTH'])) {
            throw new RuntimeException('Content-Length is not set 1');
        }
        $this->contentLength = intval($this->server['HTTP_CONTENT_LENGTH']);
        if (str_contains($contentType, 'multipart/form-data')) {
            $this->handleMultipartFormData($body, $contentType);
        } else {
            $this->content = $body;
        }
        $this->validateContentLength();
    }

    /**
     * @param string $body
     * @param string $contentType
     *
     * @return void
     * @throws FormatException
     * @throws RuntimeException
     */
    private function handleMultipartFormData(string $body, string $contentType): void
    {
        preg_match('/boundary=(.*)$/', $contentType, $matches);
        if (!isset($matches[1])) {
            throw new RuntimeException('boundary is not set');
        }

        $this->step = 3;
        if (!isset($this->multipartHandler)) {
            $this->multipartHandler = new MultipartHandler($matches[1]);
        }

        foreach ($this->multipartHandler->tick($body) as $name => $multipartResult) {
            if (is_string($multipartResult)) {
                $this->request[$name] = $multipartResult;
            } elseif (is_array($multipartResult)) {
                foreach ($multipartResult as $file) {
                    $this->files[$name][] = $file;
                }
            }
        }

        $this->validateContentLength();
    }

    /**
     * @return void
     * @throws RuntimeException
     */
    private function validateContentLength(): void
    {
        if ($this->bodyLength === $this->contentLength) {
            $this->step = 2;
        } elseif ($this->bodyLength > $this->contentLength) {
            throw new RuntimeException('Content-Length is not match');
        }
    }

    /**
     * @return void
     * @throws RuntimeException
     */
    private function handleOtherMethods(): void
    {
        if (!isset($this->server['HTTP_CONTENT_LENGTH'])) {
            $this->step = 2;
        } else {
            $this->validateContentLength();
        }
    }

    /**
     * @return void
     * @throws RuntimeException
     */
    private function handleContinuousTransfer(): void
    {
        if ($buffer = $this->readBuffer(max(0, $this->contentLength - $this->bodyLength))) {
            $this->content    .= $buffer;
            $this->bodyLength += strlen($buffer);
            $this->validateContentLength();
        }
    }

    /**
     * @return void
     * @throws FormatException
     * @throws RuntimeException
     */
    private function handleFileTransfer(): void
    {
        if ($buffer = $this->readBuffer(max(0, $this->contentLength - $this->bodyLength))) {
            $this->bodyLength += strlen($buffer);
            foreach ($this->multipartHandler->tick($buffer) as $name => $multipartResult) {
                if (is_string($multipartResult)) {
                    $this->request[$name] = $multipartResult;
                } elseif (is_array($multipartResult)) {
                    foreach ($multipartResult as $file) {
                        $this->files[$name][] = $file;
                    }
                }
            }
            $this->validateContentLength();
        }
    }

    /**
     * @return array
     */
    private function finalizeRequest(): array
    {
        $this->parseCookies();
        $this->parseRequestBody();
        $this->setUserIpInfo();

        $result = [
            'query'      => $this->query,
            'request'    => $this->request,
            'attributes' => $this->attributes,
            'cookies'    => $this->cookies,
            'files'      => $this->files,
            'server'     => $this->server,
            'content'    => $this->content,
        ];

        $this->reset();
        return $result;
    }

    /**
     * @return void
     */
    private function parseCookies(): void
    {
        if (isset($this->server['HTTP_COOKIE'])) {
            $cookie = explode('; ', $this->server['HTTP_COOKIE']);
            foreach ($cookie as $item) {
                $item                    = explode('=', $item);
                $this->cookies[$item[0]] = isset($item[1]) ? rawurldecode($item[1]) : '';
            }
        }
    }

    /**
     * @return void
     */
    private function parseRequestBody(): void
    {
        if ($this->server['REQUEST_METHOD'] === 'POST') {
            if (str_contains($this->server['HTTP_CONTENT_TYPE'] ?? '', 'application/json')) {
                $this->request = array_merge($this->request, json_decode($this->content, true) ?? []);
            } else {
                parse_str($this->content, $requestParams);
                $this->request = array_merge($this->request, $requestParams);
            }
        }
    }

    /**
     * @return void
     */
    private function setUserIpInfo(): void
    {
        $this->server['REMOTE_ADDR'] = $this->stream->getHost();
        $this->server['REMOTE_PORT'] = $this->stream->getPort();

        if ($xForwardedProto = $this->server['HTTP_X_FORWARDED_PROTO'] ?? null) {
            $this->server['HTTPS'] = $xForwardedProto === 'https' ? 'on' : 'off';
        }
    }
}
