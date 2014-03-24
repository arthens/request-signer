<?php
namespace Arthens\RequestSigner;

class Signer
{
    private $secretKey;

    /**
     * string
     * @param string $secretKey
     * @throws \InvalidArgumentException
     */
    public function __construct($secretKey)
    {
        if (!$secretKey) {
            throw new \InvalidArgumentException('Missing required secret key');
        }

        $this->secretKey = $secretKey;
    }

    /**
     * Generate a signature based on the passed request
     *
     * Inspired by http://s3.amazonaws.com/doc/s3-developer-guide/RESTAuthentication.html
     *
     * @param string $method
     * @param string $url
     * @param string|null $content
     * @param string|null $contentType
     * @param array $headers
     * @return string
     */
    public function sign($method, $url, $content = null, $contentType = null, $headers = array())
    {
        // Sorting headers by key name - ordering should not invalidate the signature
        ksort($headers);

        // Using json_encode so that we can serialize both keys and values
        $serializedHeaders = json_encode($headers);

        // Merging all values
        $data = implode("\n", array(
            $method,
            $url,
            md5($content),
            $contentType,
            $serializedHeaders
        ));

        $base64Signature = base64_encode(hash_hmac(
            "sha1",
            $data,
            $this->secretKey
        ));

        // base64_encode is not URL friendly, this fixes it
        return rtrim(strtr($base64Signature, '+/', '-_'), '=');
    }

    /**
     * Verify that the provided signature matches the expected signature
     *
     * @param string $signature
     * @param string $method
     * @param string $url
     * @param string|null $content
     * @param string|null $contentType
     * @param array $headers
     * @return bool
     */
    public function verify($signature, $method, $url, $content = null, $contentType = null, $headers = array())
    {
        $expectedSignature = $this->sign($method, $url, $content, $contentType, $headers);

        return $expectedSignature === $signature;
    }
}
