<?php
namespace App\Controllers;

use App\Controllers\Controller;

class DBcontroller extends Controller
{
    public function getUsersList()
    {
        $userlist = array('users'=>$this->db2->select('SELECT id, name, phone, role, email, updated_at, created_at, image  from users;'));
        $parsedUsers = array();
        foreach ($userlist as $key => $value) {
            foreach ($value as $subkey => $subvalue) {
                $parsedUsers[$subkey] = $subvalue;
            }
        }
        return $parsedUsers;
    }    

    public function getCoursesList()
    {
        $courselist = array('courses'=>$this->db2->select('SELECT id, name, description, image, updated_at, created_at from courses;'));
        $parsedCourses = array();
        foreach ($courselist as $key => $value) {
            foreach ($value as $subkey => $subvalue) {
                $parsedCourses[$subkey] = $subvalue;
            }
        }
        return $parsedCourses;
    }
    public function getEnrollmentsList()
    {
        $enrollmentlist = array('enrollments'=>$this->db2->select('SELECT 
            enrol.enrollment_id, 
            enrol.student_id, 
            enrol.course_id, 
            enrol.admin_id, 
            stud.name as student_name, 
            stud.image as student_image,
            cour.name as course_name, 
            user.name as user_name FROM enrollments enrol
            INNER JOIN students stud on enrol.student_id = stud.id  
            INNER JOIN courses cour on enrol.course_id = cour.id
            INNER JOIN users user on enrol.admin_id = user.id'));
        $parsedEnrollments = array();
        foreach ($enrollmentlist as $key => $value) {
            foreach ($value as $subkey => $subvalue) {
                $parsedEnrollments[$subkey] = $subvalue;
            }
        }
        return $parsedEnrollments;
    }

    public function getStudentsList()
    {
        $studentlist = array('students'=>$this->db2->select('SELECT id, name, phone, email, image, updated_at, created_at from students;'));
        $parsedStudents = array();
        foreach ($studentlist as $key => $value) {
            foreach ($value as $subkey => $subvalue) {
                $parsedStudents[$subkey] = $subvalue;
            }
        }
        return $parsedStudents;
    }

    public function getOneStudent($student_id)
    {
        $stmt = $this->db2->select("SELECT id, name, phone, email, image, updated_at, created_at FROM students WHERE id = $student_id;");
        return (array) $stmt[0];
    }

    public function getOneCourse($course_id)
    {
        $stmt = $this->db2->select("SELECT id, name, description, image, updated_at, created_at FROM courses WHERE id = $course_id;");
        return (array) $stmt[0];
    }

    public function getOneAdmin($admin_id)
    {
        $stmt = $this->db2->select("SELECT id, name, email, phone, role_id, role, image, updated_at, created_at FROM users WHERE id = $admin_id;");
        return (array) $stmt[0];
    }
    public function getHisEnroll($student_id)
    {
         $enrollmentlist = array('enrollments'=>$this->db2->select("SELECT 
                     enrol.student_id, 
                     enrol.course_id, 
                     enrol.admin_id, 
                     enrol.created_at,
                     stud.name as student_name, 
                     cour.name as course_name, 
                     user.name as user_name FROM enrollments enrol
                     INNER JOIN students stud on enrol.student_id = stud.id  
                     INNER JOIN courses cour on enrol.course_id = cour.id
                     INNER JOIN users user on enrol.admin_id = user.id
                     WHERE enrol.student_id = $student_id;"));
        $parsedEnrollments = array();
        foreach ($enrollmentlist as $key => $value) {
            foreach ($value as $subkey => $subvalue) {
                $parsedEnrollments[$subkey] = $subvalue;
            }
        }
        return $parsedEnrollments;
    }

    public function getAllRegistered($course_id)
    {
        $enrollmentlist = array('enrollments'=>$this->db2->select("SELECT 
                     enrol.student_id, 
                     enrol.course_id, 
                     enrol.admin_id, 
                     enrol.created_at,
                     stud.name as student_name, 
                     cour.name as course_name, 
                     user.name as user_name FROM enrollments enrol
                     INNER JOIN students stud on enrol.student_id = stud.id  
                     INNER JOIN courses cour on enrol.course_id = cour.id
                     INNER JOIN users user on enrol.admin_id = user.id
                     WHERE enrol.course_id = $course_id;"));
        $parsedEnrollments = array();
        foreach ($enrollmentlist as $key => $value) {
            foreach ($value as $subkey => $subvalue) {
                $parsedEnrollments[$subkey] = $subvalue;
            }
        }
        return $parsedEnrollments;
    } 
    public function getLastImage($id, $table)
    {
         $stmt = $this->db2->select("SELECT image FROM $table WHERE id = $id;");
        return (array) $stmt[0];
    }
}