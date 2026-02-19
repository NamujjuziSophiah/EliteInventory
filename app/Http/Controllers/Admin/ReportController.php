public function series(Request $request)
{
    $dateFrom = $request->input('date_from', now()->subDays(29)->toDateString());
    $dateTo   = $request->input('date_to', now()->toDateString());

    $sales = Sale::whereBetween('created_at', [$dateFrom, $dateTo])
        ->selectRaw('DATE(created_at) as date, SUM(total) as total')
        ->groupBy('date')
        ->orderBy('date')
        ->get()
        ->pluck('total', 'date')
        ->toArray();

    $purchases = Purchase::whereBetween('created_at', [$dateFrom, $dateTo])
        ->selectRaw('DATE(created_at) as date, SUM(quantity * unit_cost) as cogs')
        ->groupBy('date')
        ->orderBy('date')
        ->get()
        ->pluck('cogs', 'date')
        ->toArray();

    $labels = [];
    $values = [];

    $period = new \DatePeriod(
        new \DateTime($dateFrom),
        new \DateInterval('P1D'),
        (new \DateTime($dateTo))->modify('+1 day')
    );

    foreach ($period as $date) {

        $day = $date->format('Y-m-d');

        $saleAmount = $sales[$day] ?? 0;
        $cogsAmount = $purchases[$day] ?? 0;

        $labels[] = $day;
        $values[] = round($saleAmount - $cogsAmount, 2);
    }

    return response()->json([
        'labels' => $labels,
        'values' => $values,
    ]);
}
