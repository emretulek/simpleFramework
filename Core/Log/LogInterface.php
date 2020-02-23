<?php

namespace Core\Log;

Interface LogInterface
{
    /**
     * Log yazma işlemini gerçekleştiren ortak method
     *
     * @param $message
     * @param $data
     * @param $type
     * @return bool
     */
    public function writer($message, $data, $type);
}
