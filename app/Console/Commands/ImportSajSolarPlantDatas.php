<?php

namespace App\Console\Commands;

use App\Date;
use App\Time;
use App\Libraries\Saj;
use App\Output;
use App\Power;
use DateInterval;
use DatePeriod;
use DateTime;
use Illuminate\Console\Command;

class ImportSajSolarPlantDatas extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'solar-plant-datas:import';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Fetches datas between the given interval from SAJ's eSolar portal (https://fop.saj-electric.com)";

	protected $choices = [];

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

		$this->choices = [
			//today
			[
				"start" => date('Y-m-d'),
				"end" => date('Y-m-d', strtotime('+1 day'))
			],

			//this week
			[
				"start" => date('Y-m-d', strtotime('last monday')),
				"end" => date('Y-m-d', strtotime('+1 day'))
			],

			//last week
			[
				"start" => date('Y-m-d', strtotime('last week monday')),
				"end" => date('Y-m-d', strtotime('last week sunday +1 day'))
			],

			//this month
			[
				"start" => date('Y-m-01'),
				"end" => date('Y-m-d', strtotime('+1 day'))
			],

			//last month
			[
				"start" => date('Y-m-d', strtotime('first day of last month')),
				"end" => date('Y-m-d', strtotime('first day of this month'))
			],

			//this year
			[
				"start" => date('Y-01-01'),
				"end" => date('Y-m-d', strtotime('+1 day'))
			],

			//last year
			[
				"start" => date('Y-01-01', strtotime('last year')),
				"end" => date('Y-01-01')
			],

			//all
			[
				"start" => date('Y-m-d', strtotime('2019-10-24')),
				"end" => date('Y-m-d', strtotime('+1 day'))
			]
		];
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
		$choice_value = $this->choice('Select one:', $this->getOptions(), 0);
		$choice_key = array_search(
			$choice_value,
			$this->getOptions()
		);

		/**
		 * jeesite.session.id OR rememberMe required
		 * first one doesn't live longer than the session itself while using rememberMe can be used even after logging out
		 */
		$cookie_parameters = [
			"jeesite.session.id" => config('app.saj.session_id'),
			"rememberMe" => config('app.saj.remember_token')
		];

		$start = new DateTime($this->choices[$choice_key]["start"]);
		$end = new DateTime($this->choices[$choice_key]["end"]);
		$interval = DateInterval::createFromDateString('1 day');
		$period = new DatePeriod($start, $interval, $end);//[closed...open[ interval

		$progress_bar = $this->output->createProgressBar($end->diff($start)->days);

		$saj = new Saj(config('app.saj.plant_uid'), config('app.saj.device_serial_number'), $cookie_parameters);
		$this->info(sprintf(
				'Fetching datas in interval [%s...%s] as %s',
				$this->choices[$choice_key]["start"], date('Y-m-d', strtotime($this->choices[$choice_key]["end"] . "-1 day")), $choice_value)
		);
		$failed_days = [];
		foreach ($period as $dt) {
			$day = $dt->format('Y-m-d');
			$response = $saj->request([
				"chartDay" => $day,
			]);
			if (
				property_exists($response, "dataCountList") && !empty($response->dataCountList)
				&&
				property_exists($response, "dataTimeList") && !empty($response->dataCountList)
				&&
				property_exists($response, "dayEnergy")
				/*&&
				property_exists($response, "peakPower")*/
			) {
				foreach ($response->dataCountList as $key => $value) {
					$date = Date::firstOrCreate([
						'value' => $day
					]);
					$time = Time::firstOrCreate([
						'value' => $response->dataTimeList[$key]
					]);

					Power::updateOrCreate(
						['date_id' => $date->id, 'time_id' => $time->id],
						['value' => $value]
					);
					Output::updateOrCreate(
						['date_id' => $date->id],
						['value' => $response->dayEnergy]
					);
				}
			} else {
				$failed_days[] = [$day];
			}

			$progress_bar->advance();
		}
		$progress_bar->finish();
		$this->line(" " . "Done.");

		if (!empty($failed_days)) {
			print PHP_EOL;
			$this->error("Failed to fetch data for these days:");
			$this->table(["day"], $failed_days);
		}

		exit;
    }

	/**
	 * Get the console command options.
	 *
	 * @return array
	 */
	protected function getOptions()
	{
		return [
			'today',
			'this week',
			'last week',
			'this month',
			'last month',
			'this year',
			'last year',
			'all'
		];
	}
}
