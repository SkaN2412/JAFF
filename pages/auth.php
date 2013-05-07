<?php
$templater = new JFTemplater();

$templater->load( "auth" );

print( $templater->parse() );