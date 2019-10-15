<?php
namespace BulkGate\Magesms\Extensions;

/**
 * Class Synchronize
 * @package BulkGate\Magesms\Extensions
 */
class Synchronize extends Strict
{
    /** @var SettingsInterface */
    private $settings;

    /** @var IO\ConnectionInterface */
    private $connection;

    public function __construct(SettingsInterface $settings, IO\ConnectionInterface $connection)
    {
        $this->settings = $settings;
        $this->connection = $connection;
    }

    public function run($url, $now = false)
    {
        try {
            if ($now || ($this->settings->load('static:synchronize', 0) < time() &&
                    $this->settings->load('static:application_id', false))) {
                $self = $this;
                $this->synchronize(function ($module_settings) use ($url, $self) {
                    return $self->connection->run(new IO\Request(
                        $url,
                        ['__synchronize' => $module_settings],
                        true,
                        (int)$self->settings->load('main:synchronize_timeout', 6)
                    ));
                });
            }
        } catch (IO\Exceptions\AuthenticateException $e) {
            $this->settings->delete('static:application_token');
        }
    }

    public function synchronize($callback)
    {
        if (is_callable($callback)) {
            $module_settings = $this->settings->synchronize();
            $server_settings = call_user_func($callback, $module_settings);

            if ((isset($server_settings->exception) && $server_settings->exception) ||
                (isset($server_setting->error) && !empty($server_settings->error))) {
                return $server_settings;
            }
            if ($server_settings instanceof IO\Response) {
                foreach ((array)$server_settings->get(':synchronize:') as $server_setting) {
                    $key = $this->getKey($server_setting->scope, $server_setting->key);

                    if (isset($module_settings[$key])) {
                        $server_setting->datetime = isset($server_setting->datetime) ?
                            (int)$server_setting->datetime : 0;
                        $module_settings[$key]->datetime = isset($module_settings[$key]->datetime) ?
                            (int)$module_settings[$key]->datetime : 0;

                        if ($server_setting->datetime >= $module_settings[$key]->datetime) {
                            $this->settings->set(
                                $key,
                                $server_setting->value,
                                [
                                    'type' => isset($server_setting->type) ? $server_setting->type : 'text',
                                    'datetime' => $server_setting->datetime,
                                    'synchronize_flag' => $server_setting->synchronize_flag,
                                    'order' => isset($server_setting->order) ? $server_setting->order : 0,
                                ]
                            );
                        }
                    } else {
                        $this->settings->set(
                            $key,
                            $server_setting->value,
                            [
                                'type' => $server_setting->type ?? 'text',
                                'datetime' => $server_setting->datetime ?? 0,
                                'order' => $server_setting->order ?? 0,
                                'synchronize_flag' => $server_setting->synchronize_flag ?? 'none'
                            ]
                        );
                    }
                }

                $this->settings->delete();

                $this->settings->set(
                    'static:synchronize',
                    time() + $this->settings->load(
                        'main:synchronize_interval',
                        21600 /* 6 hours */
                    )
                );
            }
            $server_settings->remove(':synchronize:');

            return $server_settings;
        }
        return false;
    }

    private function getKey($scope, $key)
    {
        return (string)$scope . ':' . (string)$key;
    }
}
