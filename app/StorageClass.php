<?php

namespace App;
class StorageClass
{
    function __construct()
    {
        $this->startSession();
    }

    public function getSession()
    {
        return $_SESSION['oauth2'] ?? null;
    }

    public function startSession()
    {
        if (!isset($_SESSION)) {
            ini_set('session.cookie_lifetime', 3600); // Lifetime of the cookie (1 hour)
            ini_set('session.cookie_path', '/'); // Path where the cookie is available
            //ini_set('session.cookie_secure', 0); // Set to 1 for HTTPS only
            //ini_set('session.cookie_httponly', 1); // Make the cookie HTTP only (not accessible by JavaScript)

            session_start();
        }
    }

    public function setToken($token, $expires, $tenantId, $refreshToken, $idToken)
    {
        $_SESSION['oauth2'] = [
            'token' => $token,
            'expires' => $expires,
            'tenant_id' => $tenantId,
            'refresh_token' => $refreshToken,
            'id_token' => $idToken
        ];
    }

    public function getToken()
    {
        //If it doesn't exist or is expired, return null
        if (
            empty($this->getSession())
            || ($_SESSION['oauth2']['expires'] !== null
                && $_SESSION['oauth2']['expires'] <= time())
        ) {
            return null;
        }
        return $this->getSession();
    }

    public function getAccessToken()
    {
        return $_SESSION['oauth2']['token'];
    }

    // A function to retrieve the current access token (from session or DB)
    public static function getAccessTokenAndExpiry()
    {
        // Retrieve the access token and expiration details from storage
        //if ($_SESSION['oauth2']['expires'] < time()) {}
        return [
            'token' => $_SESSION['oauth2']['token'],  // Example: from session
            'expires' => $_SESSION['oauth2']['expires'],
        ];
    }

    // A function to store new tokens
    public static function storeNewTokens($accessToken, $refreshToken)
    {
        $_SESSION['oauth2']['access_token'] = $accessToken;
        $_SESSION['oauth2']['refresh_token'] = $refreshToken;
        $_SESSION['oauth2']['expires'] = time() + 3600; // Set the new expiration time (1 hour)
    }

    public function getRefreshToken()
    {
        return $_SESSION['oauth2']['refresh_token'];
    }

    public static function getRefreshTokenStatic()
    {
        return $_SESSION['oauth2']['refresh_token'];
    }

    public function getExpires()
    {
        return $_SESSION['oauth2']['expires'];
    }

    public function getXeroTenantId()
    {
        return $_SESSION['oauth2']['tenant_id'];
    }

    public function getIdToken()
    {
        return $_SESSION['oauth2']['id_token'];
    }

    public function getHasExpired()
    {
        if (!empty($this->getSession())) {
            if (time() > $this->getExpires()) {
                return true;
            } else {
                return false;
            }
        } else {
            return true;
        }
    }
}
