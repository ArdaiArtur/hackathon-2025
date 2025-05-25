<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Domain\Service\ExpenseService;
use DateTimeImmutable;
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
         
        $this->logger->info('Logging array in context', $data);

        return $this->render($response, 'expenses/index.twig', [
            'data' => $data,
            'year'     => $year,
            'month' => $month,
            'page'     => $page,
            'pageSize' => $pageSize,
            'total'=>self::PAGE_SIZE,
        ]);
    }

    public function create(Request $request, Response $response): Response
    {
        $categories = explode(',', $_ENV['EXPENSE_CATEGORIES']);
        return $this->render($response, 'expenses/create.twig', ['categories' => $categories]);
    }

    public function store(Request $request, Response $response): Response
    {
        $this->logger->info('got in');
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

        $availableCategories = explode(',', $_ENV['EXPENSE_CATEGORIES']);
        if (empty($category) || !in_array(ucfirst($category), $availableCategories)) {
            $errors['category'] = 'Invalid category.';
        }

        if (!empty($errors)) {
            return $this->render($response, 'expenses/create.twig', [
                'errors' => $errors,
                'data' => $data,
                'categories' => $availableCategories,
            ]);
        }

        $this->expenseService->create($userId, (float)$amount, $description, new DateTimeImmutable($date), $category);

        return $response->withHeader('Location', '/expenses')->withStatus(302);
    }


    public function edit(Request $request, Response $response, array $routeParams): Response
    {
        // TODO: implement this action method to display the edit expense page

        // Hints:
        // - obtain the list of available categories from configuration and pass to the view
        // - load the expense to be edited by its ID (use route params to get it)
        // - check that the logged-in user is the owner of the edited expense, and fail with 403 if not

        $expense = ['id' => 1];

        return $this->render($response, 'expenses/edit.twig', ['expense' => $expense, 'categories' => []]);
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

        return $response;
    }

    public function destroy(Request $request, Response $response, array $routeParams): Response
    {
        // TODO: implement this action method to delete an existing expense

        // - load the expense to be edited by its ID (use route params to get it)
        // - check that the logged-in user is the owner of the edited expense, and fail with 403 if not
        // - call the repository method to delete the expense
        // - redirect to the "expenses.index" page

        return $response;
    }
}
