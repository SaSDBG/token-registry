<?php

namespace SaS\Token;

/**
 *
 * @author drak3
 */
interface TokenRegistryInterface {
    public function hasToken($name);
    public function getToken($name);
    public function isToken($name, $value);
}

?>
