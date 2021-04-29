<?php

namespace App\Controllers;

use App\Models\Student;

class Students extends BaseController
{
    public function index()
    {
        $res = array("details" => "no found");

        echo json_encode($res,true);

    }

    public static function hashToken($var)
    {
       
       return crypt($var,"school");
    }

    public function create()
    {
       $request = \Config\Services::request();
       $validation = \Config\Services::validation();

       $data = array('name'=> $request->getVar('name'),
                      'email' => $request->getVar('email')
              );
       
              //validation html fields 
       $validation->setRules([
           'name' => 'required|string',
           'email'=> 'required|valid_email|is_unique[students.email]'
           //'email' => 'required|valid_email|is_unique[students.email]'
       ]);   
       
       $validation->withRequest($this->request)->run();
       $errors = $validation->getErrors();
       if($validation->getErrors()){
           

           $res = array("status" => 403,
                       "details" => $errors);

           return json_encode($res,true);
       }else{
           

           $id_student = Students::hashToken($data['name']);
           $secret_key = Students::hashToken($data['email']);

 
            $savedata = array('name'=> $data['name'],
                           'email' => $data['email'],
                           'id_student' =>$id_student,
                           'secret_key'=> $secret_key
                    );

              
                    
           $StudentsModel = new Student();
           

           
           if($StudentsModel->save($savedata)){
           
            
            return json_encode(array("status" => 201,"details" => "Ok, take your credencials and saved",
                                    "credencials" => array("id_student" => $id_student,"secret_key" => $secret_key)));
           }else{
               return json_encode(array("status"=>400,"details"=> "no se pudo procesar la solicitud,intentar de nuevo"));
           }  

           
           
       

           
       }

       
    }
}