<?php
namespace BulkGate\Magesms\Extensions;

use BulkGate\Magesms\Extensions\Exceptions\ServiceNotFoundException;

/**
 * @author Lukáš Piják 2018 TOPefekt s.r.o.
 * @link https://www.bulkgate.com/
 * @method Settings getSettings()
 * @method IO\ConnectionInterface getConnection()
 * @method Translator getTranslator()
 * @method Synchronize getSynchronize()
 * @method ProxyActions getProxy()
 */
abstract class DIContainer extends Strict
{

    /** @var array */
    protected $services = [];

    /**
     * @return Database\DatabaseInterface
     */
    abstract protected function createDatabase();

    /**
     * @return ModuleInterface
     */
    abstract protected function createModule();

    /**
     * @return CustomersInterface
     */
    abstract protected function createCustomers();

    /**
     * @return Settings
     * @throws ServiceNotFoundException
     */
    protected function createSettings()
    {
        return new Settings($this->getService('database'));
    }

    /**
     * @return IO\ConnectionInterface
     * @throws ServiceNotFoundException
     */
    protected function createConnection()
    {
        /** @var ModuleInterface $module */
        $module = $this->getService('module');

        $factory = new IO\ConnectionFactory($this->getService('settings'));

        return $factory->create($module->url(), $module->product());
    }

    /**
     * @return Translator
     * @throws ServiceNotFoundException
     */
    protected function createTranslator()
    {
        return new Translator($this->getService('settings'));
    }

    /**
     * @return Synchronize
     * @throws ServiceNotFoundException
     */
    protected function createSynchronize()
    {
        return new Synchronize($this->getService('settings'), $this->getService('connection'));
    }

    /**
     * @return ProxyActions
     * @throws ServiceNotFoundException
     */
    protected function createProxy()
    {
        return new ProxyActions(
            $this->getService('connection'),
            $this->getService('module'),
            $this->getService('synchronize'),
            $this->getService('settings'),
            $this->getService('translator'),
            $this->getService('customers')
        );
    }

    /**
     * @param $name
     * @return mixed
     * @throws ServiceNotFoundException
     */
    public function getService($name)
    {
        $name = ucfirst($name);

        if (isset($this->services[$name])) {
            return $this->services[$name];
        }
        if (method_exists($this, 'create' . $name)) {
            return $this->services[$name] = $this->{'create' . $name}();
        }
        throw new ServiceNotFoundException("Dependency injection container - Service $name not found");
    }

    /**
     * @param string $name
     * @param array $arguments
     * @return mixed
     * @throws ServiceNotFoundException
     */
    public function __call($name, array $arguments = [])
    {
        if (preg_match("~^get(?<name>[A-Z][a-zA-Z0-9_]*)~", $name, $match)) {
            return $this->getService($match['name']);
        }

        throw new ServiceNotFoundException("Dependency injection container - Service $name not found");
    }
}
