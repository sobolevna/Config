<?php

namespace PHPixie\Config\Storages;

class File extends \PHPixie\Config\Storage\Persistable
{
    protected $dataStorage;
    protected $handler;
    protected $file;
    protected $loaded = false;
    protected $modified = false;

    public function __construct($config, $dataStorage, $handler, $file, $key = null)
    {
        $this->dataStorage = $dataStorage;
        $this->handler = $handler;
        $this->file = $file;
        parent::__construct($config, $key);
    }

    public function get($key = null)
    {
        $this->requireLoad();
        $args = func_get_args();
        if (array_key_exists(1, $args))
            return $this->dataStorage->get($key, $args[1]);
        return $this->dataStorage->get($key);
    }

    public function set($key, $value)
    {
        $this->requireLoad();
        $this->dataStorage->set($key, $value);
        $this->modified = true;
    }

    public function remove($key = null)
    {
        $this->requireLoad();
        $this->dataStorage->remove($key);
        $this->modified = true;
    }

    public function persist()
    {
        if (!$this->modified)
            return;

        $data = $this->dataStorage->get(null, null);
        if (!empty($data)) {
            $this->handler->write($this->file, $data);
        }elseif(file_exists($this->file))
            unlink($this->file);
        $this->modified = false;
    }

    protected function load()
    {
        if (file_exists($this->file)) {
            $data = $this->handler->read($this->file);
        }else
            $data = array();
        $this->dataStorage->set(null, $data);
        $this->loaded = true;
    }

    protected function requireLoad()
    {
        if (!$this->loaded)
            $this->load();
    }
}
