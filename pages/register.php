<?php
$templater = new JFTemplater();

$templater->load( "registration_form" );

print( $templater->parse( array() ) );