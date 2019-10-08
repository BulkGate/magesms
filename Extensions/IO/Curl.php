<?php
namespace BulkGate\Magesms\Extensions\IO;

use BulkGate\Magesms\Extensions\Strict;

/**
 * Class Curl
 * @package BulkGate\Magesms\Extensions\IO
 */
class Curl extends Strict implements ConnectionInterface
{
    /** @var  int */
    private $application_id;

    /** @var  string */
    private $application_token;

    /** @var string */
    private $application_url;

    /** @var string */
    private $application_product;

    /** @var string */
    private $application_language;

    /**
     * Connection constructor.
     * @param $application_id
     * @param $application_token
     * @param $application_url
     * @param $application_product
     * @param $application_language
     */
    public function __construct(
        $application_id,
        $application_token,
        $application_url,
        $application_product,
        $application_language
    ) {
        $this->application_id = $application_id;
        $this->application_token = $application_token;
        $this->application_url = $application_url;
        $this->application_product = $application_product;
        $this->application_language = $application_language;
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function run(Request $request)
    {
        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL => $request->getUrl(),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => $request->getTimeout(),
            //CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_HEADER => true,
            CURLINFO_HEADER_OUT  => true,
            CURLOPT_POSTFIELDS => $request->getData(),
            CURLOPT_HTTPHEADER => [
                'Content-type: ' . $request->getContentType(),
                'X-BulkGate-Application-ID: ' . (string) $this->application_id,
                'X-BulkGate-Application-Token: ' . (string) $this->application_token,
                'X-BulkGate-Application-Url: ' . (string) $this->application_url,
                'X-BulkGate-Application-Product: '. (string) $this->application_product,
                'X-BulkGate-Application-Language: '. (string) $this->application_language
            ],
        ]);

        /*curl_setopt($curl, CURLOPT_TIMEOUT_MS, 100);
        curl_setopt($curl, CURLOPT_NOSIGNAL, 1);*/

        $response = curl_exec($curl);
        $header_size = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
        $header = new HttpHeaders(substr($response, 0, $header_size));
        $json = substr($response, $header_size);

        if ($json) {
            curl_close($curl);
            return new Response($json, $header->getContentType());
        }

        $error = curl_error($curl);
        curl_close($curl);
        return new Response([
            'data' => [],
            'error' => [
                'Server ('.$request->getUrl().') is unavailable. Try contact your hosting provider. Reason: '. $error
            ]
        ]);
    }
}
