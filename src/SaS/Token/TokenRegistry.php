<?php

namespace SaS\Token;

/**
 * Description of TokenRegistry
 *
 * @author drak3
 */
class TokenRegistry {
  
    private $givenTokens = [];
    
    public function hasToken($name) {
        return $this->tryBuildToken($name) !== false;
    }
    
    public function getToken($name) {
        if(!$this->hasToken($name)) {
            throw new \Exception(sprintf('Cannot derive token %s', $name));
        }
        return $this->tryBuildToken($name);
    }
    
    public function isToken($name, $value) {
        return \SaS\Util\SecureStringComparator::equals($this->getToken($name), $value);
    }
    
    //todo: remove debug output
    protected function tryBuildToken($name) {
        //echo PHP_EOL."trying to build token $name".PHP_EOL;
        if(isset($this->getGivenTokens()[$name])) {
            //echo "token $name is given".PHP_EOL;
            return $this->getGivenTokens()[$name];
        }
        //echo "token $name must be derived".PHP_EOL;
                
        $def = $this->findTokenDefinition($name);
        
        //echo "found definition for token $name".PHP_EOL;
        //print_r($def);
        
        if($def === false || !$this->hasToken($def['parent'])) {
            //echo "did not find definition or cannot build parent token".PHP_EOL;
            return false;
        }
        
        $parentTokenValue = $this->getToken($def['parent']);
        
        if($this->isParamTokenDef($def['name'])) {
            $def['data'] = sprintf($def['data'], $this->getParameter($def['name'], $name));
            //echo "token is paratmetric, built data ${def['data']}".PHP_EOL;
        }
        
        //echo "returning token value for $name".PHP_EOL;
        return $this->hash($parentTokenValue . $def['data']);
    }
    
    protected function findTokenDefinition($tokenName) {
        foreach($this->getTokenTree() as $defName => $definition) {
            if($defName === $tokenName || 
              ($this->isParamTokenDef($defName) && $this->matches($defName, $tokenName))) {
                return ['name' => $defName, 'parent' => $definition[0], 'data' => $definition[1]];
            }
        }
        return false;
    }
    
    protected function isParamTokenDef($def) {
        return $def[strlen($def)-1] === '?';
    }
    
    protected function matches($defName, $tName) {
        $staticLen = strpos($defName, '?');
        return substr_compare($defName, $tName, 0, $staticLen) === 0 && strlen($defName) <= strlen($tName);
    }
    
    protected function getParameter($defName, $tName) {
        //example: FOO_? and FOO_123
        $staticLen = strpos($defName, '?');
        $param = substr($tName, $staticLen);
        return $param;
    }
    
    public function addToken($name, $value) {
        $this->givenTokens[$name] = $value; 
    }
    
    public function getGivenTokens() {
        return $this->givenTokens;
    }
    
    //parametric tokens can only occur as leaves
    //parametric tokens must have exactly one parameter which is at the end of the token
    //parameters are denoted with a ? at the end of the token
    protected function getTokenTree() {
        return [
            'TB_MASTER' => ['T_MASTER', 'BANK'],
		'TB_ACCOUNT_MANAGMENT' => ['TB_MASTER', 'ACCOUNTMANAGMENT'],
		'TB_ACCOUNT' => ['TB_MASTER', 'ACCOUNT'],
			'TB_ACCOUNT_SPEC' => ['TB_ACCOUNT', 'SPEC'],
				'TB_ACCOUNT_SPEC_?' => ['TB_ACCOUNT_SPEC', 'SPEC%s'],
            'TW_MASTER' => ['T_MASTER', 'WAREN'],
		'TW_MANAGMENT' => ['TW_MASTER', 'MANAGMENT'],
			'TW_ORDERS_INFO' => ['TW_MANAGMENT', 'INFO'],
		'TW_ORDERS_SPEC' => ['TW_MASTER', 'ORDERS_SPEC'],
			'TW_ORDERS_SPEC_?' => ['TW_ORDERS_SPEC', 'SPEC%s'],
            'T_AUTH' => ['T_MASTER', 'AUTH'],
		'T_AUTH_USER' => ['T_AUTH', 'USER'],
		'T_CHECK_ROLE' => ['T_AUTH', 'ROLE'],
        ];
    }
    
    protected function hash($string) {
        return sha1($string);
    }
    
}

?>
