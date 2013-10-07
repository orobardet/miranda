<?php
namespace Application;

use Zend\Config\Config;

class TraversableConfig extends Config
{
    /**
     * Retrieve a value and return $default if there is no element set.
     * 
     * Accept complex name representing a path in the configuration, in the form
     * 'level1->level2->...->var'. If var or one of the intermediate level does not exists,
     * returns $default
     * 
     * @param  string $name
     * @param  mixed  $default
     * @return mixed
     */
    public function get($name, $default = null)
    {
        if (array_key_exists($name, $this->data)) {
            return $this->data[$name];
        }
        
        $names = explode('->', $name);
        if (count($names) > 1) {
            $firstName = array_shift($names);
            if (isset($this->$firstName)) {
            	if ($this->$firstName instanceof Config) {
                	return $this->$firstName->get(join('->', $names), $default);
            	} else {
            		$config = new self($this->$firstName);
            		return $config->get(join('->', $names), $default);
            	}
            }
        }

        return $default;
    }
}

?>