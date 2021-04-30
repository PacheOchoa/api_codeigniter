<?php

namespace App\Controllers;
use App\Models\Course;
use App\Models\Student;

class Courses extends BaseController
{
    public static function authorizationValidate(){

        $request = \Config\Services::request();
        $headers  = $request->getHeaders();

        $StudentModel = new Student();

        $students= $StudentModel->findAll();

        

        foreach($students as $student => $value){

             

            if(array_key_exists("Authorization",$headers) && !empty($headers['Authorization'])){
                 
                if($request->getHeader('Authorization') == 'Authorization: Basic '
                          .base64_encode($value['id_student']. ":" .$value['secret_key'])){

                        return true;

                }
                
                else{
                    return false;
                }
            }

        }

        
    }
    public function index()
    {
        $CourseModel = new Course();

        $courses= $CourseModel->findAll();

        $authorized =  Courses::authorizationValidate();
       
        if($authorized){
            if(!empty($courses)){

                return json_encode(array("status" => 200, "total_results" => count($courses),"message" => $courses));
            }else{
                return json_encode(array("status" => 200, "total_results" =>0,"message" =>"not fond courses"));
            }
        }else{
            return json_encode(array("status" => 401, "message" =>"Unauthorized"));
        }

        
    }
}