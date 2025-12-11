<?php

namespace App\WebhookModule\Services;

use App\WebhookModule\Models\WebhookRequest;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class WebhookSendService
{
    const RETRY_LIMIT = 3;

    protected $client;
    protected $webhook;
    protected $send_data;

    protected $request_headers;
    protected $retries = 0;

    public function __construct($webhook, $send_data)
    {
        $this->client    = new Client();
        $this->webhook   = $webhook;
        $this->send_data = $send_data;

        $encryptedBody = $this->encryptWithSecretKey($send_data, $webhook['secret_key']);
        $this->request_headers = [
            'Accept'            => 'application/json',
            'Content-Type'      => 'application/json',
            'X-Encrypted-Data'  => $encryptedBody
        ];
    }

    private function encryptWithSecretKey($data, $secretKey)
    {
        $jsonData = json_encode($data);
        $encryptedData = hash_hmac('sha256', $jsonData, $secretKey, true);

        return base64_encode($encryptedData);
    }

    public function send()
    {
        $webhook_request_result = [
            'company_id'    => $this->webhook['company_id'],
            'webhook_id'    => $this->webhook['id'],
            'webhook_url'   => $this->webhook['webhook_url'],
            'request_data'  => $this->send_data,
            'is_successful' => true,
        ];

        ++$this->retries;

        try {
            $response = $this->client->post(
                $this->webhook['webhook_url'],
                [
                    'headers' => $this->request_headers,
                    'json'    => $this->send_data
                ]
            );

            // Get the response status code
            $webhook_request_result['response_status_code'] = $response->getStatusCode();

            // Get the response body
            $responseBody = $response->getBody()->getContents();
            if ($responseBody) {
                $webhook_request_result['response_data'] = json_decode($responseBody, true);
            }

            WebhookRequest::create($webhook_request_result);
        } catch (RequestException $e) {
            if ($this->retries > self::RETRY_LIMIT) {

                $webhook_request_result['is_successful'] = false;
                // Handle request exceptions
                if ($e->hasResponse()) {
                    $response = $e->getResponse();
                    $webhook_request_result['response_status_code'] = $response->getStatusCode();
                    $webhook_request_result['response_data'] = ['error' => $response->getReasonPhrase()];
                }

                WebhookRequest::create($webhook_request_result);
            } else {
                sleep(1);
                $this->send(); // Retry
            }
        }
    }
}
