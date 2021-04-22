<?php


namespace Core\Session;


use SessionHandlerInterface;

abstract class BaseSessionHandler implements SessionHandlerInterface
{

    protected string $session;

    protected string $prefix = '';


    /**
     * @inheritDoc
     */
    public function open($path, $name):bool
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function close():bool
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function destroy($id):bool
    {
        $prefix_id = $this->prefix($id);
        return $this->delete($prefix_id);
    }

    /**
     * @inheritDoc
     */
    public function gc($max_lifetime):bool
    {
        return true;
    }


    /**
     * @inheritDoc
     */
    public function read($id):string
    {
        $prefix_id = $this->prefix($id);
        $this->session = $this->get($prefix_id);

        return $this->session;
    }

    /**
     * @inheritDoc
     */
    public function write($id, $data): bool
    {
        $prefix_id = $this->prefix($id);

        $lastVersionInStore = $this->get($prefix_id);

        if($this->session === $data){
            $this->session = $data;
        }

        if($this->session === $lastVersionInStore){
            return $this->set($prefix_id, $data);
        }

        return $this->set($prefix_id, $this->resolveConflict($lastVersionInStore, $data));
    }


    /**
     * @param $lastVersionInStore
     * @param $data
     * @return string encoded string session data
     */
    protected function resolveConflict($lastVersionInStore, $data):string
    {
        $decoded_Data = $this->unserialize($data);
        $decoded_lastVersionInStore = $this->unserialize($lastVersionInStore);

        $noConflict = array_merge($decoded_Data, $decoded_lastVersionInStore);

        return $this->serialize($noConflict);
    }

    /**
     * Session name prefix olarak kullanılacak
     * @param string $key
     * @return string key with prefix
     */
    protected function prefix(string $key):string
    {
        return $this->prefix ? $this->prefix.'_'.$key : $key;
    }


    /**
     * @param $data
     * @return string
     */
    protected function serialize($data): string
    {
        $buffer = $_SESSION;
        $_SESSION = $data;
        $serialized = session_encode();
        $_SESSION = $buffer;

        return  $serialized;
    }

    /**
     * @param string $data
     * @return mixed
     */
    public function unserialize(string $data)
    {
        $buffer = $_SESSION;
        session_decode($data);
        $unserialized = $_SESSION;
        $_SESSION = $buffer;

        return $unserialized;
    }

    /**
     * @param string $key session_id
     * @return string Encoded string session data
     */
    abstract function get(string $key):string;

    /**
     * @param string $key session_id
     * @param string $session_data
     * @return bool
     */
    abstract function set(string $key, string $session_data):bool;

    /**
     * @param string $key session_id
     * @return bool
     */
    abstract function delete(string $key):bool;
}
