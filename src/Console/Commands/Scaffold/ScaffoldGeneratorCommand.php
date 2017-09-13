<?php

/**
 * @Author: admin
 * @Date:   2017-09-13 17:51:43
 * @Last Modified by:   admin
 * @Last Modified time: 2017-09-13 17:57:32
 */

namespace Yunjuji\Generator\Console\Commands\Scaffold;

use Illuminate\Console\Command;

class ScaffoldGeneratorCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'yunjuji:scaffold';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a full CRUD views for given model';

    /**
     * Create a new command instance.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the command.
     *
     * @return void
     */
    public function handle()
    {
        echo "yunjuji:scaffold";
    }
}
