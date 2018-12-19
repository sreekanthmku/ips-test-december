<?php
namespace App\Reminders;

use App\User;
use App\Tag;
use Illuminate\Support\Facades\DB;


class ModuleReminder
{
    /*
    |--------------------------------------------------------------------------
    | ModuleReminder Class
    |--------------------------------------------------------------------------
    |
    | This class collects user email and courses that the user has subscribed
    | Outputs the id of the tag that should be sent to the infusionsoft server.
    |
    */

    /**
    * courses that the user subscribed
    * @var array
    */
    private $courses;

    /**
    * user email
    * @var string
    */
    private $email;

    /**
    * courses in the order according to the user's course subscription
    * @var array
    */
    private $order;

    /**
    * The number of modules in a course. if you ever change the number of courses, just change it here. 
    * @const integer
    */
    private const NO_OF_MODULES = 7;

    /**
    * Construct a new class instance  with the given email, courses.
    *
    * @param  array  $courses
    * @param  string  $email
    */


    public function __construct($courses, $email)
    {
        $this->courses = $courses;
        $this->email= $email;
        $this->order = $this->getModulesInOrder();
    }

    /**
    * Gets the modules in the order according to the user's course subscription.
    *
    * @return array (module as key and status (completed/Incomplete) as value)
    */
    private function getModulesInOrder(){    
        $order = [];
        foreach ($this->courses as $course) {  

            for ($i = 1; $i <= self::NO_OF_MODULES; $i++){     
                $order[strtoupper($course)." Module ".$i] = 'Incomplete';
            }

        }
        return $order;
    }

    /**
    * Returns the completed modules in the order(C1M1,C1M2...C1M7,C2M1,C2M2.. where C is course and M is module)
    *
    * @return array (module as key and status (completed/Incomplete) as value)
    */
    protected function getCompletedModulesInOrder(){
        $user = User::whereEmail($this->email)->first();
        
        if(!empty($user)){
            // get completed courses from database 
            $completedModulesInOrder = $user->completed_modules->pluck('name')->toArray();
            return array_fill_keys($completedModulesInOrder, "Completed");    
        }
    }

    /**
    * Returns an array with module as key and status (completed/Incomplete) as value in the user 
    * subscribed order.
    *
    * @return array
    */
    private function markCompletedModules(){
        
        $order = $this->order;
        $completedModulesInOrder = $this->getCompletedModulesInOrder();
        
        // if module completed, change it's value to "completed"
        $updatedModules = array_merge($order,$completedModulesInOrder);
        $updatedModules = $this->convertToMultiArray($updatedModules);

        return $updatedModules;
    }

    /**
    * converts user modules array to multidimentional array. 
    *
    * @param  array  $array
    * @return array
    */
    private function convertToMultiArray($array)
    {
        $multiArray = [];
        
        foreach ($array as $key => $value) {
            $multiArray[] = ['module' => $key, 'status' => $value];
        }

        return $multiArray;
    }


    /**
    * Split modules array into small arrays that corresponding to each course. 
    *
    * @return array
    */
    private function splitCompletedModules(){

        $modules = $this->markCompletedModules();
        return array_chunk($modules,self::NO_OF_MODULES);
    }

    /**
    * Get the next module for which reminder is to be sent.returns "completed" if all modules are completed
    *
    * @return array
    */ 
    public function getNextModule(){
        
        $modulesArray = $this->SplitCompletedModules();        
        $lastIndex = self::NO_OF_MODULES - 1;

        foreach ($modulesArray as $course) {
            
            // if last module of a course array is completed, go to next course array
            if ($course[$lastIndex]['status'] == 'Completed') {
                continue;
            } else{
                // last module is not complete. the next module should be in the same array. iterate and find the module
                for ($i = $lastIndex ; $i >= 1 ; $i--){
                    
                    // if a module is completed and it's next module is Incomplete. return the module
                    if ($course[$i]['status'] == 'Incomplete' && $course[$i-1]['status'] == 'Completed') {
                        return $course[$i];
                    }
                }

                // this code runs in the case where only first module is completed / all modules are uncomplete. 
                // if first module is complete, return second module. else return first module
                if ($course[0]['status'] == 'Incomplete') {
                    return $course[0];
                }
                return $course[1];
            }
        }
        // all modules / last modules of all courses are completed
        return ['module' => 'Completed'];
    }

    /**
    * Returns the tag id to be sent to  Infusionsoft server.
    *
    * @return string
    */ 
    public function getTagId(){
        
        $nextModule = $this->getNextModule();
        $tag = DB::table('tags')
                ->where('name', 'like','%'. $nextModule['module'] . '%')
                ->first();
        
        return $tag->id;
    }

}