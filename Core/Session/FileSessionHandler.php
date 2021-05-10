<?php


namespace Core\Session;


class FileSessionHandler extends BaseSessionHandler
{
    private string $path;
    private int $gc_probability;
    private int $gc_divisor;
    private bool $blocking = true;
    private $fp = null;

    public function __construct(array $config)
    {
        $this->gc_probability = $config['options']['gc_probability'] ?: (int)ini_get('session.gc_probability');
        $this->gc_divisor = $config['options']['gc_divisor'] ?: (int)ini_get('session.gc_divisor');
        $this->path = $config['file']['path'] ?: session_save_path();
        $this->prefix = $config['prefix'] ? session_name() : '';
        $this->blocking = $config['file']['blocking'] ?? $this->blocking;
    }

    /**
     * @inheritDoc
     */
    public function open($path, $name): bool
    {
        $this->path = $this->path ?: $path;

        if (!is_writable_dir($this->path)) {
            mkdir($this->path, 0777);
        }

        return true;
    }

    /**
     * @inheritDoc
     */
    public function get(string $key): string
    {
        $filePath = $this->path . '/' . $key;
        $sessionData = '';

        if ($this->fp = fopen($filePath, "cb+")) {

            if (flock($this->fp, LOCK_EX) && $fileSize = filesize($filePath)) {
                $sessionData = fread($this->fp, $fileSize);

                if ($this->blocking == false) {
                    flock($this->fp, LOCK_UN);
                    fclose($this->fp);
                }
            }
        }

        return $sessionData;
    }

    /**
     * @inheritDoc
     */
    public function set(string $key, string $session_data): bool
    {
        if ($this->blocking == true && $this->fp) {

            ftruncate($this->fp, 0);
            rewind($this->fp);
            fwrite($this->fp, $session_data);
            fflush($this->fp);
            flock($this->fp, LOCK_UN);
            fclose($this->fp);
        } else {

            $filePath = $this->path . '/' . $key;

            if ($this->fp = fopen($filePath, "wb+")) {
                if (flock($this->fp, LOCK_EX)) {
                    fwrite($this->fp, $session_data);
                    fflush($this->fp);
                    flock($this->fp, LOCK_UN);
                    fclose($this->fp);
                }
            }
        }

        return true;
    }

    /**
     * @inheritDoc
     */
    public function delete(string $key): bool
    {
        $filePath = $this->path . '/' . $key;

        if (is_writable_file($filePath)) {
            @unlink($filePath);
        }

        return true;
    }

    /**
     * @inheritDoc
     */
    public function gc($max_lifetime): bool
    {
        $gc = $this->gc_probability / $this->gc_divisor;
        $files = glob($this->path . '/' . $this->prefix . '*');

        if (count($files) == 0) {
            return true;
        }

        $gcCount = ceil(count($files) * $gc);
        shuffle($files);

        foreach ($files as $file) {

            if (is_readable_file($file) && filemtime($file) < time() - $max_lifetime) {
                unlink($file);
            }

            if (!--$gcCount) {
                break;
            }
        }

        return true;
    }
}
