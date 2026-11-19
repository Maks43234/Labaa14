<?php
declare(strict_types=1);

class TaskController extends BaseController
{
    private const int PER_PAGE = 5;

    private function getRepository(): TaskRepository
    {
        return new TaskRepository(Database::getConnection());
    }

    private function getCurrentPage(): int
    {
        return max(1, (int) ($_GET['page'] ?? 1));
    }

    public function index(): void
    {
        $userId = $this->requireAuth();
        $page = $this->getCurrentPage();

        $formErrors = $_SESSION['form_errors'] ?? [];
        unset($_SESSION['form_errors']);

        $feedbackMessage = $_SESSION['feedback'] ?? null;
        unset($_SESSION['feedback']);

        try {
            $repo = $this->getRepository();
            $totalTasks = $repo->countByUser($userId);
            $totalPages = max(1, (int) ceil($totalTasks / self::PER_PAGE));

            if ($page > $totalPages) {
                $page = $totalPages;
            }

            $tasks = $repo->findAllByUser($userId, $page, self::PER_PAGE);
            $completedCount = $repo->countCompletedByUser($userId);
            $overdueCount = $repo->countOverdueByUser($userId);
        } catch (Throwable) {
            $tasks = [];
            $totalTasks = 0;
            $totalPages = 1;
            $completedCount = 0;
            $overdueCount = 0;
            $formErrors[] = 'Не вдалося завантажити завдання з бази даних.';
        }

        $this->render('tasks/index', [
            'title' => 'Менеджер завдань',
            'userName' => (string) ($_SESSION['user_name'] ?? 'Адміністратор'),
            'tasks' => $tasks,
            'page' => $page,
            'totalTasks' => $totalTasks,
            'totalPages' => $totalPages,
            'completedCount' => $completedCount,
            'overdueCount' => $overdueCount,
            'formErrors' => $formErrors,
            'feedbackMessage' => $feedbackMessage,
        ]);
    }

    public function createForm(): void
    {
        $this->requireAuth();
        $page = $this->getCurrentPage();

        $errors = $_SESSION['form_errors'] ?? [];
        unset($_SESSION['form_errors']);

        $oldInput = $_SESSION['old_input'] ?? [];
        unset($_SESSION['old_input']);

        $this->render('tasks/create', [
            'title' => 'Нове завдання',
            'page' => $page,
            'errors' => $errors,
            'oldInput' => $oldInput,
        ]);
    }

    public function store(): void
    {
        $userId = $this->requireAuth();
        $page = $this->getCurrentPage();

        $title = (string) ($_POST['title'] ?? '');
        $deadline = trim((string) ($_POST['deadline'] ?? ''));

        try {
            $repo = $this->getRepository();
            $repo->add($userId, $title, $deadline !== '' ? $deadline : null);

            $_SESSION['feedback'] = 'Завдання успішно додано!';
            $this->redirect('/?page=' . $page);
        } catch (ValidationException $e) {
            $_SESSION['form_errors'] = $e->getErrors();
            $_SESSION['old_input'] = [
                'title' => $title,
                'deadline' => $deadline,
            ];
            $this->redirect('/tasks/create?page=' . $page);
        } catch (TaskManagerException $e) {
            $_SESSION['form_errors'] = [$e->getMessage()];
            $_SESSION['old_input'] = [
                'title' => $title,
                'deadline' => $deadline,
            ];
            $this->redirect('/tasks/create?page=' . $page);
        } catch (Throwable) {
            $_SESSION['form_errors'] = ['Сталася помилка під час збереження завдання.'];
            $_SESSION['old_input'] = [
                'title' => $title,
                'deadline' => $deadline,
            ];
            $this->redirect('/tasks/create?page=' . $page);
        }
    }

    public function complete(): void
    {
        $userId = $this->requireAuth();
        $page = $this->getCurrentPage();
        $taskId = (int) ($_POST['id'] ?? 0);

        try {
            $repo = $this->getRepository();
            $repo->complete($userId, $taskId);
            $_SESSION['feedback'] = 'Статус завдання оновлено!';
        } catch (ValidationException $e) {
            $_SESSION['form_errors'] = $e->getErrors();
        } catch (TaskNotFoundException | TaskManagerException $e) {
            $_SESSION['form_errors'] = [$e->getMessage()];
        } catch (Throwable) {
            $_SESSION['form_errors'] = ['Сталася помилка під час оновлення завдання.'];
        }

        $this->redirect('/?page=' . $page);
    }

    public function delete(): void
    {
        $userId = $this->requireAuth();
        $page = $this->getCurrentPage();
        $taskId = (int) ($_POST['id'] ?? 0);

        try {
            $repo = $this->getRepository();
            $repo->delete($userId, $taskId);
            $_SESSION['feedback'] = 'Завдання видалено!';
        } catch (ValidationException $e) {
            $_SESSION['form_errors'] = $e->getErrors();
        } catch (TaskNotFoundException | TaskManagerException $e) {
            $_SESSION['form_errors'] = [$e->getMessage()];
        } catch (Throwable) {
            $_SESSION['form_errors'] = ['Сталася помилка під час видалення завдання.'];
        }

        $this->redirect('/?page=' . $page);
    }
}