<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Mockery;

/**
 * Tests the edge cases of module completion.
 */
class DecisionLogic extends TestCase
{
    
    private $mock;

    public function setUp()
    {
        parent::setUp();
        $this->mock = Mockery::mock('App\Reminders\ModuleReminder[getCompletedModulesInOrder]',array(['ipa', 'iea', 'iaa'], '5c1a2a82b4969@test.com'))->shouldAllowMockingProtectedMethods();
    }

    // case 1- some modules in first course completed, but last module not completed.
    // IPA module 1 and IPA module 2 completed. Expecting output IPA module 3
    public function testsDecisionLogicCase1(){
        
        $this->mock->shouldReceive('getCompletedModulesInOrder')->andReturn(['IPA Module 1' => 'Completed', 'IPA Module 2' => 'Completed']);
        $this->assertEquals(['module' => 'IPA Module 3', 'status' => 'Incomplete'], $this->mock->getNextModule()); 
    }


    // case 2- some courses and last course of first module completed.
    // IPA module 1 and IPA module 7 completed. Expecting output IEA module 1
    public function testsDecisionLogicCase2(){
        
        $this->mock->shouldReceive('getCompletedModulesInOrder')->andReturn(['IPA Module 1' => 'Completed', 'IPA Module 7' => 'Completed']);
        $this->assertEquals(['module' => 'IEA Module 1', 'status' => 'Incomplete'], $this->mock->getNextModule()); 
    }


    // case 3- Last modules of all courses are completed.
    // PA module 7 , IEA module 7 and IAA module 7 are completed. Expecting output 'completed'
    public function testsDecisionLogicCase3(){

        $this->mock->shouldReceive('getCompletedModulesInOrder')->andReturn(['IPA Module 7' => 'Completed', 'IEA Module 7' => 'Completed', 'IAA Module 7' => 'Completed']);
        $this->assertEquals(['module' => 'Completed'], $this->mock->getNextModule()); 
    }

    // case 4- No modules compeletd
    //  Expecting output IPA module 1
    public function testsDecisionLogicCase4(){

        $this->mock->shouldReceive('getCompletedModulesInOrder')->andReturn([]);
        $this->assertEquals(['module' => 'IPA Module 1', 'status' => 'Incomplete'], $this->mock->getNextModule()); 
    }

    // Above 4 are the edge cases. Below are some other cases
    // // case 5- IPA module 1 and IPA module 4 completed. Expecting output IPA module 5
    public function testsDecisionLogicCase5(){

        $this->mock->shouldReceive('getCompletedModulesInOrder')->andReturn(['IPA Module 1' => 'Completed', 'IPA Module 4' => 'Completed']);
        $this->assertEquals(['module' => 'IPA Module 5', 'status' => 'Incomplete'], $this->mock->getNextModule()); 
    }


     // case 6- IPA module 3 and IEA module 1 completed. Expecting output IPA Module 4
    public function testsDecisionLogicCase6(){
      
        $this->mock->shouldReceive('getCompletedModulesInOrder')->andReturn(['IPA Module 3' => 'Completed', 'IEA Module 1' => 'Completed']);
        $this->assertEquals(['module' => 'IPA Module 4', 'status' => 'Incomplete'], $this->mock->getNextModule()); 
    }

     // case 7- IEA module 7 and IAA module 7 are completed . expecting IPA module 1.
    public function testsDecisionLogicCase7(){
      
        $this->mock->shouldReceive('getCompletedModulesInOrder')->andReturn(['IeA Module 7' => 'Completed', 'IAA Module 7' => 'Completed']);
        $this->assertEquals(['module' => 'IPA Module 1', 'status' => 'Incomplete'], $this->mock->getNextModule()); 
    }

}
