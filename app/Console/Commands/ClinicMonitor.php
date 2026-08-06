<?php

namespace App\Console\Commands;

use App\Services\ClinicMonitoringService;
use Illuminate\Console\Command;

class ClinicMonitor extends Command
{
    protected $signature = 'clinic:monitor {--critical} {--full} {--daily-report} {--json} {--no-write}';
    protected $description = 'Run privacy-safe clinic health and workflow integrity checks';

    public function handle(ClinicMonitoringService $monitoring)
    {
        if ($this->option('daily-report')) {
            $report = $monitoring->dailyReport();
            $path = $this->option('no-write') ? null : $monitoring->storeDailyReport($report);
            $this->line(json_encode($report, JSON_PRETTY_PRINT));
            if ($path) $this->info('Daily monitoring report saved: '.$path);
            return 0;
        }
        $result = $monitoring->run(! $this->option('no-write'), (bool) $this->option('critical'));
        if ($this->option('json')) {$this->line(json_encode($result, JSON_PRETTY_PRINT)); return $result['summary']['critical'] ? 1 : 0;}
        $this->table(['Check','Status','Count','Message'], collect($result['checks'])->map(function($c){return [$c['label'],strtoupper(str_replace('_',' ',$c['status'])),$c['count'],$c['message']];}));
        $this->info('Monitoring checks completed.');
        return $result['summary']['critical'] ? 1 : 0;
    }
}
