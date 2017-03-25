<?php

namespace Shorten;

class Url
{
    /**
     * @var string
     */
    private $base;

    /**
     * @var string
     */
    private $from;

    /**
     * @var string
     */
    private $to;

    /**
     * @var string
     */
    private $hash;

    /**
     * @var string
     */
    private $hashAlgorithm;

    /**
     * Url constructor.
     * @param string $base
     * @param string $algo
     */
    public function __construct(string $base, string $from, string $algo = 'crc32')
    {
        $this->base = $base;
        $this->from = $from;
        $this->hashAlgorithm = $algo;

        $this->getToUrl();
    }

    /**
     * @return string
     */
    public function getBase(): string
    {
        return $this->base;
    }

    /**
     * @return string
     */
    public function getHashAlgorithm(): string
    {
        return $this->hashAlgorithm;
    }

    /**
     * @param string $algo
     * @return $this
     */
    public function setHashAlgorithm(string $algo)
    {
        $this->hashAlgorithm = $algo;
        return $this;
    }

    /**
     * @return string
     */
    public function getHash(): string
    {
        return $this->hash;
    }

    /**
     * @return string
     */
    public function getFromUrl(): string
    {
        return $this->from;
    }

    /**
     * @param string $url
     * @return $this
     */
    public function setFromUrl(string $url)
    {
        $this->from = $url;
        return $this;
    }

    /**
     * @return string
     */
    public function getToUrl(): string
    {
        $this->hash = hash($this->hashAlgorithm, $this->from);
        $this->to = "{$this->base}/u/{$this->hash}";
        return $this->to;
    }
}