<?php


namespace AppBundle\Authorization;


use AppBundle\Entity\AccessToken;
use AppBundle\Entity\DiscordUser;
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use Symfony\Component\Config\Definition\Exception\Exception;

class OAuth2
{
    const KEY_CLIENT_ID = 'client_id';
    const KEY_CLIENT_SECRET = 'client_secret';
    const KEY_REDIRECT_URI = 'redirect_uri';

    /**
     * @var Client
     */
    private $client;
    /**
     * @var OAuth2Api
     */
    private $api;

    /**
     * OAuth2 constructor.
     *
     * @param Client    $client
     * @param OAuth2Api $api
     */
    public function __construct(Client $client, OAuth2Api $api)
    {
        $this->client = $client;
        $this->api    = $api;
    }

    /**
     * @param $redirectUri
     * @param $scope
     *
     * @return string
     */
    public function getAuthenticationUrl($redirectUri, $scope)
    {
        $query = http_build_query(
            [
                self::KEY_CLIENT_ID    => $this->api->getClientId(),
                self::KEY_REDIRECT_URI => $redirectUri,
                'scope'                => $scope,
                'response_type'        => 'code',
            ]
        );

        return $this->api->getAuthenticationAddress() . '?' . $query;
    }

    /**
     * @param string $redirectUri
     * @param string $code
     *
     * @return AccessToken
     */
    public function exchangeToken($redirectUri, $code)
    {
        $response = $this->client->post($this->api->getTokenUrl(), [
            RequestOptions::FORM_PARAMS => [
                self::KEY_CLIENT_ID     => $this->api->getClientId(),
                self::KEY_CLIENT_SECRET => $this->api->getSecret(),
                'code'                  => $code,
                'grant_type'            => 'authorization_code',
                'redirect_uri'          => $redirectUri,
            ],
        ]);

        $contents = $response->getBody()->getContents();
        $contents = \GuzzleHttp\json_decode($contents);

        $token = new AccessToken();
        $token->setToken($contents->access_token);
        $token->setType($contents->token_type);
        $token->setExpiration(time() + $contents->expires_in);
        $token->setRefresh($contents->refresh_token);
        $token->setScope($contents->scope);

        return $token;
    }

    public function getUserInfo(AccessToken $token)
    {
        if (strpos($token->getScope(), 'identify') === false) {
            throw new Exception('Incorrect scope');
        }

        $url = $this->api->getUserEndpoint() . '?' . http_build_query(
                [
                    self::KEY_CLIENT_ID     => $this->api->getClientId(),
                    self::KEY_CLIENT_SECRET => $this->api->getSecret(),
                ]
            );
        $options = [
            'headers' => [
                'Authorization' => $token->getType() . ' ' . $token->getToken()
            ]
        ];

        $response = $this->client->get($url, $options);

        $userInfo = \GuzzleHttp\json_decode($response->getBody()->getContents());

        $user = new DiscordUser();
        $user->setUserid($userInfo->id);
        $user->setUsername($userInfo->username);
        $user->setDiscriminator($userInfo->discriminator);
        $user->setAvatar($userInfo->avatar);

        return $user;
    }

    public function getGuilds(AccessToken $token)
    {
        $guilds = $this->queryApi($token, 'guilds');

        return $guilds;
    }

    /**
     * @param AccessToken $token
     * @param string      $endpoint
     *
     * @return object
     */
    private function queryApi(AccessToken $token, $endpoint)
    {
        switch ($endpoint) {
            case 'user':
                $scope = 'identify';
                $url = $this->api->getUserEndpoint();
                break;
            case 'guilds':
                $scope = 'guilds';
                $url = $this->api->getGuildEndpoint();
                break;
            default:
                throw new Exception();
        }

        if (strpos($token->getScope(), $scope) === false) {
            throw new Exception('Incorrect scope');
        }

        $url .= '?' . http_build_query(
                [
                    self::KEY_CLIENT_ID     => $this->api->getClientId(),
                    self::KEY_CLIENT_SECRET => $this->api->getSecret(),
                ]
            );
        $options = [
            'headers' => [
                'Authorization' => $token->getType() . ' ' . $token->getToken()
            ]
        ];

        $response = $this->client->get($url, $options);

        return \GuzzleHttp\json_decode($response->getBody()->getContents());
    }
}