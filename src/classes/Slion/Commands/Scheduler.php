<?php

namespace Slion\Commands;

/**
 * Description of Scheduler
 *
 * @author andares
 */
class Scheduler extends \Slion\Console\Command {
    protected $domain = "slion.scheduler";
    protected $actions  = [
        'run'  => '[<--help $help=1>] [<-h $help=1>]',
    ];
}
