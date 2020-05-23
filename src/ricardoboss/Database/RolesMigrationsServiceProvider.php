<?php
declare( strict_types=1 );

namespace ricardoboss\Database;

use Illuminate\Support\ServiceProvider;
use ricardoboss\Database\Console\RolesMigrationMakeCommand;

/**
 * Class ServiceProvider
 * @package ricardoboss\Database\Migrations
 * @author Ricardo Boss <contact@ricardoboss.de>
 */
class RolesMigrationsServiceProvider extends ServiceProvider
{
	/**
	 * Bootstrap any application services.
	 *
	 * @return void
	 */
	public function boot() {
		if (!$this->app->runningInConsole())
			return;

		$this->commands([
			RolesMigrationMakeCommand::class
		]);
	}
}
