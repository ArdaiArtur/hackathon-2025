<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Domain\Service\ExpenseService;
use DateTimeImmutable;
use Exception;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Log\LoggerInterface;
use Slim\Views\Twig;

class ExpenseController extends BaseController
{
    private const PAGE_SIZE = 20;

    public function __construct(
        Twig $view,
        private readonly ExpenseService $expenseService,
        private LoggerInterface $logger,
    ) {
        parent::__construct($view);
    }

    public function index(Request $request, Response $response): Response
    {
        // TODO: implement this action method to display the expenses page

        // Hints:
        // - use the session to get the current user ID
        // - use the request query parameters to determine the page number and page size
        // - use the expense service to fetch expenses for the current user
        /*
        if (!isset($_SESSION['user_id'])) {
            return $response->withHeader('Location', '/login')->withStatus(302);
        }
        */
        // parse request parameters
        $userId = $_SESSION['user_id']; // TODO: obtain logged-in user ID from session 
        $page = (int)($request->getQueryParams()['page'] ?? 1);
        $pageSize = (int)($request->getQueryParams()['pageSize'] ?? self::PAGE_SIZE);
        $year = (int)($request->getQueryParams()['year'] ?? null);
        $month = (int)($request->getQueryParams()['month'] ?? null);

        $data = $this->expenseService->list($userId, $year, $month, $page, $pageSize);

        //$this->logger->info('Logging array in context', $data);

        $_SESSION['message'] = null;
        $_SESSION['message_type'] = null;
        
        return $this->render($response, 'expenses/index.twig', [
            'data' => $data,
            'year'     => $year,
            'month' => $month,
            'page'     => $page,
            'pageSize' => $pageSize,
            'total'=>$this->expenseService->count($userId),
        ]);
    }

    public function create(Request $request, Response $response): Response
    {
        $categories = explode(',', $_ENV['EXPENSE_CATEGORIES']);
        return $this->render($response, 'expenses/create.twig', ['categories' => $categories]);
    }

    public function store(Request $request, Response $response): Response
    {
        //$this->logger->info('got in');
        $data = $request->getParsedBody();
        $description = trim($data['description'] ?? '');
        $date = trim($data['date'] ?? '');
        $amount = trim($data['amount'] ?? '');
        $category = trim($data['category'] ?? '');
        $userId = $_SESSION['user_id'];

        $errors = [];

        if (empty($description)) {
            $errors['description'] = 'Description is required.';
        }

        if (empty($date) || !strtotime($date)) {
            $errors['date'] = 'Invalid date.';
        }

        if (!is_numeric($amount) || $amount <= 0) {
            $errors['amount'] = 'Must be a positive number.';
        }

        $categories = explode(',', $_ENV['EXPENSE_CATEGORIES']);
        if (empty($category) || !in_array(ucfirst($category), $categories)) {
            $errors['category'] = 'Invalid category.';
        }

        if (!empty($errors)) {
            return $this->render($response, 'expenses/create.twig', [
                'errors' => $errors,
                'data' => $data,
                'categories' => $categories,
            ]);
        }

        $this->expenseService->create($userId, (float)$amount, $description, new DateTimeImmutable($date), $category);

        $_SESSION['message'] = 'Expense created successfully.';
        $_SESSION['message_type'] = 'success';

        return $response->withHeader('Location', '/expenses')->withStatus(302);
    }


    public function edit(Request $request, Response $response, array $routeParams): Response
    {
        $expense = $this->expenseService->find((int)$routeParams['id']);
        if (!$expense) {
            return $response->withStatus(404);
        }
        if ($expense->userId != $_SESSION['user_id']) {
            $response->getBody()->write("Forbidden");
            return $response->withStatus(403);
        }
        $categories = explode(',', $_ENV['EXPENSE_CATEGORIES']);
        $this->logger->info('Logging array in context' . $expense->amountCents);

        return $this->render($response, 'expenses/edit.twig', ['data' => $expense, 'categories' => $categories]);
    }

    public function update(Request $request, Response $response, array $routeParams): Response
    {
        // TODO: implement this action method to update an existing expense

        // Hints:
        // - load the expense to be edited by its ID (use route params to get it)
        // - check that the logged-in user is the owner of the edited expense, and fail with 403 if not
        // - get the new values from the request and prepare for update
        // - update the expense entity with the new values
        // - rerender the "expenses.edit" page with included errors in case of failure
        // - redirect to the "expenses.index" page in case of success

        $expense = $this->expenseService->find((int)$routeParams['id']);
        //$this->logger->info('got in');
        $data = $request->getParsedBody();
        $description = trim($data['description'] ?? '');
        $date = trim($data['date'] ?? '');
        $amount = trim($data['amount'] ?? '');
        $category = trim($data['category'] ?? '');

        if ($expense->userId != $_SESSION['user_id']) {
            $response->getBody()->write("Forbidden");
            return $response->withStatus(403);
        }

        $errors = [];

        if (empty($description)) {
            $errors['description'] = 'Description is required.';
        }

        if (empty($date) || !strtotime($date)) {
            $errors['date'] = 'Invalid date.';
        }

        if (!is_numeric($amount) || $amount <= 0) {
            $errors['amount'] = 'Must be a positive number.';
        }

        $categories = explode(',', $_ENV['EXPENSE_CATEGORIES']);
        if (empty($category) || !in_array(ucfirst($category), $categories)) {
            $errors['category'] = 'Invalid category.';
        }

        if (!empty($errors)) {
            return $this->render($response, 'expenses/create.twig', [
                'errors' => $errors,
                'data' => $data,
                'categories' => $categories,
            ]);
        }

        $this->expenseService->update($expense, (float)$amount, $description, new DateTimeImmutable($date), $category);

        $_SESSION['message'] = 'Expense Updated successfully.';
        $_SESSION['message_type'] = 'success';

        return $response->withHeader('Location', '/expenses')->withStatus(302);
    }

    public function destroy(Request $request, Response $response, array $routeParams): Response
    {
        $expense = $this->expenseService->find((int)$routeParams['id']);
        if (!$expense) {
            return $response->withStatus(404);
        }
        if ($expense->userId != $_SESSION['user_id']) {
            $response->getBody()->write("Forbidden");
            return $response->withStatus(403);
        }

        $this->expenseService->delete($expense->id);
        $_SESSION['message'] = 'Expense deleted successfully.';
        $_SESSION['message_type'] = 'success';


        return $response->withHeader('Location', '/expenses')->withStatus(302);
    }

    public function importCsv(Request $request, Response $response, array $args)
    {   
        $file = $request->getUploadedFiles();
        if (!isset($file) || $file['csv']->getError() !== UPLOAD_ERR_OK) {
            $_SESSION['message'] = 'Error uploading file.';
            $_SESSION['message_type'] = 'error';
            return $response->withHeader('Location', '/expenses')->withStatus(302);
        }

        $userId = $_SESSION['user_id'];
        $csvFile = $file['csv'];

        try {
            $imported = $this->expenseService->importFromCsv($userId, $csvFile);
            $_SESSION['message'] = "Imported $imported expenses successfully.";
            $_SESSION['message_type'] = 'success';
        } catch (Exception $e) {
            $_SESSION['message'] = 'Import failed: ' . $e->getMessage();
            $_SESSION['message_type'] = 'error';
        }

        return $response->withHeader('Location', '/expenses')->withStatus(302);
    }
}
