<?php
declare(strict_types=1);

namespace ricardoboss\Database\Console;

use Illuminate\Console\Command;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Composer;
use Illuminate\Support\Str;
use InvalidArgumentException;

/**
 * Class RolesMigrationMakeCommand
 * @package ricardoboss\Database\Migrations
 * @author Ricardo Boss <contact@ricardoboss.de>
 */
class RolesMigrationMakeCommand extends Command
{
	/**
	 * The console command signature.
	 *
	 * @var string
	 */
	protected $signature = 'make:rolesmigration {name : The name of the roles migration}
        {--path= : The location where the migration file should be created}
        {--realpath : Indicate any provided migration file paths are pre-resolved absolute paths}
        {--fullpath : Output the full path of the migration}';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Create a new roles migration file';

	/**
	 * The filesystem instance.
	 *
	 * @var Filesystem
	 */
	protected $files;

	/**
	 * The Composer instance.
	 *
	 * @var Composer
	 */
	protected $composer;

	/**
	 * RolesMigrationMakeCommand constructor.
	 */
	public function __construct(Filesystem $filesystem, Composer $composer)
	{
		parent::__construct();

		$this->files = $filesystem;
		$this->composer = $composer;
	}

	/**
	 * Execute the console command.
	 *
	 * @return void
	 * @throws FileNotFoundException
	 */
	public function handle()
	{
		$name = Str::snake(trim($this->input->getArgument('name')));

		// Now we are ready to write the migration out to disk. Once we've written
		// the migration out, we will dump-autoload for the entire framework to
		// make sure that the migrations are registered by the class loaders.
		$this->writeMigration($name);

		$this->composer->dumpAutoloads();
	}

	/**
	 * Write the migration file to disk.
	 *
	 * @param string $name
	 * @return void
	 * @throws FileNotFoundException
	 */
	protected function writeMigration($name)
	{
		$file = $this->create($name, $this->getMigrationPath());

		if (!$this->option('fullpath')) {
			$file = pathinfo($file, PATHINFO_FILENAME);
		}

		$this->line("<info>Created Roles Migration:</info> {$file}");
	}

	/**
	 * Create a new migration at the given path.
	 *
	 * @param string $name
	 * @param string $path
	 * @return string
	 * @throws FileNotFoundException
	 */
	public function create($name, $path)
	{
		$this->ensureMigrationDoesntAlreadyExist($name, $path);

		// First we will get the stub file for the migration, which serves as a type
		// of template for the migration. Once we have those we will populate the
		// place-holders.
		$stub = $this->getStub();

		$this->files->put(
			$path = $this->getPath($name, $path),
			$this->populateStub($name, $stub)
		);

		return $path;
	}

	/**
	 * Ensure that a migration with the given name doesn't already exist.
	 *
	 * @param string $name
	 * @param string $migrationPath
	 * @return void
	 *
	 * @throws InvalidArgumentException
	 */
	protected function ensureMigrationDoesntAlreadyExist($name, $migrationPath = null)
	{
		if (!empty($migrationPath)) {
			$migrationFiles = $this->files->glob($migrationPath . DIRECTORY_SEPARATOR . '*.php');

			foreach ($migrationFiles as $migrationFile) {
				$this->files->requireOnce($migrationFile);
			}
		}

		if (class_exists($className = $this->getClassName($name))) {
			throw new InvalidArgumentException("A {$className} class already exists.");
		}
	}

	/**
	 * Get the class name of a migration name.
	 *
	 * @param string $name
	 * @return string
	 */
	protected function getClassName($name)
	{
		return Str::studly($name);
	}

	/**
	 * Get the migration stub file.
	 *
	 * @return string
	 * @throws FileNotFoundException
	 */
	protected function getStub()
	{
		$stub = __DIR__ . DIRECTORY_SEPARATOR . 'stubs' . DIRECTORY_SEPARATOR . 'roles_migration.stub';

		return $this->files->get($stub);
	}

	/**
	 * Get the full path to the migration.
	 *
	 * @param string $name
	 * @param string $path
	 * @return string
	 */
	protected function getPath($name, $path)
	{
		return $path . DIRECTORY_SEPARATOR . $this->getDatePrefix() . '_' . $name . '.php';
	}

	/**
	 * Get the date prefix for the migration.
	 *
	 * @return string
	 */
	protected function getDatePrefix()
	{
		return date('Y_m_d_His');
	}

	/**
	 * Populate the place-holders in the migration stub.
	 *
	 * @param string $name
	 * @param string $stub
	 * @return string
	 */
	protected function populateStub($name, $stub)
	{
		return str_replace(
			'{{ class }}',
			$this->getClassName($name),
			$stub
		);
	}

	/**
	 * Get migration path (either specified by '--path' option or default location).
	 *
	 * @return string
	 */
	protected function getMigrationPath()
	{
		if (!is_null($targetPath = $this->input->getOption('path'))) {
			return !$this->usingRealPath()
				? $this->laravel->basePath() . DIRECTORY_SEPARATOR . $targetPath
				: $targetPath;
		}

		return $this->laravel->databasePath() . DIRECTORY_SEPARATOR . 'migrations';
	}

	/**
	 * Determine if the given path(s) are pre-resolved "real" paths.
	 *
	 * @return bool
	 */
	protected function usingRealPath()
	{
		return $this->input->hasOption('realpath') && $this->option('realpath');
	}
}
