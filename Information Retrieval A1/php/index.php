<?php

require_once "indexer.php";

$indexer = new Indexer(null);
$indexer ->parse_arguments();
$indexer -> initialise();
$indexer -> clear_files();
$indexer -> start_processing();

fflush(STDOUT);
exit(0);