<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Domain\Service\AlertGenerator;
use App\Domain\Service\monthlyService;
use App\Domain\Service\MonthlySummaryService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Log\LoggerInterface;
use Slim\Views\Twig;

class DashboardController extends BaseController
{
    public function __construct(
        Twig $view,
        private MonthlySummaryService $monthlyService,
        private LoggerInterface $logger,
        private AlertGenerator $alertGenerator,
    )
    {
        parent::__construct($view);
    }

    public function index(Request $request, Response $response): Response
    {
        // TODO: parse the request parameters
        // TODO: load the currently logged-in user
        // TODO: get the list of available years for the year-month selector
        // TODO: call service to generate the overspending alerts for current month
        // TODO: call service to compute total expenditure per selected year/month
        // TODO: call service to compute category totals per selected year/month
        // TODO: call service to compute category averages per selected year/month

        $userId = $_SESSION['user_id'];
        $year = (int)($request->getQueryParams()['year'] ?? (int)date('Y'));
        $month = (int)($request->getQueryParams()['month'] ?? (int)date('m'));

        //$this->logger->info('Logging array in context'.$this->monthlyService->computeTotalExpenditure($userId,$year,$month));
        $totalForMonth=$this->monthlyService->computeTotalExpenditure($userId,$year,$month);
        $totalForCategories=$this->monthlyService->computePerCategoryTotals($userId,$year,$month);
        $avgForCategories=$this->monthlyService->computePerCategoryAverages($userId,$year,$month);
       // $this->logger->info('Logging array in context'.json_encode($averagesForCategories));
        $getYears=$this->monthlyService->getYears($userId);
        $alerts= $this->alertGenerator->generate($userId, $year, $month);
        return $this->render($response, 'dashboard.twig', [

            'alerts'                => $alerts,
            'totalForMonth'         =>  $totalForMonth,
            'averagesForCategories' => $avgForCategories,
            'totalForCategories' => $totalForCategories,
            'year'     => $year,
            'month' => $month,
            'years'=>$getYears,

        ]);
    }
}
