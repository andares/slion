<?php

namespace Slion\Commands;

/**
 * Description of ApiDoc
 *
 * @author andares
 */
class ApiDoc extends \Slion\Console\Command {
    protected $domain = "slion.apidoc";
    protected $actions  = [
        'gen'  => '[-t|--target:docs/api]',
    ];

}
