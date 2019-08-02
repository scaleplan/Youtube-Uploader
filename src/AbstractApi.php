<?php

namespace Scaleplan\Youtube;

/**
 * Class AbstractApi
 *
 * @package Scaleplan\Youtube
 */
class AbstractApi
{
    /**
     * Google Client
     *
     * @var \Google_Client
     */
    protected $client;

    /**
     * Google YouTube Service
     *
     * @var \Google_Service_YouTube
     */
    protected $youtube;

    /**
     * Constructor
     *
     * @param \Google_Client $client
     */
    public function __construct(\Google_Client $client = null)
    {
        $this->client = $client ?? new \Google_Client;
        $this->client->setAccessType('offline');
        $this->client->useApplicationDefaultCredentials();
        $this->client->addScope(\Google_Service_YouTube::YOUTUBE);

        $this->youtube = new \Google_Service_YouTube($this->client);
    }
}
