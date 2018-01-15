<?php


namespace AppBundle\Authorization;


class OAuth2Api
{
    private $baseUri;
    private $clientId;
    private $secret;
    private $tokenUri;
    private $authUri;
    private $userUri;

    /**
     * OAuth2Api constructor.
     *
     * @param array $options
     */
    public function __construct(array $options)
    {
        $this->clientId = $options['client_id'];
        $this->secret   = $options['client_secret'];
        $this->baseUri  = $options['base'];
        $this->tokenUri = $options['token_uri'];
        $this->authUri  = $options['authenticate_uri'];
        $this->userUri  = $options['user_uri'];
    }

    /**
     * @return mixed
     */
    public function getBaseUri()
    {
        return $this->baseUri;
    }

    /**
     * @return mixed
     */
    public function getClientId()
    {
        return $this->clientId;
    }

    /**
     * @return mixed
     */
    public function getSecret()
    {
        return $this->secret;
    }

    /**
     * TODO make generic
     * @return string
     */
    public function getAuthenticationAddress()
    {
        return $this->baseUri . $this->authUri;
    }

    /**
     * @return string
     */
    public function getTokenUrl()
    {
        return $this->baseUri . $this->tokenUri;
    }

    /**
     * @return string
     */
    public function getUserEndpoint()
    {
        return $this->baseUri . $this->userUri;
    }
}