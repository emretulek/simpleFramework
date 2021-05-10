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

        return base64_decode($this->session);
    }

    /**
     * @inheritDoc
     */
    public function write($id, $data): bool
    {
        $prefix_id = $this->prefix($id);
        $encodedData = base64_encode($data);

        if($this->session === $encodedData){
            return true;
        }

        return $this->set($prefix_id, $encodedData);
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
