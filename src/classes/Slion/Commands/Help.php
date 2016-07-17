<?php

namespace Slion\Commands;

/**
 * Description of Help
 *
 * @author andares
 */
class Help extends \Slion\Console\Command {
    protected $domain = "slion.help";
    protected $actions  = [
        'version'  => '[<--help $help=1>] [<-h $help=1>]',
    ];

}
