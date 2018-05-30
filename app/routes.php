<?php
use App\Models\User;
use App\Controllers\Controller;
use Respect\Validation\Validator as v;
use App\Middleware\AuthMiddleware;
use App\Middleware\GuestMiddleware;
use App\Middleware\RoleMiddleware;
use Slim\Views\Twig as View;
use App\Models\Student;
use App\Models\Course;
use App\Models\Enrollment;

$app->group('', function () {
    $this->get('/auth/signin', function ($request, $response)
    {
        return $this->view->render($response, 'auth/signin.twig');
    })->setName('auth.signin');

    $this->post('/auth/signin', function($request, $response)
    {
        $validation = $this->validator->validate($request, 
        [
            'email' => v::noWhitespace()->notEmpty()->email(),
            'password' => v::noWhitespace()->notEmpty(),

        ]);
        if ($validation->failed()) 
        {
            $this->flash->addMessage('error', 'could not connect with those details.' );
            return $response->withRedirect($this->router->pathFor('auth.signin'));
        }
        $auth = $this->auth->attempt(
            $request->getParam('email'),
            $request->getParam('password')
        );
        if (!$auth) 
            {
                $this->flash->addMessage('error', 'could not connect with those details. one from those details is wrong.' );
                return $response->withRedirect($this->router->pathFor('auth.signin'));
            }       
        $this->flash->addMessage('info', 'You have been sign in');
        return $response->withRedirect($this->router->pathFor('home'));
    });
})->add(new GuestMiddleware($container));

$app->group('', function () {

//indexAdmin=============================================
    $this->get('/admin', function ($request, $response) 
    {
        $adminPage = 'forTwigCheck';
        return $this->view->render($response, '/admin.twig', ['adminPage' => $adminPage]);
    })->setName('admin');

// getAdmin=============================================
    $this->get('/admin/manage/showadmin/{admin_id:\d+}',  function($request, $response, $args)
    {
        $admin_id = $args['admin_id'];
        $admin = $this->DBcontroller->getOneAdmin($admin_id);
        return $this->view->render($response, '/manage/showadmin.twig', ['admin' => $admin]);
    })->setName('manage.showadmin');

//CreateAdmin=============================================
    $this->get('/manage/createadmin', function($request, $response)
    {
        return $this->view->render($response, '/manage/createadmin.twig');
    })->setName('manage.createadmin');

    $this->post('/manage/createadmin', function($request, $response)
    {
        $validation = $this->validator->validate($request, [
            'email' => v::noWhitespace()->notEmpty()->email()->EmailAvailable(),
            'name' => v::notEmpty()->alpha(),
            'phone' => v::notEmpty()->PhoneValid(),
            'password' => v::noWhitespace()->notEmpty(),
            'confirm_password' => v::noWhitespace()->notEmpty(),
            'role' => v::notEmpty(),
        ]);

        $file = $request->getUploadedFiles();
        $profileImage = $file['image'];
        $table = 'users';

        $password = $request->getParam('password'); 
        $confirm_password = $request->getParam('confirm_password');

        if ($password !== $confirm_password) {
            $this->flash->addMessage('error', 'could not signup. Un-match passwords.' );
            return $response->withRedirect($this->router->pathFor('manage.createadmin'));
        }
        
        if ($validation->failed() || $this->ImageValidator->failed($profileImage)) {
            $this->flash->addMessage('error', 'could not signup. details is wrong.' );
            return $response->withRedirect($this->router->pathFor('manage.createadmin'));
        }


        $image = $this->ImageValidator->moveUploadedFile($this->directory_IMG_admins, $profileImage, $table, $id);

        $user = User::create([
            'email' => $request->getParam('email'),
            'name' => $request->getParam('name'),
            'phone' => $request->getParam('phone'),
            'password' => password_hash($request->getParam('password'), PASSWORD_DEFAULT),
            'role_id' => $request->getParam('role'),
            'role' => ($request->getParam('role') == '1') ? 'Sales' : 'Administrator',
            'image' => $image,
        ]);


        $this->flash->addMessage('info', 'successfully added User.');

        return $response->withRedirect($this->router->pathFor('admin'));
    });

// DeleteAdmin=============================================
    $this->get('/admin/manage/deleteadmin/{admin_id:\d+}',  function($request, $response, $args)
    {
        $admin_id = $args['admin_id'];
        $admin = $this->DBcontroller->getOneAdmin($admin_id);
        return $this->view->render($response, '/manage/deleteadmin.twig', ['admin' => $admin]);
    })->setName('manage.deleteadmin');

    $this->post('/admin/manage/deleteadmin/{admin_id:\d+}', function($request, $response, $args)
    {
        $id = $args['admin_id'];
        $user = User::where('id', $id)->delete([
            'email' => $request->getParam('email'),
            'name' => $request->getParam('name'),
            'phone' => $request->getParam('phone'),
            'role_id' => $request->getParam('role'),
            'role' => ($request->getParam('role') == '1') ? 'Sales' : 'Administrator',
            // 'image' => $request->getParam('image'),
        ]);
        $this->flash->addMessage('info', 'successfully deleted User.');

        return $response->withRedirect($this->router->pathFor('admin'));
    });

// EditAdmin=============================================
    $this->get('/admin/manage/editadmin/{admin_id:\d+}', function($request, $response, $args)
    {
        $admin_id = $args['admin_id'];
        $admin = $this->DBcontroller->getOneAdmin($admin_id);
        return $this->view->render($response, '/manage/editadmin.twig', ['admin' => $admin]);
    })->setName('manage.editadmin');

    $this->post('/admin/manage/editadmin/{admin_id:\d+}', function($request, $response, $args)
    {
        $id = $args['admin_id'];
        $validation = $this->validator->validate($request, [
            'email' => v::noWhitespace()->notEmpty()->email(),
            'name' => v::notEmpty()->alpha(),
            'phone' => v::notEmpty()->PhoneValid(),
            'role' => v::notEmpty(),
        ]);

        $file = $request->getUploadedFiles();
        $profileImage = $file['image'];
        $table = 'users';

        if ($validation->failed() || $this->ImageValidator->failed($profileImage)) {
            $this->flash->addMessage('error', 'could not update user. details is wrong.' );
            return $response->withRedirect($this->router->pathFor('manage.editadmin',$args));
        }

        $image = $this->ImageValidator->moveUploadedFile($this->container->directory_IMG_admins, $profileImage, $id, $table);

        switch ($request->getParam('role')) {
            case '1':
                $role_name = 'sales';
                break;
            case '2':
                $role_name = 'Administrator';
                break;
            case '3':
                $role_name = 'owner';
                break;
        }

        $user = User::where('id', $id)->update([
            'email' => $request->getParam('email'),
            'name' => $request->getParam('name'),
            'phone' => $request->getParam('phone'),
            'role_id' => $request->getParam('role'),
            'role' => $role_name,
            'image' => $image,
        ]);

        $this->flash->addMessage('info', 'successfully updated User.');

        return $response->withRedirect($this->router->pathFor('manage.showadmin',$args));
    });

// EditStudent=============================================
    $this->get('/manage/editstudent/{student_id:\d+}', function($request, $response, $args)
    {
        $student_id = $args['student_id'];
        $student = $this->DBcontroller->getOneStudent($student_id);
        $hisEnroll = $this->DBcontroller->getHisEnroll($student_id);
        return $this->view->render($response, '/manage/editstudent.twig', ['student' => $student, 'hisEnroll' => $hisEnroll]);
    })->setName('manage.editstudent');

    $this->post('/manage/editstudent/{student_id:\d+}', function($request, $response, $args)
    {
        $id = $args['student_id'];
        $table = 'students';
        $validation = $this->validator->validate($request, [
            'name' => v::notEmpty()->alpha(),
            'phone' => v::notEmpty()->PhoneValid(),
            'email' => v::noWhitespace()->notEmpty()->email(),
        ]);

        $file = $request->getUploadedFiles();
        $profileImage = $file['image'];
     
        

        if ($validation->failed() || $this->ImageValidator->failed($profileImage)) {
            $this->flash->addMessage('error', 'could not update Student. details is wrong.' );
            return $response->withRedirect($this->router->pathFor('manage.editstudent',$args));
        }

        $body = $request->getParsedBody();
        $image = $this->ImageValidator->moveUploadedFile($this->container->directory_IMG_students, $profileImage, $id, $table);
        $student = Student::where('id', $id)->update([
            'name' => $body['name'],
            'phone' => $body['phone'],
            'email' => $body['email'],
            'image' => $image,
        ]);
 
        $courses =  $body['course'];

        Enrollment::where('student_id', $id)->delete();

        foreach ($courses as $course) {
            $Enrollment = Enrollment::create([

                'student_id' => $id,
                'course_id' => $course,
                'admin_id' => $body['admin'],
            ]);
        }

        $this->flash->addMessage('info', 'successfully updated Student.');

        return $response->withRedirect($this->router->pathFor('manage.showstudent',$args));
    });
    
// EditCourse=============================================
    $this->get('/manage/editcourse/{course_id:\d+}', function($request, $response, $args)
    {
        $course_id = $args['course_id'];
        $course = $this->DBcontroller->getOneCourse($course_id);
        return $this->view->render($response, '/manage/editcourse.twig', ['course' => $course]);
    })->setName('manage.editcourse');

    $this->post('/manage/editcourse/{course_id:\d+}', function($request, $response, $args)
    {
        $id = $args['course_id'];
        $validation = $this->validator->validate($request, [
            'name' => v::notEmpty()->alpha(),
            'description' => v::notEmpty(),
        ]);

        $file = $request->getUploadedFiles();
        $profileImage = $file['image'];
        $table = 'courses';

        if ($validation->failed() || $this->ImageValidator->failed($profileImage)) {
            $this->flash->addMessage('error', 'could not update this Course. details is wrong.' );
            return $response->withRedirect($this->router->pathFor('manage.editcourse', $args));
        }
        $image = $this->ImageValidator->moveUploadedFile($this->container->directory_IMG_courses, $profileImage, $id, $table);

        $course = Course::where('id', $id)->update([
            'name' => $request->getParam('name'),
            'description' => $request->getParam('description'),
            'image' => $image,
        ]);

        $this->flash->addMessage('info', 'successfully updated Course.');

        return $response->withRedirect($this->router->pathFor('manage.showcourse', $args));
    });

// DeleteCourse=============================================
    $this->get('/manage/deletecourse/{course_id:\d+}', function($request, $response, $args)
    {
        $course_id = $args['course_id'];
        $course = $this->DBcontroller->getOneCourse($course_id);
        return $this->view->render($response, '/manage/deletecourse.twig', ['course' => $course]);
    })->setName('manage.deletecourse');

    $this->post('/manage/deletecourse/{course_id:\d+}', function($request, $response, $args)
    {
        $id = $args['course_id'];
        $course = Course::where('id', $id)->delete([
            'name' => $request->getParam('name'),
            'description' => $request->getParam('description'),
            // 'image' => $request->getParam('image'),
        ]);
        $this->flash->addMessage('info', 'successfully delete Course.');

        return $response->withRedirect($this->router->pathFor('home'));
    });    
})->add(new RoleMiddleware($container));


$app->group('', function () {
//indexHome=============================================
    $this->get('/', function($request, $response) {
        $homePage = 'forTwigCheck';
        return $this->view->render($response, 'home.twig', ['homePage' => $homePage]);
    })->setName('home');

//CreateStudent=============================================
    $this->get('/manage/createstudent', function($request, $response)
    {
        return $this->view->render($response, '/manage/createstudent.twig');
    })->setName('manage.createstudent');

    $this->post('/manage/createstudent', function($request, $response)
    {


        $validation = $this->validator->validate($request, [
            'name' => v::notEmpty()->alpha(),
            'phone' => v::notEmpty()->PhoneValid(),
            'email' => v::noWhitespace()->notEmpty()->email()->EmailAvailable(),

        ]);

        $file = $request->getUploadedFiles();
        $profileImage = $file['image'];
        $table = 'students';

        if ($validation->failed() || $this->ImageValidator->failed($profileImage)) {
            $this->flash->addMessage('error', 'could not add this Student. details is wrong.' );
            return $response->withRedirect($this->router->pathFor('manage.createstudent'));
        }

        $image = $this->ImageValidator->moveUploadedFile($this->container->directory_IMG_students, $profileImage, $table, $id);
    
        $student = Student::create([
            'name' => $request->getParam('name'),
            'phone' => $request->getParam('phone'),
            'email' => $request->getParam('email'),
            'image' => $image,

        ]);

        $this->flash->addMessage('info', 'successfully added the Student .');

        return $response->withRedirect($this->router->pathFor('home'));
    });

// getStudent=============================================
    $this->get('/manage/showstudent/{student_id:\d+}',  function($request, $response, $args)
    {
        $student_id = $args['student_id'];
        $student = $this->DBcontroller->getOneStudent($student_id);
        $hisEnroll = $this->DBcontroller->getHisEnroll($student_id);

        return $this->view->render($response, '/manage/showstudent.twig', ['student' => $student, 'hisEnroll' => $hisEnroll]);
    })->setName('manage.showstudent');

// DeleteStudent=============================================
    $this->get('/manage/deletestudent/{student_id:\d+}', function($request, $response, $args)
    {
        $student_id = $args['student_id'];
        $student = $this->DBcontroller->getOneStudent($student_id);
        return $this->view->render($response, '/manage/deletestudent.twig', ['student' => $student]);
    })->setName('manage.deletestudent');

    $this->post('/manage/deletestudent/{student_id:\d+}', function($request, $response, $args)
    {
        $id = $args['student_id'];
        $student = Student::where('id', $id)->delete([
            'name' => $request->getParam('name'),
            'phone' => $request->getParam('phone'),
            'email' => $request->getParam('email'),
            // 'image' => $request->getParam('image'),
        ]);
        $this->flash->addMessage('info', 'successfully delete Student.');

        return $response->withRedirect($this->router->pathFor('home'));
    });    
    
//CreateCourse=============================================
    $this->get('/manage/createcourse', function($request, $response)
    {
        return $this->view->render($response, '/manage/createcourse.twig');
    })->setName('manage.createcourse');

    $this->post('/manage/createcourse', function($request, $response)
    {
        $validation = $this->validator->validate($request, [
            'name' => v::notEmpty()->alpha(),
            'description' => v::notEmpty(),
        ]);

        $file = $request->getUploadedFiles();
        $profileImage = $file['image'];
        $table = 'courses';

        if ($validation->failed() || $this->ImageValidator->failed($profileImage)) {
            $this->flash->addMessage('error', 'could not add this Course. details is wrong.' );
            return $response->withRedirect($this->router->pathFor('manage.createcourse'));
        }

        $image = $this->ImageValidator->moveUploadedFile($this->container->directory_IMG_courses, $profileImage, $table, $id);

        $course = Course::create([
            'name' => $request->getParam('name'),
            'description' => $request->getParam('description'),
            'image' => $image,

        ]);

        $this->flash->addMessage('info', 'successfully added the Course.');


        return $response->withRedirect($this->router->pathFor('home'));
    });

// getCourse=============================================
    $this->get('/manage/showcourse/{course_id:\d+}', function($request, $response, $args)
    {
        $course_id = $args['course_id'];
        $course = $this->DBcontroller->getOneCourse($course_id);
        $AllRegistered = $this->DBcontroller->getAllRegistered($course_id);
        return $this->view->render($response, '/manage/showcourse.twig', ['course' => $course, 'allRegistered' => $AllRegistered]);
    })->setName('manage.showcourse');

//ChangePassword=============================================
    $this->get('/auth/password/change', function($request, $response)
    {
        return $this->view->render($response, 'auth/password/change.twig');
    })->setName('auth.password.change');

    $this->post('/auth/password/change', function($request, $response)
    {
        $validation = $this->validator->validate($request, [
            'password_old' => v::noWhitespace()->notEmpty()->MatchesPassword($this->auth->user()->password),
            'password' => v::noWhitespace()->notEmpty(),
        ]);
        
        if ($validation->failed())
        {
            $this->flash->addMessage('error', 'could not change your password passwords is Un-match.');
            return $response->withRedirect($this->router->pathFor('auth.password.change'));
        }
        
        $this->auth->user()->setPassword($request->getParam('password'));

        $this->flash->addMessage('info', 'your password was updated.');
        return $response->withRedirect($this->router->pathFor('home'));
    });

//SignOut=============================================
    $this->get('/auth/signout', function($request, $response)
    {
        $this->auth->logout();
        return $response->withRedirect($this->router->pathFor('home'));
    })->setName('auth.signout');

})->add(new AuthMiddleware($container));
