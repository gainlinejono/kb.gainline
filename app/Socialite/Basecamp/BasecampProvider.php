<?php

namespace App\Socialite\Basecamp;

use GuzzleHttp\RequestOptions;
use Illuminate\Support\Arr;
use Laravel\Socialite\Two\AbstractProvider;
use Laravel\Socialite\Two\ProviderInterface;
use Laravel\Socialite\Two\User;

class BasecampProvider extends AbstractProvider implements ProviderInterface
{
    protected $scopes = [];
    protected $scopeSeparator = ' ';

    protected function getAuthUrl($state): string
    {
        return $this->buildAuthUrlFromBase('https://launchpad.37signals.com/authorization/new', $state);
    }

    protected function getTokenUrl(): string
    {
        return 'https://launchpad.37signals.com/authorization/token';
    }

    protected function getUserByToken($token): array
    {
        $response = $this->getHttpClient()->get('https://launchpad.37signals.com/authorization.json', [
            RequestOptions::HEADERS => [
                'Authorization' => 'Bearer ' . $token,
            ],
        ]);

        return json_decode($response->getBody()->getContents(), true);
    }

    protected function mapUserToObject(array $user): User
    {
        $identity = $user['identity'] ?? [];

        return (new User)->setRaw($user)->map([
            'id' => $identity['id'] ?? null,
            'nickname' => null,
            'name' => trim(($identity['first_name'] ?? '') . ' ' . ($identity['last_name'] ?? '')),
            'email' => $identity['email_address'] ?? null,
            'avatar' => null,
        ]);
    }

    public function getAccessTokenResponse($code): array
    {
        $response = $this->getHttpClient()->post($this->getTokenUrl(), [
            RequestOptions::HEADERS => [
                'Accept' => 'application/json',
            ],
            RequestOptions::FORM_PARAMS => $this->getTokenFields($code),
        ]);

        return json_decode($response->getBody()->getContents(), true);
    }

    protected function getTokenFields($code): array
    {
        return [
            'type' => 'web_server',
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'redirect_uri' => $this->redirectUrl,
            'code' => $code,
        ];
    }

    public function getAccounts(string $token): array
    {
        $response = $this->getHttpClient()->get('https://launchpad.37signals.com/authorization.json', [
            RequestOptions::HEADERS => [
                'Authorization' => 'Bearer ' . $token,
            ],
        ]);

        $data = json_decode($response->getBody()->getContents(), true);

        return $data['accounts'] ?? [];
    }
}
