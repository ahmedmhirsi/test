<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class HolidayService
{
    private HttpClientInterface $httpClient;
    private string $countryCode;

    public function __construct(HttpClientInterface $httpClient, string $countryCode = 'TN')
    {
        $this->httpClient = $httpClient;
        $this->countryCode = $countryCode;
    }

    /**
     * Get public holidays for a given year and country
     * Uses the free Nager.Date API: https://date.nager.at/
     * 
     * @param int $year The year to fetch holidays for
     * @param string|null $countryCode Optional country code (default: TN for Tunisia)
     * @return array List of holidays with date, name, etc.
     */
    public function getPublicHolidays(int $year, ?string $countryCode = null): array
    {
        $country = $countryCode ?? $this->countryCode;

        try {
            $response = $this->httpClient->request(
                'GET',
                "https://date.nager.at/api/v3/PublicHolidays/{$year}/{$country}"
            );

            if ($response->getStatusCode() === 200) {
                return $response->toArray();
            }
        } catch (\Exception $e) {
            // Log error and return empty array on failure
            return [];
        }

        return [];
    }

    /**
     * Get upcoming holidays (next 3 months)
     * 
     * @param string|null $countryCode Optional country code
     * @return array List of upcoming holidays
     */
    public function getUpcomingHolidays(?string $countryCode = null): array
    {
        $today = new \DateTime();
        $threeMonthsLater = (new \DateTime())->modify('+3 months');
        $year = (int) $today->format('Y');

        $holidays = $this->getPublicHolidays($year, $countryCode);

        // If we're near end of year, also get next year's holidays
        if ((int) $today->format('m') >= 10) {
            $nextYearHolidays = $this->getPublicHolidays($year + 1, $countryCode);
            $holidays = array_merge($holidays, $nextYearHolidays);
        }

        // Filter to only upcoming holidays within 3 months
        $upcoming = [];
        foreach ($holidays as $holiday) {
            $holidayDate = new \DateTime($holiday['date']);
            if ($holidayDate >= $today && $holidayDate <= $threeMonthsLater) {
                $upcoming[] = [
                    'date' => $holidayDate,
                    'name' => $holiday['localName'] ?? $holiday['name'],
                    'nameEn' => $holiday['name'],
                    'countryCode' => $holiday['countryCode'],
                ];
            }
        }

        // Sort by date
        usort($upcoming, fn($a, $b) => $a['date'] <=> $b['date']);

        return array_slice($upcoming, 0, 5); // Return max 5 upcoming holidays
    }

    /**
     * Calculate the number of working days between two dates
     * Excludes weekends (Saturday, Sunday) and public holidays
     * 
     * @param \DateTime $startDate Start date
     * @param \DateTime $endDate End date
     * @param string|null $countryCode Optional country code
     * @return array [workingDays, totalDays, holidays]
     */
    public function calculateWorkingDays(\DateTime $startDate, \DateTime $endDate, ?string $countryCode = null): array
    {
        $year = (int) $startDate->format('Y');
        $holidays = $this->getPublicHolidays($year, $countryCode);

        // Get holidays in the second year if the period spans two years
        if ((int) $endDate->format('Y') > $year) {
            $nextYearHolidays = $this->getPublicHolidays($year + 1, $countryCode);
            $holidays = array_merge($holidays, $nextYearHolidays);
        }

        // Convert holidays to date strings for easy lookup
        $holidayDates = array_map(fn($h) => $h['date'], $holidays);

        $workingDays = 0;
        $totalDays = 0;
        $holidaysInPeriod = [];

        $current = clone $startDate;
        while ($current <= $endDate) {
            $totalDays++;
            $dayOfWeek = (int) $current->format('N'); // 1=Monday, 7=Sunday
            $dateStr = $current->format('Y-m-d');

            $isWeekend = ($dayOfWeek >= 6); // Saturday or Sunday
            $isHoliday = in_array($dateStr, $holidayDates);

            if (!$isWeekend && !$isHoliday) {
                $workingDays++;
            }

            if ($isHoliday && !$isWeekend) {
                // Find the holiday name
                foreach ($holidays as $h) {
                    if ($h['date'] === $dateStr) {
                        $holidaysInPeriod[] = [
                            'date' => $current->format('Y-m-d'),
                            'name' => $h['localName'] ?? $h['name'],
                        ];
                        break;
                    }
                }
            }

            $current->modify('+1 day');
        }

        return [
            'workingDays' => $workingDays,
            'totalDays' => $totalDays,
            'weekends' => $totalDays - $workingDays - count($holidaysInPeriod),
            'holidays' => $holidaysInPeriod,
        ];
    }

    /**
     * Check if a specific date is a public holiday
     * 
     * @param \DateTime $date The date to check
     * @param string|null $countryCode Optional country code
     * @return array|null Holiday info if it's a holiday, null otherwise
     */
    public function isHoliday(\DateTime $date, ?string $countryCode = null): ?array
    {
        $year = (int) $date->format('Y');
        $holidays = $this->getPublicHolidays($year, $countryCode);
        $dateStr = $date->format('Y-m-d');

        foreach ($holidays as $holiday) {
            if ($holiday['date'] === $dateStr) {
                return [
                    'name' => $holiday['localName'] ?? $holiday['name'],
                    'nameEn' => $holiday['name'],
                ];
            }
        }

        return null;
    }
}
