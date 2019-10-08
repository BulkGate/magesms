<?php
namespace BulkGate\Magesms\Extensions\Api;

use BulkGate\Magesms\Extensions;

/**
 * Class Authenticator
 * @package BulkGate\Magesms\Extensions\Api
 */
class Authenticator extends Extensions\Strict
{
    /** @var Extensions\Settings */
    private $settings;

    public function __construct(Extensions\SettingsInterface $settings)
    {
        $this->settings = $settings;
    }

    public function authenticate($application_id, $application_token)
    {
        if ($this->settings->load('static:application_id') === $application_id &&
            $this->settings->load('static:application_token') === $application_token) {
            return true;
        }

        throw new Exceptions\ConnectionException('Unauthorized', 401);
    }
}
