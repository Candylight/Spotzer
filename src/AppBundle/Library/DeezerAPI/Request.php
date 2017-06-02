<?php
namespace AppBundle\Library\DeezerAPI;

use AppBundle\Library\DeezerAPI\Exception\DeezerAPIException;
use Symfony\Component\Config\Definition\Exception\Exception;

class Request
{
    const ACCOUNT_URL = 'https://connect.deezer.com';
    const API_URL = 'http://api.deezer.com';

    const RETURN_ASSOC = 'assoc';
    const RETURN_OBJECT = 'object';

    protected $lastResponse = [];
    protected $returnAssoc = false;
    protected $returnType = self::RETURN_OBJECT;

    /**
     * Parse the response body and handle API errors.
     *
     * @param string $body The raw, unparsed response body.
     * @param int $status The HTTP status code, used to see if additional error handling is needed.
     *
     * @throws DeezerAPIException
     *
     * @return array|object The parsed response body. Type is controlled by `Request::setReturnType()`.
     */
    protected function parseBody($body, $status)
    {
        parse_str($body, $output);

        $this->lastResponse['body'] = json_decode(\GuzzleHttp\json_encode($output), $this->returnType == self::RETURN_ASSOC);

        if ($status >= 200 && $status <= 299) {
            return $this->lastResponse['body'];
        }

        $body = json_decode($body);
        $error = (isset($body->error)) ? $body->error : null;

        if (isset($error->message) && isset($error->status)) {
            // API call error
            throw new DeezerAPIException($error->message, $error->status);
        } elseif (isset($body->error_description)) {
            // Auth call error
            throw new DeezerAPIException($body->error_description, $status);
        } else {
            // Something went really wrong
            throw new DeezerAPIException('An unknown error occurred.', $status);
        }
    }

    /**
     * Parse HTTP response headers.
     *
     * @param string $headers The raw, unparsed response headers.
     *
     * @return array Headers as key–value pairs.
     */
    protected function parseHeaders($headers)
    {
        $headers = str_replace("\r\n", "\n", $headers);
        $headers = explode("\n", $headers);

        array_shift($headers);

        $parsedHeaders = [];
        foreach ($headers as $header) {
            list($key, $value) = explode(':', $header, 2);

            $parsedHeaders[$key] = trim($value);
        }

        return $parsedHeaders;
    }

    /**
     * Make a request to the "account" endpoint.
     *
     * @param string $method The HTTP method to use.
     * @param string $uri The URI to request.
     * @param array $parameters Optional. Query parameters.
     * @param array $headers Optional. HTTP headers.
     *
     * @return array Response data.
     * - array|object body The response body. Type is controlled by `Request::setReturnType()`.
     * - string headers Response headers.
     * - int status HTTP status code.
     * - string url The requested URL.
     */
    public function account($method, $uri, $parameters = [], $headers = [])
    {
        return $this->send($method, self::ACCOUNT_URL . $uri, $parameters, $headers);
    }

    /**
     * Make a request to the "api" endpoint.
     *
     * @param string $method The HTTP method to use.
     * @param string $uri The URI to request.
     * @param array $parameters Optional. Query parameters.
     * @param array $headers Optional. HTTP headers.
     *
     * @return array Response data.
     * - array|object body The response body. Type is controlled by `Request::setReturnType()`.
     * - string headers Response headers.
     * - int status HTTP status code.
     * - string url The requested URL.
     */
    public function api($method, $uri, $parameters = [], $headers = [])
    {
        return $this->send($method, self::API_URL . $uri, $parameters, $headers);
    }

    /**
     * Get the latest full response from the DeezerAPI API.
     *
     * @return array Response data.
     * - array|object body The response body. Type is controlled by `Request::setReturnType()`.
     * - array headers Response headers.
     * - int status HTTP status code.
     * - string url The requested URL.
     */
    public function getLastResponse()
    {
        return $this->lastResponse;
    }

    /**
     * Get a value indicating the response body type.
     *
     * @deprecated Use `Request::getReturnType()` instead.
     *
     * @return bool Whether the body is returned as an associative array or an stdClass.
     */
    public function getReturnAssoc()
    {
        trigger_error(
            'Request::getReturnAssoc() is deprecated. Use Request::getReturnType() instead.',
            E_USER_DEPRECATED
        );

        return $this->returnAssoc;
    }

    /**
     * Get a value indicating the response body type.
     *
     * @return string A value indicating if the response body is an object or associative array.
     */
    public function getReturnType()
    {
        return $this->returnType;
    }

    /**
     * Make a request to DeezerAPI.
     * You'll probably want to use one of the convenience methods instead.
     *
     * @param string $method The HTTP method to use.
     * @param string $url The URL to request.
     * @param array $parameters Optional. Query parameters.
     * @param array $headers Optional. HTTP headers.
     *
     * @throws DeezerAPIException
     *
     * @return array Response data.
     * - array|object body The response body. Type is controlled by `Request::setReturnType()`.
     * - array headers Response headers.
     * - int status HTTP status code.
     * - string url The requested URL.
     */
    public function send($method, $url, $parameters = [], $headers = [])
    {
        // Reset any old responses
        $this->lastResponse = [];

        // Sometimes a stringified JSON object is passed
        if (is_array($parameters) || is_object($parameters)) {
            $parameters = http_build_query($parameters);
        }

        $mergedHeaders = [];
        foreach ($headers as $key => $val) {
            $mergedHeaders[] = "$key: $val";
        }

        $options = [
            CURLOPT_CAINFO => __DIR__ . '/cacert.pem',
            CURLOPT_ENCODING => '',
            CURLOPT_HEADER => true,
            CURLOPT_HTTPHEADER => $mergedHeaders,
            CURLOPT_RETURNTRANSFER => true,
        ];

        $url = rtrim($url, '/');
        $method = strtoupper($method);

        switch ($method) {
            case 'DELETE': // No break
            case 'PUT':
                $options[CURLOPT_CUSTOMREQUEST] = $method;
                $options[CURLOPT_POSTFIELDS] = $parameters;

                break;
            case 'POST':
                $options[CURLOPT_POST] = true;
                $options[CURLOPT_POSTFIELDS] = $parameters;

                break;
            default:
                $options[CURLOPT_CUSTOMREQUEST] = $method;

                if ($parameters) {
                    $url .= '/?' . $parameters;
                }

                break;
        }
        $options[CURLOPT_URL] = $url;

        $ch = curl_init();

        curl_setopt_array($ch, $options);

        $response = curl_exec($ch);


        if (curl_error($ch)) {
            throw new DeezerAPIException('cURL transport error: ' . curl_errno($ch) . ' ' .  curl_error($ch));
        }

        list($headers, $body) = explode("\r\n\r\n", $response, 2);


        $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);

        $headers = $this->parseHeaders($headers);

        $this->lastResponse = [
            'headers' => $headers,
            'status' => $status,
            'url' => $url,
        ];


        $body = $this->parseBody($body, $status);

        curl_close($ch);

        return $this->lastResponse;
    }

    /**
     * Set the return type for the response body.
     *
     * @deprecated Use `Request::setReturnType()` instead.
     *
     * @param bool $returnAssoc Whether to return an associative array or an stdClass.
     *
     * @return void
     */
    public function setReturnAssoc($returnAssoc)
    {
        trigger_error(
            'Request::setReturnType() is deprecated. Use Request::setReturnType() instead.',
            E_USER_DEPRECATED
        );

        $this->returnAssoc = $returnAssoc;
        $this->returnType = $returnAssoc ? self::RETURN_ASSOC : self::RETURN_OBJECT;
    }

    /**
     * Set the return type for the response body.
     *
     * @param string $returnType One of the `Request::RETURN_*` constants.
     *
     * @return void
     */
    public function setReturnType($returnType)
    {
        $this->returnAssoc = $returnType == self::RETURN_ASSOC;
        $this->returnType = $returnType;
    }
}
