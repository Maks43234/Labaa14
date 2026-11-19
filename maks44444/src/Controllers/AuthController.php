<?php
declare(strict_types=1);

class AuthController extends BaseController
{
    public function loginForm(): void
    {
        if (isset($_SESSION['is_logged_in']) && $_SESSION['is_logged_in'] === true) {
            $this->redirect('/');
        }

        $error = $_SESSION['auth_error'] ?? null;
        unset($_SESSION['auth_error']);

        $this->render('auth/login', [
            'title' => 'Вхід',
            'error' => $error,
        ]);
    }

    public function login(): void
    {
        $username = trim((string) ($_POST['username'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');

        if ($username === 'admin' && $password === '12345') {
            session_regenerate_id(true);
            $_SESSION['is_logged_in'] = true;
            $_SESSION['user_id'] = 1;
            $_SESSION['user_name'] = 'Адміністратор';
            unset($_SESSION['auth_error']);

            $this->redirect('/');
        }

        $_SESSION['auth_error'] = 'Невірний логін або пароль!';
        $this->redirect('/login');
    }

    public function logout(): void
    {
        $_SESSION = [];
        session_destroy();
        $this->redirect('/login');
    }
}