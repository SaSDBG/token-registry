<?php

namespace SaS\Token;

/**
 * Generated by PHPUnit_SkeletonGenerator on 2013-04-28 at 18:31:40.
 */
class TokenRegistryTest extends \PHPUnit_Framework_TestCase {

    /**
     *
     * @var \SaS\Token\TokenRegistry
     */
    protected $reg;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp() {
        $this->reg = new TokenRegistry;
    }
    
    public function testGetToken_OneBelowMaster() {
        $this->reg->addToken('T_MASTER', sha1('foo'));
        $expectedToken = sha1(sha1('foo').'BANK');
        $this->assertEquals($expectedToken, $this->reg->getToken('TB_MASTER'));
    }
    
    public function testGetToken_ParametricToken() {
        $this->reg->addToken('TB_ACCOUNT_SPEC', sha1('foo'));
        $expectedToken = sha1(sha1('foo').'SPEC123');
        
        $this->assertEquals($expectedToken, $this->reg->getToken('TB_ACCOUNT_SPEC_123'));
    }
    
    public function testHasToken_Full() {
        $this->reg->addToken('T_MASTER', sha1('foo'));
        
        $this->assertHasTokens([
            'T_MASTER',
            'TB_MASTER',
            'TB_ACCOUNT_MANAGMENT',
            'TB_ACCOUNT',
            'TB_ACCOUNT_SPEC',
            'TB_ACCOUNT_SPEC_1234',
            'TB_ACCOUNT_SPEC_FOO',
            'TW_MASTER',
            'TW_MANAGMENT',
            'TW_ORDERS_INFO',
            'TW_ORDERS_SPEC',
            'TW_ORDERS_SPEC_123',
            'TW_ORDERS_SPEC_BAR',
            'T_AUTH',
            'T_AUTH_USER',
            'T_CHECK_ROLE',
        ]);
        
        $this->assertDoesNotHaveTokens([
            'T_FOO',
            'T_AUTH_BAR',
            'TB_ACCOUNT_SPECFOO',
            'TW_ORDERS_SPECC',
            'TW_ORDERS_SPE',
            'TW_ORDERS_SPEC_',
            'TB_ACCOUNT_SPEC_',
        ]);
    }
    
    public function testHasToken_Partial() {
        $this->reg->addToken('T_AUTH', sha1('auth'));
        $this->reg->addToken('TW_ORDERS_SPEC', sha1('waren'));
        
        $this->assertHasTokens([
            'T_AUTH',
            'T_AUTH_USER',
            'T_CHECK_ROLE',
            'TW_ORDERS_SPEC',
            'TW_ORDERS_SPEC_123',
            'TW_ORDERS_SPEC_23',
        ]);
        
        $this->assertDoesNotHaveTokens([
            'T_MASTER',
            'TB_MASTER',
            'TB_ACCOUNT_MANAGMENT',
            'TB_ACCOUNT',
            'TB_ACCOUNT_SPEC',
            'TB_ACCOUNT_SPEC_123',
            'TW_MASTER',
            'TW_MANAGMENT',
            'TW_ORDERS_INFO',
            'T_FOO'
        ]);
    }
    
    /*
     * 'TB_MASTER' => ['T_MASTER', 'BANK'],
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
     */
    public function testGetToken_Full() {
        $tokens = [
            'T_MASTER' => sha1('master'),
        ];
        $tokens['TB_MASTER'] = sha1($tokens['T_MASTER'].'BANK');
            $tokens['TB_ACCOUNT_MANAGMENT'] = sha1($tokens['TB_MASTER'].'ACCOUNTMANAGMENT');
            $tokens['TB_ACCOUNT'] = sha1($tokens['TB_MASTER'].'ACCOUNT');
                $tokens['TB_ACCOUNT_SPEC'] = sha1($tokens['TB_ACCOUNT'].'SPEC');
                    $tokens['TB_ACCOUNT_SPEC_123'] = sha1($tokens['TB_ACCOUNT_SPEC'].'SPEC123');
                    
        $tokens['TW_MASTER'] = sha1($tokens['T_MASTER'].'WAREN');
            $tokens['TW_MANAGMENT'] = sha1($tokens['TW_MASTER'].'MANAGMENT');
                $tokens['TW_ORDERS_INFO'] = sha1($tokens['TW_MANAGMENT']. 'INFO');
            $tokens['TW_ORDERS_SPEC'] = sha1($tokens['TW_MASTER']. 'ORDERS_SPEC');
                $tokens['TW_ORDERS_SPEC_234'] = sha1($tokens['TW_ORDERS_SPEC'].'SPEC234');
                
        $tokens['T_AUTH'] = sha1($tokens['T_MASTER'].'AUTH');
            $tokens['T_AUTH_USER'] = sha1($tokens['T_AUTH']. 'USER');
            $tokens['T_CHECK_ROLE'] = sha1($tokens['T_AUTH']. 'ROLE');
        
        $this->reg->addToken('T_MASTER', $tokens['T_MASTER']);
        foreach($tokens as $name => $val) {
            $this->assertSame($val, $this->reg->getToken($name));
            $this->assertTrue($this->reg->isToken($name, $val));
        }
        
    }
    
    protected function assertHasTokens(array $tokens) {
        foreach($tokens as $t) {
            $this->assertTrue($this->reg->hasToken($t));
        }
    }
    
    protected function assertDoesNotHaveTokens(array $tokens) {
        foreach($tokens as $t) {
            $this->assertFalse($this->reg->hasToken($t));
        }
    }

    

}
