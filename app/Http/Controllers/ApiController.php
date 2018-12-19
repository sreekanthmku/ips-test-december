<?php

namespace App\Http\Controllers;

use App\Http\Helpers\InfusionsoftHelper;
use Illuminate\Http\Request;
use Response;
use App\User;
use App\Module;
use App\Reminders\ModuleReminder;



class ApiController extends Controller
{    
    /**
    * Creates an example user in both local database and infusionsoft server and returns user details
    *
    * @return App\User
    */
    public function exampleCustomer(){

        $infusionsoft = new InfusionsoftHelper();

        $uniqid = uniqid();

        $infusionsoft->createContact([
            'Email' => $uniqid.'@test.com',
            "_Products" => 'ipa,iea'
        ]);

        $user = User::create([
            'name' => 'Test ' . $uniqid,
            'email' => $uniqid.'@test.com',
            'password' => bcrypt($uniqid)
        ]);

        // attach IPA M1-3 & M5
        $user->completed_modules()->attach(Module::where('course_key', 'ipa')->limit(3)->get());
        $user->completed_modules()->attach(Module::where('name', 'IPA Module 5')->first());

        return $user;
    }


    /**
    * Sends the module reminder tag to infusionsoft server. returns status
    *
    * @param  Illuminate\Http\Request  $request
    * @param App\Http\Helpers\InfusionsoftHelper $infusionsoft;
    * @return array
    */
    public function moduleReminderAssigner(Request $request,InfusionsoftHelper $infusionsoft){

        $email = $request->input('contact_email');
        
        $out = ['success' => false, 'message' => ''];
        
        // check email
        if (isset($email) && !empty($email)) {
            $user = $infusionsoft->getContact($email);
            if ($user) {
                
                if (isset($user["_Products"]) && !empty($user["_Products"])) {
                    $courses = explode(',', $user["_Products"]);
                    // create new modulereminder class and get the tag id to send to infusionsoft server
                    
                    $moduleReminder = new ModuleReminder($courses,$email);
                    $tag =  $moduleReminder->getTagId();
                    $infusionsoft->addTag($user['Id'],$tag);

                    $out = ['success' => true, 'message' => 'Tag added successfully'];
                } else{
                // no courses found for user
                    $out['message'] = 'No courses found';
                }
                // user not found
            } else{
                $out['message'] = 'User not found';
            } 
        } else{
            // email empty or not set
            $out['message'] = 'Email not set';
        }
        return Response::json($out);
    }
}
