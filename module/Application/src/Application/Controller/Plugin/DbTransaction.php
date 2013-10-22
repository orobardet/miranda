<?php
namespace Application\Controller\Plugin;

use Zend\Mvc\Controller\Plugin\AbstractPlugin;
use Zend\Db\Adapter\Adapter as DbAdapter;

class DbTransaction extends AbstractPlugin
{

    /**
     * Adapteur de connexion DB
     *
     * @var \Zend\Db\Adapter\Adapter
     */
    protected $dbAdapter;

    public function __construct(DbAdapter $dbAdapater = null)
    {
        if ($dbAdapater) {
            $this->dbAdapter = $dbAdapater;
        }
    }

    public function begin()
    {
        if ($this->dbAdapter instanceof DbAdapter) {
            $this->dbAdapter->getDriver()
                ->getConnection()
                ->beginTransaction();
        }
    }

    public function start()
    {
        $this->begin();
    }

    public function commit()
    {
        if ($this->dbAdapter instanceof DbAdapter) {
            $this->dbAdapter->getDriver()
                ->getConnection()
                ->commit();
        }
    }

    public function rollback()
    {
        if ($this->dbAdapter instanceof DbAdapter) {
            $this->dbAdapter->getDriver()
                ->getConnection()
                ->rollback();
        }
    }
}
