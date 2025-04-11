<?php

namespace App;

class BloomFilter
{
    private $size; // Size of the bit array
    private $hashFunctions; // Number of hash functions
    private $bitArray; // The bit array

    public function __construct($size, $hashFunctions)
    {
        $this->size = $size;
        $this->hashFunctions = $hashFunctions;
        $this->bitArray = array_fill(0, $size, 0);
    }

    // Add an element to the filter
    public function add($item)
    {
        foreach ($this->getHashes($item) as $hash) {
            $this->bitArray[$hash] = 1;
        }
    }

    // Check if an element might be in the filter
    public function contains($item)
    {
        foreach ($this->getHashes($item) as $hash) {
            if ($this->bitArray[$hash] === 0) {
                return false; // Definitely not in the set
            }
        }
        return true; // Might be in the set
    }

    // Generate hash values for the item
    private function getHashes($item)
    {
        $hashes = [];
        for ($i = 0; $i < $this->hashFunctions; $i++) {
            $hash = crc32($item . $i) % $this->size;
            $hashes[] = abs($hash); // Ensure the hash is positive
        }
        return $hashes;
    }
}