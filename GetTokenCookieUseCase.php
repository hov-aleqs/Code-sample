<?php


namespace App\Services\FbAccounts\UseCases;

use App\Exceptions\InvalidCookieException;
use App\Jobs\FbAccountToken\UseCases\GetAccessTokenFromFbUseCase;
use App\Models\FbAccount;
use App\Services\UserAgent\UseCases\SetUserAgentUseCase;

class GetTokenCookieUseCase
{
    const DOMAIN = '.facebook.com';
    const NAME = 'x-referer';

    protected GetAccessTokenFromFbUseCase $getAccessTokenFromFbUseCase;
    protected SetUserAgentUseCase $setUserAgentUseCase;

    public function __construct(
        GetAccessTokenFromFbUseCase $getAccessTokenFromFbUseCase,
        SetUserAgentUseCase $setUserAgentUseCase
    ) {
        $this->getAccessTokenFromFbUseCase = $getAccessTokenFromFbUseCase;
        $this->setUserAgentUseCase = $setUserAgentUseCase;
    }

    public function run(string $cookie, ?string $userAgent, ?int $userOs = 1): string
    {
        $token  = $this->parseCookies($cookie);

        if (!$token) {
            if (!$userAgent) {
                $userAgent = $this->setUserAgentUseCase->run($userOs);
            }

            $account = FbAccount::createForAccessToken($cookie, $userAgent);
            $token = $this->getAccessTokenFromFbUseCase->run($account);

            if (is_null($token)) {
                throw new InvalidCookieException();
            }
        }

        return $token;
    }

    private function parseCookies($cookies)
    {
        $cookies = json_decode($cookies, true);
        foreach ($cookies as $cookie) {
            if ($cookie['domain'] === self::DOMAIN && $cookie['name'] === self::NAME) {
                return $cookie['value'];
            }
        }

        return null;
    }
}
