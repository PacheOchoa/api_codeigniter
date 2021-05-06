<?php

namespace App\Controllers;

use App\Models\Course;
use App\Models\Student;
use DateTime;
session_start();
class Courses extends BaseController

{
     
    public static function authorizationValidate()
    {

        $request = \Config\Services::request();
        $headers  = $request->getHeaders();

        $StudentModel = new Student();

        $students = $StudentModel->findAll();



        foreach ($students as $student) {

            //echo $student['id_student'] . "<br>";
           

            if (array_key_exists("Authorization", $headers) && !empty($headers['Authorization'])) {
                /*$dat = base64_encode($student['id_student'] . ":" . $student['secret_key']);
                echo $request->getHeader('Authorization') ."<br>" .$dat; */
                 
                if (
                    $request->getHeader('Authorization') == 'Authorization: Basic '
                    . base64_encode($student['id_student'] . ":" . $student['secret_key'])
                ) {
                    
                    $_SESSION['id_student'] = $student['id'];
                    
                    return true;
                }

                
            }else{
                return false;
            } 
            
            
        }
        
    }

    public function index()
    {
        $db  = \Config\Database::connect();
        

        $authorized =  Courses::authorizationValidate();
        $id_student = $_SESSION['id_student'];
        
        $stm = $db->query("CALL sp_get_courses(".$id_student.")");
        $courses = $stm->getResult();
      

       

        

        

        if ($authorized) {
            if (!empty($courses)) {

                return json_encode(array("status" => 200, "total_results" => count($courses), "message" => $courses));
            } else {
                return json_encode(array("status" => 200, "total_results" => 0, "message" => "not found courses"));
            }
        } else {
            return json_encode(array("status" => 401, "message" => "Unauthorized"));
        }
    }



    public function create()
    {

        $authorized =  Courses::authorizationValidate();

        $request = \Config\Services::request();
        $validation = \Config\Services::validation();
        $created_at = new DateTime();


        if ($authorized) {
            $data = array(
                'name' => $request->getVar('name'),
                'description' => $request->getVar('description'),

            );

            //validation html fields 
            $validation->setRules([
                'name' => 'required|string',
                'description' => 'required|string'

            ]);

            $validation->withRequest($this->request)->run();
            $errors = $validation->getErrors();
            if ($validation->getErrors()) {


                $res = array(
                    "status" => 403,
                    "details" => $errors
                );

                return json_encode($res, true);

            } else {
                $savedata = array(
                    'name' => $data['name'],
                    'description' => $data['description'],
                    'created_at' => $created_at->format("Y-m-d H:i:s"),
                    'id_student' => $_SESSION['id_student']
                );



                $CoursesModel = new Course();



                if ($CoursesModel->save($savedata)) {


                    return json_encode(array("status" => 201, "details" => "success, course created"));
                } else {
                    return json_encode(array("status" => 409, "details" => "no se pudo procesar la solicitud,intentar de nuevo"));
                }
            }
        } else {
            return json_encode(array("status" => 401, "message" => "Unauthorized"));
        }
    }

   
    public function show($id)
    {
        $CourseModel = new Course();

        $course = $CourseModel->find($id);

        $authorized =  Courses::authorizationValidate();

        

        if ($authorized) {
            if (!empty($course)) {

                return json_encode(array("status" => 200,  "data" => $course));
            } else {
                return json_encode(array("status" => 404,  "data" => "not found course"));
            }
        } else {
            return json_encode(array("status" => 401, "message" => "Unauthorized"));
        }
    }

    public function update($id)
    {

        $authorized =  Courses::authorizationValidate();

        $request = \Config\Services::request();
        $validation = \Config\Services::validation();
        $created_at = new DateTime();


        if ($authorized) {
            $data = array(
                'name' => $request->getVar('name'),
                'description' => $request->getVar('description'),

            );

            //validation html fields 
            $validation->setRules([
                'name' => 'required|string',
                'description' => 'required|string'

            ]);

            $validation->withRequest($this->request)->run();
            $errors = $validation->getErrors();
            if ($validation->getErrors()) {


                $res = array(
                    "status" => 403,
                    "details" => $errors
                );

                return json_encode($res, true);

            } else {

                $savedata = array(
                    'name' => $data['name'],
                    'description' => $data['description'],
                    'updated_at' => $created_at->format("Y-m-d H:i:s"),
                    
                );

               


                $CourseModel = new Course();
                $course = $CourseModel->find($id);
                


                if($_SESSION['id_student'] == $course['id_student']){
                    $CourseModel->update($id,$savedata);
                    if ($CourseModel) {


                        return json_encode(array("status" => 201, "details" => "success, update course"));
                    }else {
                        return json_encode(array("status" => 409, "details" => "it could not update the course"));
                    }
                }else{
                    return json_encode(array("status" => 403, "details" => "The client does not have access rights to the content"));
                }
                 
            }
        } else {
            return json_encode(array("status" => 401, "message" => "Unauthorized"));
        }
    }

    public function delete($id)
    {

        $authorized =  Courses::authorizationValidate();

        if ($authorized) {
           

                $CourseModel = new Course();
                $course = $CourseModel->find($id);
                


                if($_SESSION['id_student'] == $course['id_student']){
                    $CourseModel->delete($id);
                    if ($CourseModel) {


                        return json_encode(array("status" => 201, "details" => "success, deleted course"));
                    }else {
                        return json_encode(array("status" => 409, "details" => "it could not delete the course"));
                    }
                }else{
                    return json_encode(array("status" => 403, "details" => "The client does not have access rights to the content"));
                }
                 
            }
         else {
            return json_encode(array("status" => 401, "message" => "Unauthorized"));
        }

    }
}

