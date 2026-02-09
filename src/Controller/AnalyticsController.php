<?php

namespace App\Controller;

use App\Service\AnalyticsService;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/collaboration/analytics')]
#[IsGranted('ROLE_PROJECTMANAGER')]
class AnalyticsController extends AbstractController
{
    public function __construct(
        private AnalyticsService $analyticsService
    ) {}

    #[Route('/', name: 'app_analytics_index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        // Get period filter
        $period = $request->query->get('period', '30'); // days
        $startDate = new \DateTime("-{$period} days");
        $endDate = new \DateTime();

        // Get all metrics
        $overallStats = $this->analyticsService->getOverallStats();
        $participationRates = $this->analyticsService->getUserParticipationRates($startDate, $endDate);
        $avgDuration = $this->analyticsService->getAverageMeetingDuration($startDate, $endDate);
        $teamProductivity = $this->analyticsService->getTeamProductivity($startDate, $endDate);
        $meetingTrends = $this->analyticsService->getMeetingTrends((int)$period);

        // Prepare chart data
        $trendsLabels = array_keys($meetingTrends);
        $trendsData = array_values($meetingTrends);

        return $this->render('analytics/index.html.twig', [
            'overall_stats' => $overallStats,
            'participation_rates' => $participationRates,
            'avg_duration' => $avgDuration,
            'team_productivity' => $teamProductivity,
            'meeting_trends' => $meetingTrends,
            'trends_labels' => $trendsLabels,
            'trends_data' => $trendsData,
            'period' => $period,
        ]);
    }

    #[Route('/export-csv', name: 'app_analytics_export_csv', methods: ['GET'])]
    public function exportCsv(Request $request): Response
    {
        $period = $request->query->get('period', '30');
        $startDate = new \DateTime("-{$period} days");
        $endDate = new \DateTime();

        $participationRates = $this->analyticsService->getUserParticipationRates($startDate, $endDate);
        $teamProductivity = $this->analyticsService->getTeamProductivity($startDate, $endDate);

        $response = new StreamedResponse(function() use ($participationRates, $teamProductivity) {
            $handle = fopen('php://output', 'w');
            
            // UTF-8 BOM for Excel compatibility
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // Participation rates sheet
            fputcsv($handle, ['TAUX DE PARTICIPATION PAR UTILISATEUR']);
            fputcsv($handle, ['Nom', 'Meetings Participés', 'Total Meetings', 'Taux (%)']);
            foreach ($participationRates as $row) {
                fputcsv($handle, [
                    $row['name'],
                    $row['meetings_attended'],
                    $row['total_meetings'],
                    $row['participation_rate']
                ]);
            }
            
            fputcsv($handle, []); // Empty line
            
            // Team productivity sheet
            fputcsv($handle, ['PRODUCTIVITÉ PAR ÉQUIPE']);
            fputcsv($handle, ['Équipe', 'Meetings Total', 'Meetings Complétés', 'Taux Complétion (%)', 'Sondages', 'Tableaux Blancs', 'Score Productivité']);
            foreach ($teamProductivity as $row) {
                fputcsv($handle, [
                    $row['channel_name'],
                    $row['total_meetings'],
                    $row['completed_meetings'],
                    $row['completion_rate'],
                    $row['polls_created'],
                    $row['whiteboards_created'],
                    $row['productivity_score']
                ]);
            }
            
            fclose($handle);
        });

        $response->headers->set('Content-Type', 'text/csv; charset=utf-8');
        $response->headers->set('Content-Disposition', 'attachment; filename="analytics-' . date('Y-m-d') . '.csv"');

        return $response;
    }

    #[Route('/export-pdf', name: 'app_analytics_export_pdf', methods: ['GET'])]
    public function exportPdf(Request $request): Response
    {
        $period = $request->query->get('period', '30');
        $startDate = new \DateTime("-{$period} days");
        $endDate = new \DateTime();

        $overallStats = $this->analyticsService->getOverallStats();
        $participationRates = $this->analyticsService->getUserParticipationRates($startDate, $endDate);
        $avgDuration = $this->analyticsService->getAverageMeetingDuration($startDate, $endDate);
        $teamProductivity = $this->analyticsService->getTeamProductivity($startDate, $endDate);

        $options = new \Dompdf\Options();
        $options->set('defaultFont', 'Arial');
        $options->set('isHtml5ParserEnabled', true);
        
        $dompdf = new \Dompdf\Dompdf($options);

        $html = $this->renderView('analytics/pdf.html.twig', [
            'overall_stats' => $overallStats,
            'participation_rates' => $participationRates,
            'avg_duration' => $avgDuration,
            'team_productivity' => $teamProductivity,
            'period' => $period,
            'generated_at' => new \DateTime(),
        ]);

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return new Response(
            $dompdf->output(),
            Response::HTTP_OK,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="analytics-report-' . date('Y-m-d') . '.pdf"',
            ]
        );
    }
}
