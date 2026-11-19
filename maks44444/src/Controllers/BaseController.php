<?php
declare(strict_types=1);

class BaseController
{
    protected function render(string $view, array $data = [], string $layout = 'layouts/main'): void
    {
        extract($data, EXTR_SKIP);

        $viewsPath = dirname(__DIR__, 2) . '/views/';
        $viewFile = $viewsPath . $view . '.php';
        $layoutFile = $viewsPath . $layout . '.php';

        ob_start();
        require $viewFile;
        $content = ob_get_clean();

        require $layoutFile;
    }

    protected function redirect(string $url): never
    {
        header('Location: ' . $url);
        exit;
    }

    protected function requireAuth(): int
    {
        if (!isset($_SESSION['is_logged_in']) || $_SESSION['is_logged_in'] !== true) {
            $this->redirect('/login');
        }

        return (int) ($_SESSION['user_id'] ?? 1);
    }
}