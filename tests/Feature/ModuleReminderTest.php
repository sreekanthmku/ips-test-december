<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use App\Http\Helpers\InfusionsoftHelper;
use App\User;



class ReminderTest extends TestCase
{
    /**
     * Tests the reminder api endpoint - api/module_reminder_assigner.
     *
     */

    /**
    * chenge this to true/false to make addTag fail/success.
    * @var bool
    */
    private $savedTag;
    private $email;


    public function setUp()
    {
        parent::setUp();
        $this->email = User::first()->pluck('email')[0];
    }

    /**
    * Mock InfusionsoftHelper class
    */
    private function bindInfusionsoftHelper()
    {
        //  Partially mock InfusionsoftHelper class - mock addTag  
        $this->app->bind(InfusionsoftHelper::class, function ($app) {
            
            $mock = $this->createMock(InfusionsoftHelper::class);

            $mock->method('getContact')->willReturn([
                            "Email" => $this->email,
                            "_Products" => "ipa,iea",
                            "Id" => rand(1000, 9999)
                        ]);
            
            if ($this->savedTag === false) {
                $mock->method('addTag')->willReturn(true);
            } elseif ($this->savedTag === true) {
                $mock->method('addTag')->willReturn(false);
            }
                        
            return $mock;      
        });
           
        \App::Make('App\Http\Helpers\InfusionsoftHelper');
    }

    /**
    * Case where addTag returns true
    */
    public function testSendModuleReminderSuccess()
    {
        $this->savedTag = false;
        $this->bindInfusionsoftHelper();
        $response = $this->json('POST', 'api/module_reminder_assigner', ['contact_email' => $this->email]);

        // verify the response and status code of the request
        $response
        ->assertStatus(200)
        ->assertJson([
            'success' => true,
            'message' => 'Tag added successfully'
        ]);
    }


    /**
    * Case where addTag returns false
    */
    public function testSendModuleReminderFail()
    {
        $this->savedTag = true;
        $this->bindInfusionsoftHelper();
        $response = $this->json('POST', 'api/module_reminder_assigner', ['contact_email' => $this->email]);

        // verify the response and status code of the request
        $response
        ->assertStatus(200)
        ->assertJson([
            'success' => false,
            'message' => 'Something went wrong or tag already added'
        ]);
    }

}
