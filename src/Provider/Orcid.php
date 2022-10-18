<?php

namespace Alexandrump\OAuth2\Client\Provider;

use League\OAuth2\Client\Provider\Exception\OrcidIdentityProviderException;
use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Tool\BearerAuthorizationTrait;
use Psr\Http\Message\ResponseInterface;

class Orcid extends AbstractProvider
{
    use BearerAuthorizationTrait;

    /**
     * Domain
     *
     * @var string
     */
    public $domain = 'https://orcid.org';

    /**
     * Api domain
     *
     * @var string
     */
    public $apiDomain = 'https://pub.orcid.org';

    /**
     * Get authorization url to begin OAuth flow
     *
     * @return string
     */
    public function getBaseAuthorizationUrl()
    {
        return $this->domain . '/oauth/authorize';
    }

    /**
     * Get access token url to retrieve token
     *
     * @param array $params
     *
     * @return string
     */
    public function getBaseAccessTokenUrl(array $params)
    {
        return $this->domain . '/oauth/token';
    }

    /**
     * Get provider url to fetch user details
     *
     * @param AccessToken $token
     *
     * @return string
     */
    public function getResourceOwnerDetailsUrl(AccessToken $token)
    {
        if ($this->domain === 'https://orcid.org') {
            return $this->apiDomain . '/v3.0/oauth/userinfo';
        }
        return $this->domain . '/oauth/userinfo';
    }

    /**
     * Get the default scopes used by this provider.
     *
     * This should not be a complete list of all scopes, but the minimum
     * required for the provider user interface!
     *
     * @return array
     */
    protected function getDefaultScopes()
    {
        return [];
    }

    /**
     * Check a provider response for errors.
     *
     * @throws IdentityProviderException
     * @param  ResponseInterface $response
     * @param  array             $data     Parsed response data
     * @return void
     */
    protected function checkResponse(ResponseInterface $response, $data)
    {
        if ($response->getStatusCode() >= 400) {
            throw OrcidIdentityProviderException::clientException($response, $data);
        } elseif (isset($data['error'])) {
            throw OrcidIdentityProviderException::oauthException($response, $data);
        }
    }

    /**
     * Generate a user object from a successful user details request.
     *
     * @param  array       $response
     * @param  AccessToken $token
     * @return \League\OAuth2\Client\Provider\ResourceOwnerInterface
     */
    protected function createResourceOwner(array $response, AccessToken $token)
    {
        $user = new OrcidResourceOwner($response);

        return $user->setDomain($this->domain);
    }
}
