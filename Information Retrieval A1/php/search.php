<?php

require_once("searcher.php");

$searcher = new searcher(null);

$searcher->parse_arguments();
$searcher->initialise();
$searcher->start_processing();

fflush(STDOUT);
exit(0);
