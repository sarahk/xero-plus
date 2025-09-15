<?php

namespace App;

use DateTime;
use Dotenv\Dotenv;

class StorageClass
{
    function __construct()
    {
        $this->startSession();
    }

    public function getSession(): array
    {
        return $_SESSION['oauth2'] ?? ['tenant_id' => '', 'oauth2' => []];
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
        return $_SESSION['oauth2']['refresh_token'] ?? '';
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

    public function hasExpired()
    {
        if ($this->shouldBypassExpiry()) {
            return false;
        }
        if (empty($this->getSession())) {
            return true;
        }

        // Expiry check
        return time() > (int)$this->getExpires();
    }

    private function shouldBypassExpiry(): bool
    {
        $isTesting = filter_var(CKM_TESTING_MODE, FILTER_VALIDATE_BOOLEAN);
        $bypass = filter_var(CKM_BYPASS_EXPIRY, FILTER_VALIDATE_BOOLEAN);

        return $isTesting && $bypass;
    }

    public function saveUrl(string $url): void
    {
        $_SESSION['ckm']['url'] = '.' . $url;
    }

    public function getUrl(): string
    {
        return $_SESSION['ckm']['url'] ?? './page.php';
    }

    public function setNotification($notify): void
    {
        $_SESSION['ckm']['notification'] = $notify;
    }

    public function getNotification(): array
    {
        $output = $_SESSION['ckm']['notification'] ?? [];
        unset($_SESSION['ckm']['notification']);
        return $output;
    }

    public function getNextRuntime(string $type): array
    {
        $next_runtime = new DateTime($_SESSION['ckm'][$type]['next_runtime'] ?? '');
        $current_time = new DateTime(); // Get the current time
        $future = $next_runtime > $current_time; // Compare the times
        return ['next_runtime' => $next_runtime, 'future' => $future, 'last_batch' => $_SESSION['ckm'][$type]['last_batch']];
    }

    public function setNextRuntime(string $type): void
    {
        $minutes = 30;
        $date_time = new DateTime();
        $date_time->modify("+{$minutes} minutes");
        $next_runtime = $date_time->format('Y-m-d H:i:s');
        $_SESSION['ckm'][$type]['next_runtime'] = $next_runtime;
        $_SESSION['ckm'][$type]['last_batch'] = 0;
    }

    /**
     * @param string $type
     * @param int $count
     * @return void
     */
    public function setLastBatch(string $type, int $count): void
    {
        $_SESSION['ckm'][$type]['last_batch'] = $count;
    }

    public function getMonologCheckStatus(): string
    {
        return $_SESSION['ckm']['monolog_check_status'] ?? '0';
    }

    public function setMonologCheckStatus(string $status): void
    {
        $_SESSION['ckm']['monolog_check_status'] = $status;
    }
}
